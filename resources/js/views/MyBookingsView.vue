<template>
  <div class="my-bookings-page py-5">
    <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-10">
        <h2 class="serif-font mb-4">My Bookings</h2>
        
        <!-- Skeleton Loading State -->
        <div v-if="loading" class="d-flex flex-column gap-4">
          <div v-for="n in 3" :key="'skel-' + n" class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
              <div class="row g-0">
                <div class="col-md-4">
                  <div class="skeleton-image skeleton-shimmer" style="min-height: 200px;"></div>
                </div>
                <div class="col-md-8 p-4">
                  <div class="d-flex justify-content-between mb-3">
                    <div>
                      <div class="skeleton-line skeleton-shimmer mb-2" style="width: 80px; height: 14px;"></div>
                      <div class="skeleton-line skeleton-shimmer" style="width: 140px; height: 20px;"></div>
                    </div>
                    <div class="text-end">
                      <div class="skeleton-line skeleton-shimmer mb-2" style="width: 40px; height: 10px; margin-left: auto;"></div>
                      <div class="skeleton-line skeleton-shimmer" style="width: 100px; height: 22px; margin-left: auto;"></div>
                    </div>
                  </div>
                  <div class="row g-3 my-3 border-top border-bottom py-3">
                    <div class="col-4">
                      <div class="skeleton-line skeleton-shimmer mb-2" style="width: 70%; height: 8px;"></div>
                      <div class="skeleton-line skeleton-shimmer" style="width: 90%; height: 14px;"></div>
                    </div>
                    <div class="col-4">
                      <div class="skeleton-line skeleton-shimmer mb-2" style="width: 70%; height: 8px;"></div>
                      <div class="skeleton-line skeleton-shimmer" style="width: 90%; height: 14px;"></div>
                    </div>
                    <div class="col-4">
                      <div class="skeleton-line skeleton-shimmer mb-2" style="width: 60%; height: 8px;"></div>
                      <div class="skeleton-line skeleton-shimmer" style="width: 80px; height: 22px; border-radius: 50px;"></div>
                    </div>
                  </div>
                  <div class="d-flex gap-2 mt-2">
                    <div class="skeleton-line skeleton-shimmer" style="width: 110px; height: 34px; border-radius: 50px;"></div>
                    <div class="skeleton-line skeleton-shimmer" style="width: 110px; height: 34px; border-radius: 50px;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="bookings.length === 0" class="card border-0 shadow-sm rounded-4 mb-4">
          <div class="card-body p-5 text-center">
            <div class="mb-4">
              <i class="bi bi-calendar-x display-1 text-muted opacity-25"></i>
            </div>
            <h4 class="fw-bold mb-2">No Bookings Yet</h4>
            <p class="text-muted mb-4">You haven't made any reservations yet. Ready to experience EME's Apartelle?</p>
            <router-link to="/rooms" class="btn btn-gold rounded-pill px-4 py-2 fw-bold text-uppercase">Explore Rooms</router-link>
          </div>
        </div>

        <!-- Bookings List -->
        <div v-else class="d-flex flex-column gap-4">
          <div v-for="booking in bookings" :key="booking.id" class="card border-0 shadow-sm rounded-4 overflow-hidden animate-fade-up">
            <div class="card-body p-0">
              <div class="row g-0">
                <!-- Room Image -->
                <div class="col-md-4 position-relative">
                   <div class="h-100 bg-secondary" style="min-height: 200px;">
                      <img 
                        :src="getRoomImage(booking.room)" 
                        class="w-100 h-100 object-fit-cover position-absolute top-0 start-0" 
                        alt="Room Image"
                        loading="lazy"
                      >
                   </div>
                </div>
                
                <!-- Booking Details -->
                <div class="col-md-8 p-4 d-flex flex-column justify-content-center">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                      <span class="badge bg-gold-subtle text-gold text-uppercase tracking-widest small fw-bold mb-2 d-inline-block">
                        {{ booking.room?.room_type || 'Room' }}
                      </span>
                      <h4 class="serif-font fw-bold mb-1">Room #{{ booking.room?.room_number }}</h4>
                    </div>
                    <div class="text-end">
                      <span class="d-block small text-muted text-uppercase fw-bold">Total</span>
                      <span class="h4 text-gold serif-font fw-bold">₱{{ formatPrice(booking.total_amount) }}</span>
                    </div>
                  </div>

                  <div class="row g-3 my-3 border-top border-bottom py-3">
                    <div class="col-6 col-sm-4">
                      <small class="text-muted d-block text-uppercase fw-bold x-small">Check-in</small>
                      <span class="fw-semibold">{{ formatDate(booking.check_in) }}</span>
                    </div>
                    <div class="col-6 col-sm-4">
                      <small class="text-muted d-block text-uppercase fw-bold x-small">Check-out</small>
                      <span class="fw-semibold">{{ formatDate(booking.check_out) }}</span>
                    </div>
                    <div class="col-6 col-sm-4">
                      <small class="text-muted d-block text-uppercase fw-bold x-small">Status</small>
                      <span :class="getStatusBadgeClass(booking.status)">
                        {{ capitalize(booking.status) }}
                      </span>
                    </div>
                    <!-- Cancellation Reason -->
                    <div v-if="booking.status === 'cancelled' && booking.cancellation_reason" class="col-12 mt-2 pt-2 border-top">
                      <small class="text-danger d-block text-uppercase fw-bold x-small mb-1">
                        <i class="bi bi-info-circle me-1"></i>Cancellation Reason
                      </small>
                      <p class="text-danger mb-0 small bg-danger-subtle p-2 rounded border border-danger-subtle" style="word-break: break-word;">
                        {{ booking.cancellation_reason }}
                      </p>
                    </div>
                    <!-- Cancellation Pending -->
                    <div v-if="booking.status === 'cancellation_pending'" class="col-12 mt-2 pt-2 border-top">
                      <small class="text-warning d-block text-uppercase fw-bold x-small mb-1">
                        <i class="bi bi-hourglass-split me-1"></i>Cancellation Pending Approval
                      </small>
                      <p class="text-warning-emphasis mb-0 small bg-warning-subtle p-2 rounded border border-warning-subtle" style="word-break: break-word;" v-if="booking.cancellation_reason">
                        Reason: {{ booking.cancellation_reason }}
                      </p>
                    </div>
                  </div>

                  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-auto gap-2">
                    <span class="small text-muted mb-2 mb-md-0">
                      <i class="bi bi-clock me-1"></i> Booked on {{ formatDateTime(booking.created_at) }}
                    </span>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 flex-wrap justify-content-start justify-content-md-end align-items-center mt-2 mt-md-0">
                        <!-- Extend Stay Button -->
                        <button 
                            v-if="isExtendable(booking)" 
                            @click="handleExtendStay(booking)" 
                            class="btn btn-sm btn-gold rounded-pill px-3 py-1 fw-bold x-small text-uppercase text-white border-0 shadow-sm"
                            :disabled="loading || state.user?.is_suspended"
                            :title="state.user?.is_suspended ? 'Restricted due to account suspension' : 'Extend your stay'"
                            style="font-size: 0.7rem; font-weight: 700; height: 32px;"
                        >
                            <i class="bi bi-calendar-plus me-1"></i>
                            Extend Stay
                        </button>

                        <!-- Write Review Button -->
                        <button 
                            v-if="booking.status === 'completed' && !booking.review" 
                            @click="handleWriteReview(booking)" 
                            class="btn btn-sm btn-gold rounded-pill px-3 py-1 fw-bold x-small text-uppercase text-white border-0 shadow-sm d-inline-flex align-items-center justify-content-center"
                            style="font-size: 0.7rem; font-weight: 700; height: 32px;"
                        >
                            <i class="bi bi-star-fill me-1"></i>
                            Write a Review
                        </button>

                        <!-- Already Reviewed Badge -->
                        <span 
                            v-if="booking.status === 'completed' && booking.review" 
                            class="badge bg-light text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center justify-content-center"
                            style="font-size: 0.7rem; height: 32px;"
                        >
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Reviewed ({{ booking.review.rating }} ★)
                        </span>

                        <!-- Rebook Now Button -->
                        <router-link 
                            v-if="isPast(booking) && booking.room?.id" 
                            :to="`/rooms/${booking.room.id}`" 
                            class="btn btn-sm btn-gold rounded-pill px-3 py-1 fw-bold x-small text-uppercase text-white border-0 shadow-sm d-inline-flex align-items-center justify-content-center"
                            :title="'Book Room #' + booking.room?.room_number + ' again'"
                            style="font-size: 0.7rem; font-weight: 700; height: 32px; text-decoration: none;"
                        >
                            <i class="bi bi-arrow-repeat me-1"></i>
                            Rebook Now
                        </router-link>

                        <!-- E-Receipt Button -->
                        <button 
                            v-if="booking.payment_status === 'paid' || booking.payment_status === 'partially_paid' || ['confirmed', 'checked-in', 'completed'].includes(booking.status)"
                            @click="handleViewReceipt(booking)" 
                            class="btn btn-sm btn-outline-gold rounded-pill px-3 py-1 fw-bold x-small text-uppercase d-inline-flex align-items-center justify-content-center"
                            style="font-size: 0.7rem; font-weight: 700; height: 32px;"
                            title="View your official E-Receipt"
                        >
                            <i class="bi bi-receipt me-1"></i>
                            E-Receipt
                        </button>

                        <!-- File Dispute Button -->
                        <button 
                            v-if="!getDisputeForBooking(booking.id) && ['confirmed', 'checked-in', 'completed'].includes(booking.status)" 
                            @click="handleFileDispute(booking)" 
                            class="btn btn-sm btn-outline-gold rounded-pill px-3 py-1 fw-bold x-small text-uppercase d-inline-flex align-items-center justify-content-center"
                            :disabled="loading || state.user?.is_suspended"
                            :title="state.user?.is_suspended ? 'Restricted due to account suspension' : 'File a dispute regarding this booking'"
                            style="font-size: 0.7rem; font-weight: 700; height: 32px;"
                        >
                            <i class="bi bi-exclamation-octagon me-1"></i>
                            File Dispute
                        </button>

                        <!-- Clickable Dispute Status Badge -->
                        <span 
                            v-if="getDisputeForBooking(booking.id)" 
                            @click="handleViewDisputeDetails(getDisputeForBooking(booking.id))" 
                            :class="getDisputeStatusClass(getDisputeForBooking(booking.id).status)"
                            class="d-inline-flex align-items-center justify-content-center cursor-pointer hover-shadow transition-all"
                            style="cursor: pointer; height: 32px; font-size: 0.7rem; border-radius: 50rem;"
                            title="Click to view dispute details"
                        >
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            {{ formatDisputeStatusLabel(getDisputeForBooking(booking.id).status) }}
                        </span>

                        <!-- Cancel Booking Button -->
                        <button 
                            v-if="isCancellable(booking)"
                            @click="handleCancelBooking(booking)" 
                            class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold x-small text-uppercase d-inline-flex align-items-center justify-content-center"
                            :disabled="cancelling === booking.id || state.user?.is_suspended"
                            :title="state.user?.is_suspended ? 'Restricted due to account suspension' : 'Cancel this booking'"
                            style="font-size: 0.7rem; font-weight: 700; height: 32px;"
                        >
                            <span v-if="cancelling === booking.id" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            <i v-else class="bi bi-x-circle me-1"></i>
                            {{ state.user?.is_suspended ? 'Suspended' : 'Cancel Booking' }}
                        </button>
                    </div>
                  </div>
                  <p v-if="state.user?.is_suspended" class="text-danger x-small mt-2 mb-0 fw-bold">
                    <i class="bi bi-lock-fill me-1"></i> Actions restricted due to account suspension.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useAuth } from '../store/auth';

const { state } = useAuth();

const bookings = ref([]);
const disputes = ref([]);
const loading = ref(true);
const cancelling = ref(null);

const fetchBookings = async () => {
    loading.value = true;
    try {
        const [bookingsRes, disputesRes] = await Promise.all([
            axios.get('/api/reservations'),
            axios.get('/api/disputes')
        ]);
        bookings.value = bookingsRes.data;
        disputes.value = disputesRes.data;
    } catch (error) {
        console.error('Error fetching data:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatDateTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const datePart = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
    const timePart = date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    return `${datePart} at ${timePart}`;
};

const formatPrice = (price) => {
    return parseFloat(price || 0).toLocaleString();
};

const capitalize = (s) => {
    if (!s) return '';
    return s.replace(/[-_]/g, ' ').split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};

const getStatusBadgeClass = (status) => {
    const base = 'badge rounded-pill px-3 py-1 fw-bold small ';
    switch(status) {
        case 'pending': return base + 'bg-warning text-dark';
        case 'confirmed': return base + 'bg-success text-white';
        case 'checked-in': return base + 'bg-info text-white';
        case 'cancelled': return base + 'bg-danger text-white';
        case 'cancellation_pending': return base + 'bg-warning text-dark';
        case 'completed': return base + 'bg-secondary text-white';
        default: return base + 'bg-secondary text-white';
    }
};

const getRoomImage = (room) => {
    if (room?.image) return room.image;
    // Fallback logic matches other views
    const type = (room?.room_type || '').toLowerCase();
    if (type.includes('suite')) return '/images/unsplash/suite-room.jpg';
    return '/images/unsplash/standard-room.jpg';
};

const isCancellable = (booking) => {
    // Cancellable only within 1 hour of booking
    if (booking.created_at) {
        const createdAt = new Date(booking.created_at);
        const now = new Date();
        const diffInHours = (now - createdAt) / (1000 * 60 * 60);
        if (diffInHours > 1) {
            return false;
        }
    }

    if (booking.status === 'pending') return true;
    
    if (booking.status === 'confirmed') {
        const checkInDate = new Date(booking.check_in);
        const now = new Date();
        const diffInHours = (checkInDate - now) / (1000 * 60 * 60);
        return diffInHours > 24; // 24-hour cutoff
    }
    
    return false;
};

const handleCancelBooking = async (booking) => {
    let reason = null;

    if (booking.status === 'confirmed') {
        const { value: text, isDismissed } = await Swal.fire({
            title: 'Reason for Cancelling',
            html: `
                <div class="text-start">
                    <p class="small text-muted mb-3">Please let us know why you are cancelling your paid reservation. Refund will be processed according to our policy.</p>
                    <div class="mb-3">
                        <label for="swal-cancellation-select" class="form-label small fw-bold text-muted text-uppercase">Cancellation Reason</label>
                        <select id="swal-cancellation-select" class="form-select py-2">
                            <option value="" disabled selected>Select a reason...</option>
                            <option value="Change of travel plans">Change of travel plans</option>
                            <option value="Travel dates changed">Travel dates changed</option>
                            <option value="Personal emergency / Medical reasons">Personal emergency / Medical reasons</option>
                            <option value="Found better accommodation price/location">Found better accommodation price/location</option>
                            <option value="Other reason">Other reason (please describe below)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="swal-cancellation-details" class="form-label small fw-bold text-muted text-uppercase">Additional Details</label>
                        <textarea id="swal-cancellation-details" class="form-control" rows="3" placeholder="Please provide details (minimum 5 characters if selecting 'Other reason')..."></textarea>
                    </div>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Confirm Cancellation',
            cancelButtonText: 'Keep Booking',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#718096',
            reverseButtons: true,
            preConfirm: () => {
                const selectVal = document.getElementById('swal-cancellation-select').value;
                const detailsVal = document.getElementById('swal-cancellation-details').value.trim();

                if (!selectVal) {
                    Swal.showValidationMessage('Please select a cancellation reason.');
                    return false;
                }

                if (selectVal === 'Other reason' && detailsVal.length < 5) {
                    Swal.showValidationMessage('Please provide cancellation details (minimum 5 characters) for "Other reason".');
                    return false;
                }

                if (detailsVal && detailsVal.length > 0 && detailsVal.length < 5) {
                    Swal.showValidationMessage('Additional details must be at least 5 characters if provided.');
                    return false;
                }

                return detailsVal ? `${selectVal}: ${detailsVal}` : selectVal;
            }
        });

        if (isDismissed) {
            return;
        }
        reason = text;
    } else {
        const result = await Swal.fire({
            title: 'Cancel Reservation?',
            text: "Are you sure you want to cancel this reservation?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#718096',
            confirmButtonText: 'Yes, Cancel Now',
            cancelButtonText: 'Keep Booking',
            reverseButtons: true
        });

        if (!result.isConfirmed) {
            return;
        }
    }

    cancelling.value = booking.id;
    try {
        const response = await axios.post(`/api/reservations/${booking.id}/cancel`, {
            reason: reason
        });
        await Swal.fire({
            icon: 'success',
            title: 'Cancelled',
            text: response.data.message || 'Your reservation has been cancelled.',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
        });
        await fetchBookings();
    } catch (error) {
        console.error('Error cancelling booking:', error);
        Swal.fire({
            icon: 'error',
            title: 'Cancellation Failed',
            text: error.response?.data?.message || 'Something went wrong. Please try again or contact management.'
        });
    } finally {
        cancelling.value = null;
    }
};

const isPast = (booking) => {
    if (!booking.check_out) return false;
    const checkout = new Date(booking.check_out);
    const now = new Date();
    return now > checkout;
};

const isExtendable = (booking) => {
    return (booking.status === 'confirmed' || booking.status === 'checked-in') && !isPast(booking);
};

const handleExtendStay = async (booking) => {
    const minCheckoutStr = (booking.check_out || '').split(' ')[0] || (booking.check_out || '').split('T')[0];
    
    // We get the next day as the default new checkout date
    const nextDay = new Date(minCheckoutStr);
    nextDay.setDate(nextDay.getDate() + 1);
    const defaultNewCheckout = nextDay.toISOString().split('T')[0];

    const { value: newCheckoutDate } = await Swal.fire({
        title: 'Extend Your Stay',
        html: `
            <div class="text-start">
                <p class="small text-muted mb-3">You can extend your stay in Room #${booking.room?.room_number}. Enter your new checkout date below:</p>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Current Checkout</label>
                    <input type="text" class="form-control bg-light border-0 py-2.5 small" value="${formatDate(booking.check_out)}" readonly>
                </div>
                <div class="mb-3">
                    <label for="swal-input-checkout" class="form-label small fw-bold text-muted">New Checkout Date</label>
                    <input id="swal-input-checkout" type="date" class="form-control py-2.5" min="${minCheckoutStr}" value="${defaultNewCheckout}">
                </div>
                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-2 rounded-3 py-2 px-3 mb-0">
                    <i class="bi bi-info-circle-fill text-info fs-6"></i>
                    <span class="x-small text-muted">Additional nights will be charged at <strong>₱${formatPrice(booking.room?.price_per_night)}</strong> per night.</span>
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonColor: '#BC9151',
        cancelButtonColor: '#718096',
        confirmButtonText: 'Submit Extension',
        preConfirm: () => {
            const checkoutInput = document.getElementById('swal-input-checkout').value;
            if (!checkoutInput) {
                Swal.showValidationMessage('Please select a new checkout date.');
                return false;
            }
            if (checkoutInput <= minCheckoutStr) {
                Swal.showValidationMessage('New checkout date must be after your current checkout date.');
                return false;
            }
            return checkoutInput;
        }
    });

    if (newCheckoutDate) {
        loading.value = true;
        try {
            // Append standard check-out time "T12:00" to the selected date
            const checkOutWithTime = `${newCheckoutDate}T12:00`;
            const response = await axios.put(`/api/reservations/${booking.id}`, {
                check_out: checkOutWithTime
            });

            await Swal.fire({
                icon: 'success',
                title: 'Stay Extended!',
                html: `
                    <div class="small text-muted">
                        Your stay in Room #${booking.room?.room_number} has been extended.<br>
                        New Checkout: <strong>${formatDate(response.data.check_out)}</strong>.<br>
                        Total Amount: <strong>₱${formatPrice(response.data.total_amount)}</strong>.<br><br>
                        <span class="text-danger fw-bold">Please settle the additional amount at the hotel upon checkout.</span>
                    </div>
                `,
                confirmButtonColor: '#BC9151'
            });

            await fetchBookings();
        } catch (error) {
            console.error('Error extending stay:', error);
            Swal.fire({
                icon: 'error',
                title: 'Extension Failed',
                text: error.response?.data?.message || 'Could not extend stay. The room might be booked by another guest for those dates.'
            });
        } finally {
            loading.value = false;
        }
    }
};

const handleWriteReview = async (booking) => {
    let selectedRating = 5;
    
    const { value: formValues } = await Swal.fire({
        title: 'Review Your Stay',
        html: `
            <div class="text-start">
                <p class="small text-muted mb-3">We hope you had a wonderful stay in Room #${booking.room?.room_number}! Share your experience with us and future guests.</p>
                <div class="mb-4 text-center">
                    <label class="form-label d-block small fw-bold text-muted text-uppercase mb-2">Overall Rating</label>
                    <div class="star-rating-selector fs-3 d-flex justify-content-center gap-2">
                        <span class="star-btn cursor-pointer text-gold text-warning" data-val="1" style="cursor: pointer;"><i class="bi bi-star-fill text-warning"></i></span>
                        <span class="star-btn cursor-pointer text-gold text-warning" data-val="2" style="cursor: pointer;"><i class="bi bi-star-fill text-warning"></i></span>
                        <span class="star-btn cursor-pointer text-gold text-warning" data-val="3" style="cursor: pointer;"><i class="bi bi-star-fill text-warning"></i></span>
                        <span class="star-btn cursor-pointer text-gold text-warning" data-val="4" style="cursor: pointer;"><i class="bi bi-star-fill text-warning"></i></span>
                        <span class="star-btn cursor-pointer text-gold text-warning" data-val="5" style="cursor: pointer;"><i class="bi bi-star-fill text-warning"></i></span>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="swal-input-comment" class="form-label small fw-bold text-muted text-uppercase">Your Review</label>
                    <textarea id="swal-input-comment" class="form-control" rows="4" placeholder="How was the bed? The cleanliness? The service? (Min. 5 characters)"></textarea>
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonColor: '#BC9151',
        cancelButtonColor: '#718096',
        confirmButtonText: 'Submit Review',
        didOpen: () => {
            const stars = Swal.getHtmlContainer().querySelectorAll('.star-btn');
            stars.forEach(star => {
                star.addEventListener('click', (e) => {
                    const val = parseInt(star.getAttribute('data-val'));
                    selectedRating = val;
                    // Update star colors
                    stars.forEach(s => {
                        const sVal = parseInt(s.getAttribute('data-val'));
                        const icon = s.querySelector('i');
                        if (sVal <= val) {
                            icon.className = 'bi bi-star-fill text-warning';
                        } else {
                            icon.className = 'bi bi-star text-muted';
                        }
                    });
                });
            });
        },
        preConfirm: () => {
            const comment = document.getElementById('swal-input-comment').value;
            if (!comment || comment.trim().length < 5) {
                Swal.showValidationMessage('Please write a review comment (minimum 5 characters).');
                return false;
            }
            return {
                rating: selectedRating,
                comment: comment
            };
        }
    });

    if (formValues) {
        loading.value = true;
        try {
            await axios.post('/api/reviews', {
                room_id: booking.room.id,
                rating: formValues.rating,
                comment: formValues.comment
            });
            
            await Swal.fire({
                icon: 'success',
                title: 'Review Submitted!',
                text: 'Thank you for your feedback! It has been successfully posted.',
                confirmButtonColor: '#BC9151'
            });
            
            await fetchBookings();
        } catch (error) {
            console.error('Error submitting review:', error);
            Swal.fire({
                icon: 'error',
                title: 'Submission Failed',
                text: error.response?.data?.message || 'Could not submit your review. Please try again later.'
            });
        } finally {
            loading.value = false;
        }
    }
};

const getDisputeForBooking = (bookingId) => {
    return disputes.value.find(d => d.reservation_id === bookingId);
};

const getDisputeStatusClass = (status) => {
    const base = 'badge rounded-pill px-3 py-1 fw-bold small ';
    switch(status) {
        case 'pending': return base + 'bg-warning text-dark border border-warning-subtle';
        case 'under_investigation': return base + 'bg-info text-white border border-info-subtle';
        case 'resolved': return base + 'bg-success text-white border border-success-subtle';
        case 'rejected': return base + 'bg-danger text-white border border-danger-subtle';
        default: return base + 'bg-secondary text-white';
    }
};

const formatDisputeStatusLabel = (status) => {
    switch(status) {
        case 'pending': return 'Dispute: Pending Review';
        case 'under_investigation': return 'Dispute: Under Investigation';
        case 'resolved': return 'Dispute: Resolved';
        case 'rejected': return 'Dispute: Rejected';
        default: return 'Dispute';
    }
};

const handleViewDisputeDetails = (dispute) => {
    Swal.fire({
        title: `Dispute Details`,
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase mb-1">Reason</label>
                    <div class="fw-semibold text-secondary-dark">${capitalize(dispute.reason.replace('_', ' '))}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase mb-1">Description</label>
                    <p class="text-muted small bg-light p-2.5 rounded-3 font-italic mb-0">"${dispute.description}"</p>
                </div>
                ${dispute.admin_remarks ? `
                <div class="alert alert-success border-0 shadow-sm rounded-4 p-3 mb-0">
                    <h6 class="fw-bold mb-1"><i class="bi bi-award-fill me-1"></i> Management Response</h6>
                    <p class="small text-muted mb-0 font-italic">"${dispute.admin_remarks}"</p>
                </div>
                ` : `
                <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mb-0">
                    <p class="small text-muted mb-0"><i class="bi bi-clock-history me-1"></i> Our management is currently reviewing your dispute. We will respond with remarks shortly.</p>
                </div>
                `}
            </div>
        `,
        confirmButtonColor: '#BC9151',
        confirmButtonText: 'Got It'
    });
};

const handleViewReceipt = (booking) => {
    const checkInDate = new Date(booking.check_in);
    const checkOutDate = new Date(booking.check_out);
    const totalNights = Math.max(1, Math.round((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24)));
    
    // Status Badge Class
    let badgeClass = 'bg-secondary text-white';
    const paymentStatus = booking.payment_status?.toLowerCase() || 'unpaid';
    if (paymentStatus === 'paid') badgeClass = 'bg-success text-white';
    else if (paymentStatus === 'partially_paid') badgeClass = 'bg-warning text-dark';
    else if (paymentStatus === 'unpaid') badgeClass = 'bg-warning text-dark';
    else if (paymentStatus === 'refunded') badgeClass = 'bg-info text-white';

    const formattedCheckIn = formatDate(booking.check_in);
    const formattedCheckOut = formatDate(booking.check_out);
    const formattedCreated = formatDate(booking.created_at || new Date());
    const receiptNo = `#EME-${String(booking.id).padStart(5, '0')}`;
    
    const nightlyRate = booking.room?.price_per_night || 0;
    const subtotal = booking.total_amount || 0;
    
    let balanceHtml = '';
    let totalPaidHtml = '';
    if (booking.payment_option === 'half') {
        const remainingBalance = subtotal / 2;
        const paidAmount = booking.downpayment_amount || (subtotal / 2);
        balanceHtml = `
            <div class="d-flex justify-content-between align-items-center mb-2 text-muted">
                <span class="small fw-semibold mb-0">Remaining Balance (Pay at Hotel)</span>
                <span class="fw-bold mb-0">₱${formatPrice(remainingBalance)}</span>
            </div>
        `;
        totalPaidHtml = `
            <span class="total-label serif-font h5 fw-bold text-dark mb-0" style="font-size: 0.95rem;">Amount Paid (Downpayment)</span>
            <span class="total-amount text-gold serif-font h3 fw-bold mb-0" style="font-size: 1.5rem;">₱${formatPrice(paidAmount)}</span>
        `;
    } else {
        totalPaidHtml = `
            <span class="total-label serif-font h5 fw-bold text-dark mb-0" style="font-size: 0.95rem;">Total Amount Paid</span>
            <span class="total-amount text-gold serif-font h3 fw-bold mb-0" style="font-size: 1.5rem;">₱${formatPrice(subtotal)}</span>
        `;
    }

    Swal.fire({
        title: '',
        html: `
            <div class="receipt-popup-container text-start">
                <!-- Receipt Header -->
                <div class="receipt-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="serif-font fw-bold text-gold mb-0" style="font-size: 1.5rem;">EME's Apartelle</h4>
                            <span class="receipt-subtitle">Official E-Receipt</span>
                        </div>
                        <div class="text-end">
                            <span class="badge ${badgeClass} px-3 py-1 rounded-pill fw-bold small">
                                ${paymentStatus.replace('_', ' ').toUpperCase()}
                            </span>
                        </div>
                    </div>
                    <div class="receipt-meta row g-2 mt-3 pt-3 border-top border-light">
                        <div class="col-6">
                            <span class="meta-label">Receipt No.</span>
                            <span class="meta-value">${receiptNo}</span>
                        </div>
                        <div class="col-6 text-end">
                            <span class="meta-label">Date Issued</span>
                            <span class="meta-value">${formattedCreated}</span>
                        </div>
                    </div>
                </div>

                <!-- Receipt Body -->
                <div class="receipt-body">
                    <!-- Guest Info -->
                    <div class="receipt-section">
                        <h6 class="section-title mb-2" style="font-size: 0.75rem;"><i class="bi bi-person me-1.5 text-gold"></i> Guest Details</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <span class="field-label">Name</span>
                                <span class="field-value">${state.user?.name || 'Guest'}</span>
                            </div>
                            <div class="col-6 text-end">
                                <span class="field-label">Email</span>
                                <span class="field-value text-truncate d-block">${state.user?.email || ''}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Info -->
                    <div class="receipt-section mt-4 pt-3 border-top border-dashed">
                        <h6 class="section-title mb-2" style="font-size: 0.75rem;"><i class="bi bi-key me-1.5 text-gold"></i> Booking Details</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <span class="field-label">Room Type & Number</span>
                                <span class="field-value fw-bold">Room #${booking.room?.room_number} (${booking.room?.room_type})</span>
                            </div>
                            <div class="col-6 text-end">
                                <span class="field-label">Nights</span>
                                <span class="field-value">${totalNights} Night${totalNights > 1 ? 's' : ''}</span>
                            </div>
                            <div class="col-6">
                                <span class="field-label">Check-In</span>
                                <span class="field-value">${formattedCheckIn} (12:00 PM)</span>
                            </div>
                            <div class="col-6 text-end">
                                <span class="field-label">Check-Out</span>
                                <span class="field-value">${formattedCheckOut} (12:00 PM)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Breakdown -->
                    <div class="receipt-section mt-4 pt-3 border-top border-dashed">
                        <h6 class="section-title mb-2" style="font-size: 0.75rem;"><i class="bi bi-receipt me-1.5 text-gold"></i> Charges Details</h6>
                        <div class="price-row d-flex justify-content-between mb-2">
                            <span class="price-label">Room Rate (${totalNights} Nights)</span>
                            <span class="price-val">₱${formatPrice(nightlyRate)} / night</span>
                        </div>
                        <div class="price-row d-flex justify-content-between mb-2">
                            <span class="price-label">Subtotal</span>
                            <span class="price-val">₱${formatPrice(subtotal)}</span>
                        </div>
                        <div class="price-row d-flex justify-content-between text-success">
                            <span class="price-label">Taxes & Service Fees</span>
                            <span class="price-val fw-bold">INCLUDED</span>
                        </div>
                    </div>
                </div>

                <!-- Receipt Footer / Total -->
                <div class="receipt-footer mt-4 pt-3 border-top border-3">
                    ${booking.payment_option === 'half' ? `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-muted mb-0">Total Room Rate</span>
                        <span class="fw-bold text-dark mb-0">₱${formatPrice(subtotal)}</span>
                    </div>
                    ` : ''}
                    ${balanceHtml}
                    <div class="d-flex justify-content-between align-items-center mb-4 pt-2 ${booking.payment_option === 'half' ? 'border-top border-dashed' : ''}">
                        ${totalPaidHtml}
                    </div>
                    <div class="text-center pt-2">
                        <p class="receipt-thankyou mb-3 italic-text">Thank you for staying at EME's Apartelle!</p>
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: '<i class="bi bi-printer me-1"></i> Print',
        cancelButtonText: '<i class="bi bi-file-earmark-pdf me-1"></i> Download PDF',
        denyButtonText: 'Close',
        showDenyButton: true,
        confirmButtonColor: '#BC9151',
        cancelButtonColor: '#1A2634',
        denyButtonColor: '#718096',
        customClass: {
            popup: 'premium-swal-popup receipt-popup',
            actions: 'premium-swal-actions d-flex flex-wrap gap-2 justify-content-center'
        },
        width: '550px'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(`/invoices/${booking.id}/view?print=true`, '_blank');
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            window.open(`/invoices/${booking.id}/download`, '_blank');
        }
    });
};

const handleFileDispute = async (booking) => {
    const { value: formValues } = await Swal.fire({
        title: 'File a Dispute',
        html: `
            <div class="text-start">
                <p class="small text-muted mb-3">Please provide the details of your dispute. Our support and management team will investigate immediately.</p>
                <div class="mb-3">
                    <label for="swal-input-reason" class="form-label small fw-bold text-muted text-uppercase">Dispute Reason</label>
                    <select id="swal-input-reason" class="form-select py-2">
                        <option value="" disabled selected>Select a reason...</option>
                        <option value="billing">Billing or Payment Issue</option>
                        <option value="room_condition">Room or Amenity Condition</option>
                        <option value="service_issue">Customer Service or Guest Care</option>
                        <option value="other">Other Issue</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="swal-input-description" class="form-label small fw-bold text-muted text-uppercase">Describe Your Issue</label>
                    <textarea id="swal-input-description" class="form-control" rows="5" placeholder="Please provide specific details so we can investigate accurately (Min. 10 characters)..."></textarea>
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonColor: '#BC9151',
        cancelButtonColor: '#718096',
        confirmButtonText: 'Submit Dispute',
        preConfirm: () => {
            const reason = document.getElementById('swal-input-reason').value;
            const description = document.getElementById('swal-input-description').value;
            if (!reason) {
                Swal.showValidationMessage('Please select a dispute reason.');
                return false;
            }
            if (!description || description.trim().length < 10) {
                Swal.showValidationMessage('Please provide a description (minimum 10 characters).');
                return false;
            }
            return { reason, description };
        }
    });

    if (formValues) {
        loading.value = true;
        try {
            const response = await axios.post('/api/disputes', {
                reservation_id: booking.id,
                reason: formValues.reason,
                description: formValues.description
            });

            await Swal.fire({
                icon: 'success',
                title: 'Dispute Filed',
                text: response.data.message || 'Your dispute has been successfully recorded. We will review it shortly.',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            });

            await fetchBookings();
        } catch (error) {
            console.error('Error filing dispute:', error);
            Swal.fire({
                icon: 'error',
                title: 'Filing Failed',
                text: error.response?.data?.message || 'Could not register your dispute. Please try again.'
            });
        } finally {
            loading.value = false;
        }
    }
};

onMounted(fetchBookings);
</script>

<style scoped>
.my-bookings-page {
    padding-top: 100px !important;
}
.bg-gold-subtle {
    background-color: rgba(188, 145, 81, 0.1);
}
.x-small {
    font-size: 0.7rem;
    letter-spacing: 0.5px;
}
.animate-fade-up {
    animation: fadeUp 0.5s ease-out forwards;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ========== SKELETON LOADING ========== */
.skeleton-image {
    background: #e2e8f0;
}

.skeleton-line {
    background: #e2e8f0;
    border-radius: 6px;
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
