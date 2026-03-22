<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useAuth } from '../store/auth';
import CalendarPicker from '../components/CalendarPicker.vue';

const router = useRouter();

const route = useRoute();
const { state } = useAuth();
const isSuspended = computed(() => state.user?.is_suspended);
// Steps: 1 = Select Dates/Room, 2 = Guest Details, 3 = Payment
const currentStep = ref(1);

// Parse guests from query string "1 Guest" -> 1
const parseGuests = (guestsStr) => {
    if (!guestsStr) return 1;
    const match = guestsStr.match(/(\d+)/);
    return match ? parseInt(match[1]) : 1;
};

// Form Data - Initialize with auth state if available
const booking = ref({
    checkIn: route.query.checkIn || '',
    checkOut: route.query.checkOut || '',
    roomId: '',
    guests: parseGuests(route.query.guests),
    firstName: state.user?.first_name || '',
    lastName: state.user?.last_name || '',
    email: state.user?.email || '',
    phone: state.user?.phone || '',
    address: state.user?.address || '',
    paymentMethod: 'paymongo', // Default
});

const rooms = ref([]);
const loading = ref(true);
const fetchRooms = async () => {
    loading.value = true;
    try {
        const params = {};
        if (booking.value.checkIn && booking.value.checkOut) {
            params.check_in = booking.value.checkIn;
            params.check_out = booking.value.checkOut;
        }
        
        const response = await axios.get('/api/rooms', { params });
        rooms.value = response.data.filter(r => r.status === 'available');
        
        // If selected room is no longer available for new dates, reset it
        if (booking.value.roomId && !rooms.value.find(r => r.id === parseInt(booking.value.roomId))) {
            booking.value.roomId = '';
        }

        // Auto-select first available room if none selected
        if (!booking.value.roomId && rooms.value.length > 0) {
            booking.value.roomId = rooms.value[0].id;
        }
    } catch (err) {
        console.error("Error fetching rooms", err);
        rooms.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(fetchRooms);

// Watch for date changes to update available rooms
watch(() => [booking.value.checkIn, booking.value.checkOut], () => {
    if (booking.value.checkIn && booking.value.checkOut) {
        fetchRooms();
    }
});

// Watch for room selection to auto-adjust guests based on capacity
watch(() => booking.value.roomId, (newId) => {
    if (!newId) return;
    const room = rooms.value.find(r => r.id === parseInt(newId));
    if (room) {
        // Automatically cap guests based on room occupancy
        if (booking.value.guests > room.max_occupancy) {
            booking.value.guests = room.max_occupancy;
        }
        
        // Special case for Single rooms to be exactly 1 guest (if desired by logic, otherwise cap is enough)
        if (room.room_type.toLowerCase().includes('single')) {
            booking.value.guests = 1;
        }
    }
});

// Watch for guest changes to ensure they stay within selected room's capacity
watch(() => booking.value.guests, (newGuests) => {
    if (selectedRoom.value && newGuests > selectedRoom.value.max_occupancy) {
        booking.value.guests = selectedRoom.value.max_occupancy;
    }
});

const selectedRoom = computed(() => {
    return rooms.value.find(r => r.id === parseInt(booking.value.roomId));
});

const showRoomModal = ref(false);

const openRoomModal = () => {
    if (selectedRoom.value) {
        showRoomModal.value = true;
    }
};

const getRoomImage = (room) => {
    if (!room) return '';
    if (room.image) return room.image;
    
    // Fallback based on type keywords
    const type = room.room_type.toLowerCase();
    if (type.includes('suite')) return 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=800&q=80';
    if (type.includes('deluxe')) return 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80';
    return 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=800&q=80';
};

const totalNights = computed(() => {
    if (!booking.value.checkIn || !booking.value.checkOut) return 0;
    const start = new Date(booking.value.checkIn);
    const end = new Date(booking.value.checkOut);
    const diff = end - start;
    const nights = diff / (1000 * 60 * 60 * 24);
    return nights > 0 ? nights : 0;
});

const totalPrice = computed(() => {
    if (!selectedRoom.value || totalNights.value <= 0) return 0;
    return selectedRoom.value.price_per_night * totalNights.value;
});

const validateStep = (step) => {
    if (step === 1) {
        if (!booking.value.checkIn) return "Please select a Check-in date.";
        if (!booking.value.checkOut) return "Please select a Check-out date.";
        if (totalNights.value <= 0) return "Invalid date range selected.";
        if (!booking.value.roomId) return "Please select a Room.";
        
        // Check max occupancy
        if (selectedRoom.value && booking.value.guests > selectedRoom.value.max_occupancy) {
            return `Maximum guests for ${selectedRoom.value.room_type} is ${selectedRoom.value.max_occupancy}.`;
        }
    } else if (step === 2) {
        if (!booking.value.firstName) return "First Name is required.";
        if (!booking.value.lastName) return "Last Name is required.";
        if (!booking.value.email) return "Email Address is required.";
        if (!booking.value.phone) return "Phone Number is required.";
        if (!/^\S+@\S+\.\S+$/.test(booking.value.email)) return "Please enter a valid email address.";
    }
    return null;
};

const nextStep = () => {
    const error = validateStep(currentStep.value);
    if (error) {
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Details',
            text: error,
            confirmButtonColor: '#BC9151'
        });
        return;
    }
    if (currentStep.value < 3) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const formatPrice = (price) => {
    return parseFloat(price || 0).toLocaleString();
};

const handleBooking = async () => {
    if (isSuspended.value) {
        Swal.fire({
            icon: 'error',
            title: 'Account Suspended',
            text: 'Your account is suspended and cannot perform bookings. Please contact support.',
            confirmButtonColor: '#d33'
        });
        return;
    }

    if (!state.isAuthenticated) {
        Swal.fire({
            icon: 'info',
            title: 'Sign In Required',
            text: 'You need to be logged in to make a reservation.',
            showCancelButton: true,
            confirmButtonText: 'Sign In',
            confirmButtonColor: '#BC9151'
        }).then((result) => {
            if (result.isConfirmed) {
                router.push('/login?redirect=/booking');
            }
        });
        return;
    }
    
    loading.value = true;
    try {
        const payload = {
            user_id: state.user.id,
            room_id: booking.value.roomId,
            check_in: booking.value.checkIn,
            check_out: booking.value.checkOut
        };

        const response = await axios.post('/api/reservations', payload);
        
        // If the backend returns a PayMongo checkout URL, redirect to it
        if (response.data.checkout_url) {
            window.location.href = response.data.checkout_url;
        } else {
            router.push('/booking/success');
        }
    } catch (err) {
        console.error("Booking failed", err);
        Swal.fire({
            icon: 'error',
            title: 'Booking Failed',
            text: err.response?.data?.message || 'An error occurred while creating your reservation.'
        });
    } finally {
        loading.value = false;
    }
};

// Calendar logic
const days = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

const formatToYMD = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
};
</script>

<template>
    <div class="booking-page bg-cream min-vh-100">
        <!-- Hero Header -->
        <section class="booking-hero py-5">
            <div class="container text-center animate-fade-up">
                <span class="section-label-alt mb-2 d-block">MAKE A RESERVATION</span>
                <h1 class="display-3 fw-bold serif-font mb-3 text-secondary-dark">Book Your Stay</h1>
                <p class="section-description mx-auto text-muted" style="max-width: 500px;">
                    Select your dates and room, then complete your booking in just a few steps.
                </p>
            </div>
        </section>

        <!-- Progress Steps -->
        <div class="container mb-5">
            <div class="booking-steps justify-content-center animate-fade-up delay-1">
                <div class="step-item" :class="{ 'active': currentStep >= 1 }">
                    <div class="step-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <span class="step-label">Select Dates</span>
                </div>
                <div class="step-line"></div>
                <div class="step-item" :class="{ 'active': currentStep >= 2 }">
                    <div class="step-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <span class="step-label">Guest Details</span>
                </div>
                <div class="step-line"></div>
                <div class="step-item" :class="{ 'active': currentStep >= 3 }">
                    <div class="step-icon">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <span class="step-label">Payment</span>
                </div>
            </div>
        </div>

        <div class="container pb-5">
            <div class="row g-4 g-lg-5">
                <!-- Main Form Area -->
                <div class="col-lg-8">
                    <!-- Step 1: Select Dates -->
                    <div v-if="currentStep === 1" class="booking-section animate-fade-up delay-2">
                        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 booking-main-card">
                            <h2 class="serif-font h3 mb-4 text-secondary-dark">Select Your Dates</h2>
                            
                            <div class="row g-4 mb-5">
                                <!-- Check-in Calendar Box -->
                                <div class="col-md-6">
                                    <label class="form-label-custom mb-3">Check-in Date</label>
                                    <div class="calendar-container">
                                        <CalendarPicker 
                                          v-model="booking.checkIn" 
                                          :min-date="formatToYMD(new Date())" 
                                        />
                                        <input type="date" class="form-control form-control-custom mt-3 border-0 bg-light text-center" v-model="booking.checkIn">
                                    </div>
                                 </div>
                                 <!-- Check-out Calendar Box -->
                                 <div class="col-md-6">
                                     <label class="form-label-custom mb-3">Check-out Date</label>
                                     <div class="calendar-container">
                                         <CalendarPicker 
                                          v-model="booking.checkOut" 
                                          :min-date="booking.checkIn || formatToYMD(new Date())" 
                                        />
                                        <input type="date" class="form-control form-control-custom mt-3 border-0 bg-light text-center" v-model="booking.checkOut">
                                    </div>
                                 </div>
                            </div>

                            <div class="select-room-section pt-4 border-top">
                                <h2 class="serif-font h3 mb-4 text-secondary-dark">Select Room</h2>
                                <div class="row g-4">
                                    <div class="col-12">
                                        <label class="form-label-custom">Room Type</label>
                                        <div class="input-group">
                                            <select class="form-select form-control-custom py-3" v-model="booking.roomId" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                                <option value="" disabled>Select a room</option>
                                                <option v-for="room in rooms" :key="room.id" :value="room.id">
                                                    {{ room.room_type }} #{{ room.room_number }} - ₱{{ formatPrice(room.price_per_night) }}
                                                </option>
                                            </select>
                                            <button 
                                                class="btn btn-outline-secondary px-3" 
                                                type="button"
                                                :disabled="!booking.roomId"
                                                @click="openRoomModal"
                                                title="View Room Details"
                                                style="border-color: #eee; border-left: 0;"
                                            >
                                                <i class="bi bi-eye text-primary"></i> <span class="d-none d-sm-inline ms-1 small">View</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Room Details Modal -->
                                    <Teleport to="body">
                                        <div v-if="showRoomModal" class="modal-backdrop fade show" style="z-index: 1050;"></div>
                                        <div v-if="showRoomModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1055;" @click.self="showRoomModal = false">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">
                                                    <div class="modal-header border-0 position-absolute end-0 top-0 z-2 p-3">
                                                        <button type="button" class="btn-close bg-white opacity-75 shadow-sm rounded-circle p-2" @click="showRoomModal = false"></button>
                                                    </div>
                                                    <div class="modal-body p-0" v-if="selectedRoom">
                                                        <div class="row g-0">
                                                            <div class="col-md-6 bg-light" style="min-height: 300px;">
                                                                <img :src="getRoomImage(selectedRoom)" class="w-100 h-100 object-fit-cover" :alt="selectedRoom.room_type">
                                                            </div>
                                                            <div class="col-md-6 p-4 p-lg-5 d-flex flex-column">
                                                                <span class="text-gold text-uppercase letter-spacing-wide small fw-bold mb-2">{{ selectedRoom.room_type }}</span>
                                                                <h3 class="serif-font mb-3">Room #{{ selectedRoom.room_number }}</h3>
                                                                
                                                                <div class="d-flex align-items-center gap-3 mb-4 text-muted small">
                                                                     <span v-if="selectedRoom.room_size"><i class="bi bi-arrows-fullscreen me-1"></i> {{ selectedRoom.room_size }} m²</span>
                                                                     <span v-if="selectedRoom.bed_type"><i class="bi bi-hdd-stack me-1"></i> {{ selectedRoom.bed_type }}</span>
                                                                     <span><i class="bi bi-people me-1"></i> Max {{ selectedRoom.max_occupancy }} Guests</span>
                                                                </div>

                                                                <p class="text-muted mb-4 small flex-grow-1">{{ selectedRoom.description }}</p>
                                                                
                                                                <div class="mb-4" v-if="selectedRoom.amenities && selectedRoom.amenities.length">
                                                                    <p class="small fw-bold text-uppercase text-muted mb-2">Amenities</p>
                                                                    <div class="d-flex flex-wrap gap-2">
                                                                        <span v-for="amenity in selectedRoom.amenities" :key="amenity.id" class="badge bg-light text-dark fw-normal py-2 px-3 rounded-pill border">
                                                                            <i :class="['bi', amenity.icon, 'text-gold me-1']"></i> {{ amenity.name }}
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                                <div class="pt-3 border-top mt-auto">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <small class="text-muted text-uppercase fw-bold">Price per night</small>
                                                                        <span class="h2 serif-font text-gold mb-0">₱{{ formatPrice(selectedRoom.price_per_night) }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </Teleport>
                                    <div class="col-12">
                                        <label class="form-label-custom">Number of Guests</label>
                                        <select class="form-select form-control-custom py-3" v-model="booking.guests">
                                            <option :value="1">1 Guest</option>
                                            <option :value="2" :disabled="selectedRoom && selectedRoom.max_occupancy < 2">2 Guests</option>
                                            <option :value="3" :disabled="selectedRoom && selectedRoom.max_occupancy < 3">3 Guests</option>
                                            <option :value="4" :disabled="selectedRoom && selectedRoom.max_occupancy < 4">4+ Guests</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <button @click="nextStep" :disabled="isSuspended" class="btn btn-gold w-100 py-3 mt-5 text-uppercase fw-bold letter-spacing-wide shadow-none continue-btn">
                                {{ isSuspended ? 'Account Suspended' : 'Continue to Guest Details' }}
                            </button>
                            <p v-if="isSuspended" class="text-danger small text-center mt-3 fw-bold">
                               <i class="bi bi-exclamation-triangle-fill me-1"></i> 
                               Your account is restricted from making new bookings.
                            </p>
                        </div>
                    </div>

                    <!-- Step 2: Guest Details -->
                    <div v-if="currentStep === 2" class="booking-section animate-fade-up">
                        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 booking-main-card">
                            <h2 class="serif-font h3 mb-4 text-secondary-dark">Guest Information</h2>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label-custom">First Name</label>
                                    <input type="text" class="form-control form-control-custom py-3" v-model="booking.firstName" placeholder="Enter first name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">Last Name</label>
                                    <input type="text" class="form-control form-control-custom py-3" v-model="booking.lastName" placeholder="Enter last name">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label-custom">Email Address</label>
                                    <input type="email" class="form-control form-control-custom py-3" v-model="booking.email" placeholder="example@email.com">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label-custom">Phone Number</label>
                                    <input type="tel" class="form-control form-control-custom py-3" v-model="booking.phone" placeholder="+63 9xx xxx xxxx">
                                </div>
                                <div class="col-12">
                                    <label class="form-label-custom">Special Requests (Optional)</label>
                                    <textarea class="form-control form-control-custom" rows="3" placeholder="If you have any special requirements, please let us know..."></textarea>
                                </div>
                            </div>

                            <div class="d-flex gap-3 mt-5">
                                <button @click="prevStep" class="btn btn-outline-dark-custom flex-grow-1 py-3 text-uppercase fw-bold">
                                    Back
                                </button>
                                <button @click="nextStep" :disabled="isSuspended" class="btn btn-gold flex-grow-2 w-100 py-3 text-uppercase fw-bold letter-spacing-wide shadow-none continue-btn">
                                    {{ isSuspended ? 'Restricted' : 'Continue to Payment' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Payment -->
                    <div v-if="currentStep === 3" class="booking-section animate-fade-up">
                        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 booking-main-card">
                            <h2 class="serif-font h3 mb-4 text-secondary-dark">Payment Method</h2>
                            <p class="text-muted mb-4 small mb-5">Select your preferred payment method. You will be redirected to PayMongo's secure portal to complete the transaction.</p>
                            
                            <div class="payment-methods row g-3">
                                <!-- GCash Option -->
                                <div class="col-md-6">
                                    <div 
                                        class="payment-card-method p-4 border rounded-4 h-100" 
                                        :class="{ 'active': booking.paymentMethod === 'gcash' }"
                                        @click="booking.paymentMethod = 'gcash'"
                                    >
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="icon-box-gold">
                                                <i class="bi bi-wallet2 fs-4"></i>
                                            </div>
                                            <div v-if="booking.paymentMethod === 'gcash'" class="check-circle animate-pop">
                                                <i class="bi bi-check-circle-fill text-gold fs-5"></i>
                                            </div>
                                        </div>
                                        <h6 class="fw-bold mb-1">GCash</h6>
                                        <p class="small text-muted mb-0">Faster check-outs with GCash</p>
                                    </div>
                                </div>

                                <!-- Maya Option -->
                                <div class="col-md-6">
                                    <div 
                                        class="payment-card-method p-4 border rounded-4 h-100" 
                                        :class="{ 'active': booking.paymentMethod === 'maya' }"
                                        @click="booking.paymentMethod = 'maya'"
                                    >
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="icon-box-gold">
                                                <i class="bi bi-phone fs-4"></i>
                                            </div>
                                            <div v-if="booking.paymentMethod === 'maya'" class="check-circle animate-pop">
                                                <i class="bi bi-check-circle-fill text-gold fs-5"></i>
                                            </div>
                                        </div>
                                        <h6 class="fw-bold mb-1">Maya</h6>
                                        <p class="small text-muted mb-0">Safe & secure digital wallet</p>
                                    </div>
                                </div>

                                <!-- Card Option -->
                                <div class="col-md-12">
                                    <div 
                                        class="payment-card-method p-4 border rounded-4 h-100" 
                                        :class="{ 'active': booking.paymentMethod === 'card' }"
                                        @click="booking.paymentMethod = 'card'"
                                    >
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="icon-box-gold">
                                                    <i class="bi bi-credit-card fs-4"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-1">Credit / Debit Card</h6>
                                                    <p class="small text-muted mb-0">Visa, Mastercard, JCB supported</p>
                                                </div>
                                            </div>
                                            <div v-if="booking.paymentMethod === 'card'" class="check-circle animate-pop">
                                                <i class="bi bi-check-circle-fill text-gold fs-5"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-3 mt-5">
                                <button @click="prevStep" class="btn btn-outline-dark-custom flex-grow-1 py-3 text-uppercase fw-bold">
                                    Back
                                </button>
                                <button @click="handleBooking" :disabled="loading || isSuspended" class="btn btn-gold flex-grow-2 w-100 py-3 text-uppercase fw-bold letter-spacing-wide shadow-none continue-btn">
                                    <template v-if="isSuspended">Account Suspended</template>
                                    <template v-else>{{ loading ? 'Processing...' : `Secure Payment - ₱${formatPrice(totalPrice)}` }}</template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Summary -->
                <div class="col-lg-4">
                    <div class="booking-summary-sidebar sticky-top" style="top: 100px;">
                        <div class="card border-0 shadow-sm rounded-4 p-4 overflow-hidden">
                            <h3 class="serif-font h4 mb-4 text-secondary-dark border-bottom pb-3">Booking Summary</h3>
                            <div class="summary-details">
                                <div class="summary-item mb-3">
                                    <span class="label text-muted small text-uppercase fw-bold">Check-in</span>
                                    <span class="value fs-6 fw-bold text-secondary-dark d-block mt-1">{{ booking.checkIn || '-' }}</span>
                                </div>
                                <div class="summary-item mb-3">
                                    <span class="label text-muted small text-uppercase fw-bold">Check-out</span>
                                    <span class="value fs-6 fw-bold text-secondary-dark d-block mt-1">{{ booking.checkOut || '-' }}</span>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <span class="label text-muted small text-uppercase fw-bold">Nights</span>
                                        <span class="value fw-bold text-secondary-dark d-block mt-1">{{ totalNights || '-' }}</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="label text-muted small text-uppercase fw-bold">Guests</span>
                                        <span class="value fw-bold text-secondary-dark d-block mt-1">{{ booking.guests }} {{ booking.guests > 1 ? 'Guests' : 'Guest' }}</span>
                                    </div>
                                </div>
                                <div class="summary-item v-if" v-if="selectedRoom">
                                    <span class="label text-muted small text-uppercase fw-bold">Room</span>
                                    <span class="value fw-bold text-secondary-dark d-block mt-1">{{ selectedRoom.room_type }} #{{ selectedRoom.room_number }}</span>
                                </div>
                                
                                <div class="total-section mt-4 pt-4 border-top">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="h6 mb-0 text-muted">Estimated Total</span>
                                        <span class="h4 mb-0 serif-font text-gold fw-bold">₱{{ formatPrice(totalPrice) }}</span>
                                    </div>
                                    <p class="small text-muted text-center mt-3">Taxes and fees included</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 p-4 rounded-4 bg-white shadow-sm border border-light animate-fade-up">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box-gold" style="width: 40px; height: 40px;">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">100% Secure</h6>
                                    <span class="small text-muted">Your data is safe with us</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.booking-page {
    line-height: 1.6;
    padding-top: 100px;
}

.section-label-alt {
    color: var(--primary-gold);
    letter-spacing: 4px;
    font-size: 0.8rem;
    font-weight: 700;
}

.booking-steps {
    display: flex;
    align-items: center;
}

.step-item {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    background: #f0f0f0;
    border-radius: var(--radius-full);
    transition: all var(--transition-normal);
}

.step-item.active {
    background: var(--primary-gold);
}

.step-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    color: #888;
}

.step-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: #888;
    white-space: nowrap;
}

.step-item.active .step-icon,
.step-item.active .step-label {
    color: white;
}

.step-line {
    height: 1px;
    background-color: #ddd;
    flex-grow: 1;
    max-width: 60px;
    margin: 0 1rem;
}

@media (max-width: 992px) {
    .booking-steps {
        overflow-x: auto;
        justify-content: flex-start !important;
        padding-bottom: 1rem;
    }
}

/* Calendar Mock UI */
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}

.calendar-head {
    text-align: center;
    font-size: 0.75rem;
    font-weight: 700;
    color: #aaa;
    padding: 8px 0;
}

.calendar-date {
    text-align: center;
    padding: 10px 0;
    font-size: 0.85rem;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.2s;
    user-select: none;
}

.calendar-date:hover:not(.empty):not(.disabled) {
    background-color: var(--primary-gold-subtle);
}

.calendar-date.selected {
    background-color: var(--primary-gold) !important;
    color: white !important;
    font-weight: 700;
}

.calendar-date.disabled {
    color: #d1d1d1;
    cursor: not-allowed;
    pointer-events: none;
}

.calendar-date.empty {
    cursor: default;
}

.booking-main-card {
    border: 1px solid rgba(0,0,0,0.03) !important;
}

.continue-btn {
    background: var(--primary-gold-light) !important;
    border-color: var(--primary-gold-light) !important;
    color: #8D6E63 !important; /* Muted brown from screenshot */
}

.continue-btn:hover {
    background: var(--primary-gold) !important;
    color: white !important;
}

.form-control-custom {
    border: 1px solid #eee !important;
}

.payment-card-method {
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
}

.payment-card-method:hover {
    border-color: var(--primary-gold-light) !important;
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(188, 145, 81, 0.05);
}

.payment-card-method.active {
    border-color: var(--primary-gold) !important;
    background: var(--primary-gold-subtle);
}

.animate-pop {
    animation: pop 0.3s cubic-bezier(0.26, 0.53, 0.74, 1.48);
}

@keyframes pop {
    0% { transform: scale(0.5); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.icon-box-gold {
    background: var(--primary-gold-subtle);
    color: var(--primary-gold);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

.flex-grow-2 {
    flex-grow: 2;
}

.booking-summary-sidebar .card {
    border: 1px solid rgba(0,0,0,0.03) !important;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
