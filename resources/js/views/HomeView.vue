<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

import Swal from 'sweetalert2';

const router = useRouter();

// Get today's date in YYYY-MM-DD format for min date constraint
const today = computed(() => {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
});

// Real API Data
const rooms = ref([]);
const amenities = ref([]);
const loadingRooms = ref(true);
const checking = ref(false);

const fetchFeaturedRooms = async () => {
    loadingRooms.value = true;
    try {
        const response = await axios.get('/api/rooms');
        // Take first 3 rooms for the homepage features
        rooms.value = response.data.slice(0, 3);
    } catch (error) {
        console.error('Failed to fetch rooms:', error);
    } finally {
        loadingRooms.value = false;
    }
};

const fetchAmenities = async () => {
    try {
        const response = await axios.get('/api/amenities');
        // Only show active amenities on the homepage
        amenities.value = response.data.filter(a => a.is_active);
    } catch (error) {
        console.error('Failed to fetch amenities:', error);
    }
};

onMounted(() => {
    fetchFeaturedRooms();
    fetchAmenities();
});

// Testimonials Data
const testimonials = ref([
  {
    name: 'Margette Revelo',
    role: 'Business Traveler',
    image: '/images/margette.png',
    text: 'The staff was incredibly welcoming and the room was spotless. Perfect location for my business trip. Will definitely book again!',
    rating: 5
  },
  {
    name: 'RJ Estodillo',
    role: 'Family Vacation',
    image: '/images/avatars/testimonial-male.jpg',
    text: 'Our family had an amazing stay. The suite was spacious, clean, and had everything we needed. The kids loved it!',
    rating: 5
  },
  {
    name: 'Nico Monderno',
    role: 'Solo Traveler',
    image: '/images/avatars/testimonial-female.jpg',
    text: 'Best value for money in the area. The breakfast was delicious and the WiFi was fast. Highly recommended!',
    rating: 5
  }
]);

// Booking Form Data
const booking = ref({
  checkIn: '',
  checkOut: '',
  guests: '1 Adult'
});

const handleCheckAvailability = async () => {
    if (!booking.value.checkIn || !booking.value.checkOut) {
         Swal.fire({
            icon: 'warning',
            title: 'Dates Required',
            text: 'Please select check-in and check-out dates.',
            confirmButtonColor: '#BC9151'
        });
        return;
    }

    checking.value = true;
    try {
        const response = await axios.get('/api/rooms', {
             params: {
                check_in: booking.value.checkIn,
                check_out: booking.value.checkOut
            }
        });
        
        const availableRooms = response.data.filter(r => r.status === 'available');
        
        if (availableRooms.length > 0) {
             Swal.fire({
                icon: 'success',
                title: 'Rooms Available!',
                text: `We found ${availableRooms.length} available room(s) for your dates.`,
                showCancelButton: true,
                confirmButtonText: 'Book Now',
                cancelButtonText: 'Close',
                confirmButtonColor: '#BC9151',
                cancelButtonColor: '#718096',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    router.push({
                        path: '/book-now',
                        query: {
                            checkIn: booking.value.checkIn,
                            checkOut: booking.value.checkOut,
                            guests: booking.value.guests
                        }
                    });
                }
            });
        } else {
             Swal.fire({
                icon: 'error',
                title: 'No Availability',
                text: 'Sorry, we are fully booked for these dates.',
                confirmButtonColor: '#BC9151'
            });
        }
    } catch (e) {
        console.error(e);
        Swal.fire({
            icon: 'error',
            title: 'System Error',
            text: 'Unable to check availability. Please try again.',
            confirmButtonColor: '#BC9151'
        });
    } finally {
        checking.value = false;
    }
};

const getRoomImage = (room) => {
    if (room.image) return room.image;
    
    // Fallback images based on room type
    const type = room.room_type.toLowerCase();
    if (type.includes('suite')) return '/images/unsplash/suite-room.jpg';
    if (type.includes('deluxe')) return '/images/unsplash/deluxe-room.jpg';
    return '/images/unsplash/standard-room.jpg';
};
const formatIconClass = (icon) => {
  if (!icon) return '';
  return icon.startsWith('bi-') ? icon : `bi-${icon}`;
};
</script>

<template>
  <div class="home-page">
    <!-- ========== HERO SECTION ========== -->
    <section class="hero-section">
      <div class="hero-overlay"></div>
      <div class="container position-relative z-1 pt-5">
        <div class="row align-items-center min-vh-100">
          <!-- Left Content -->
          <div class="col-lg-7 hero-content">
            <!-- Trust Badge -->
            <div class="trust-badge animate-fade-up d-inline-flex align-items-center gap-3 bg-white-10 p-2 px-3 rounded-pill mb-4 border border-white-10 backdrop-blur">
              <span class="stars-gold">
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
              </span>
              <span class="trust-text text-white small fw-bold tracking-wide">Trusted by 500+ Guests</span>
            </div>
            
            <!-- Headline -->
            <h1 class="hero-title animate-fade-up delay-1 text-white mb-4">
              Your Home  <br class="d-none d-lg-block">
              <span class="text-gold-italic">Away From Home</span>
            </h1>
            
            <!-- Subheadline -->
            <p class="hero-subtitle animate-fade-up delay-2 leading-relaxed mb-5">
              Experience warm Filipino hospitality at EME's Apartelle. 
              Comfortable rooms, modern amenities, and a prime location await you.
            </p>
            
            <!-- CTA Buttons -->
            <div class="hero-cta animate-fade-up delay-3 d-flex flex-wrap gap-4 align-items-center">
              <router-link to="/rooms" class="btn btn-gold btn-lg px-5 py-3 rounded-pill fw-bold text-uppercase tracking-wider shadow-gold hover-scale">
                <i class="bi bi-calendar-check me-2"></i> Book Your Stay
              </router-link>
              <div class="location-badge d-flex align-items-center gap-2 text-white-50 border-start border-white-20 ps-4">
                <div class="icon-circle border border-white-20 rounded-circle p-2 text-gold">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div class="d-flex flex-column">
                    <span class="small text-gold fw-bold text-uppercase">Located at</span>
                    <span class="text-white small">General Luna, Philippines </span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Right Content - Booking Card -->
          <div class="col-lg-5 animate-fade-up delay-2">
            <div class="booking-card-premium glass-morph shadow-2xl overflow-hidden">
              <!-- Decorative Top Bar -->
              <div class="bg-gold" style="height: 4px; width: 100%;"></div>
              
              <div class="p-4 p-md-5">
                <div class="mb-4 text-center">
                  <span class="text-gold small fw-bold text-uppercase tracking-widest mb-1 d-block">Book Form</span>
                  <h3 class="booking-card-title serif-font mb-2">Check Availability</h3>
                  <div class="divider mx-auto bg-gold-subtle" style="width: 50px; height: 2px;"></div>
                </div>

                <form @submit.prevent="handleCheckAvailability">
                  <div class="row g-3 mb-4">
                    <div class="col-md-6">
                      <label class="form-label-premium">Check-in</label>
                      <div class="input-wrapper-premium">
                        <i class="bi bi-calendar-event input-icon"></i>
                        <input type="date" class="form-control-premium" v-model="booking.checkIn" :min="today">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label-premium">Check-out</label>
                      <div class="input-wrapper-premium">
                        <i class="bi bi-calendar-check input-icon"></i>
                        <input type="date" class="form-control-premium" v-model="booking.checkOut" :min="today">
                      </div>
                    </div>
                  </div>

                  <div class="mb-5">
                    <label class="form-label-premium">Total Guests</label>
                    <div class="input-wrapper-premium">
                      <i class="bi bi-people input-icon"></i>
                      <select class="form-select form-control-premium" v-model="booking.guests">
                        <option>1 Adult</option>
                        <option>2 Adults</option>
                        <option>3 Adults</option>
                        <option>Family (4+ Guests)</option>
                      </select>
                    </div>
                  </div>

                  <button type="submit" class="btn btn-gold-premium w-100 py-3 fw-bold text-uppercase tracking-widest shadow-gold d-flex align-items-center justify-content-center gap-2" :disabled="checking">
                    <span v-if="checking" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    <span>{{ checking ? 'Checking...' : 'Check Availability' }}</span>
                    <i v-if="!checking" class="bi bi-arrow-right"></i>
                  </button>
                  
                  <p class="text-center text-muted small mt-4 mb-0 opacity-75">
                    <i class="bi bi-shield-check text-success me-1"></i> Best Rate Guaranteed
                  </p>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ========== FEATURED ROOMS SECTION ========== -->
    <section class="rooms-section py-7 bg-cream position-relative">
      <div class="container">
        <div class="section-header text-center mb-5 animate-fade-up">
          <div class="designer-label mx-auto mb-3">
            <span class="label-dot"></span>
            <span class="label-text">EME'S APARTELLE</span>
            <span class="label-dot"></span>
          </div>
          <h2 class="section-title display-4 serif-font text-secondary-dark">Available Rooms</h2>
          <p class="section-description mx-auto mt-3 text-muted" style="max-width: 700px;">
            Check out our clean and comfortable rooms for your stay.
          </p>
        </div>

        <div v-if="loadingRooms" class="row g-4 justify-content-center">
          <div v-for="n in 3" :key="'skel-' + n" class="col-lg-4 col-md-6">
            <div class="room-card-premium bg-white rounded-5 overflow-hidden border-0" style="box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
              <div class="skeleton-image skeleton-shimmer" style="height: 280px;"></div>
              <div class="p-4 text-center">
                <div class="skeleton-line skeleton-shimmer mx-auto mb-2" style="width: 50%; height: 10px;"></div>
                <div class="skeleton-line skeleton-shimmer mx-auto mb-3" style="width: 60%; height: 20px;"></div>
                <div class="d-flex justify-content-center gap-3 mb-4">
                  <div class="skeleton-line skeleton-shimmer" style="width: 60px; height: 12px;"></div>
                  <div class="skeleton-line skeleton-shimmer" style="width: 60px; height: 12px;"></div>
                </div>
                <div class="skeleton-btn skeleton-shimmer mx-auto" style="width: 100%; height: 38px; border-radius: 50px;"></div>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="row g-4 justify-content-center">
          <div v-for="(room, index) in rooms" :key="room.id" class="col-lg-4 col-md-6 animate-fade-up" :style="{ animationDelay: index * 100 + 'ms' }">
            <div class="room-card-premium card-hover h-100 bg-white rounded-5 shadow-sm overflow-hidden border-0">
              <div class="room-card-image">
                <img :src="getRoomImage(room)" :alt="room.room_type" loading="lazy">
                <span class="room-badge" :class="room.status === 'available' ? 'bg-success text-white' : 'bg-danger text-white'">
                    {{ room.status === 'available' ? 'Available' : 'Occupied' }}
                </span>
              </div>
              <div class="room-card-body p-4 text-center">
                <span class="text-gold small fw-bold text-uppercase tracking-widest mb-2 d-block">{{ room.room_type }}</span>
                <h4 class="room-card-title serif-font mb-3">Room #{{ room.room_number }}</h4>
                <div class="d-flex justify-content-center gap-3 mb-4 text-muted small">
                    <span v-if="room.room_size"><i class="bi bi-aspect-ratio me-1"></i>{{ room.room_size }} m²</span>
                    <span><i class="bi bi-people me-1"></i>Max {{ room.max_occupancy }}</span>
                </div>
                <router-link :to="`/rooms/${room.id}`" class="btn btn-gold w-100 py-2 rounded-pill fw-bold text-uppercase small">
                  View Room
                </router-link>
              </div>
            </div>
          </div>
        </div>
        
        <div class="text-center mt-5" v-if="rooms.length > 0">
           <router-link to="/rooms" class="btn btn-outline-dark-custom px-5 py-3 rounded-pill fw-bold text-uppercase tracking-wider small">
             View All Rooms
           </router-link>
        </div>
      </div>
    </section>

    <!-- ========== AMENITIES SECTION ========== -->
    <section class="amenities-section py-7 bg-white">
      <div class="container">
        <div class="section-header text-center mb-5 animate-fade-up">
          <div class="designer-label mx-auto mb-3">
            <span class="label-dot"></span>
            <span class="label-text">AMENITIES</span>
            <span class="label-dot"></span>
          </div>
          <h2 class="section-title display-4 serif-font text-secondary-dark">What We Offer</h2>
          <p class="section-description mx-auto mt-3 text-muted" style="max-width: 650px;">
            We have everything you need for a good stay.
          </p>
        </div>

        <div class="row g-4">
          <div v-for="(amenity, index) in amenities" :key="index" class="col-lg-3 col-md-6 animate-fade-up" :style="{ animationDelay: index * 50 + 'ms' }">
            <div class="amenity-card-premium hover-lift text-center p-4 rounded-4 bg-light border-0">
              <div class="amenity-icon icon-box-gold mx-auto mb-3">
                <i :class="['bi', formatIconClass(amenity.icon)]"></i>
              </div>
              <h5 class="amenity-title fw-bold">{{ amenity.name }}</h5>
              <p class="amenity-description small opacity-75">{{ amenity.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ========== TESTIMONIALS SECTION ========== -->
    <section class="testimonials-section">
      <div class="container">
        <div class="section-header text-center animate-fade-up">
          <span class="section-label text-primary-gold">Testimonials</span>
          <h2 class="section-title display-4 text-white serif-font">What Our Guests Say</h2>
          <p class="section-description text-white-50">
            Don't just take our word for it. Here's what our guests have to say about their stay.
          </p>
        </div>

        <div class="row g-4">
          <div v-for="(testimonial, index) in testimonials" :key="index" class="col-md-4 animate-fade-up" :style="{ animationDelay: index * 100 + 'ms' }">
            <div class="testimonial-card-premium hover-lift glass-panel p-4 rounded-4">
              <div class="quote-icon text-gold fs-1 mb-2">"</div>
              <p class="testimonial-text text-white-50 font-italic small mb-4 line-clamp-4">"{{ testimonial.text }}"</p>
              <div class="testimonial-author d-flex align-items-center gap-3">
                <img :src="testimonial.image" :alt="testimonial.name" class="testimonial-avatar rounded-circle border border-2 border-gold" width="50" height="50" loading="lazy">
                <div>
                  <h6 class="testimonial-name text-white mb-0 fw-bold">{{ testimonial.name }}</h6>
                  <span class="testimonial-role text-gold small">{{ testimonial.role }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ========== CTA SECTION ========== -->
    <section class="cta-section py-7 bg-white position-relative overflow-hidden">
      <!-- Decorative background blur -->
      <div class="cta-blur-blob"></div>
      
      <div class="container position-relative z-1">
        <div class="cta-content text-center animate-fade-up">
          <div class="designer-label mx-auto mb-4 justify-content-center">
            <span class="label-dot"></span>
            <span class="label-text">YOUR JOURNEY AWAITS</span>
            <span class="label-dot"></span>
          </div>
          <h2 class="cta-title display-3 serif-font text-secondary-dark mb-4">
            Ready to book your <span class="text-gold-italic">stay?</span>
          </h2>
          <p class="cta-description mx-auto mb-5 text-muted" style="max-width: 600px;">
            Contact us or book online to reserve your room today.
          </p>
          <div class="cta-buttons d-flex flex-wrap justify-content-center gap-4">
            <router-link to="/rooms" class="btn btn-gold btn-lg px-5 py-3 rounded-pill fw-bold text-uppercase tracking-wider shadow-gold hover-scale">
               Check Availability
            </router-link>
            <a href="tel:+639123456789" class="btn btn-outline-dark-custom btn-lg px-5 py-3 rounded-pill fw-bold text-uppercase tracking-wider small">
              Call Us
            </a>
          </div>
          <div class="cta-features mt-5 d-flex flex-wrap justify-content-center gap-lg-5 gap-3 text-muted small fw-bold">
            <span><i class="bi bi-patch-check text-success me-2"></i>Instant Confirmation</span>
            <span><i class="bi bi-patch-check text-success me-2"></i>Secure Payment</span>
            <span><i class="bi bi-patch-check text-success me-2"></i>Zero Hidden Fees</span>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
/* ========== HERO SECTION ========== */
.hero-section {
  background: url('/images/unsplash/hero-resort.jpg') center/cover no-repeat;
  position: relative;
  min-height: 100vh;
  display: flex;
  align-items: center;
  background-attachment: fixed;
  padding-top: 80px; /* Offset for fixed navbar */
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to right, rgba(26, 38, 52, 0.95) 0%, rgba(26, 38, 52, 0.7) 50%, rgba(26, 38, 52, 0.4) 100%);
}

.hero-title {
  font-size: 5rem;
  font-weight: 700;
  line-height: 1;
  margin-bottom: 2rem;
  letter-spacing: -2px;
  text-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.hero-subtitle {
  font-size: 1.25rem;
  color: rgba(255, 255, 255, 0.8);
  max-width: 550px;
  margin-bottom: 3.5rem;
  font-weight: 400;
}

/* ========== DESIGNER LABEL ========== */
.designer-label {
  display: flex;
  align-items: center;
  gap: 1rem;
  width: fit-content;
  justify-content: center;
}

.label-dot {
  width: 5px;
  height: 5px;
  background-color: var(--primary-gold);
  border-radius: 50%;
  display: inline-block;
  opacity: 1;
}

.label-text {
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 5px;
  color: var(--primary-gold);
  text-transform: uppercase;
}

/* ========== CTA SECTION ========== */
.cta-blur-blob {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(188, 145, 81, 0.08) 0%, transparent 70%);
  filter: blur(60px);
  z-index: 0;
  pointer-events: none;
}

/* ========== SPACING & UTILITIES ========== */
.py-7 {
  padding-top: 8rem;
  padding-bottom: 8rem;
}

@media (max-width: 767.98px) {
  .py-7 {
    padding-top: 5rem;
    padding-bottom: 5rem;
  }
}

.bg-white-10 { background-color: rgba(255, 255, 255, 0.1); }
.border-white-10 { border-color: rgba(255, 255, 255, 0.1) !important; }
.border-white-20 { border-color: rgba(255, 255, 255, 0.2) !important; }
.backdrop-blur { backdrop-filter: blur(12px); }

/* ========== PREMIUM BOOKING CARD ========== */
.glass-morph {
  background: rgba(255, 255, 255, 0.94);
  backdrop-filter: blur(25px);
  border: 1px solid rgba(255, 255, 255, 0.5);
  border-radius: 2.5rem;
}

.booking-card-premium {
  transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.form-label-premium {
  display: block;
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 1.5px;
  margin-bottom: 0.75rem;
}

.input-wrapper-premium {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 1.25rem;
  color: var(--primary-gold);
  font-size: 1.1rem;
  pointer-events: none;
}

.form-control-premium {
  width: 100%;
  padding: 1.1rem 1rem 1.1rem 3.25rem;
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 1.1rem;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.3s ease;
  color: #1e293b;
}

.form-control-premium:focus {
  background-color: white;
  border-color: var(--primary-gold);
  box-shadow: 0 10px 25px rgba(188, 145, 81, 0.12);
  outline: none;
}

.btn-gold-premium {
  background: linear-gradient(135deg, var(--primary-gold) 0%, #a37d45 100%);
  color: white;
  border: none;
  border-radius: 1.1rem;
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-gold-premium:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 40px rgba(188, 145, 81, 0.4);
  filter: brightness(1.1);
}

.shadow-2xl {
    box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.25);
}

/* Room Cards */
.room-card-premium {
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.room-card-premium:hover {
    transform: translateY(-12px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.12);
}

.room-card-image {
    height: 280px;
    overflow: hidden;
    position: relative;
}

.room-card-image img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 1.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.room-card-premium:hover .room-card-image img {
    transform: scale(1.1);
}

.room-badge {
    position: absolute;
    top: 1.25rem; right: 1.25rem;
    padding: 0.4rem 1.25rem;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    border-radius: 50px;
    letter-spacing: 1px;
    z-index: 2;
}

/* Amenities Card */
.amenity-card-premium {
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.amenity-card-premium:hover {
    background: white !important;
    box-shadow: 0 20px 40px rgba(188, 145, 81, 0.12);
    transform: translateY(-8px);
}

.icon-box-gold {
    width: 64px; height: 64px;
    display: flex; align-items: center; justify-content: center;
    background: var(--primary-gold-subtle);
    color: var(--primary-gold);
    border-radius: 20px;
    font-size: 1.6rem;
}

/* Testimonials */
.testimonials-section {
    background-color: #0f172a;
    padding: 8rem 0;
}

.testimonial-card-premium {
    background: rgba(255,255,255, 0.03) !important;
    border: 1px solid rgba(255,255,255,0.06) !important;
    backdrop-filter: blur(15px);
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.testimonial-card-premium:hover {
    background: rgba(255,255,255, 0.06) !important;
    transform: translateY(-5px);
}

.testimonial-avatar {
    object-fit: cover;
}

.line-clamp-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@media (max-width: 991.98px) {
  .hero-title {
    font-size: 4rem;
  }
}

@media (max-width: 767.98px) {
  .hero-section {
    min-height: auto;
    padding-bottom: 4rem;
  }
  .hero-title {
    font-size: 3rem;
  }
}

/* Utilities */
.hover-scale { transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.hover-scale:hover { transform: scale(1.05); }
.shadow-gold { box-shadow: 0 10px 30px rgba(188, 145, 81, 0.4); }

/* ========== SKELETON LOADING ========== */
.skeleton-image {
    background: #e2e8f0;
}

.skeleton-line {
    background: #e2e8f0;
    border-radius: 8px;
}

.skeleton-btn {
    background: #e2e8f0;
}

.skeleton-shimmer {
    position: relative;
    overflow: hidden;
}

.skeleton-shimmer::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
        90deg,
        transparent 0%,
        rgba(255, 255, 255, 0.5) 50%,
        transparent 100%
    );
    animation: shimmer-sweep 1.5s ease-in-out infinite;
}

@keyframes shimmer-sweep {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
</style>
