<template>
  <div class="reservations-management-view">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden animate-fade-up">
      <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
          <h5 class="serif-font fw-bold mb-0 text-secondary-dark">Manage Bookings</h5>
          <!-- View Toggle Buttons -->
          <div class="btn-group btn-group-sm shadow-sm" role="group">
            <button class="btn btn-sm" :class="currentView === 'list' ? 'btn-gold text-white font-semibold' : 'btn-outline-gold'" @click="currentView = 'list'" style="font-size: 0.75rem; font-weight: 700;">
              <i class="bi bi-list-ul me-1"></i> List
            </button>
            <button class="btn btn-sm" :class="currentView === 'calendar' ? 'btn-gold text-white font-semibold' : 'btn-outline-gold'" @click="currentView = 'calendar'" style="font-size: 0.75rem; font-weight: 700;">
              <i class="bi bi-calendar3 me-1"></i> Calendar
            </button>
          </div>
        </div>
        <div class="d-flex gap-2">
           <div class="input-group input-group-sm" style="width: 240px;">
             <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
             <input type="text" class="form-control bg-light border-0" placeholder="Search ID, guest or room..." v-model="searchQuery">
           </div>
           <button class="btn btn-outline-gold btn-sm px-3 fw-bold text-uppercase shadow-sm" @click="openManageBlockedDatesModal">
             <i class="bi bi-slash-circle me-1"></i> Block Dates
           </button>
           <button class="btn btn-gold btn-sm px-3 fw-bold text-uppercase shadow-sm" @click="openNewBookingModal">
             <i class="bi bi-plus-lg me-1"></i> New Booking
           </button>
        </div>
      </div>
      <div class="card-body p-0">
        <!-- List View -->
        <div v-show="currentView === 'list'">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th class="ps-4 border-0 text-muted small fw-bold text-uppercase">Booking #</th>
                  <th class="border-0 text-muted small fw-bold text-uppercase">Guest Info</th>
                  <th class="border-0 text-muted small fw-bold text-uppercase">Room</th>
                  <th class="border-0 text-muted small fw-bold text-uppercase">Check-in</th>
                  <th class="border-0 text-muted small fw-bold text-uppercase">Check-out</th>
                  <th class="border-0 text-muted small fw-bold text-uppercase">Price</th>
                  <th class="border-0 text-muted small fw-bold text-uppercase">Status</th>
                  <th class="border-0 text-muted small fw-bold text-uppercase">Payment</th>
                  <th class="pe-4 border-0 text-muted small fw-bold text-uppercase text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="res in paginatedReservations" :key="res.id">
                  <td class="ps-4 py-3">
                    <span class="small fw-bold text-secondary-dark">#RES-{{ res.id.toString().padStart(3, '0') }}</span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <div class="avatar-md bg-gold-subtle rounded-circle d-flex align-items-center justify-content-center fw-bold text-gold overflow-hidden" style="width: 40px; height: 40px;">
                        <img v-if="res.user?.profile_photo_url" :src="res.user.profile_photo_url" :alt="res.user?.name" class="w-100 h-100 object-fit-cover">
                        <span v-else>{{ (res.user?.name || 'G').charAt(0) }}</span>
                      </div>
                      <div>
                        <span class="small fw-bold text-secondary-dark d-block">{{ res.user?.name || 'Guest Name' }}</span>
                        <small class="text-muted d-block">{{ res.user?.email }}</small>
                        <small class="text-muted" v-if="res.user?.phone"><i class="bi bi-telephone small me-1"></i>{{ res.user.phone }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="small fw-medium text-secondary-dark d-block">{{ res.room?.room_type }} #{{ res.room?.room_number }}</span>
                    <small class="text-muted d-block" v-if="res.guests"><i class="bi bi-people me-1"></i>{{ res.guests }} Guest{{ res.guests > 1 ? 's' : '' }}</small>
                  </td>
                  <td>
                    <input type="datetime-local" 
                      :value="formatToDateTimeLocal(res.check_in)" 
                      @change="e => updateCheckTimes(res, 'check_in', e.target.value)" 
                      class="form-control form-control-sm border-0 bg-light py-1 px-2 rounded-3 small text-muted"
                      style="width: 160px; font-size: 0.75rem;"
                    >
                  </td>
                  <td>
                    <input type="datetime-local" 
                      :value="formatToDateTimeLocal(res.check_out)" 
                      @change="e => updateCheckTimes(res, 'check_out', e.target.value)" 
                      class="form-control form-control-sm border-0 bg-light py-1 px-2 rounded-3 small text-muted"
                      style="width: 160px; font-size: 0.75rem;"
                    >
                  </td>
                  <td><span class="fw-bold text-secondary-dark">₱{{ formatPrice(res.total_amount) }}</span></td>
                  <td>
                    <select 
                      class="form-select form-select-sm rounded-pill w-auto fw-bold text-uppercase border-0 shadow-sm px-3" 
                      :class="statusBadgeClass(res.status)"
                      v-model="res.status"
                      @change="updateStatus(res)"
                      style="font-size: 0.65rem;"
                    >
                      <option value="pending">PENDING</option>
                      <option value="confirmed">CONFIRMED</option>
                      <option value="completed">COMPLETED</option>
                      <option value="cancelled">CANCELLED</option>
                      <option value="cancellation_pending" v-if="res.status === 'cancellation_pending'">CANCELLATION PENDING</option>
                    </select>
                  </td>
                  <td>
                    <select 
                      class="form-select form-select-sm rounded-pill w-auto fw-bold text-uppercase border-0 shadow-sm px-3" 
                      :class="paymentBadgeClass(res.payment_status)"
                      v-model="res.payment_status"
                      @change="updatePaymentStatus(res)"
                      style="font-size: 0.65rem;"
                    >
                      <option value="unpaid">UNPAID</option>
                      <option value="partially_paid">PARTIALLY PAID</option>
                      <option value="paid">PAID</option>
                      <option value="refunded">REFUNDED</option>
                    </select>
                  </td>
                  <td class="pe-4 text-end">
                    <div class="d-flex justify-content-end gap-2">
                      <button class="btn btn-action-view" title="View Booking Details" @click="openReservationDetailModal(res)">
                        <i class="bi bi-eye"></i>
                      </button>
                      <a :href="'/invoices/' + res.id + '/download'" class="btn btn-action-invoice" title="Download Invoice">
                        <i class="bi bi-file-earmark-pdf"></i>
                      </a>
                      <button class="btn btn-action-delete" title="Delete Booking" @click="deleteReservation(res.id)">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredReservations.length === 0">
                  <td colspan="9" class="text-center py-5 text-muted">No reservations found.</td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <AdminPagination 
            :current-page="currentPage" 
            :total-items="filteredReservations.length" 
            :page-size="pageSize"
            @change="page => currentPage = page"
            @page-size-change="size => { pageSize = size; currentPage = 1; }"
          />
        </div>

        <!-- Calendar View -->
        <div v-show="currentView === 'calendar'" class="calendar-wrapper p-4 animate-fade-in">
          <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
              <button @click="changeCalendarMonth(-1)" type="button" class="btn btn-sm btn-light rounded-circle calend-nav-btn d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="bi bi-chevron-left small text-dark"></i>
              </button>
              <h5 class="serif-font fw-bold text-secondary-dark mb-0 min-w-150 text-center">{{ calendarMonthName }}</h5>
              <button @click="changeCalendarMonth(1)" type="button" class="btn btn-sm btn-light rounded-circle calend-nav-btn d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="bi bi-chevron-right small text-dark"></i>
              </button>
            </div>
            
            <!-- Legend -->
            <div class="d-flex align-items-center gap-3 flex-wrap small fw-bold text-uppercase text-muted" style="font-size: 0.7rem;">
              <span class="d-flex align-items-center gap-1.5"><span class="legend-dot bg-warning" style="width: 8px; height: 8px; border-radius: 50%;"></span> Pending</span>
              <span class="d-flex align-items-center gap-1.5"><span class="legend-dot bg-success" style="width: 8px; height: 8px; border-radius: 50%;"></span> Confirmed</span>
              <span class="d-flex align-items-center gap-1.5"><span class="legend-dot bg-info" style="width: 8px; height: 8px; border-radius: 50%;"></span> Completed</span>
              <span class="d-flex align-items-center gap-1.5"><span class="legend-dot bg-danger" style="width: 8px; height: 8px; border-radius: 50%;"></span> Cancelled</span>
            </div>
          </div>

          <div class="admin-calendar-grid">
            <!-- Day Header -->
            <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" class="admin-calendar-header">
              {{ day }}
            </div>
            
            <!-- Day Cells -->
            <div 
              v-for="(cell, idx) in adminCalendarDays" 
              :key="idx" 
              class="admin-calendar-cell"
              :class="{
                'empty': !cell,
                'today': cell && isTodayDate(cell.dateStr)
              }"
              @click.self="cell && handleEmptyDayClick(cell.dateStr)"
            >
              <template v-if="cell">
                <div class="d-flex justify-content-between align-items-center mb-1 cell-header" @click.self="handleEmptyDayClick(cell.dateStr)">
                  <span class="cell-day-num" :class="{'today-badge': isTodayDate(cell.dateStr)}">{{ cell.day }}</span>
                </div>
                <div class="cell-bookings">
                  <div 
                    v-for="booking in getReservationsForDate(cell.dateStr)" 
                    :key="booking.id"
                    class="booking-ribbon"
                    :class="booking.status"
                    @click="booking.isBlock ? openManageBlockedDatesModal() : openReservationDetailModal(booking)"
                    :title="booking.isBlock ? `Blocked: ${booking.user?.name}` : `${booking.user?.name || 'Guest'} - Room ${booking.room?.room_number}`"
                  >
                    <span class="ribbon-text d-flex align-items-center gap-1">
                      <i v-if="booking.isBlock" class="bi bi-slash-circle me-1 small"></i>
                      #{{ booking.room?.room_number }} {{ booking.user?.name || 'Guest' }}
                    </span>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- New Booking Modal -->
    <Teleport to="body">
       <div v-if="showNewBookingModal" class="modal-backdrop fade show" style="z-index: 1050;"></div>
       <div v-if="showNewBookingModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1055;" @click.self="closeNewBookingModal">
         <div class="modal-dialog modal-dialog-centered modal-lg">
           <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
             <div class="modal-header border-bottom px-4 py-3 align-items-center">
               <h5 class="modal-title serif-font fw-bold text-secondary-dark">Add New Booking</h5>
               <button type="button" class="btn-close shadow-none" @click="closeNewBookingModal"></button>
             </div>
             <div class="modal-body p-4 bg-light-subtle">
                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-3 rounded-3 mb-4">
                  <i class="bi bi-info-circle-fill text-info fs-5"></i>
                  <span class="small text-muted">Use this form for walk-in guests or phone reservations.</span>
                </div>
                
                <form @submit.prevent="createBooking">
                  <div class="row g-3">
                    <!-- Guest Selection -->
                    <div class="col-12">
                      <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Select Guest</label>
                      <select class="form-select form-control-custom py-2" v-model="newBooking.user_id" required>
                        <option value="" disabled>Choose a guest...</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">
                          {{ user.name || (user.first_name + ' ' + user.last_name) }} ({{ user.email }})
                        </option>
                      </select>
                      <div class="form-text text-end"><a href="#" class="text-gold text-decoration-none small fw-bold" @click.prevent="showRegisterGuestModal = true">+ Register New Guest</a></div>
                    </div>

                    <!-- Dates -->
                    <div class="col-md-6">
                       <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Check-in Date & Time</label>
                       <input type="datetime-local" class="form-control form-control-custom py-2" v-model="newBooking.check_in" :min="todayDateTimeStr" required>
                    </div>
                    <div class="col-md-6">
                       <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Check-out Date & Time</label>
                       <input type="datetime-local" class="form-control form-control-custom py-2" v-model="newBooking.check_out" :min="newBooking.check_in || todayDateTimeStr" required>
                    </div>
                    <!-- Room Selection -->
                    <div class="col-12">
                       <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Select Room</label>
                       <div class="input-group">
                         <select class="form-select form-control-custom py-2" v-model="newBooking.room_id" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                           <option value="" disabled>Choose a room...</option>
                           <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                             {{ room.room_type }} #{{ room.room_number }} - ₱{{ (room.room_type === 'Family Room' || room.room_type === 'Barkadahan Room') ? formatPrice(room.price_per_head) + '/head/night' : formatPrice(room.price_per_night) + '/night' }}
                           </option>
                         </select>
                         <button 
                            class="btn btn-outline-secondary px-3 bg-white" 
                            type="button" 
                            :disabled="!newBooking.room_id"
                            @click="viewRoomDetails"
                            title="View Room"
                            style="border-color: #dee2e6; border-left: 0;"
                          >
                            <i class="bi bi-eye text-primary"></i>
                          </button>
                       </div>
                    </div>

                    <!-- Guest Count Selection -->
                    <div class="col-12" v-if="newBooking.room_id">
                       <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Number of Guests</label>
                       <select class="form-select form-control-custom py-2" v-model="newBooking.guests" required>
                         <option v-for="n in (selectedNewBookingRoom ? selectedNewBookingRoom.max_occupancy : 12)" 
                                 :key="n" 
                                 :value="n" 
                                 :disabled="selectedNewBookingRoom && selectedNewBookingRoom.min_occupancy && n < selectedNewBookingRoom.min_occupancy">
                           {{ n }} Guest{{ n > 1 ? 's' : '' }}
                         </option>
                       </select>
                     </div>
                  </div>
                  
                  <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                    <button type="button" class="btn btn-light px-4 fw-bold text-muted" @click="closeNewBookingModal">Cancel</button>
                    <button type="submit" class="btn btn-gold px-4 fw-bold text-uppercase shadow-sm" :disabled="isSubmitting">
                      <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                      {{ isSubmitting ? 'Creating...' : 'Create Booking' }}
                    </button>
                  </div>
                </form>
             </div>
           </div>
         </div>
       </div>

       <!-- Room Detail Modal (Nested) -->
       <div v-if="showRoomDetailModal" class="modal-backdrop fade show" style="z-index: 1060;"></div>
       <div v-if="showRoomDetailModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1065;" @click.self="showRoomDetailModal = false">
         <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
             <div class="modal-header border-0 position-absolute end-0 top-0 z-2 p-3">
                <button type="button" class="btn-close bg-white opacity-75 shadow-sm rounded-circle p-2" @click="showRoomDetailModal = false"></button>
             </div>
             <div class="modal-body p-0" v-if="selectedRoomDetail">
                <div class="position-relative" style="height: 250px;">
                  <img :src="getRoomImage(selectedRoomDetail)" class="w-100 h-100 object-fit-cover" :alt="selectedRoomDetail.room_type">
                  <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-dark text-white">
                    <h5 class="mb-0 fw-bold serif-font">{{ selectedRoomDetail.room_type }} #{{ selectedRoomDetail.room_number }}</h5>
                  </div>
                </div>
                <div class="p-4">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-gold-subtle text-gold px-3 py-2 rounded-pill text-uppercase fw-bold" style="font-size: 0.7rem;">
                       {{ selectedRoomDetail.status }}
                    </span>
                    <span class="h4 serif-font text-secondary-dark mb-0">
                      ₱{{ (selectedRoomDetail.room_type === 'Family Room' || selectedRoomDetail.room_type === 'Barkadahan Room') ? formatPrice(selectedRoomDetail.price_per_head) : formatPrice(selectedRoomDetail.price_per_night) }}
                      <span class="fs-6 text-muted" style="font-size: 0.8rem;">
                        {{ (selectedRoomDetail.room_type === 'Family Room' || selectedRoomDetail.room_type === 'Barkadahan Room') ? '/head/night' : '/night' }}
                      </span>
                    </span>
                  </div>
                  <p class="text-muted small mb-3">{{ selectedRoomDetail.description }}</p>
                  
                  <div class="row g-2 mb-3">
                     <div class="col-6" v-if="selectedRoomDetail.room_size">
                       <small class="text-muted d-flex align-items-center gap-2"><i class="bi bi-arrows-fullscreen"></i> {{ selectedRoomDetail.room_size }} m²</small>
                     </div>
                     <div class="col-6">
                       <small class="text-muted d-flex align-items-center gap-2"><i class="bi bi-people"></i> Max {{ selectedRoomDetail.max_occupancy }} Guests</small>
                     </div>
                  </div>

                  <div class="d-flex flex-wrap gap-2 mt-3" v-if="selectedRoomDetail.amenities?.length">
                    <span v-for="amenity in selectedRoomDetail.amenities" :key="amenity.id" class="badge bg-light text-muted border fw-normal">
                      {{ amenity.name }}
                    </span>
                  </div>
                </div>
             </div>
           </div>
         </div>
       </div>
       <!-- Register New Guest Modal -->
       <div v-if="showRegisterGuestModal" class="modal-backdrop fade show" style="z-index: 1070;"></div>
       <div v-if="showRegisterGuestModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1075;" @click.self="showRegisterGuestModal = false">
         <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content border-0 rounded-4 shadow-lg">
             <div class="modal-header border-bottom px-4 py-3">
               <h5 class="modal-title serif-font fw-bold text-secondary-dark">Register New Guest</h5>
               <button type="button" class="btn-close shadow-none" @click="showRegisterGuestModal = false"></button>
             </div>
             <div class="modal-body p-4 bg-light-subtle">
                <form @submit.prevent="registerGuest">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">First Name</label>
                      <input type="text" class="form-control form-control-custom" v-model="guestForm.first_name" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Last Name</label>
                      <input type="text" class="form-control form-control-custom" v-model="guestForm.last_name" required>
                    </div>
                    <div class="col-12">
                      <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Email Address</label>
                      <input type="email" class="form-control form-control-custom" v-model="guestForm.email" required>
                    </div>
                    <div class="col-12">
                      <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Phone Number</label>
                      <input type="tel" class="form-control form-control-custom" v-model="guestForm.phone">
                    </div>
                  </div>
                  <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-light px-4" @click="showRegisterGuestModal = false">Cancel</button>
                    <button type="submit" class="btn btn-gold px-4 fw-bold" :disabled="isRegistering">
                      <span v-if="isRegistering" class="spinner-border spinner-border-sm me-2"></span>
                      Register Guest
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Reservation Details Modal -->
        <div v-if="showReservationDetailModal" class="modal-backdrop fade show" style="z-index: 1050;"></div>
        <div v-if="showReservationDetailModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1055;" @click.self="closeReservationDetailModal">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-premium overflow-hidden">
              <!-- Modal Header with premium theme -->
              <div class="modal-header border-0 bg-secondary-dark text-white px-4 py-3.5 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-circle bg-white-glass d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-calendar-check text-gold fs-5"></i>
                  </div>
                  <div>
                    <h5 class="modal-title serif-font fw-bold mb-0 text-white" style="letter-spacing: 0.5px;">Reservation Details</h5>
                    <small class="text-white-50 fw-semibold text-uppercase tracking-wider" style="font-size: 0.65rem;">#RES-{{ selectedReservation.id.toString().padStart(3, '0') }}</small>
                  </div>
                </div>
                <button type="button" class="btn-close btn-close-white shadow-none" @click="closeReservationDetailModal"></button>
              </div>
              
              <div class="modal-body p-4 bg-light-subtle" v-if="selectedReservation">
                <div class="row g-4">
                  
                  <!-- Left Column: Guest & Stay Details -->
                  <div class="col-md-7 d-flex flex-column gap-3">
                    <!-- Guest Card -->
                    <div class="detail-card rounded-4 p-3 bg-white shadow-sm border border-light-subtle animate-fade-in">
                      <h6 class="text-uppercase text-gold fw-bold small tracking-wider mb-3"><i class="bi bi-person-badge me-2"></i>Guest Profile</h6>
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar-lg bg-gold-subtle rounded-circle d-flex align-items-center justify-content-center fw-bold text-gold overflow-hidden" style="width: 52px; height: 52px; min-width: 52px;">
                          <img v-if="selectedReservation.user?.profile_photo_url" :src="selectedReservation.user.profile_photo_url" :alt="selectedReservation.user?.name" class="w-100 h-100 object-fit-cover">
                          <span v-else style="font-size: 1.2rem;">{{ (selectedReservation.user?.name || 'G').charAt(0) }}</span>
                        </div>
                        <div class="overflow-hidden">
                          <h6 class="fw-bold text-secondary-dark mb-1 fs-6 text-truncate">{{ selectedReservation.user?.name || 'Guest' }}</h6>
                          <p class="text-muted mb-1 small text-truncate"><i class="bi bi-envelope me-2"></i>{{ selectedReservation.user?.email || 'N/A' }}</p>
                          <p class="text-muted mb-0 small"><i class="bi bi-telephone me-2"></i>{{ selectedReservation.user?.phone || 'N/A' }}</p>
                        </div>
                      </div>
                    </div>

                    <!-- Room Card -->
                    <div class="detail-card rounded-4 p-3 bg-white shadow-sm border border-light-subtle animate-fade-in">
                      <h6 class="text-uppercase text-gold fw-bold small tracking-wider mb-3"><i class="bi bi-door-closed me-2"></i>Room Details</h6>
                      <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-3 overflow-hidden bg-light" style="width: 80px; height: 60px; min-width: 80px;">
                          <img :src="getRoomImage(selectedReservation.room)" class="w-100 h-100 object-fit-cover" alt="Room image">
                        </div>
                        <div>
                          <h6 class="fw-bold text-secondary-dark mb-1 small">{{ selectedReservation.room?.room_type }} - Room #{{ selectedReservation.room?.room_number }}</h6>
                          <p class="text-muted mb-0 small">
                            <template v-if="selectedReservation.room?.room_type === 'Family Room' || selectedReservation.room?.room_type === 'Barkadahan Room'">
                              ₱{{ formatPrice(selectedReservation.room?.price_per_head) }} / head / night
                            </template>
                            <template v-else>
                              ₱{{ formatPrice(selectedReservation.room?.price_per_night) }} / night
                            </template>
                          </p>
                        </div>
                      </div>
                    </div>

                    <!-- Stay Schedule -->
                    <div class="detail-card rounded-4 p-3 bg-white shadow-sm border border-light-subtle animate-fade-in">
                      <h6 class="text-uppercase text-gold fw-bold small tracking-wider mb-3"><i class="bi bi-clock me-2"></i>Stay Schedule</h6>
                      <div class="row g-3">
                        <div class="col-6 border-end">
                          <small class="text-muted text-uppercase tracking-wider d-block mb-1" style="font-size: 0.65rem;">Check-In</small>
                          <span class="fw-bold text-secondary-dark small d-block">{{ formatFullDate(selectedReservation.check_in) }}</span>
                        </div>
                        <div class="col-6 ps-3">
                          <small class="text-muted text-uppercase tracking-wider d-block mb-1" style="font-size: 0.65rem;">Check-Out</small>
                          <span class="fw-bold text-secondary-dark small d-block">{{ formatFullDate(selectedReservation.check_out) }}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Right Column: Status & Financial Breakdown -->
                  <div class="col-md-5 d-flex flex-column gap-3">
                    <!-- Status Card -->
                    <div class="detail-card rounded-4 p-3 bg-white shadow-sm border border-light-subtle animate-fade-in">
                      <h6 class="text-uppercase text-gold fw-bold small tracking-wider mb-3"><i class="bi bi-tag-fill me-2"></i>Booking Status</h6>
                      <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center">
                          <small class="text-muted">Reservation Status</small>
                          <span class="badge rounded-pill fw-bold text-uppercase px-2.5 py-1" :class="statusBadgeClass(selectedReservation.status)" style="font-size: 0.65rem;">
                            {{ selectedReservation.status }}
                          </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                          <small class="text-muted">Payment Status</small>
                          <span class="badge rounded-pill fw-bold text-uppercase px-2.5 py-1" :class="paymentBadgeClass(selectedReservation.payment_status)" style="font-size: 0.65rem;">
                            {{ selectedReservation.payment_status }}
                          </span>
                        </div>
                        <div class="d-flex flex-column gap-1 border-top pt-2 mt-2" v-if="selectedReservation.status === 'cancelled' && selectedReservation.cancellation_reason">
                          <small class="text-muted fw-semibold">Cancellation Reason</small>
                          <div class="text-danger small mb-0 bg-danger-subtle p-2 rounded border border-danger-subtle" style="word-break: break-word; font-family: inherit;">
                            {{ selectedReservation.cancellation_reason }}
                          </div>
                        </div>
                        <div class="d-flex flex-column gap-1 border-top pt-2 mt-2" v-if="selectedReservation.status === 'cancellation_pending'">
                          <template v-if="selectedReservation.cancellation_reason">
                            <small class="text-muted fw-semibold text-warning-emphasis">Requested Cancellation Reason</small>
                            <div class="text-warning-emphasis small mb-2 bg-warning-subtle p-2 rounded border border-warning-subtle" style="word-break: break-word; font-family: inherit;">
                              {{ selectedReservation.cancellation_reason }}
                            </div>
                          </template>
                          <!-- Cancellation Approval Actions -->
                          <div class="d-flex gap-2 pt-1">
                            <button @click="approveCancellation(selectedReservation)" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-bold x-small text-uppercase flex-grow-1" style="font-size: 0.65rem;">
                              <i class="bi bi-check-circle me-1"></i> Approve
                            </button>
                            <button @click="declineCancellation(selectedReservation)" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 fw-bold x-small text-uppercase flex-grow-1" style="font-size: 0.65rem;">
                              <i class="bi bi-x-circle me-1"></i> Decline
                            </button>
                          </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center" v-if="selectedReservation.payment_option">
                          <small class="text-muted">Payment Option</small>
                          <span class="badge rounded-pill fw-bold text-uppercase bg-light text-secondary border px-2.5 py-1" style="font-size: 0.65rem;">
                            {{ selectedReservation.payment_option === 'downpayment' || selectedReservation.payment_option === 'half_downpayment' ? '50% Downpayment' : 'Full Payment' }}
                          </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center" v-if="selectedReservation.xendit_invoice_id">
                          <small class="text-muted">Invoice ID</small>
                          <span class="text-muted font-monospace small" :title="selectedReservation.xendit_invoice_id">{{ selectedReservation.xendit_invoice_id.substring(0, 12) }}...</span>
                        </div>
                      </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="detail-card rounded-4 p-3 bg-white shadow-sm border border-light-subtle animate-fade-in">
                      <h6 class="text-uppercase text-gold fw-bold small tracking-wider mb-3"><i class="bi bi-credit-card me-2"></i>Financial Summary</h6>
                      <div class="d-flex flex-column gap-2 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                          <small class="text-muted">
                            {{ (selectedReservation.room?.room_type === 'Family Room' || selectedReservation.room?.room_type === 'Barkadahan Room') ? 'Price Per Head / Night' : 'Price Per Night' }}
                          </small>
                          <span class="small fw-semibold text-secondary-dark">
                            ₱{{ formatPrice((selectedReservation.room?.room_type === 'Family Room' || selectedReservation.room?.room_type === 'Barkadahan Room') ? selectedReservation.room?.price_per_head : selectedReservation.room?.price_per_night) }}
                          </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center" v-if="selectedReservation.guests && (selectedReservation.room?.room_type === 'Family Room' || selectedReservation.room?.room_type === 'Barkadahan Room')">
                          <small class="text-muted">Guests</small>
                          <span class="small fw-semibold text-secondary-dark">{{ selectedReservation.guests }} Guest{{ selectedReservation.guests > 1 ? 's' : '' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                          <small class="text-muted">Nights</small>
                          <span class="small fw-semibold text-secondary-dark">{{ totalReservationNights(selectedReservation) }} Night{{ totalReservationNights(selectedReservation) > 1 ? 's' : '' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-1">
                          <small class="text-muted">Breakdown</small>
                          <span class="x-small text-muted" style="font-style: italic;">
                            <template v-if="selectedReservation.room?.room_type === 'Family Room' || selectedReservation.room?.room_type === 'Barkadahan Room'">
                              ((₱{{ formatPrice(selectedReservation.room?.price_per_head) }} × {{ selectedReservation.guests || 1 }} guests) × {{ totalReservationNights(selectedReservation) }} nights)
                            </template>
                            <template v-else>
                              (₱{{ formatPrice(selectedReservation.room?.price_per_night) }} × {{ totalReservationNights(selectedReservation) }} nights)
                            </template>
                          </span>
                        </div>
                        
                        <!-- If 50% downpayment option -->
                        <template v-if="selectedReservation.payment_option === 'downpayment' || selectedReservation.payment_option === 'half_downpayment' || selectedReservation.downpayment_amount > 0">
                          <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Downpayment Paid (50%)</small>
                            <span class="small fw-bold text-success">₱{{ formatPrice(selectedReservation.downpayment_amount || (selectedReservation.total_amount / 2)) }}</span>
                          </div>
                          <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Remaining Balance</small>
                            <span class="small fw-bold text-danger">₱{{ formatPrice(selectedReservation.total_amount - (selectedReservation.downpayment_amount || (selectedReservation.total_amount / 2))) }}</span>
                          </div>
                        </template>
                        <template v-else>
                          <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Downpayment</small>
                            <span class="small text-muted">-</span>
                          </div>
                        </template>
                      </div>
                      
                      <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-secondary-dark">Total Price</span>
                        <span class="h5 serif-font text-gold fw-bold mb-0">₱{{ formatPrice(selectedReservation.total_amount) }}</span>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

              <!-- Modal Footer -->
              <div class="modal-footer border-top bg-light-soft px-4 py-3 d-flex justify-content-end gap-2">
                <a 
                  :href="'/invoices/' + selectedReservation.id + '/download'" 
                  class="btn btn-outline-gold btn-sm fw-bold text-uppercase d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3"
                  title="Download E-Receipt"
                >
                  <i class="bi bi-file-earmark-pdf"></i> Receipt
                </a>
                <button 
                  class="btn btn-danger btn-sm fw-bold text-uppercase d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3"
                  @click="deleteReservationFromDetail(selectedReservation.id)"
                >
                  <i class="bi bi-trash"></i> Delete
                </button>
                <button type="button" class="btn btn-light btn-sm fw-bold px-4 py-2 rounded-3 border" @click="closeReservationDetailModal">Close</button>
              </div>

            </div>
          </div>
        </div>

        <!-- ========== MANAGE BLOCKED DATES MODAL ========== -->
        <div v-if="showBlockedDatesModal" class="modal-backdrop fade show" style="z-index: 1050;" @click="showBlockedDatesModal = false"></div>
        <div v-if="showBlockedDatesModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1055;" @click.self="showBlockedDatesModal = false">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden shadow-2xl">
              <!-- Modal Header -->
              <div class="modal-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                <h3 class="modal-title serif-font text-secondary-dark mb-0 fs-4">Manage Blocked Dates</h3>
                <button type="button" class="btn-close bg-light rounded-circle p-2 shadow-none" @click="showBlockedDatesModal = false" aria-label="Close"></button>
              </div>
              <!-- Modal Body -->
              <div class="modal-body bg-cream p-4">
                <div class="row g-4">
                  <!-- Add New Block Form -->
                  <div class="col-md-5 border-end pe-md-4">
                    <h5 class="serif-font fw-bold text-secondary-dark mb-3">Block a Date Range</h5>
                    <form @submit.prevent="submitBlockDates">
                      <div class="mb-3">
                        <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1 d-block">Select Room</label>
                        <select v-model="blockForm.room_id" class="form-select rounded-3">
                          <option value="">Global (Block All Rooms)</option>
                          <option v-for="room in allRooms" :key="room.id" :value="room.id">
                            Room #{{ room.room_number }} ({{ room.room_type }})
                          </option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1 d-block">Start Date & Time</label>
                        <input type="datetime-local" v-model="blockForm.start_date" class="form-control rounded-3" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1 d-block">End Date & Time</label>
                        <input type="datetime-local" v-model="blockForm.end_date" class="form-control rounded-3" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1 d-block">Reason / Event Name</label>
                        <input type="text" v-model="blockForm.reason" placeholder="e.g. Summer Renovation" class="form-control rounded-3" required>
                      </div>
                      <button type="submit" class="btn btn-gold w-100 rounded-pill shadow-md mt-2 fw-bold text-uppercase" :disabled="isSubmittingBlock" style="font-size: 0.8rem; padding: 0.6rem;">
                        <span v-if="isSubmittingBlock" class="spinner-border spinner-border-sm me-2"></span>
                        Block Selected Dates
                      </button>
                    </form>
                  </div>

                  <!-- List of Blocked Dates -->
                  <div class="col-md-7 ps-md-4">
                    <h5 class="serif-font fw-bold text-secondary-dark mb-3">Current Active Blocks</h5>
                    <div class="table-responsive rounded-3 border bg-white" style="max-height: 350px; overflow-y: auto;">
                      <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                        <thead class="sticky-top bg-light border-bottom">
                          <tr>
                            <th class="border-0 text-muted fw-bold text-uppercase p-2" style="font-size: 0.7rem;">Room</th>
                            <th class="border-0 text-muted fw-bold text-uppercase p-2" style="font-size: 0.7rem;">Dates</th>
                            <th class="border-0 text-muted fw-bold text-uppercase p-2" style="font-size: 0.7rem;">Reason</th>
                            <th class="border-0 text-muted fw-bold text-uppercase p-2 text-end" style="font-size: 0.7rem;">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="block in blockedDates" :key="block.id">
                            <td class="p-2 fw-bold text-secondary-dark">
                              {{ block.room ? `Room #${block.room.room_number}` : 'Global' }}
                            </td>
                            <td class="p-2 text-muted" style="font-size: 0.75rem; line-height: 1.2;">
                              {{ formatDateShort(block.start_date) }} - <br>
                              {{ formatDateShort(block.end_date) }}
                            </td>
                            <td class="p-2 text-dark">{{ block.reason || 'N/A' }}</td>
                            <td class="p-2 text-end">
                              <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle d-inline-flex align-items-center justify-content-center p-1" @click="deleteBlockedDate(block.id)" title="Unblock Dates" style="width: 28px; height: 28px;">
                                <i class="bi bi-trash"></i>
                              </button>
                            </td>
                          </tr>
                          <tr v-if="blockedDates.length === 0">
                            <td colspan="4" class="text-center py-4 text-muted">No active blocks found.</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
     </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
import { notify, confirm } from '../../utils/sweetalert';
import AdminPagination from '../../components/AdminPagination.vue';

const router = useRouter();
const route = useRoute();
const reservations = ref([]);
const blockedDates = ref([]);
const showBlockedDatesModal = ref(false);
const isSubmittingBlock = ref(false);
const allRooms = ref([]);

const blockForm = reactive({
  room_id: '',
  start_date: '',
  end_date: '',
  reason: ''
});

const searchQuery = ref('');
const currentPage = ref(1);
const pageSize = ref(5);

// Calendar State
const currentView = ref('list'); // 'list' or 'calendar'
const currentCalendarDate = ref(new Date());
const showReservationDetailModal = ref(false);
const selectedReservation = ref(null);

const calendarMonthName = computed(() => {
  return currentCalendarDate.value.toLocaleString('default', { month: 'long', year: 'numeric' });
});

const changeCalendarMonth = (direction) => {
  const newDate = new Date(currentCalendarDate.value);
  newDate.setMonth(newDate.getMonth() + direction);
  currentCalendarDate.value = newDate;
};

const adminCalendarDays = computed(() => {
  const year = currentCalendarDate.value.getFullYear();
  const month = currentCalendarDate.value.getMonth();
  const firstDay = new Date(year, month, 1).getDay();
  const lastDate = new Date(year, month + 1, 0).getDate();

  const daysArr = [];
  // Padding for empty days
  for (let i = 0; i < firstDay; i++) {
    daysArr.push(null);
  }
  // Days of the month
  for (let i = 1; i <= lastDate; i++) {
    daysArr.push({
      day: i,
      dateStr: `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`,
      fullDate: new Date(year, month, i)
    });
  }
  return daysArr;
});

const isTodayDate = (dateStr) => {
  const today = new Date();
  const y = today.getFullYear();
  const m = String(today.getMonth() + 1).padStart(2, '0');
  const d = String(today.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}` === dateStr;
};

const getReservationsForDate = (dateStr) => {
  if (!dateStr) return [];
  
  // 1. Get reservations for this date
  const dateReservations = reservations.value.filter(res => {
    const checkInDateStr = (res.check_in || '').split(' ')[0] || (res.check_in || '').split('T')[0];
    const checkOutDateStr = (res.check_out || '').split(' ')[0] || (res.check_out || '').split('T')[0];
    return dateStr >= checkInDateStr && dateStr <= checkOutDateStr;
  });

  // 2. Get blocked dates for this date
  const dateBlocks = blockedDates.value.filter(block => {
    const startDateStr = (block.start_date || '').split(' ')[0] || (block.start_date || '').split('T')[0];
    const endDateStr = (block.end_date || '').split(' ')[0] || (block.end_date || '').split('T')[0];
    return dateStr >= startDateStr && dateStr <= endDateStr;
  }).map(block => {
    return {
      id: `block-${block.id}`,
      isBlock: true,
      blockData: block,
      status: 'blocked',
      room: block.room ? block.room : { room_number: 'All' },
      user: { name: block.reason || 'Blocked (Event/Maintenance)' },
      check_in: block.start_date,
      check_out: block.end_date
    };
  });

  return [...dateReservations, ...dateBlocks];
};

const handleEmptyDayClick = (dateStr) => {
  openNewBookingModal();
  newBooking.value.check_in = `${dateStr}T14:00`;
  const checkOutDate = new Date(dateStr);
  checkOutDate.setDate(checkOutDate.getDate() + 1);
  const nextDayStr = `${checkOutDate.getFullYear()}-${String(checkOutDate.getMonth() + 1).padStart(2, '0')}-${String(checkOutDate.getDate()).padStart(2, '0')}`;
  newBooking.value.check_out = `${nextDayStr}T12:00`;
};

const openReservationDetailModal = (res) => {
  selectedReservation.value = res;
  showReservationDetailModal.value = true;
};

const closeReservationDetailModal = () => {
  showReservationDetailModal.value = false;
  selectedReservation.value = null;
  if (route.query.id) {
    router.replace({ path: '/admin/reservations', query: {} });
  }
};

const deleteReservationFromDetail = async (id) => {
  closeReservationDetailModal();
  await deleteReservation(id);
};

const formatFullDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric', 
    hour: '2-digit', 
    minute: '2-digit' 
  });
};

// New Booking State
const showNewBookingModal = ref(false);
const showRoomDetailModal = ref(false);
const showRegisterGuestModal = ref(false);
const isSubmitting = ref(false);
const isRegistering = ref(false);
const users = ref([]);
const availableRooms = ref([]);
const selectedRoomDetail = ref(null);
const newBooking = ref({
  user_id: '',
  room_id: '',
  check_in: '',
  check_out: '',
  guests: 1
});

const selectedNewBookingRoom = computed(() => {
  return availableRooms.value.find(r => r.id === newBooking.value.room_id);
});

// Watch selected room to auto-adjust guests count
watch(selectedNewBookingRoom, (newRoom) => {
  if (newRoom) {
    if (newBooking.value.guests > newRoom.max_occupancy) {
      newBooking.value.guests = newRoom.max_occupancy;
    }
    if (newRoom.min_occupancy && newBooking.value.guests < newRoom.min_occupancy) {
      newBooking.value.guests = newRoom.min_occupancy;
    }
    if (newRoom.room_type.toLowerCase().includes('single')) {
      newBooking.value.guests = 1;
    }
  }
});

// Watch guests count to stay within selected room constraints
watch(() => newBooking.value.guests, (newGuests) => {
  if (selectedNewBookingRoom.value) {
    if (newGuests > selectedNewBookingRoom.value.max_occupancy) {
      newBooking.value.guests = selectedNewBookingRoom.value.max_occupancy;
    }
    if (selectedNewBookingRoom.value.min_occupancy && newGuests < selectedNewBookingRoom.value.min_occupancy) {
      newBooking.value.guests = selectedNewBookingRoom.value.min_occupancy;
    }
  }
});


const guestForm = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: ''
});

const todayDateTimeStr = computed(() => {
  const today = new Date();
  today.setSeconds(0, 0);
  return today.toISOString().slice(0, 16);
});

const formatToDateTimeLocal = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const tzOffset = date.getTimezoneOffset() * 60000;
  const localISOTime = (new Date(date - tzOffset)).toISOString().slice(0, 16);
  return localISOTime;
};

const fetchReservations = async () => {
  try {
    const response = await axios.get('/api/reservations');
    reservations.value = response.data;
    
    // Check if there is an id in the query parameters to open
    if (route.query.id) {
      const resId = parseInt(route.query.id);
      const res = reservations.value.find(r => r.id === resId);
      if (res) {
        openReservationDetailModal(res);
      }
    }
  } catch (err) {
    console.error('Failed to fetch reservations', err);
    reservations.value = [];
  }
};

const fetchUsers = async () => {
  try {
    const response = await axios.get('/api/admin/guests');
    users.value = response.data;
  } catch (err) {
    console.error('Failed to fetch users', err);
  }
};

const registerGuest = async () => {
  isRegistering.value = true;
  try {
    const response = await axios.post('/api/admin/guests', guestForm.value);
    notify.success('Guest Registered', 'The guest has been successfully registered.');
    
    // Refresh user list and select the new user
    await fetchUsers();
    newBooking.value.user_id = response.data.id;
    
    // Reset and close modal
    guestForm.value = { first_name: '', last_name: '', email: '', phone: '' };
    showRegisterGuestModal.value = false;
  } catch (err) {
    console.error('Registration failed', err);
    notify.error('Register Failed', err.response?.data?.message || 'Could not register guest.');
  } finally {
    isRegistering.value = false;
  }
};

const fetchAvailableRooms = async () => {
  try {
    const params = {};
    if (newBooking.value.check_in && newBooking.value.check_out) {
       params.check_in = newBooking.value.check_in;
       params.check_out = newBooking.value.check_out;
    }
    const response = await axios.get('/api/rooms', { params });
    // Filter out only available rooms generally, or specific logic if backend handles it
    availableRooms.value = response.data.filter(r => r.status === 'available');
  } catch (err) {
    console.error('Failed to fetch rooms', err);
  }
};

const openNewBookingModal = () => {
  showNewBookingModal.value = true;
  fetchUsers();
  fetchAvailableRooms();
  // Reset form
  newBooking.value = { user_id: '', room_id: '', check_in: '', check_out: '', guests: 1 };
};

const closeNewBookingModal = () => {
  showNewBookingModal.value = false;
};

const validateDates = () => {
  if (newBooking.value.check_in && newBooking.value.check_out) {
     if (newBooking.value.check_out <= newBooking.value.check_in) {
        newBooking.value.check_out = '';
        notify.warning('Invalid Date', 'Check-out date must be after check-in date.');
     } else {
        fetchAvailableRooms(); // Refresh available rooms based on dates
     }
  }
};

const viewRoomDetails = () => {
  const room = availableRooms.value.find(r => r.id === newBooking.value.room_id);
  if (room) {
    selectedRoomDetail.value = room;
    showRoomDetailModal.value = true;
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

const createBooking = async () => {
   isSubmitting.value = true;
   try {
     await axios.post('/api/reservations', {
       ...newBooking.value,
       created_by_admin: true // Flag to backend if needed
     });
     notify.success('Success', 'Booking created successfully.');
     closeNewBookingModal();
     fetchReservations();
   } catch (err) {
     console.error('Create booking failed', err);
     notify.error('Failed', err.response?.data?.message || 'Could not create booking.');
   } finally {
     isSubmitting.value = false;
   }
};

const filteredReservations = computed(() => {
  if (!searchQuery.value) return reservations.value;
  const q = searchQuery.value.toLowerCase();
  return reservations.value.filter(res => {
    const resId = `res-${res.id.toString().padStart(3, '0')}`;
    return (
      res.user?.name?.toLowerCase().includes(q) || 
      res.user?.email?.toLowerCase().includes(q) || 
      res.user?.phone?.toLowerCase().includes(q) || 
      res.room?.room_number?.toString().includes(q) ||
      res.room?.room_type?.toLowerCase().includes(q) ||
      res.status?.toLowerCase().includes(q) ||
      resId.includes(q.replace('#', '')) ||
      res.id.toString().includes(q)
    );
  });
});

const paginatedReservations = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredReservations.value.slice(start, end);
});

// Reset to page 1 when searching
watch(searchQuery, () => {
  currentPage.value = 1;
});

watch(() => route.query.id, (newId) => {
  if (newId) {
    const resId = parseInt(newId);
    const res = reservations.value.find(r => r.id === resId);
    if (res) {
      openReservationDetailModal(res);
    }
  }
});

const updateStatus = async (res) => {
  let cancellationReason = null;

  if (res.status === 'cancelled') {
    if (res.cancellation_reason) {
      const confirmApprove = await Swal.fire({
        title: 'Approve Cancellation?',
        text: `Do you want to approve this cancellation request? Reason provided by guest: "${res.cancellation_reason}"`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#718096',
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Abort',
        reverseButtons: true
      });

      if (!confirmApprove.isConfirmed) {
        fetchReservations();
        return;
      }
      cancellationReason = res.cancellation_reason;
    } else {
      const { value: text, isDismissed } = await Swal.fire({
        title: 'Reason for Cancellation',
        html: `
          <div class="text-start">
            <p class="small text-muted mb-3">Please specify why this reservation is being cancelled. This will be recorded and displayed to the guest.</p>
            <div class="mb-3">
              <label for="swal-cancellation-select" class="form-label small fw-bold text-muted text-uppercase">Cancellation Reason</label>
              <select id="swal-cancellation-select" class="form-select py-2">
                <option value="" disabled selected>Select a reason...</option>
                <option value="Guest request">Guest request</option>
                <option value="Booking error / Overbooking">Booking error / Overbooking</option>
                <option value="No-show / Policy violation">No-show / Policy violation</option>
                <option value="Maintenance / Facility issue">Maintenance / Facility issue</option>
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
        cancelButtonText: 'Abort',
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
        fetchReservations();
        return;
      }
      cancellationReason = text;
    }
  }

  try {
    await axios.put(`/api/reservations/${res.id}`, { 
      status: res.status, 
      cancellation_reason: cancellationReason 
    });
    notify.success('Status Updated', `Reservation #RES-${res.id.toString().padStart(3, '0')} is now ${res.status.toUpperCase()}.`);
    
    // Redirect to payments if confirmed (optional, but requested)
    if (res.status === 'confirmed') {
        router.push('/admin/payments');
    }
    
    fetchReservations();
  } catch (err) {
    console.error('Failed to update status', err);
    notify.error('Update Failed', 'Could not save status change to the server.');
    fetchReservations();
  }
};

const approveCancellation = async (res) => {
  const result = await Swal.fire({
    title: 'Approve Cancellation?',
    text: `Are you sure you want to approve the cancellation request for Reservation #RES-${res.id.toString().padStart(3, '0')}? This will cancel the booking and process the refund.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#28a745',
    cancelButtonColor: '#718096',
    confirmButtonText: 'Yes, Approve',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  });

  if (!result.isConfirmed) return;

  try {
    await axios.put(`/api/reservations/${res.id}`, { 
      status: 'cancelled',
      cancellation_reason: res.cancellation_reason
    });
    notify.success('Cancellation Approved', `Reservation #RES-${res.id.toString().padStart(3, '0')} has been cancelled and refunded.`);
    
    // Update local state if details modal is open
    if (selectedReservation.value && selectedReservation.value.id === res.id) {
      selectedReservation.value.status = 'cancelled';
      selectedReservation.value.payment_status = 'refunded';
    }
    fetchReservations();
  } catch (err) {
    console.error('Failed to approve cancellation', err);
    notify.error('Action Failed', err.response?.data?.message || 'Could not approve cancellation.');
  }
};

const declineCancellation = async (res) => {
  const result = await Swal.fire({
    title: 'Decline Cancellation?',
    text: `Are you sure you want to decline the cancellation request for Reservation #RES-${res.id.toString().padStart(3, '0')}? This will restore the booking to CONFIRMED.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#718096',
    confirmButtonText: 'Yes, Decline & Restore',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  });

  if (!result.isConfirmed) return;

  try {
    await axios.put(`/api/reservations/${res.id}`, { 
      status: 'confirmed'
    });
    notify.success('Cancellation Declined', `Reservation #RES-${res.id.toString().padStart(3, '0')} remains CONFIRMED.`);
    
    // Update local state if details modal is open
    if (selectedReservation.value && selectedReservation.value.id === res.id) {
      selectedReservation.value.status = 'confirmed';
      selectedReservation.value.cancellation_reason = null;
    }
    fetchReservations();
  } catch (err) {
    console.error('Failed to decline cancellation', err);
    notify.error('Action Failed', err.response?.data?.message || 'Could not decline cancellation.');
  }
};

const updatePaymentStatus = async (res) => {
  try {
    const response = await axios.put(`/api/reservations/${res.id}`, { payment_status: res.payment_status });
    // If backend auto-confirmed it, update local status
    if (response.data.status) {
        res.status = response.data.status;
    }
    notify.success('Payment Updated', `Reservation #RES-${res.id.toString().padStart(3, '0')} marked as ${res.payment_status.toUpperCase()}.`);
  } catch (err) {
    console.error('Failed to update payment status', err);
    notify.error('Update Failed', 'Could not save payment change to the server.');
    fetchReservations();
  }
};

const updateCheckTimes = async (res, field, value) => {
  try {
    await axios.put(`/api/reservations/${res.id}`, { [field]: value });
    res[field] = value; // Update local state
    notify.success('Time Updated', `Reservation ${field.replace('_', ' ')} updated successfully.`);
  } catch (err) {
    console.error(`Failed to update ${field}`, err);
    notify.error('Update Failed', err.response?.data?.message || 'Conflict detected or server error.');
    fetchReservations(); // Revert
  }
};

const syncPayment = async (res) => {
    res.isSyncing = true;
    try {
        const response = await axios.post(`/api/reservations/${res.id}/sync-payment`);
        if (response.data.status === 'confirmed') {
            res.status = 'confirmed';
            res.payment_status = 'paid';
        }
        notify.success('Sync Successful', response.data.message);
        // Refresh full data to show new payment details in other views if needed
        fetchReservations();
    } catch (err) {
        console.error('Sync failed', err);
        notify.error('Sync Failed', err.response?.data?.message || 'Could not synchronize with Xendit.');
    } finally {
        res.isSyncing = false;
    }
};

const statusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-warning-subtle text-warning',
    confirmed: 'bg-success-subtle text-success',
    completed: 'bg-info-subtle text-info',
    cancelled: 'bg-danger-subtle text-danger',
    cancellation_pending: 'bg-warning-subtle text-warning border border-warning-subtle'
  };
  return classes[status] || 'bg-light text-muted';
};

const paymentBadgeClass = (status) => {
  const classes = {
    unpaid: 'bg-danger-subtle text-danger',
    partially_paid: 'bg-warning-subtle text-warning',
    paid: 'bg-success-subtle text-success',
    refunded: 'bg-secondary-subtle text-secondary'
  };
  return classes[status] || 'bg-light text-muted';
};

const deleteReservation = async (id) => {
  const res = await confirm({
    title: 'Delete Booking?',
    text: 'Are you sure you want to delete this booking?',
    confirmText: 'Yes, Delete'
  });

  if (res.isConfirmed) {
    try {
      await axios.delete(`/api/reservations/${id}`);
      reservations.value = reservations.value.filter(r => r.id !== id);
      notify.success('Deleted', 'Booking removed successfully.');
    } catch (err) {
      console.error('Failed to delete reservation', err);
      notify.error('Error', 'Could not delete booking from the server.');
    }
  }
};

const formatPrice = (price) => {
  return parseFloat(price || 0).toLocaleString();
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const totalReservationNights = (res) => {
  if (!res || !res.check_in || !res.check_out) return 0;
  const start = new Date(res.check_in.replace(' ', 'T'));
  const end = new Date(res.check_out.replace(' ', 'T'));
  const diff = end - start;
  const nights = Math.round(diff / (1000 * 60 * 60 * 24));
  return nights > 0 ? nights : 0;
};

const fetchBlockedDates = async () => {
  try {
    const response = await axios.get('/api/admin/blocked-dates');
    blockedDates.value = response.data;
  } catch (err) {
    console.error('Failed to fetch blocked dates', err);
  }
};


const openManageBlockedDatesModal = () => {
  blockForm.room_id = '';
  blockForm.start_date = '';
  blockForm.end_date = '';
  blockForm.reason = '';
  showBlockedDatesModal.value = true;
  fetchAllRooms();
  fetchBlockedDates();
};

const fetchAllRooms = async () => {
  try {
    const response = await axios.get('/api/rooms');
    allRooms.value = response.data;
  } catch (err) {
    console.error('Failed to fetch rooms', err);
  }
};

const submitBlockDates = async () => {
  if (blockForm.end_date <= blockForm.start_date) {
    notify.error('Invalid Dates', 'End date must be after start date.');
    return;
  }
  
  isSubmittingBlock.value = true;
  try {
    const response = await axios.post('/api/admin/blocked-dates', blockForm);
    notify.success('Success', response.data.message || 'Dates blocked successfully.');
    // Refresh lists
    fetchBlockedDates();
    fetchReservations(); // To clear cache and reload
    // Reset form
    blockForm.room_id = '';
    blockForm.start_date = '';
    blockForm.end_date = '';
    blockForm.reason = '';
  } catch (err) {
    console.error('Failed to block dates', err);
    notify.error('Failed', err.response?.data?.message || 'Could not block dates.');
  } finally {
    isSubmittingBlock.value = false;
  }
};

const deleteBlockedDate = async (id) => {
  const isConfirmed = await Swal.fire({
    title: 'Unblock Dates?',
    text: 'Are you sure you want to remove this block? Guests will be able to reserve during these dates again.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#718096',
    confirmButtonText: 'Yes, Unblock'
  });
  
  if (isConfirmed.isConfirmed) {
    try {
      const response = await axios.delete(`/api/admin/blocked-dates/${id}`);
      notify.success('Success', response.data.message || 'Dates unblocked successfully.');
      fetchBlockedDates();
      fetchReservations();
    } catch (err) {
      console.error('Failed to delete blocked date', err);
      notify.error('Failed', 'Could not delete blocked date.');
    }
  }
};

const formatDateShort = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

onMounted(() => {
  fetchReservations();
  fetchBlockedDates();
});
</script>

<style scoped>
.bg-gold-subtle { 
  background-color: var(--primary-gold-subtle) !important; 
}
.bg-light-danger {
  background-color: #FFF5F5;
}

.table th {
  background-color: transparent !important;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.spin {
  display: inline-block;
  animation: spin 1s linear infinite;
}

.form-select-sm:focus {
  box-shadow: none;
}

.btn-action-invoice, .btn-action-delete {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 1.1rem;
}

.btn-action-invoice {
  background-color: var(--primary-gold-subtle);
  color: var(--primary-gold);
}

.btn-action-invoice:hover {
  background-color: var(--primary-gold);
  color: white;
  transform: translateY(-3px) rotate(8deg);
  box-shadow: 0 5px 15px rgba(188, 145, 81, 0.3);
}

.btn-action-delete {
  background-color: #fee2e2;
  color: #ef4444;
}

.btn-action-delete:hover {
  background-color: #ef4444;
  color: white;
  transform: translateY(-3px) rotate(-8deg);
  box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
}

.btn-action-view {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 1.1rem;
  background-color: #f1f5f9;
  color: #64748b;
}

.btn-action-view:hover {
  background-color: #e2e8f0;
  color: var(--secondary-dark);
  transform: translateY(-3px) rotate(-8deg);
  box-shadow: 0 5px 15px rgba(100, 116, 139, 0.2);
}

.detail-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.detail-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05) !important;
}

.bg-white-glass {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(4px);
}

.font-monospace {
  font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

/* Modal Styles */
.form-label-custom {
  font-size: 0.75rem;
  letter-spacing: 0.5px;
}

.form-control-custom {
  border-color: #eee;
  font-size: 0.9rem;
}

.form-control-custom:focus {
  border-color: var(--primary-gold);
  box-shadow: 0 0 0 4px var(--primary-gold-subtle);
}

.bg-gradient-dark {
  background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
}

/* Admin Calendar Styles */
.admin-calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  background-color: #f1f5f9; /* Slate 100 as grid border */
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
}

.admin-calendar-header {
  background-color: #f8fafc;
  padding: 12px 8px;
  text-align: center;
  font-weight: 700;
  font-size: 0.75rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #e2e8f0;
}

.admin-calendar-cell {
  background-color: white;
  min-height: 120px;
  padding: 8px;
  display: flex;
  flex-direction: column;
  transition: background-color 0.2s ease;
  position: relative;
  cursor: pointer;
}

.admin-calendar-cell:hover:not(.empty) {
  background-color: #fafaf9;
}

.admin-calendar-cell.empty {
  background-color: #f8fafc;
  cursor: default;
}

.cell-day-num {
  font-weight: 700;
  font-size: 0.85rem;
  color: #475569;
  padding: 2px 6px;
}

.today-badge {
  background-color: var(--primary-gold);
  color: white !important;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  box-shadow: 0 2px 6px rgba(188, 145, 81, 0.3);
}

.cell-bookings {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex-grow: 1;
  overflow-y: auto;
  max-height: 85px;
  margin-top: 4px;
}

/* Booking Ribbons */
.booking-ribbon {
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 700;
  cursor: pointer;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 4px;
}

.booking-ribbon:hover {
  transform: translateY(-1px);
  filter: brightness(0.95);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.booking-ribbon.pending {
  background-color: #FEF3C7; /* Yellow 100 */
  color: #D97706; /* Yellow 600 */
  border-left: 3px solid #F59E0B;
}

.booking-ribbon.cancellation_pending {
  background-color: #FEF3C7; /* Yellow 100 */
  color: #D97706; /* Yellow 600 */
  border-left: 3px dashed #F59E0B;
}

.booking-ribbon.confirmed {
  background-color: #D1FAE5; /* Green 100 */
  color: #059669; /* Green 600 */
  border-left: 3px solid #10B981;
}

.booking-ribbon.completed {
  background-color: #DBEAFE; /* Blue 100 */
  color: #2563EB; /* Blue 600 */
  border-left: 3px solid #3B82F6;
}

.booking-ribbon.cancelled {
  background-color: #FEE2E2; /* Red 100 */
  color: #DC2626; /* Red 600 */
  border-left: 3px solid #EF4444;
}

.booking-ribbon.blocked {
  background-color: #F1F5F9; /* Slate 100 */
  color: #475569; /* Slate 600 */
  border-left: 3px solid #64748B;
  background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(100, 116, 139, 0.05) 10px, rgba(100, 116, 139, 0.05) 20px);
}

.legend-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.min-w-150 {
  min-width: 180px;
}

.calend-nav-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
}

.calend-nav-btn:hover {
  background-color: var(--primary-gold-subtle);
  color: var(--primary-gold);
  transform: scale(1.05);
  border-color: var(--primary-gold-subtle);
}
</style>
