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
    roomId: route.query.roomId ? parseInt(route.query.roomId) : '',
    guests: parseGuests(route.query.guests),
    firstName: state.user?.first_name || '',
    lastName: state.user?.last_name || '',
    email: state.user?.email || '',
    phone: state.user?.phone || '',
    address: state.user?.address || '',
    paymentOption: 'full', // Default to full payment
});

const rooms = ref([]);
const loading = ref(true);
const bookedDates = ref([]);

const fetchBookedDates = async () => {
    try {
        let url = '/api/rooms/booked-dates';
        if (booking.value.roomId) {
            url = `/api/rooms/${booking.value.roomId}/booked-dates`;
        }
        
        const response = await axios.get(url);
        
        if (booking.value.roomId) {
            // Room specific: just use the dates directly
            bookedDates.value = response.data;
        } else {
            // Global: find dates where all rooms (Total: 4) are booked
            // We group reservations by date and see if count >= 4
            const dateCounts = {};
            response.data.forEach(res => {
                let start = new Date(res.check_in);
                let end = new Date(res.check_out);
                for (let d = new Date(start); d < end; d.setDate(d.getDate() + 1)) {
                    let dStr = d.toISOString().split('T')[0];
                    dateCounts[dStr] = (dateCounts[dStr] || 0) + 1;
                }
            });

            const fullyBookedRanges = [];
            Object.keys(dateCounts).forEach(dStr => {
                if (dateCounts[dStr] >= 4) {
                    fullyBookedRanges.push({ check_in: dStr, check_out: dStr });
                }
            });
            bookedDates.value = fullyBookedRanges;
        }
    } catch (err) {
        console.error("Error fetching booked dates", err);
    }
};

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

        // Auto-select first available room if none selected and dates are picked
        if (!booking.value.roomId && rooms.value.length > 0 && booking.value.checkIn) {
            booking.value.roomId = rooms.value[0].id;
        }
    } catch (err) {
        console.error("Error fetching rooms", err);
        rooms.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchRooms();
    fetchBookedDates();
});

// Watch for date changes to update available rooms
watch(() => [booking.value.checkIn, booking.value.checkOut], () => {
    if (booking.value.checkIn && booking.value.checkOut) {
        fetchRooms();
    }
});

const selectedRoom = computed(() => {
    return rooms.value.find(r => r.id === parseInt(booking.value.roomId));
});

// Watch for selectedRoom changes to auto-adjust guests based on capacity and min occupancy
watch(selectedRoom, (newRoom) => {
    if (newRoom) {
        // Automatically cap guests based on room occupancy
        if (booking.value.guests > newRoom.max_occupancy) {
            booking.value.guests = newRoom.max_occupancy;
        }
        
        // Auto adjust booking guests to min_occupancy if it is less than the minimum
        if (newRoom.min_occupancy && booking.value.guests < newRoom.min_occupancy) {
            booking.value.guests = newRoom.min_occupancy;
        }
        
        // Special case for Single rooms to be exactly 1 guest
        if (newRoom.room_type.toLowerCase().includes('single')) {
            booking.value.guests = 1;
        }
    }
});

// Watch for guest changes to ensure they stay within selected room's capacity and minimum occupancy constraints
watch(() => booking.value.guests, (newGuests) => {
    if (selectedRoom.value) {
        if (newGuests > selectedRoom.value.max_occupancy) {
            booking.value.guests = selectedRoom.value.max_occupancy;
        }
        if (selectedRoom.value.min_occupancy && newGuests < selectedRoom.value.min_occupancy) {
            booking.value.guests = selectedRoom.value.min_occupancy;
        }
    }
});

// Watch for room change to update booked dates
watch(() => booking.value.roomId, (newId) => {
    fetchBookedDates();
    showAllAmenities.value = false; // Reset toggle on room change
});

const showAllAmenities = ref(false);

const displayedAmenities = computed(() => {
    if (!selectedRoom.value?.amenities) return [];
    if (showAllAmenities.value) {
        return selectedRoom.value.amenities;
    }
    return selectedRoom.value.amenities.slice(0, 6);
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
    if (type.includes('family')) return '/images/unsplash/suite-room.jpg';
    if (type.includes('barkadahan')) return '/images/unsplash/deluxe-room.jpg';
    return '/images/unsplash/standard-room.jpg';
};

const totalNights = computed(() => {
    if (!booking.value.checkIn || !booking.value.checkOut) return 0;
    const start = new Date(booking.value.checkIn);
    const end = new Date(booking.value.checkOut);
    const diff = end - start;
    const nights = diff / (1000 * 60 * 60 * 24);
    return nights > 0 ? nights : 0;
});

const maxCheckOutDate = computed(() => {
    if (!booking.value.checkIn || !bookedDates.value.length) return null;
    const checkInDate = new Date(booking.value.checkIn);
    checkInDate.setHours(0, 0, 0, 0);

    let firstNextDate = null;
    bookedDates.value.forEach(range => {
        const start = new Date(range.check_in || range.start);
        start.setHours(0, 0, 0, 0);
        if (start >= checkInDate) {
            if (!firstNextDate || start < new Date(firstNextDate)) {
                firstNextDate = range.check_in || range.start;
            }
        }
    });

    return firstNextDate ? firstNextDate.split(' ')[0] : null;
});

watch(() => booking.value.checkIn, (newCheckIn) => {
    if (booking.value.checkOut) {
        if (newCheckIn && booking.value.checkOut <= newCheckIn) {
            booking.value.checkOut = '';
        } else if (maxCheckOutDate.value && booking.value.checkOut > maxCheckOutDate.value) {
            booking.value.checkOut = '';
        }
    }
});

const totalPrice = computed(() => {
    if (!selectedRoom.value || totalNights.value <= 0) return 0;
    if (selectedRoom.value.room_type === 'Family Room' || selectedRoom.value.room_type === 'Barkadahan Room') {
        return selectedRoom.value.price_per_head * booking.value.guests * totalNights.value;
    }
    return selectedRoom.value.price_per_night * totalNights.value;
});

const validateStep = (step) => {
    if (step === 1) {
        if (!booking.value.checkIn) return "Please select a Check-in date.";
        if (!booking.value.checkOut) return "Please select a Check-out date.";
        if (totalNights.value <= 0) return "Invalid date range selected.";
        if (!booking.value.roomId) return "Please select a Room.";
        
        // Check for booked dates overlap
        if (bookedDates.value.length > 0) {
            const start = new Date(booking.value.checkIn);
            const end = new Date(booking.value.checkOut);
            start.setHours(0, 0, 0, 0);
            end.setHours(0, 0, 0, 0);

            const isOverlapping = bookedDates.value.some(range => {
                const bStart = new Date(range.check_in);
                const bEnd = new Date(range.check_out);
                bStart.setHours(0, 0, 0, 0);
                bEnd.setHours(0, 0, 0, 0);
                return start < bEnd && end > bStart;
            });

            if (isOverlapping) {
                return "The selected room is already booked for one or more dates in your range.";
            }
        }
        
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
            confirmButtonColor: '#dc3545'
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
            check_out: booking.value.checkOut,
            guests: booking.value.guests,
            payment_option: booking.value.paymentOption
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
                    <!-- Step 1: Select Room and Dates -->
                    <div v-if="currentStep === 1" class="booking-section animate-fade-up delay-2">
                        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 booking-main-card">
                            <h2 class="serif-font h3 mb-4 text-secondary-dark">Select Room</h2>
                            <div class="row g-4 mb-5">
                                <div class="col-12">
                                    <label class="form-label-custom">Room Type</label>
                                    <div class="input-group">
                                        <select class="form-select form-control-custom py-3" v-model="booking.roomId" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                            <option value="" disabled>Select a room</option>
                                            <option v-for="room in rooms" :key="room.id" :value="room.id">
                                                {{ room.room_type }} #{{ room.room_number }} - 
                                                <template v-if="room.room_type === 'Family Room' || room.room_type === 'Barkadahan Room'">
                                                    ₱{{ formatPrice(room.price_per_head) }}/head/night
                                                </template>
                                                <template v-else>
                                                    ₱{{ formatPrice(room.price_per_night) }}/night
                                                </template>
                                            </option>
                                        </select>
                                        <button 
                                            v-if="booking.roomId"
                                            class="btn btn-outline-secondary px-3" 
                                            type="button"
                                            @click="booking.roomId = ''"
                                            title="Clear Selection"
                                            style="border-color: #eee; border-left: 0;"
                                        >
                                            <i class="bi bi-x-lg text-danger"></i> <span class="d-none d-sm-inline ms-1 small text-danger">Clear</span>
                                        </button>
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

                                <!-- Selected Room Details & Amenities Preview -->
                                <div v-if="selectedRoom" class="col-12 mt-4 animate-fade-up">
                                    <div class="card border border-light-subtle rounded-4 p-4 shadow-sm" style="background-color: var(--bg-light);">
                                        <div class="row g-4 align-items-center">
                                            <div class="col-md-5">
                                                <div class="position-relative overflow-hidden rounded-3 shadow-sm" style="height: 180px;">
                                                    <img :src="getRoomImage(selectedRoom)" class="w-100 h-100 object-fit-cover animate-fade-in" :alt="selectedRoom.room_type">
                                                    <div class="position-absolute top-0 start-0 m-2">
                                                        <span class="badge bg-gold text-white px-3 py-2 rounded-pill shadow-sm">
                                                            Room #{{ selectedRoom.room_number }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <span class="text-gold text-uppercase letter-spacing-wide small fw-bold mb-1 d-block">{{ selectedRoom.room_type }}</span>
                                                <h4 class="serif-font mb-2 text-secondary-dark">Room #{{ selectedRoom.room_number }}</h4>
                                                
                                                <div class="d-flex align-items-center gap-3 mb-3 text-muted small">
                                                     <span v-if="selectedRoom.room_size"><i class="bi bi-arrows-fullscreen text-gold me-1"></i> {{ selectedRoom.room_size }} m²</span>
                                                     <span v-if="selectedRoom.bed_type"><i class="bi bi-hdd-stack text-gold me-1"></i> {{ selectedRoom.bed_type }}</span>
                                                     <span><i class="bi bi-people text-gold me-1"></i> Max {{ selectedRoom.max_occupancy }} Guests</span>
                                                </div>
                                                
                                                <p class="text-muted small mb-3 line-clamp-2" style="font-size: 0.85rem; line-height: 1.5;">{{ selectedRoom.description }}</p>
                                                
                                                <div v-if="selectedRoom.amenities && selectedRoom.amenities.length">
                                                    <p class="small fw-bold text-uppercase text-muted mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Included Amenities</p>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <span v-for="amenity in displayedAmenities" :key="amenity.id" class="badge bg-white text-dark fw-normal py-2 px-3 rounded-pill border small">
                                                            <i :class="['bi', amenity.icon, 'text-gold me-1']"></i> {{ amenity.name }}
                                                        </span>
                                                    </div>
                                                    
                                                    <button 
                                                        v-if="selectedRoom.amenities.length > 6" 
                                                        @click="showAllAmenities = !showAllAmenities" 
                                                        class="btn btn-link text-gold p-0 mt-3 small fw-bold text-decoration-none d-inline-flex align-items-center gap-1 shadow-none"
                                                        style="font-size: 0.75rem; border: none; background: transparent;"
                                                    >
                                                        <template v-if="showAllAmenities">
                                                            <span>Hide amenities</span> <i class="bi bi-chevron-up"></i>
                                                        </template>
                                                        <template v-else>
                                                            <span>View all amenities (+{{ selectedRoom.amenities.length - 6 }})</span> <i class="bi bi-chevron-down"></i>
                                                        </template>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
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
                                                                    <small class="text-muted text-uppercase fw-bold">
                                                                        {{ (selectedRoom.room_type === 'Family Room' || selectedRoom.room_type === 'Barkadahan Room') ? 'Price per head / night' : 'Price per night' }}
                                                                    </small>
                                                                    <span class="h2 serif-font text-gold mb-0">₱{{ formatPrice((selectedRoom.room_type === 'Family Room' || selectedRoom.room_type === 'Barkadahan Room') ? selectedRoom.price_per_head : selectedRoom.price_per_night) }}</span>
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
                                        <template v-if="selectedRoom">
                                            <option v-for="n in selectedRoom.max_occupancy" :key="n" :value="n" :disabled="selectedRoom.min_occupancy && n < selectedRoom.min_occupancy">
                                                {{ n }} Guest{{ n > 1 ? 's' : '' }}
                                            </option>
                                        </template>
                                        <template v-else>
                                            <option v-for="n in 12" :key="n" :value="n">
                                                {{ n }} Guest{{ n > 1 ? 's' : '' }}
                                            </option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div class="select-date-section pt-4 border-top">
                                <h2 class="serif-font h3 mb-4 text-secondary-dark">Select Your Dates</h2>
                                <div class="row g-4">
                                    <!-- Check-in Calendar Box -->
                                    <div class="col-md-6">
                                        <label class="form-label-custom mb-3">Check-in Date</label>
                                        <div class="calendar-container">
                                            <CalendarPicker 
                                              v-model="booking.checkIn" 
                                              :min-date="formatToYMD(new Date())" 
                                              :disabled-dates="bookedDates"
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
                                              :max-date="maxCheckOutDate"
                                              :disabled-dates="bookedDates"
                                              :is-checkout="true"
                                            />
                                            <input type="date" class="form-control form-control-custom mt-3 border-0 bg-light text-center" v-model="booking.checkOut">
                                        </div>
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
                            <!-- Payment Option Selection -->
                            <h2 class="serif-font h3 mb-4 text-secondary-dark">Payment Option</h2>
                            <div class="payment-options row g-3 mb-5">
                                <!-- Full Payment Option -->
                                <div class="col-md-6">
                                    <div 
                                        class="payment-card-method p-4 border rounded-4 h-100 cursor-pointer" 
                                        :class="{ 'active': booking.paymentOption === 'full' }"
                                        @click="booking.paymentOption = 'full'"
                                    >
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="icon-box-gold">
                                                <i class="bi bi-wallet2 fs-4"></i>
                                            </div>
                                            <div v-if="booking.paymentOption === 'full'" class="check-circle animate-pop">
                                                <i class="bi bi-check-circle-fill text-gold fs-5"></i>
                                            </div>
                                        </div>
                                        <h6 class="fw-bold mb-1">Full Payment</h6>
                                        <p class="small text-muted mb-0">Pay 100% of the total amount now (₱{{ formatPrice(totalPrice) }})</p>
                                    </div>
                                </div>

                                <!-- Half Downpayment Option -->
                                <div class="col-md-6">
                                    <div 
                                        class="payment-card-method p-4 border rounded-4 h-100 cursor-pointer" 
                                        :class="{ 'active': booking.paymentOption === 'half' }"
                                        @click="booking.paymentOption = 'half'"
                                    >
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="icon-box-gold">
                                                <i class="bi bi-cash-stack fs-4"></i>
                                            </div>
                                            <div v-if="booking.paymentOption === 'half'" class="check-circle animate-pop">
                                                <i class="bi bi-check-circle-fill text-gold fs-5"></i>
                                            </div>
                                        </div>
                                        <h6 class="fw-bold mb-1">50% Downpayment</h6>
                                        <p class="small text-muted mb-0">Pay 50% now (₱{{ formatPrice(totalPrice / 2) }}) and settle the rest at the hotel</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Secure Payment Redirection Banner -->
                            <div class="pt-4 border-top mt-5">
                                <div class="alert alert-info border-0 shadow-sm d-flex align-items-start gap-3 rounded-4 p-4 mb-0">
                                    <div class="icon-box-gold mt-1 flex-shrink-0" style="background: rgba(188, 145, 81, 0.1); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #BC9151;">
                                        <i class="bi bi-shield-lock-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-secondary-dark mb-1">Secure External Payment</h6>
                                        <p class="small text-muted mb-0" style="line-height: 1.6;">
                                            To complete your booking, you will be redirected to Xendit's secure payment portal. 
                                            There, you can choose your preferred payment method, including <strong>GCash</strong>, <strong>Maya</strong>, or <strong>Credit/Debit Card</strong> (Visa/Mastercard/JCB).
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-3 mt-5">
                                <button @click="prevStep" class="btn btn-outline-dark-custom flex-grow-1 py-3 text-uppercase fw-bold">
                                    Back
                                </button>
                                <button @click="handleBooking" :disabled="loading || isSuspended" class="btn btn-gold flex-grow-2 w-100 py-3 text-uppercase fw-bold letter-spacing-wide shadow-none continue-btn">
                                    <template v-if="isSuspended">Account Suspended</template>
                                    <template v-else>{{ loading ? 'Processing...' : `Secure Payment - ₱${formatPrice(booking.paymentOption === 'half' ? totalPrice / 2 : totalPrice)}` }}</template>
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
                                         <span class="h6 mb-0 text-muted">Total Room Rate</span>
                                         <span class="h5 mb-0 serif-font text-dark fw-bold">₱{{ formatPrice(totalPrice) }}</span>
                                     </div>
                                     <div class="d-flex justify-content-end mb-3" v-if="selectedRoom && totalNights > 0">
                                         <small class="text-muted italic-text text-end" style="font-size: 0.75rem; font-style: italic;">
                                             <template v-if="selectedRoom.room_type === 'Family Room' || selectedRoom.room_type === 'Barkadahan Room'">
                                                 ((₱{{ formatPrice(selectedRoom.price_per_head) }} × {{ booking.guests }} guest{{ booking.guests > 1 ? 's' : '' }}) × {{ totalNights }} night{{ totalNights > 1 ? 's' : '' }})
                                             </template>
                                             <template v-else>
                                                 (₱{{ formatPrice(selectedRoom.price_per_night) }} × {{ totalNights }} night{{ totalNights > 1 ? 's' : '' }})
                                             </template>
                                         </small>
                                     </div>
                                     <div v-if="booking.paymentOption === 'half'" class="d-flex justify-content-between align-items-center mb-1 text-gold">
                                         <span class="small fw-bold">50% Downpayment Due Now</span>
                                         <span class="h5 mb-0 serif-font fw-bold">₱{{ formatPrice(totalPrice / 2) }}</span>
                                     </div>
                                     <div v-if="booking.paymentOption === 'half'" class="d-flex justify-content-between align-items-center mb-1 text-muted">
                                         <span class="x-small fw-bold">Pay at Hotel Remaining</span>
                                         <span class="small serif-font fw-bold">₱{{ formatPrice(totalPrice / 2) }}</span>
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
