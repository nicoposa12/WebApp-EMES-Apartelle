<template>
  <div class="messages-view flex-grow-1 d-flex flex-column animate-fade-up h-100 overflow-hidden">
    <div class="card border-0 h-100 flex-grow-1 shadow-lg rounded-4 overflow-hidden d-flex flex-row">
      
      <!-- Conversations List (Left Pane) -->
      <div class="conversations-list border-end d-flex flex-column bg-white h-100" style="width: 360px; min-width: 320px;">
        <div class="p-4 border-bottom bg-light-subtle sticky-top z-1">
          <div class="d-flex align-items-center justify-content-between mb-3">
             <h5 class="serif-font fw-bold mb-0 text-secondary-dark">Messages</h5>
             <span class="badge bg-gold-subtle text-gold rounded-pill px-3 py-2 fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ users.length }} ONLINE</span>
          </div>

          <!-- Type Tabs -->
          <div class="nav nav-pills nav-fill bg-light p-1 rounded-pill mb-3 shadow-inner" style="font-size: 0.82rem;">
            <button class="nav-link rounded-pill py-2 border-0 transition-all fw-bold px-2 d-flex align-items-center justify-content-center gap-2" 
                    :class="activeTab === 'support' ? 'bg-premium-dark text-white shadow-sm' : 'text-muted'" 
                    @click="switchTab('support')">
               Support
               <span v-if="tabCounts.support > 0" class="badge rounded-pill bg-danger animate-pulse-slow" style="font-size: 0.65rem; padding: 0.35em 0.65em;">
                 {{ tabCounts.support }}
               </span>
            </button>
            <button class="nav-link rounded-pill py-2 border-0 transition-all fw-bold px-2 d-flex align-items-center justify-content-center gap-2" 
                    :class="activeTab === 'chatbot' ? 'bg-premium-dark text-white shadow-sm' : 'text-muted'" 
                    @click="switchTab('chatbot')">
               Chatbot
               <span v-if="tabCounts.chatbot > 0" class="badge rounded-pill bg-gold animate-pulse-slow" style="font-size: 0.65rem; padding: 0.35em 0.65em;">
                 {{ tabCounts.chatbot }}
               </span>
            </button>
            <button class="nav-link rounded-pill py-2 border-0 transition-all fw-bold px-2" 
                    :class="activeTab === 'suspended' ? 'bg-danger text-white shadow-sm' : 'text-muted'" 
                    @click="switchTab('suspended')">
               Suspended
            </button>
          </div>

          <div class="search-wrapper position-relative">
            <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">
              <i class="bi bi-search"></i>
            </span>
            <input type="text" 
                   class="form-control border-0 py-2.5 ps-5 rounded-pill shadow-none bg-light" 
                   placeholder="Search people..." 
                   style="font-size: 0.9rem;"
                   v-model="searchQuery">
          </div>
        </div>
        
        <div class="list-group list-group-flush overflow-y-auto flex-grow-1 custom-scrollbar">
          <button 
            v-for="user in filteredUsers" 
            :key="user.id"
            @click="selectUser(user)"
            class="list-group-item list-group-item-action border-0 p-3 d-flex align-items-center gap-3 transition-all user-item border-start border-4"
            :class="selectedUser?.id === user.id ? (activeTab === 'support' ? 'active-chat-premium' : 'active-chat-gold') : 'border-transparent'"
          >
            <div class="position-relative">
              <div class="avatar shadow-sm rounded-circle d-flex align-items-center justify-content-center fw-bold text-white overflow-hidden" 
                   :class="[
                     selectedUser?.id === user.id ? 
                       (activeTab === 'support' ? 'bg-premium-dark' : (activeTab === 'chatbot' ? 'bg-gold' : 'bg-danger')) : 
                       'bg-secondary-subtle text-secondary'
                   ]"
                   style="width: 52px; height: 52px; font-size: 1.1rem;">
                <img v-if="user.profile_photo_url" :src="user.profile_photo_url" :alt="user.name" class="w-100 h-100 object-fit-cover">
                <span v-else>{{ user.name.charAt(0).toUpperCase() }}</span>
              </div>
              <span v-if="user.unread_count > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white shadow-sm" style="font-size: 0.65rem;">
                {{ user.unread_count }}
              </span>
            </div>
            
            <div class="flex-grow-1 overflow-hidden">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="mb-0 fw-bold text-truncate" :class="selectedUser?.id === user.id ? 'text-dark' : 'text-secondary-dark'" style="font-size: 0.95rem;">{{ user.name }}</h6>
                <small v-if="activeTab !== 'suspended' && user.last_message" class="text-muted x-small fw-medium">{{ formatTime(user.last_message.created_at) }}</small>
                <span v-else-if="activeTab === 'suspended'" class="badge bg-danger-subtle text-danger x-small rounded-pill fw-bold">SUSPENDED</span>
              </div>
              <p class="mb-0 text-muted small text-truncate d-flex align-items-center" v-if="user.last_message" style="font-size: 0.8rem;">
                <span v-if="user.last_message.sender_id === adminId" class="text-gold me-1 fw-bold">You:</span>
                <span :class="{ 'fw-bold text-dark': user.unread_count > 0 && activeTab !== 'suspended' }">{{ user.last_message.message }}</span>
              </p>
              <p v-else class="mb-0 text-muted small fst-italic">{{ activeTab === 'suspended' ? 'Account Restricted' : 'No messages yet' }}</p>
            </div>
          </button>
          
          <div v-if="filteredUsers.length === 0" class="text-center py-5 text-muted opacity-50">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <small>No conversations found.</small>
          </div>
        </div>
      </div>

      <!-- Chat Window (Right Pane) -->
      <div class="chat-window flex-grow-1 d-flex flex-column bg-light-gray overflow-hidden position-relative">
        <!-- Empty State -->
        <div v-if="!selectedUser" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-5 text-center bg-dots">
           <div class="bg-white p-5 rounded-circle shadow-gold-sm mb-4 animate-float border-2 border border-gold-subtle">
             <i class="bi bi-chat-heart fs-1 text-gold"></i>
           </div>
           <h4 class="fw-bold mb-2 serif-font text-secondary-dark">Start Chatting</h4>
           <p class="text-muted opacity-75 max-w-sm ml-auto mr-auto">Pick a person from the list on the left to see your messages or send a new one.</p>
        </div>

        <template v-else>
          <!-- Chat Header -->
          <div class="chat-header bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between shadow-sm z-3">
             <div class="d-flex align-items-center gap-3">
                <div class="avatar rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm overflow-hidden" 
                     :class="activeTab === 'support' ? 'bg-premium-dark' : (activeTab === 'chatbot' ? 'bg-gold' : 'bg-danger')"
                     style="width: 48px; height: 48px; font-size: 1.2rem;">
                  <img v-if="selectedUser.profile_photo_url" :src="selectedUser.profile_photo_url" :alt="selectedUser.name" class="w-100 h-100 object-fit-cover text-white">
                  <span v-else class="text-white">{{ selectedUser.name.charAt(0).toUpperCase() }}</span>
                </div>
                <div>
                   <h6 class="mb-0 fw-bold text-dark" style="font-size: 1.1rem;">{{ selectedUser.name }}</h6>
                   <div class="d-flex align-items-center gap-2">
                     <span class="status-indicator bg-success rounded-circle pulse-green" v-if="activeTab !== 'suspended'"></span>
                     <span class="small text-muted fw-medium" v-if="activeTab !== 'suspended'">{{ selectedUser.email }}</span>
                     <span v-else class="small text-danger fw-medium">Suspended</span>
                   </div>
                </div>
             </div>
             <div class="d-flex gap-2 align-items-center">
                <button class="btn btn-light rounded-pill px-3 small fw-bold d-none d-md-block" @click="fetchMessages">
                  <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                </button>

                <!-- Chat Actions Dropdown -->
                <div class="dropdown">
                  <button class="btn btn-icon-action bg-light text-secondary shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical fs-5"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-xl border-0 rounded-4 p-2 animate-fade-up" style="min-width: 200px;">
                    <li><h6 class="dropdown-header x-small text-uppercase tracking-widest text-muted fw-bold p-3">Chat Options</h6></li>
                    <li>
                      <button class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-3" @click="viewProfile">
                        <div class="bg-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                          <i class="bi bi-person text-secondary"></i>
                        </div>
                        <span class="fw-bold small">View Profile</span>
                      </button>
                    </li>
                    <li v-if="!selectedUser.is_suspended">
                      <button class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-3" @click="suspendGuest">
                        <div class="bg-warning-subtle rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                          <i class="bi bi-person-x text-warning"></i>
                        </div>
                        <span class="fw-bold small">Suspend Guest</span>
                      </button>
                    </li>
                    <li v-else>
                      <button class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-3 text-success" @click="confirmUnsuspend">
                        <div class="bg-success-subtle rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                          <i class="bi bi-person-check text-success"></i>
                        </div>
                        <span class="fw-bold small">Unsuspend Account</span>
                      </button>
                    </li>
                    <li><hr class="dropdown-divider bg-light mx-2"></li>
                    <li>
                      <button class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-3 text-danger" @click="confirmDeleteChat">
                        <div class="bg-danger-subtle rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                          <i class="bi bi-trash3 text-danger"></i>
                        </div>
                        <span class="fw-bold small">Delete Chat</span>
                      </button>
                    </li>
                  </ul>
                </div>
             </div>
          </div>

          <!-- Suspension Warning Banner -->
          <div v-if="selectedUser.is_suspended" class="suspension-warning-banner bg-danger-subtle border-bottom border-danger p-3 d-flex align-items-center justify-content-between animate-fade-in shadow-sm">
            <div class="d-flex align-items-center gap-3">
              <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <i class="bi bi-shield-exclamation fs-5"></i>
              </div>
              <div class="text-start">
                <p class="mb-0 fw-bold text-danger small">ACCOUNT SUSPENDED</p>
                <p class="mb-0 x-small text-muted">{{ selectedUser.suspension_reason || 'Violation of house rules.' }}</p>
              </div>
            </div>
            <button @click="confirmUnsuspend" class="btn btn-gold btn-sm rounded-pill px-4 fw-bold shadow-sm">
              <i class="bi bi-person-check me-1"></i> Unsuspend Guest
            </button>
          </div>

          <!-- Messages Area -->
          <div class="chat-messages flex-grow-1 p-4 overflow-y-auto custom-scrollbar d-flex flex-column gap-3 bg-dots" ref="messagesContainer">
             <div v-if="loadingMessages" class="text-center py-5 mt-5">
               <div class="spinner-border text-gold" style="width: 3rem; height: 3rem;" role="status"></div>
               <p class="mt-3 text-muted fw-bold">Loading messages...</p>
             </div>
             
             <template v-else>
                <div class="date-divider text-center my-4" v-if="messages.length > 0">
                  <span class="bg-white px-4 py-1.5 rounded-pill small text-muted border shadow-sm fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">MESSAGE HISTORY</span>
                </div>

                <div v-for="(msg, index) in messages" :key="msg.id" 
                     class="d-flex w-100" 
                     :class="msg.sender_id === adminId ? 'justify-content-end' : 'justify-content-start'">
                  
                  <div class="message-wrapper d-flex flex-column" :class="msg.sender_id === adminId ? 'align-items-end' : 'align-items-start'" style="max-width: 80%;">
                    <div class="message-bubble p-3 shadow-sm position-relative text-break" 
                         :class="msg.sender_id === adminId ? 
                           (activeTab === 'chatbot' ? 'bg-gold-gradient text-white rounded-user' : 'bg-premium-dark-gradient text-white rounded-user') : 
                           'bg-white text-dark rounded-guest border border-light-dark'">
                       
                       <!-- Message Text with Link Detection -->
                       <p v-if="msg.message" class="mb-0" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.5;">
                         <template v-for="(part, i) in formatMessageWithLinks(msg.message)" :key="i">
                           <a v-if="part.type === 'link'" :href="part.content" target="_blank" class="chat-link fw-bold" :class="msg.sender_id === adminId ? 'text-white text-decoration-underline' : 'text-gold'">
                             {{ part.content }}
                           </a>
                           <span v-else>{{ part.content }}</span>
                         </template>
                       </p>

                       <div v-if="msg.image" class="message-image mt-2 rounded-3 overflow-hidden border border-white border-2 shadow-sm">
                         <img :src="msg.image" class="w-100 object-fit-cover cursor-pointer" @click="openImageModal(msg.image)" style="max-height: 300px;">
                       </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-1 px-1">
                      <small class="text-muted" style="font-size: 0.7rem;">
                         {{ formatTime(msg.created_at) }}
                      </small>
                      <i v-if="msg.sender_id === adminId" class="bi fs-6" 
                         :class="msg.is_read ? (activeTab === 'chatbot' ? 'bi-check-all text-gold' : 'bi-check-all text-primary-light') : 'bi-check text-muted'"></i>
                    </div>
                  </div>

                </div>
                
                <div v-if="messages.length === 0" class="text-center mt-5">
                  <div class="bg-white d-inline-block px-5 py-4 rounded-4 shadow-sm border border-light">
                    <i class="bi bi-chat-heart fs-2 text-gold d-block mb-2"></i>
                    <p class="text-dark mb-0 fw-bold">No message history yet</p>
                    <p class="text-muted small mb-0">Be the first to say hello!</p>
                  </div>
                </div>
             </template>
          </div>

          <!-- Attachment Preview -->
          <div v-if="selectedImageFile" class="attachment-preview p-4 bg-white border-top animate-fade-in d-flex align-items-center gap-4">
             <div class="position-relative rounded-4 overflow-hidden border shadow-sm" style="width: 100px; height: 100px;">
               <img :src="imageFilePreview" class="w-100 h-100 object-fit-cover">
               <button @click="clearSelectedImage" class="btn btn-dark btn-sm rounded-circle position-absolute top-0 end-0 m-2 p-0 d-flex align-items-center justify-content-center shadow" style="width: 24px; height: 24px;">
                 <i class="bi bi-x fs-6"></i>
               </button>
             </div>
             <div class="flex-grow-1">
               <p class="mb-1 fw-bold text-dark">{{ selectedImageFile.name }}</p>
               <div class="d-flex align-items-center gap-2">
                 <span class="badge bg-gold-subtle text-gold">{{ (selectedImageFile.size / 1024 / 1024).toFixed(2) }} MB</span>
                 <span class="text-muted small">Ready to send</span>
               </div>
             </div>
          </div>

          <!-- Input Area -->
          <div class="chat-input p-4 bg-dots border-top">
             <form @submit.prevent="sendMessage" class="d-flex gap-3 align-items-center bg-light-soft p-3 rounded-4 border focus-within-premium transition-all">
               <!-- Image Attachment -->
               <button type="button" @click="$refs.fileInput.click()" class="btn btn-icon text-muted hover-gold" title="Attach Image">
                 <i class="bi bi-image fs-5"></i>
               </button>
               <input type="file" ref="fileInput" class="d-none" @change="handleFileChange" accept="image/*">
               
               <!-- Link Room Button -->
               <div class="dropdown">
                 <button type="button" class="btn btn-icon text-muted hover-gold" data-bs-toggle="dropdown" title="Link a Room">
                    <i class="bi bi-house-add fs-5"></i>
                 </button>
                 <ul class="dropdown-menu shadow-xl border-0 rounded-4 p-2 custom-scrollbar overflow-y-auto" style="max-height: 350px; width: 320px;">
                   <li><h6 class="dropdown-header small text-gold text-uppercase tracking-widest fw-bold p-3">Quick Room Link</h6></li>
                   <li v-for="room in rooms" :key="room.id">
                     <button type="button" class="dropdown-item rounded-3 p-2 d-flex align-items-center gap-3" @click="insertRoomLink(room)">
                        <img :src="getRoomImage(room)" class="rounded-3 shadow-sm border" style="width: 50px; height: 40px; object-fit: cover;">
                        <div class="overflow-hidden">
                          <span class="d-block small fw-bold text-truncate text-secondary-dark">{{ room.room_type }} #{{ room.room_number }}</span>
                          <span class="d-block x-small text-gold fw-bold">₱{{ room.price_per_night }} <span class="text-muted fw-normal">/ night</span></span>
                        </div>
                     </button>
                   </li>
                   <li v-if="rooms.length === 0" class="p-4 text-center text-muted">
                     <i class="bi bi-door-closed fs-3 d-block mb-2 opacity-50"></i>
                     <span class="small">No rooms available.</span>
                   </li>
                 </ul>
               </div>

               <input 
                 type="text" 
                 class="form-control border-0 bg-transparent shadow-none px-2" 
                 placeholder="Type a message..." 
                 style="font-size: 1rem;"
                 v-model="newMessage"
                 :disabled="sending"
               >
               
               <button type="submit" 
                       class="btn btn-icon-lg rounded-circle d-flex align-items-center justify-content-center shadow-gold transition-all" 
                       :class="activeTab === 'chatbot' ? 'bg-gold-gradient' : 'bg-premium-dark-gradient'"
                       style="width: 48px; height: 48px;" 
                       :disabled="(!newMessage.trim() && !selectedImageFile) || sending">
                 <i class="bi bi-send-fill text-white fs-5" v-if="!sending"></i>
                 <span class="spinner-border spinner-border-sm text-white" v-else></span>
               </button>
             </form>
          </div>
        </template>
      </div>
    </div>

    <!-- Full Image Preview Modal -->
    <teleport to="body">
      <div v-if="isPreviewOpen" class="image-preview-overlay d-flex align-items-center justify-content-center p-4 animate-fade-in" @click="isPreviewOpen = false" style="z-index: 9999;">
        <img :src="previewImageUrl" class="mh-100 mw-100 rounded-4 shadow-lg animate-scale-up">
        <button class="btn btn-light rounded-circle position-absolute top-0 end-0 m-4 shadow-lg">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
    </teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import axios from 'axios';
import { useAuth } from '../../store/auth';
import Swal from 'sweetalert2';
import { useRouter } from 'vue-router';

const router = useRouter();

const { state } = useAuth();
const adminId = computed(() => state.user?.id);

const users = ref([]);
const rooms = ref([]);
const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value;
  const q = searchQuery.value.toLowerCase();
  return users.value.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q));
});

const searchQuery = ref('');
const selectedUser = ref(null);
const activeTab = ref('support'); // 'support' or 'chatbot'
const messages = ref([]);
const newMessage = ref('');
const loadingMessages = ref(false);
const sending = ref(false);
const messagesContainer = ref(null);

const tabCounts = ref({ support: 0, chatbot: 0 });

const fetchTabCounts = async () => {
  try {
    const response = await axios.get('/api/messages/unread');
    tabCounts.value = {
      support: response.data.support,
      chatbot: response.data.chatbot
    };
  } catch (err) {
    console.error('Failed to fetch tab counts', err);
  }
};

const fileInput = ref(null);
const selectedImageFile = ref(null);
const imageFilePreview = ref(null);

const isPreviewOpen = ref(false);
const previewImageUrl = ref('');

let pollingInterval = null;

const fetchConversations = async () => {
  try {
    const response = await axios.get(`/api/messages?type=${activeTab.value}`);
    users.value = response.data;
  } catch (err) {
    console.error('Failed to fetch conversations', err);
  }
};

const markAsRead = async () => {
    if (!selectedUser.value) return;
    try {
        await axios.put(`/api/messages/${selectedUser.value.id}/read?type=${activeTab.value}`);
        const userIndex = users.value.findIndex(u => u.id === selectedUser.value.id);
        if (userIndex !== -1) {
            users.value[userIndex].unread_count = 0;
        }
        fetchTabCounts(); // Refresh global counts
    } catch (err) {
        console.error('Failed to mark as read', err);
    }
};

const viewProfile = () => {
  if (!selectedUser.value) return;
  // Redirect to individual guest view or similar
  Swal.fire({
    title: selectedUser.value.name,
    text: `Email: ${selectedUser.value.email}`,
    imageUrl: selectedUser.value.profile_photo_url || null,
    imageWidth: 100,
    imageHeight: 100,
    imageAlt: 'Profile',
    confirmButtonColor: '#0f172a',
    confirmButtonText: 'Go to Guests',
    showCancelButton: true
  }).then((result) => {
    if (result.isConfirmed) {
      router.push({ path: '/admin/guests', query: { search: selectedUser.value.email } });
    }
  });
};

const suspendGuest = () => {
  if (!selectedUser.value) return;

  Swal.fire({
    title: '✅ Suspend the Guest',
    html: `
      <div class="text-start small">
        <p class="mb-2 fw-bold text-premium-dark">Use suspension when the guest:</p>
        <ul class="text-muted ps-3 mb-0">
          <li class="mb-1">Violates house rules (noise, smoking, damage, abuse).</li>
          <li class="mb-1">Cancels repeatedly to abuse the system.</li>
          <li class="mb-1">Uses fake info.</li>
          <li class="mb-1">Doesn’t pay or attempts fraud.</li>
          <li>Harasses staff or other guests.</li>
        </ul>
      </div>
    `,
    icon: 'warning',
    input: 'select',
    inputOptions: {
      'Violation of House Rules': 'Violation of House Rules',
      'System Abuse (Cancellations)': 'System Abuse (Cancellations)',
      'Fake Information': 'Fake Information',
      'Fraud/Non-payment': 'Fraud/Non-payment',
      'Harassment': 'Harassment',
      'Other': 'Other'
    },
    inputPlaceholder: 'Select a reason for suspension',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#718096',
    confirmButtonText: 'Suspend Now',
    inputValidator: (value) => {
      if (!value) {
        return 'You need to select a reason!'
      }
    }
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.post(`/api/admin/guests/${selectedUser.value.id}/suspend`, {
          reason: result.value
        });
        
        Swal.fire({
          icon: 'success',
          title: 'Guest Suspended',
          text: `Account for ${selectedUser.value.name} has been suspended.`,
          timer: 2000,
          showConfirmButton: false
        });
        
        fetchConversations();
      } catch (err) {
        console.error('Failed to suspend guest', err);
        Swal.fire('Error', 'Failed to suspend guest. Please try again.', 'error');
      }
    }
  });
};

const confirmDeleteChat = async () => {
  const result = await Swal.fire({
    title: 'Delete Chat?',
    text: "This will remove the conversation for you. This action cannot be undone.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#718096',
    confirmButtonText: 'Yes, delete it'
  });

  if (result.isConfirmed) {
    try {
      await axios.delete(`/api/messages/${selectedUser.value.id}?type=${activeTab.value}`);
      selectedUser.value = null;
      messages.value = [];
      fetchConversations();
      Swal.fire({
        icon: 'success',
        title: 'Deleted',
        text: 'Conversation deleted successfully.',
        timer: 1500,
        showConfirmButton: false
      });
    } catch (err) {
      console.error('Failed to delete chat', err);
      Swal.fire('Error', 'Failed to delete the conversation.', 'error');
    }
  }
};

const confirmUnsuspend = async () => {
  if (!selectedUser.value) return;

  const result = await Swal.fire({
    title: 'Unsuspend Account?',
    text: `Restore account access for ${selectedUser.value.name}? Once resolved, the guest will be able to book and message normally.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#BC9151',
    cancelButtonColor: '#718096',
    confirmButtonText: 'Yes, Unsuspend Now'
  });

  if (result.isConfirmed) {
    try {
      await axios.post(`/api/admin/guests/${selectedUser.value.id}/unsuspend`);
      
      Swal.fire({
        icon: 'success',
        title: 'Account Restored',
        text: 'The guest account has been unsuspended.',
        timer: 1500,
        showConfirmButton: false
      });
      
      // Update local state
      selectedUser.value.is_suspended = false;
      selectedUser.value.suspension_reason = null;
      
      // Also update in the users list
      const uIdx = users.value.findIndex(u => u.id === selectedUser.value.id);
      if (uIdx !== -1) {
        users.value[uIdx].is_suspended = false;
      }
      
      fetchConversations();
    } catch (err) {
      console.error('Failed to unsuspend guest', err);
      Swal.fire('Error', 'Failed to unsuspend guest account.', 'error');
    }
  }
};

const switchTab = (tab) => {
  activeTab.value = tab;
  selectedUser.value = null;
  messages.value = [];
  fetchConversations();
};

const fetchRooms = async () => {
  try {
    const response = await axios.get('/api/rooms');
    rooms.value = response.data;
  } catch (err) {
    console.error('Failed to fetch rooms', err);
  }
};

const selectUser = async (user) => {
  selectedUser.value = user;
  messages.value = [];
  loadingMessages.value = true;
  await fetchMessages();
  loadingMessages.value = false;
  scrollToBottom();
  
  if (user.unread_count > 0) {
      markAsRead();
  }
};

const formatMessageWithLinks = (message) => {
  if (!message) return [];
  const urlRegex = /(https?:\/\/[^\s]+)/g;
  const parts = [];
  let lastIndex = 0;
  let match;

  while ((match = urlRegex.exec(message)) !== null) {
    if (match.index > lastIndex) {
      parts.push({ type: 'text', content: message.substring(lastIndex, match.index) });
    }
    parts.push({ type: 'link', content: match[0] });
    lastIndex = urlRegex.lastIndex;
  }

  if (lastIndex < message.length) {
    parts.push({ type: 'text', content: message.substring(lastIndex) });
  }

  return parts.length > 0 ? parts : [{ type: 'text', content: message }];
};

const fetchMessages = async () => {
  if (!selectedUser.value) return;
  try {
    const response = await axios.get(`/api/messages/${selectedUser.value.id}?type=${activeTab.value}`);
    const newCount = response.data.length;
    const oldCount = messages.value.length;
    messages.value = response.data;
    
    if (newCount > oldCount) {
       scrollToBottom();
       if (document.visibilityState === 'visible') {
           markAsRead();
       }
    }
  } catch (err) {
    console.error('Failed to fetch messages', err);
  }
};

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file && file.type.startsWith('image/')) {
    selectedImageFile.value = file;
    imageFilePreview.value = URL.createObjectURL(file);
  }
};

const clearSelectedImage = () => {
  selectedImageFile.value = null;
  imageFilePreview.value = null;
  if (fileInput.value) fileInput.value.value = '';
};

const insertRoomLink = (room) => {
  const link = `${window.location.origin}/rooms/${room.id}`;
  if (newMessage.value) {
    newMessage.value += ' ' + link;
  } else {
    newMessage.value = link;
  }
};

const sendMessage = async () => {
  if ((!newMessage.value.trim() && !selectedImageFile.value) || !selectedUser.value) return;
  
  const text = newMessage.value;
  const image = selectedImageFile.value;
  const currentTab = activeTab.value;
  
  newMessage.value = ''; // Optimistic clear
  clearSelectedImage();
  sending.value = true;

  try {
    const formData = new FormData();
    formData.append('message', text);
    formData.append('receiver_id', selectedUser.value.id);
    formData.append('is_chatbot', currentTab === 'chatbot' ? '1' : '0');
    if (image) {
      formData.append('image', image);
    }

    const response = await axios.post('/api/messages', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    messages.value.push(response.data);
    scrollToBottom();
    fetchConversations();
  } catch (err) {
    console.error('Send failed', err);
    newMessage.value = text; 
    selectedImageFile.value = image;
  } finally {
    sending.value = false;
  }
};


const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
};

const formatTime = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const now = new Date();
  const isToday = date.toDateString() === now.toDateString();
  
  if (isToday) {
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  } else {
    return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
  }
};

const openImageModal = (url) => {
  previewImageUrl.value = url;
  isPreviewOpen.value = true;
};

const getRoomImage = (room) => {
  if (room.image) return room.image;
  const type = room.room_type.toLowerCase();
  if (type.includes('suite')) return 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=100&q=80';
  if (type.includes('deluxe')) return 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=100&q=80';
  return 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=100&q=80';
};

onMounted(() => {
  fetchConversations();
  fetchRooms();
  fetchTabCounts();
  
  pollingInterval = setInterval(() => {
    fetchConversations(); 
    fetchTabCounts();
    if (selectedUser.value) {
       fetchMessages(); 
    }
  }, 3000);
});

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval);
});
</script>

<style scoped>
.bg-premium-dark {
  background-color: #0f172a !important;
}

.bg-premium-dark-gradient {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
}

.bg-gold-gradient {
  background: linear-gradient(135deg, var(--primary-gold) 0%, #a67c3b 100%);
}

.bg-light-soft {
  background-color: #f8fafc;
}

.bg-light-gray {
  background-color: #f1f5f9;
}

.bg-dots {
  background-color: #f8fafc;
  background-image: radial-gradient(#e2e8f0 1.5px, transparent 1.5px);
  background-size: 24px 24px;
}

.active-chat-premium {
  background-color: #f8fafc !important;
  border-left-color: #0f172a !important;
}

.active-chat-gold {
  background-color: #fdf8f0 !important;
  border-left-color: var(--primary-gold) !important;
}

.user-item:hover {
  background-color: #f8fafc;
}

.border-transparent {
  border-left-color: transparent !important;
}

.status-indicator {
  width: 10px;
  height: 10px;
  display: inline-block;
}

.pulse-green {
  box-shadow: 0 0 0 rgba(34, 197, 94, 0.4);
  animation: pulse-green 2s infinite;
}

@keyframes pulse-green {
  0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
  100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background-color: transparent;
}

.rounded-user {
  border-radius: 1.2rem 1.2rem 0.2rem 1.2rem;
}

.rounded-guest {
  border-radius: 1.2rem 1.2rem 1.2rem 0.2rem;
}

.focus-within-premium:focus-within {
  border-color: #0f172a !important;
  background-color: white !important;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
}

.btn-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s;
  border: none;
  background: transparent;
}

.btn-icon:hover {
  background-color: #f1f5f9;
}

.btn-icon-lg {
  border: none;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.btn-icon-lg:hover:not(:disabled) {
  transform: scale(1.1) rotate(-5deg);
  box-shadow: 0 8px 15px rgba(188, 145, 81, 0.3);
}

.shadow-xl {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.shadow-lg-top {
  box-shadow: 0 -10px 15px -3px rgba(0, 0, 0, 0.05);
}

.animate-float {
  animation: float 4s ease-in-out infinite;
}

@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-15px); }
  100% { transform: translateY(0px); }
}

.shadow-gold-sm {
  box-shadow: 0 10px 30px rgba(188, 145, 81, 0.15);
}

.text-primary-light {
  color: #60a5fa;
}

.shadow-gold {
  box-shadow: 0 4px 12px rgba(188, 145, 81, 0.2);
}

.max-w-sm {
  max-width: 400px;
}

/* Image Preview Modal */
.image-preview-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(8px);
}

.animate-fade-in {
  animation: fadeIn 0.3s ease-out;
}

.animate-scale-up {
  animation: scaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes scaleUp {
  from { transform: scale(0.8); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
@keyframes pulse-slow {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.animate-pulse-slow {
  animation: pulse-slow 2s infinite ease-in-out;
}
</style>
