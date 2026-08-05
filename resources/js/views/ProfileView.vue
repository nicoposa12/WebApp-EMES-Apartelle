<template>
  <div class="profile-view-container">
    <div class="profile-page py-5">
    <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-9">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden animate-fade-up">
          <div class="card-header bg-gold p-4 text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0 serif-font">My Profile</h4>
            <span class="badge bg-white text-gold rounded-pill px-3 py-2 small fw-bold">MEMBER</span>
          </div>
          <div class="card-body p-4 p-md-5">
            
            <!-- Profile Header with Photo -->
            <div class="d-flex flex-column align-items-center mb-5">
              <div class="position-relative">
                <div class="profile-photo-container rounded-circle shadow-md border border-4 border-white overflow-hidden" style="width: 140px; height: 140px;">
                  <img 
                    v-if="state.user?.profile_photo_url" 
                    :src="state.user.profile_photo_url" 
                    alt="Profile Photo" 
                    class="w-100 h-100 object-fit-cover"
                  >
                  <div v-else class="w-100 h-100 bg-light d-flex align-items-center justify-content-center text-secondary fs-1 fw-bold">
                    {{ state.user?.first_name?.charAt(0) || 'U' }}
                  </div>
                </div>
                
                <button 
                  @click="triggerFileInput" 
                  class="btn btn-gold rounded-circle position-absolute bottom-0 end-0 shadow-sm d-flex align-items-center justify-content-center p-0"
                  style="width: 40px; height: 40px; transform: translate(10%, 10%);"
                  title="Update Profile Photo"
                >
                  <i class="bi bi-camera-fill"></i>
                </button>
                <input 
                  type="file" 
                  ref="fileInput" 
                  class="d-none" 
                  accept="image/*" 
                  @change="handleFileChange"
                >
              </div>
              <h3 class="serif-font mt-3 mb-1">{{ state.user?.first_name }} {{ state.user?.last_name }}</h3>
              <p class="text-muted small mb-0">{{ state.user?.email }}</p>
              
              <!-- Suspension Status Badge in Header -->
              <div v-if="state.user?.is_suspended" class="mt-3">
                <span class="badge bg-danger rounded-pill px-3 py-2 animate-pulse">
                  <i class="bi bi-shield-lock me-1"></i> ACCOUNT SUSPENDED
                </span>
              </div>
            </div>

            <!-- Suspension Info Section -->
            <div v-if="state.user?.is_suspended" class="suspension-info-card bg-danger-subtle border border-danger p-4 rounded-4 mb-5 animate-fade-in">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    </div>
                    <h5 class="mb-0 fw-bold text-danger serif-font">Account Restrictions Applied</h5>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-7">
                        <p class="fw-bold mb-2 text-danger">Suspension Reason:</p>
                        <p class="bg-white p-3 rounded-3 border border-danger-subtle text-muted mb-0">
                            {{ state.user?.suspension_reason || 'Violation of house rules.' }}
                        </p>
                    </div>
                    <div class="col-md-5">
                        <p class="fw-bold mb-2 text-danger">Restricted Actions:</p>
                        <ul class="list-unstyled small mb-0 text-muted">
                            <li class="mb-1 text-danger"><i class="bi bi-x-circle-fill me-2"></i> Book Rooms</li>
                            <li class="mb-1 text-danger"><i class="bi bi-x-circle-fill me-2"></i> Cancel / Modify Bookings</li>
                            <li class="mb-1 text-danger"><i class="bi bi-x-circle-fill me-2"></i> Make Payments</li>
                            <li class="text-danger"><i class="bi bi-x-circle-fill me-2"></i> Send New Requests</li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-4 pt-3 border-top border-danger-subtle d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-danger"></i>
                    <span class="small text-danger opacity-75">You can still Login, View Profile, and Contact Support.</span>
                </div>
            </div>

            <!-- Profile Details Grid -->
            <h5 class="section-label ps-1 mb-4 border-bottom pb-2">Personal Information</h5>
            <div class="row g-4">
              <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 h-100 border border-light transition-all hover-lift">
                  <label class="form-label small text-muted text-uppercase tracking-wider fw-bold mb-1">First Name</label>
                  <div class="fw-bold text-dark">{{ state.user?.first_name }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 h-100 border border-light transition-all hover-lift">
                  <label class="form-label small text-muted text-uppercase tracking-wider fw-bold mb-1">Last Name</label>
                  <div class="fw-bold text-dark">{{ state.user?.last_name }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 h-100 border border-light transition-all hover-lift">
                  <label class="form-label small text-muted text-uppercase tracking-wider fw-bold mb-1">Email Address</label>
                  <div class="fw-bold text-dark">{{ state.user?.email }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 h-100 border border-light transition-all hover-lift">
                  <label class="form-label small text-muted text-uppercase tracking-wider fw-bold mb-1">Phone Number</label>
                  <div class="fw-bold text-dark">{{ state.user?.phone }}</div>
                </div>
              </div>
              <div class="col-12">
                <div class="p-3 bg-light rounded-3 h-100 border border-light transition-all hover-lift">
                  <label class="form-label small text-muted text-uppercase tracking-wider fw-bold mb-1">Address</label>
                  <div class="fw-bold text-dark">{{ state.user?.address }}, {{ state.user?.city }}, {{ state.user?.zip_code }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 h-100 border border-light transition-all hover-lift">
                  <label class="form-label small text-muted text-uppercase tracking-wider fw-bold mb-1">Birthdate</label>
                  <div class="fw-bold text-dark">{{ state.user?.birthdate }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 bg-light rounded-3 h-100 border border-light transition-all hover-lift">
                  <label class="form-label small text-muted text-uppercase tracking-wider fw-bold mb-1">Gender</label>
                  <div class="fw-bold text-dark text-capitalize">{{ state.user?.gender }}</div>
                </div>
              </div>
            </div>
            
            <div class="mt-5 d-flex gap-3 justify-content-end">
              <button @click="openChangePasswordModal" class="btn btn-outline-dark-custom px-4 hover-lift">Change Password</button>
              <button @click="openEditProfileModal" class="btn btn-gold px-4 shadow-md hover-lift">Edit Profile</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ========== EDIT PROFILE MODAL ========== -->
<div v-if="showEditProfileModal" class="modal-backdrop fade show" style="z-index: 1050;" @click="showEditProfileModal = false"></div>
<div v-if="showEditProfileModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1055;" @click.self="showEditProfileModal = false">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 rounded-4 overflow-hidden shadow-2xl">
      <!-- Modal Header -->
      <div class="modal-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
        <h3 class="modal-title serif-font text-secondary-dark mb-0 fs-4">Edit Profile</h3>
        <button type="button" class="btn-close bg-light rounded-circle p-2" @click="showEditProfileModal = false" aria-label="Close"></button>
      </div>
      <!-- Modal Body -->
      <div class="modal-body bg-cream p-4">
        <form @submit.prevent="submitProfileUpdate">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small text-muted text-uppercase tracking-wider fw-bold">First Name</label>
              <input type="text" v-model="profileForm.first_name" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small text-muted text-uppercase tracking-wider fw-bold">Last Name</label>
              <input type="text" v-model="profileForm.last_name" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small text-muted text-uppercase tracking-wider fw-bold">Phone Number</label>
              <input type="text" v-model="profileForm.phone" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small text-muted text-uppercase tracking-wider fw-bold">Birthdate</label>
              <input type="date" v-model="profileForm.birthdate" class="form-control rounded-3" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small text-muted text-uppercase tracking-wider fw-bold">Gender</label>
              <select v-model="profileForm.gender" class="form-select rounded-3" required>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small text-muted text-uppercase tracking-wider fw-bold">Zip Code</label>
              <input type="text" v-model="profileForm.zip_code" class="form-control rounded-3" required>
            </div>
            <div class="col-md-8">
              <label class="form-label small text-muted text-uppercase tracking-wider fw-bold">Address</label>
              <input type="text" v-model="profileForm.address" class="form-control rounded-3" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small text-muted text-uppercase tracking-wider fw-bold">City</label>
              <input type="text" v-model="profileForm.city" class="form-control rounded-3" required>
            </div>
          </div>
          <div class="mt-4 d-flex gap-3 justify-content-end">
            <button type="button" class="btn btn-secondary px-4 rounded-pill" @click="showEditProfileModal = false">Cancel</button>
            <button type="submit" class="btn btn-gold px-4 rounded-pill shadow-md" :disabled="isSavingProfile">
              <span v-if="isSavingProfile" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ========== CHANGE PASSWORD MODAL ========== -->
<div v-if="showChangePasswordModal" class="modal-backdrop fade show" style="z-index: 1050;" @click="showChangePasswordModal = false"></div>
<div v-if="showChangePasswordModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1055;" @click.self="showChangePasswordModal = false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 overflow-hidden shadow-2xl">
      <!-- Modal Header -->
      <div class="modal-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
        <h3 class="modal-title serif-font text-secondary-dark mb-0 fs-4">Change Password</h3>
        <button type="button" class="btn-close bg-light rounded-circle p-2" @click="showChangePasswordModal = false" aria-label="Close"></button>
      </div>
      <!-- Modal Body -->
      <div class="modal-body bg-cream p-4">
        <form @submit.prevent="submitPasswordChange">
          <div class="mb-3">
            <label class="form-label small text-muted text-uppercase tracking-wider fw-bold">Current Password</label>
            <input type="password" v-model="passwordForm.current_password" class="form-control rounded-3" required>
          </div>
          <div class="mb-3">
            <label class="form-label small text-muted text-uppercase tracking-wider fw-bold">New Password</label>
            <input type="password" v-model="passwordForm.password" class="form-control rounded-3" required>
          </div>
          <div class="mb-3">
            <label class="form-label small text-muted text-uppercase tracking-wider fw-bold">Confirm New Password</label>
            <input type="password" v-model="passwordForm.password_confirmation" class="form-control rounded-3" required>
          </div>
          <div class="mt-4 d-flex gap-3 justify-content-end">
            <button type="button" class="btn btn-secondary px-4 rounded-pill" @click="showChangePasswordModal = false">Cancel</button>
            <button type="submit" class="btn btn-gold px-4 rounded-pill shadow-md" :disabled="isChangingPassword">
              <span v-if="isChangingPassword" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              Update Password
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useAuth } from '../store/auth';
import axios from 'axios';
import Swal from 'sweetalert2';

const { state, fetchUser } = useAuth();
const fileInput = ref(null);

// Modal state variables
const showEditProfileModal = ref(false);
const showChangePasswordModal = ref(false);
const isSavingProfile = ref(false);
const isChangingPassword = ref(false);

// Reactive form structures
const profileForm = reactive({
  first_name: '',
  last_name: '',
  phone: '',
  address: '',
  city: '',
  zip_code: '',
  birthdate: '',
  gender: 'other'
});

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
});

// Modal Actions
const openEditProfileModal = () => {
  if (state.user) {
    profileForm.first_name = state.user.first_name || '';
    profileForm.last_name = state.user.last_name || '';
    profileForm.phone = state.user.phone || '';
    profileForm.address = state.user.address || '';
    profileForm.city = state.user.city || '';
    profileForm.zip_code = state.user.zip_code || '';
    profileForm.birthdate = state.user.birthdate || '';
    profileForm.gender = state.user.gender || 'other';
  }
  showEditProfileModal.value = true;
};

const openChangePasswordModal = () => {
  passwordForm.current_password = '';
  passwordForm.password = '';
  passwordForm.password_confirmation = '';
  showChangePasswordModal.value = true;
};

// Form Submissions
const submitProfileUpdate = async () => {
  isSavingProfile.value = true;
  try {
    const response = await axios.put('/api/user/profile', profileForm);
    
    // Refresh user state
    await fetchUser();
    
    showEditProfileModal.value = false;
    
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: response.data.message || 'Profile updated successfully.',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (error) {
    console.error('Failed to update profile:', error);
    Swal.fire({
      icon: 'error',
      title: 'Update Failed',
      text: error.response?.data?.message || 'Something went wrong. Please check your inputs.',
      confirmButtonColor: '#BC9151'
    });
  } finally {
    isSavingProfile.value = false;
  }
};

const submitPasswordChange = async () => {
  // Validate confirm password matches
  if (passwordForm.password !== passwordForm.password_confirmation) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'New password and password confirmation do not match.',
      confirmButtonColor: '#BC9151'
    });
    return;
  }

  isChangingPassword.value = true;
  try {
    const response = await axios.put('/api/user/change-password', passwordForm);
    
    showChangePasswordModal.value = false;
    
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: response.data.message || 'Password changed successfully.',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (error) {
    console.error('Failed to change password:', error);
    Swal.fire({
      icon: 'error',
      title: 'Password Change Failed',
      text: error.response?.data?.message || 'Current password might be incorrect or new password doesn\'t meet complexity requirements.',
      confirmButtonColor: '#BC9151'
    });
  } finally {
    isChangingPassword.value = false;
  }
};

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileChange = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validation
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
  if (!validTypes.includes(file.type)) {
    Swal.fire({
      icon: 'error',
      title: 'Invalid File',
      text: 'Please select a valid image file (JPG, PNG, WebP).',
      confirmButtonColor: '#BC9151'
    });
    return;
  }

  if (file.size > 2 * 1024 * 1024) { // 2MB
    Swal.fire({
      icon: 'error',
      title: 'File Too Large',
      text: 'Image size must be less than 2MB.',
      confirmButtonColor: '#BC9151'
    });
    return;
  }

  // Upload
  const formData = new FormData();
  formData.append('photo', file);

  try {
    // Show loading
    Swal.fire({
      title: 'Uploading...',
      text: 'Please wait while we update your profile photo.',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    await axios.post('/api/user/profile-photo', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    // Refresh user data
    await fetchUser();

    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: 'Profile photo updated successfully.',
      timer: 2000,
      showConfirmButton: false
    });

  } catch (error) {
    console.error('Upload failed:', error);
    Swal.fire({
      icon: 'error',
      title: 'Upload Failed',
      text: error.response?.data?.message || 'Something went wrong. Please try again.',
      confirmButtonColor: '#BC9151'
    });
  }
};
</script>

<style scoped>
.profile-page {
  padding-top: 100px !important;
}
.bg-gold {
  background-color: var(--primary-gold);
}
.tracking-wider {
  letter-spacing: 0.1em;
}
.hover-lift {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.hover-lift:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
}
.object-fit-cover {
  object-fit: cover;
}

/* Modal styles overlay */
.modal-backdrop.show {
  opacity: 0.65;
  background-color: #0f172a;
  backdrop-filter: blur(4px);
}
.modal.show {
  display: block;
}
</style>
