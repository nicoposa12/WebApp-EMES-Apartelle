<template>
  <div class="chat-widget position-fixed bottom-0 end-0 m-4 z-3 d-flex flex-column align-items-end">
    <!-- Chat Options Menu -->
    <transition name="slide-up">
      <div v-if="showOptions && !isOpen" class="chat-options-card card border-0 shadow-lg mb-3 overflow-hidden" style="width: 320px;">
        <div class="card-header bg-gold text-white p-4 border-0">
          <h5 class="mb-1 fw-bold serif-font">How can we help?</h5>
          <p class="small opacity-75 mb-0">Select an option to start</p>
        </div>
        <div class="card-body p-2 bg-white">
          <div class="list-group list-group-flush">
            <!-- FAQ / Chatbot Option -->
            <button @click="startChat('chatbot')" class="option-item list-group-item list-group-item-action border-0 d-flex align-items-center gap-3 p-3 rounded-3">
              <div class="option-icon bg-gold-subtle text-gold rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                 <i class="bi bi-robot fs-5"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0 fw-bold small">Quick FAQs</h6>
                <small class="text-muted" style="font-size: 0.7rem;">Instant answers from our bot</small>
              </div>
              <i class="bi bi-chevron-right small text-muted"></i>
            </button>

            <!-- Support / Messages Option -->
            <button v-if="state.user" @click="startChat('support')" class="option-item list-group-item list-group-item-action border-0 d-flex align-items-center gap-3 p-3 rounded-3 mt-1">
              <div class="option-icon bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                 <i class="bi bi-chat-text fs-5"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0 fw-bold small">Live Support</h6>
                <small class="text-muted" style="font-size: 0.7rem;">Chat with our staff</small>
              </div>
              <span v-if="unreadCount > 0" class="badge rounded-pill bg-danger">{{ unreadCount }}</span>
              <i v-else class="bi bi-chevron-right small text-muted"></i>
            </button>

            <!-- Prominently show Login if not logged in -->
            <router-link v-else to="/login" class="option-item list-group-item list-group-item-action border-0 d-flex align-items-center gap-3 p-3 rounded-3 mt-1">
              <div class="option-icon bg-secondary-subtle text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                 <i class="bi bi-person fs-5"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0 fw-bold small">Sign In for History</h6>
                <small class="text-muted" style="font-size: 0.7rem;">Save your conversations</small>
              </div>
              <i class="bi bi-chevron-right small text-muted"></i>
            </router-link>
          </div>
        </div>
        <div v-if="hasBookings" class="booking-notice p-3 bg-light-gold border-top">
           <div class="d-flex align-items-center gap-2">
              <i class="bi bi-calendar-check text-gold"></i>
              <small class="fw-bold text-secondary-dark" style="font-size: 0.75rem;">Your stay is upcoming! Priority support active.</small>
           </div>
        </div>
      </div>
    </transition>

    <!-- Chat Window -->
    <transition name="slide-up">
      <div v-if="isOpen" class="chat-window card border-0 shadow-lg mb-3 overflow-hidden d-flex flex-column" style="width: 350px; height: 500px; max-height: 80vh;">
        <!-- Header -->
        <div class="card-header text-white p-3 d-flex justify-content-between align-items-center border-0"
             :class="chatMode === 'chatbot' ? 'bg-gold' : 'bg-premium-dark'">
           <div class="d-flex align-items-center gap-2">
             <button @click="backToOptions" class="btn btn-sm btn-link text-white p-0 me-1">
               <i class="bi bi-chevron-left"></i>
             </button>
             <div class="avatar-sm bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                <i :class="chatMode === 'chatbot' ? 'bi-robot text-gold' : 'bi-headset text-premium-dark'"></i>
             </div>
             <div class="d-flex flex-column">
               <h6 class="mb-0 fw-bold small text-white">{{ chatMode === 'chatbot' ? 'EA Chatbot' : 'Customer Support' }}</h6>
               <div class="d-flex align-items-center gap-1">
                 <span class="status-dot-pulse bg-success rounded-circle" style="width: 6px; height: 6px;"></span>
                 <span class="text-white-50 fw-bold" style="font-size: 0.6rem; letter-spacing: 0.5px;">ONLINE</span>
               </div>
             </div>
           </div>
           <button class="btn btn-sm btn-link text-white p-0" @click="toggleWidget">
             <i class="bi bi-x-lg"></i>
           </button>
        </div>

        <!-- Messages Area -->
        <div class="card-body p-3 bg-light overflow-y-auto custom-scrollbar d-flex flex-column gap-3 flex-grow-1" ref="messagesContainer">
          <!-- Chatbot Welcome -->
          <div v-if="chatMode === 'chatbot' && messages.length === 0" class="bot-welcome animate-fade-in">
              <div class="d-flex gap-2 mb-3">
                 <div class="avatar-bot bg-white text-gold rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 30px; height: 30px;">
                    <i class="bi bi-robot"></i>
                 </div>
                 <div class="message-bubble bg-white text-dark p-3 rounded-admin shadow-sm small">
                    Hello! I'm the EA Chatbot. Ask me about **WiFi**, **Price**, **Location**, or **Amenities** for instant help!
                 </div>
              </div>
          </div>

          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border spinner-border-sm" :class="chatMode === 'chatbot' ? 'text-gold' : 'text-premium-dark'" role="status"></div>
            <p class="text-muted small mt-2">Connecting to concierge...</p>
          </div>
          
          <div v-else-if="messages.length === 0 && chatMode === 'support'" class="text-center py-5 text-muted">
             <i class="bi bi-chat-dots fs-1 opacity-25"></i>
             <p class="small mt-2">Connecting to our concierge team...</p>
          </div>

          <template v-else>
             <div v-for="msg in messages" :key="msg.id" class="d-flex" :class="isOwnMessage(msg) ? 'justify-content-end' : 'justify-content-start'">
                <!-- Only show bot avatar for bot replies if in chatbot mode -->
                <div v-if="!isOwnMessage(msg) && chatMode === 'chatbot'" class="me-2 align-self-end mb-4">
                    <div class="bg-white text-gold rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 24px; height: 24px;">
                        <i class="bi bi-robot small"></i>
                    </div>
                </div>
                
                <div class="message-wrapper d-flex flex-column" :class="isOwnMessage(msg) ? 'align-items-end' : 'align-items-start'" style="max-width: 85%;">
                  <div class="p-3 shadow-sm position-relative" 
                       :class="isOwnMessage(msg) ? (chatMode === 'chatbot' ? 'bg-gold text-white rounded-user' : 'bg-premium-dark text-white rounded-user') : 'bg-white text-dark rounded-admin'">
                     
                     <!-- Message Text with Link Detection -->
                     <p v-if="msg.message" class="mb-0 small text-break" style="white-space: pre-wrap;">
                       <template v-for="(part, i) in formatMessageWithLinks(msg.message)" :key="i">
                         <a v-if="part.type === 'link'" :href="part.content" target="_blank" class="chat-link" :class="isOwnMessage(msg) ? 'text-white' : (chatMode === 'chatbot' ? 'text-gold fw-bold' : 'text-premium-dark fw-bold')">
                           {{ part.content }}
                         </a>
                         <span v-else>{{ part.content }}</span>
                       </template>
                     </p>
                     
                     <!-- Image Display -->
                     <div v-if="msg.image" class="mt-2 rounded-2 overflow-hidden border border-white border-2 shadow-sm">
                       <img :src="msg.image" class="w-100 object-fit-cover cursor-pointer" @click="openImageModal(msg.image)" style="max-height: 180px;">
                     </div>
                  </div>
                  <small class="time-stamp mt-1 px-1" :class="isOwnMessage(msg) ? 'text-muted text-end' : 'text-muted'">
                    {{ formatTime(msg.created_at) }}
                    <i v-if="isOwnMessage(msg)" class="bi ms-1" :class="msg.is_read ? (chatMode === 'chatbot' ? 'bi-check-all text-gold' : 'bi-check-all text-premium-dark') : 'bi-check'"></i>
                  </small>
                </div>
             </div>
          </template>

          <!-- Typing Indicator -->
          <div v-if="isTyping" class="d-flex align-items-center gap-2 mb-3 animate-fade-in">
             <div class="avatar-bot bg-white text-gold rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 24px; height: 24px;">
                <i class="bi bi-robot x-small"></i>
             </div>
             <div class="message-bubble bg-white text-dark py-2 px-3 rounded-admin shadow-sm d-flex gap-1 align-items-center">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
             </div>
          </div>
        </div>

        <!-- Attachment Preview -->
        <div v-if="selectedImageFile" class="attachment-preview p-2 bg-white border-top d-flex align-items-center gap-2">
           <div class="position-relative rounded-2 overflow-hidden border" style="width: 50px; height: 50px;">
             <img :src="imageFilePreview" class="w-100 h-100 object-fit-cover">
             <button @click="clearSelectedImage" class="btn btn-dark btn-sm rounded-circle position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center" style="width: 16px; height: 16px;">
               <i class="bi bi-x x-small"></i>
             </button>
           </div>
           <p class="x-small fw-bold mb-0 text-truncate" style="max-width: 150px;">{{ selectedImageFile.name }}</p>
        </div>

        <!-- Input Area -->
        <div class="card-footer bg-white p-3 border-top">
           <form @submit.prevent="sendMessage" class="d-flex gap-2 align-items-center">
             <button v-if="chatMode === 'support'" type="button" @click="$refs.fileInput.click()" class="btn btn-link text-muted p-0 border-0" title="Attach Image">
               <i class="bi bi-paperclip fs-5 transform-rotate-45"></i>
             </button>
             <input type="file" ref="fileInput" class="d-none" @change="handleFileChange" accept="image/*">

             <input 
               type="text" 
               class="form-control form-control-sm border bg-light py-2 px-3 rounded-pill shadow-none" 
               :placeholder="chatMode === 'chatbot' ? 'Ask the bot...' : 'Type a message...'" 
               v-model="newMessage"
               :disabled="sending"
             >
             <button type="submit" 
                class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" 
                :class="chatMode === 'chatbot' ? 'btn-gold' : 'btn-premium-dark'"
                style="width: 34px; height: 34px;" 
                :disabled="(!newMessage.trim() && !selectedImageFile) || sending">
               <i class="bi bi-send-fill small" v-if="!sending"></i>
               <span class="spinner-border spinner-border-sm" style="width: 0.8rem; height: 0.8rem;" v-else></span>
             </button>
           </form>
        </div>
      </div>
    </transition>

    <!-- Toggle Buttons (Separated) -->
    <div v-if="!isOpen && !showOptions" class="d-flex flex-column gap-3 align-items-end">
      <!-- Live Chat Button (Logged-in users only) -->
      <div v-if="state.user" class="position-relative">
        <button 
          class="btn btn-premium-dark rounded-circle shadow-lg d-flex align-items-center justify-content-center chat-toggle-btn" 
          style="width: 55px; height: 55px;" 
          @click="startChat('support')"
          title="Chat with Us"
        >
          <i class="bi bi-chat-text-fill fs-4"></i>
        </button>
        <!-- Unread Badge -->
        <span v-if="unreadCount > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
          {{ unreadCount }}
        </span>
      </div>

      <!-- Chatbot Button (Always visible for everyone) -->
      <button 
        class="btn btn-gold rounded-circle shadow-lg d-flex align-items-center justify-content-center chat-toggle-btn" 
        style="width: 55px; height: 55px;" 
        @click="startChat('chatbot')"
        title="Quick Help"
      >
        <i class="bi bi-robot fs-4"></i>
      </button>
    </div>

    <!-- Close button when open -->
    <button 
      v-if="isOpen || showOptions"
      class="btn btn-dark rounded-circle shadow-lg d-flex align-items-center justify-content-center chat-toggle-btn" 
      style="width: 60px; height: 60px;" 
      @click="toggleWidget"
    >
      <i class="bi bi-x-lg fs-4"></i>
    </button>

    <!-- Full Image Preview Modal -->
    <teleport to="body">
      <div v-if="isPreviewOpen" class="image-preview-overlay d-flex align-items-center justify-content-center p-4 animate-fade-in" @click="isPreviewOpen = false" style="z-index: 99999;">
        <img :src="previewImageUrl" class="mh-100 mw-100 rounded-4 shadow-lg">
        <button class="btn btn-light rounded-circle position-absolute top-0 end-0 m-4 shadow-lg">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
    </teleport>
  </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import { useAuth } from '../store/auth';

const { state } = useAuth();
const isOpen = ref(false);
const showOptions = ref(false);
const chatMode = ref('support'); // 'support' or 'chatbot'
const hasBookings = ref(false);
const supportMessages = ref([]);
const chatbotMessages = ref([]);
const messages = computed(() => chatMode.value === 'support' ? supportMessages.value : chatbotMessages.value);

const newMessage = ref('');
const loading = ref(false);
const sending = ref(false);
const messagesContainer = ref(null);
const unreadCount = ref(0);

const fileInput = ref(null);
const selectedImageFile = ref(null);
const imageFilePreview = ref(null);

const isPreviewOpen = ref(false);
const previewImageUrl = ref('');
const isTyping = ref(false);
const publicRules = ref([]);

let pollingInterval = null;

const fetchPublicRules = async () => {
  try {
    const response = await axios.get('/api/chatbot-rules/public');
    publicRules.value = response.data;
  } catch (err) {
    console.error('Failed to fetch public chatbot rules', err);
  }
};

const getLocalBotReply = (message) => {
  const lowercaseMsg = (message || '').toLowerCase();
  
  for (const rule of publicRules.value) {
    const triggers = rule.trigger.split(',').map(t => t.trim().toLowerCase());
    let matched = false;

    if (rule.match_type === 'exact') {
      matched = triggers.some(t => lowercaseMsg === t);
    } else {
      matched = triggers.some(t => {
        if (!t) return false;
        const pattern = new RegExp(`\\b${t}\\b`, 'i');
        return pattern.test(lowercaseMsg);
      });
    }

    if (matched) return rule.response;
  }
  
  if (!state.user) {
    return "Hello Ma'am/Sir! I'm sorry, I don't know that yet. Ask about **WiFi**, **Price**, or **Amenities**. To talk to a real person and save your chat, please **Sign In**!";
  }
  
  return `Hi ${state.user.name.split(' ')[0]}! I didn't catch that. Ask about our **WiFi**, **Price**, or **Location**, or switch to **Live Chat** to speak with our staff.`;
};

const toggleWidget = () => {
  if (isOpen.value || showOptions.value) {
    isOpen.value = false;
    showOptions.value = false;
  } else {
    showOptions.value = true;
    checkUserStatus();
  }
};

const checkUserStatus = async () => {
    if (state.user) {
        try {
            const res = await axios.get('/api/reservations');
            const activeReservations = res.data.filter(r => ['pending', 'confirmed', 'checked-in'].includes(r.status));
            hasBookings.value = activeReservations.length > 0;
        } catch (e) {
            console.error(e);
        }
    }
};

const startChat = (mode) => {
  chatMode.value = mode;
  showOptions.value = false;
  isOpen.value = true;
  
  // For chatbot, we can work locally even without login
  if (mode === 'chatbot') {
      if (state.user) {
          fetchMessages(mode);
      }
      // Non-logged-in users use local chatbotMessages (already initialized)
  } else if (mode === 'support') {
      if (state.user) {
          fetchMessages(mode);
          unreadCount.value = 0;
      }
  }
  scrollToBottom();
};

const backToOptions = () => {
  isOpen.value = false;
  showOptions.value = true;
};

const toggleChat = toggleWidget; 

const isOwnMessage = (msg) => {
  // For local messages (non-logged-in), check is_own flag
  if (msg.is_own !== undefined) return msg.is_own;
  return msg.sender_id === state.user?.id;
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

const fetchMessages = async (type = 'support') => {
  try {
    const response = await axios.get(`/api/messages?type=${type}`);
    const newMsgs = response.data;
    
    if (type === 'support') {
        supportMessages.value = newMsgs;
        // Accurate unread count calculation
        const unreadFromAdmin = newMsgs.filter(m => !isOwnMessage(m) && !m.is_read).length;
        unreadCount.value = unreadFromAdmin;

        if (isOpen.value && chatMode.value === 'support' && unreadFromAdmin > 0) {
            markAsRead('support');
        }
    } else {
        chatbotMessages.value = newMsgs;
        if (isOpen.value && chatMode.value === 'chatbot') {
            const hasUnreadBot = newMsgs.some(m => !isOwnMessage(m) && !m.is_read);
            if (hasUnreadBot) markAsRead('chatbot');
        }
    }
    
    const wasAtBottom = isAtBottom();
    if (isOpen.value && chatMode.value === type && wasAtBottom) {
       scrollToBottom();
    }
  } catch (err) {
    if (err.response?.status !== 401) {
       console.error(`Failed to fetch ${type} messages`, err);
    }
  } finally {
    loading.value = false;
  }
};

const markAsRead = async (type = 'support') => {
    try {
        // For guest, sender is always admin (role-wise, but we need ID)
        // We'll let the controller handle finding the admin sender ID if not provided, 
        // or we can pass a special flag. 
        // Actually, our API: put('/api/messages/{senderId}/read')
        // Let's find admin ID first or use a general endpoint.
        const res = await axios.put(`/api/messages/admin/read-all?type=${type}`);
        if (type === 'support') unreadCount.value = 0;
    } catch (e) {
        // If 404, might need the specific ID, but let's add a read-all-from-admin route
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

const sendMessage = async () => {
  if ((!newMessage.value.trim() && !selectedImageFile.value) || sending.value) return;
  
  const text = newMessage.value;
  const image = selectedImageFile.value;
  const currentMode = chatMode.value;

  newMessage.value = '';
  clearSelectedImage();
  sending.value = true;

  // Local chatbot for non-logged-in users
  if (currentMode === 'chatbot' && !state.user) {
    const localMsg = {
      id: Date.now(),
      message: text,
      is_own: true,
      created_at: new Date().toISOString()
    };
    chatbotMessages.value.push(localMsg);
    scrollToBottom();

    isTyping.value = true;
    setTimeout(() => {
      const botReply = getLocalBotReply(text);
      chatbotMessages.value.push({
        id: Date.now() + 1,
        message: botReply,
        is_own: false,
        created_at: new Date().toISOString()
      });
      isTyping.value = false;
      scrollToBottom();
    }, 1500);
    sending.value = false;
    return;
  }

  try {
    const formData = new FormData();
    formData.append('message', text);
    formData.append('is_chatbot', currentMode === 'chatbot' ? '1' : '0');
    if (image) {
      formData.append('image', image);
    }

    const response = await axios.post('/api/messages', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    if (currentMode === 'support') {
        supportMessages.value.push(response.data);
    } else {
        chatbotMessages.value.push(response.data);
    }

    scrollToBottom();
    // Fetch again after a short delay to get chatbot response
    if (currentMode === 'chatbot') {
        isTyping.value = true;
        setTimeout(async () => {
            await fetchMessages(currentMode);
            isTyping.value = false;
        }, 1500);
    } else {
        setTimeout(() => fetchMessages(currentMode), 1000);
    }
  } catch (err) {
    console.error('Message send failed', err);
    newMessage.value = text;
    selectedImageFile.value = image;
  } finally {
    sending.value = false;
  }
};

const openImageModal = (url) => {
  previewImageUrl.value = url;
  isPreviewOpen.value = true;
};

const isAtBottom = () => {
   if (!messagesContainer.value) return true;
   return messagesContainer.value.scrollHeight - messagesContainer.value.scrollTop - messagesContainer.value.clientHeight < 100;
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
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const startPolling = () => {
    if (pollingInterval) return;
    fetchMessages('support');
    pollingInterval = setInterval(() => {
        if (state.user) {
            fetchMessages('support');
            if (isOpen.value && chatMode.value === 'chatbot') {
                fetchMessages('chatbot');
            }
        }
    }, 4000);
};

const stopPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
};

watch(() => state.user, (newUser) => {
    if (newUser) {
        startPolling();
    } else {
        stopPolling();
        supportMessages.value = [];
        chatbotMessages.value = [];
        unreadCount.value = 0;
    }
}, { immediate: true });

onMounted(() => {
    fetchPublicRules();
    if (state.user) startPolling();
});

onUnmounted(() => {
  stopPolling();
});
</script>

<style scoped>
.chat-window {
  border-radius: 12px;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(0,0,0,0.1);
  border-radius: 4px;
}

.rounded-user {
  border-radius: 12px 12px 2px 12px;
}
.rounded-admin {
  border-radius: 12px 12px 12px 2px;
}
.time-stamp {
  font-size: 0.65rem;
}

.x-small {
  font-size: 0.65rem;
}

.transform-rotate-45 {
  transform: rotate(45deg);
}

.image-preview-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100vh;
  background: rgba(0,0,0,0.85);
}

.animate-fade-in {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Animations */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

.scale-enter-active,
.scale-leave-active {
  transition: transform 0.2s ease;
}
.scale-enter-from,
.scale-leave-to {
  transform: scale(0);
}

.btn-gold {
     background-color: var(--primary-gold);
     color: white;
     border: none;
}
.bg-gold {
    background-color: var(--primary-gold);
}

.btn-gold:hover {
  background-color: var(--primary-gold-dark);
  color: white;
  transform: scale(1.05);
}

.chat-options-card {
  border-radius: 16px;
  background-color: #ffffff;
  z-index: 1060;
}

.option-item {
  transition: all 0.2s ease;
  cursor: pointer;
}

.option-item:hover {
  background-color: #fdf8f0 !important;
  transform: translateX(5px);
}

.bg-gold-subtle {
  background-color: rgba(188, 145, 81, 0.1);
}

.bg-primary-subtle {
  background-color: rgba(13, 110, 253, 0.1);
}

.bg-light-gold {
  background-color: #fdf8f4;
}

.rounded-admin {
  border-radius: 12px 12px 12px 2px;
}

.avatar-sm, .avatar-bot {
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.chat-toggle-btn {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.chat-toggle-btn:hover {
  transform: rotate(15deg) scale(1.1);
}

.bg-premium-dark {
  background-color: #0f172a; /* Slate 900 - Matching footer */
}

.text-premium-dark {
  color: #0f172a;
}

.btn-premium-dark {
  background-color: #0f172a;
  color: white;
  border: none;
}

.btn-premium-dark:hover {
  background-color: #1e293b; /* Slate 800 */
  color: white;
  transform: scale(1.05);
}

.dot {
  width: 4px;
  height: 4px;
  background-color: #adb5bd;
  border-radius: 50%;
  animation: bounce 1.4s infinite ease-in-out both;
}

.dot:nth-child(1) { animation-delay: -0.32s; }
.dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes bounce {
  0%, 80%, 100% { transform: scale(0); }
  40% { transform: scale(1.0); }
}

.status-dot-pulse {
  position: relative;
}

.status-dot-pulse::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background-color: inherit;
  opacity: 0.8;
  animation: pulse-out 2s cubic-bezier(0.455, 0.03, 0.515, 0.955) infinite;
}

@keyframes pulse-out {
  0% { transform: scale(1); opacity: 0.8; }
  100% { transform: scale(3.5); opacity: 0; }
}

.text-white-50 {
  color: rgba(255, 255, 255, 0.6) !important;
}
</style>
