<template>
  <div class="rooms-inventory-view">
    <!-- Header Summary Stats -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="stats-card p-4 rounded-4 bg-white shadow-sm border-0">
          <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-primary-gold-subtle text-primary-gold rounded-3">
              <i class="bi bi-door-closed fs-4"></i>
            </div>
            <div>
              <h3 class="fw-bold mb-0 text-secondary-dark">{{ rooms.length }}</h3>
              <p class="text-muted small mb-0">Total Rooms</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stats-card p-4 rounded-4 bg-white shadow-sm border-0">
          <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-success-subtle text-success rounded-3">
              <i class="bi bi-check-circle fs-4"></i>
            </div>
            <div>
              <h3 class="fw-bold mb-0 text-secondary-dark text-success">{{ availableRoomsCount }}</h3>
              <p class="text-muted small mb-0">Available Now</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden animate-fade-up">
      <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
        <div>
          <h5 class="serif-font fw-bold mb-0 text-secondary-dark">All Rooms</h5>
          <p class="text-muted small mb-0">List of all rooms in the building.</p>
        </div>
        <div class="d-flex gap-2">
           <div class="input-group input-group-sm" style="width: 280px;">
             <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
             <input type="text" class="form-control bg-light border-0" placeholder="Search Room # or Type..." v-model="searchQuery">
           </div>
           <button class="btn btn-gold px-4 d-flex align-items-center gap-2 rounded-pill shadow-sm py-2" @click="openAddModal">
             <i class="bi bi-plus-lg fw-bold"></i> 
             <span class="fw-bold text-uppercase small tracking-wider">Add Room</span>
           </button>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light border-bottom">
              <tr>
                <th class="ps-4 py-3 text-muted small fw-bold text-uppercase tracking-wider">Room Details</th>
                <th class="py-3 text-muted small fw-bold text-uppercase tracking-wider">Type & Size</th>
                <th class="py-3 text-muted small fw-bold text-uppercase tracking-wider">Price / Night</th>
                <th class="py-3 text-muted small fw-bold text-uppercase tracking-wider">Status</th>
                <th class="pe-4 py-3 text-muted small fw-bold text-uppercase tracking-wider text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="6" class="py-5 text-center">
                  <div class="spinner-border text-gold" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <p class="text-muted mt-2">Loading rooms...</p>
                </td>
              </tr>
              <tr v-for="room in paginatedRooms" :key="room.id" v-else>
                <td class="ps-4 py-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="room-thumb rounded-3 overflow-hidden shadow-sm" style="width: 70px; height: 50px;">
                      <img :src="room.image || getDefaultImage(room.room_type)" class="w-100 h-100 object-fit-cover">
                    </div>
                    <div>
                      <span class="fw-bold text-secondary-dark d-block">Room #{{ room.room_number }}</span>
                      <small class="text-muted"><i class="bi bi-people me-1"></i>Max {{ room.max_occupancy }} Guests</small>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="d-block fw-semibold text-dark">{{ room.room_type }}</span>
                  <small class="text-muted" v-if="room.room_size"><i class="bi bi-arrows-fullscreen me-1 small"></i>{{ room.room_size }} sq.m</small>
                </td>
                <td><span class="fw-bold text-gold fs-5">₱{{ formatPrice(room.price_per_night) }}</span></td>
                <td>
                  <span class="badge rounded-pill fw-bold text-uppercase px-3 py-2" :class="statusBadgeClass(room.status)" style="font-size: 0.7rem;">
                    {{ room.status }}
                  </span>
                </td>
                <td class="pe-4 text-end">
                  <div class="btn-group gap-2">
                    <button class="btn btn-icon btn-light" @click="viewRoom(room)" title="View Room">
                      <i class="bi bi-eye text-secondary-dark"></i>
                    </button>
                    <button class="btn btn-icon btn-light-gold" @click="openEditModal(room)" title="Edit Room">
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-icon btn-light-danger" @click="confirmDelete(room.id)" title="Delete Room">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredRooms.length === 0 && !loading">
                <td colspan="6" class="text-center py-5 text-muted">
                  <i class="bi bi-inbox fs-1 opacity-25 d-block mb-3"></i>
                  No rooms found matching your search.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <AdminPagination 
          :current-page="currentPage" 
          :total-items="filteredRooms.length" 
          :page-size="pageSize"
          @change="page => currentPage = page"
          @page-size-change="size => { pageSize = size; currentPage = 1; }"
        />
      </div>
    </div>

    <!-- Advanced UI/UX Modular Modal -->
    <div v-if="showModal" class="modal-overlay d-flex align-items-center justify-content-center p-3 animate-fade-in">
      <div class="modal-card-premium bg-white rounded-5 shadow-2xl w-100 d-flex flex-column overflow-hidden" style="max-width: 1000px; height: 90vh; max-height: 850px;">
        
        <!-- Premium Modal Header -->
        <div class="modal-header-premium p-4 d-flex justify-content-between align-items-center border-bottom bg-white-glass">
          <div class="d-flex align-items-center gap-3">
             <div class="brand-icon-box bg-gold text-white rounded-4 d-flex align-items-center justify-content-center shadow-gold">
               <i class="bi bi-house-add-fill fs-4" v-if="!editingRoom"></i>
               <i class="bi bi-pencil-square fs-4" v-else></i>
             </div>
             <div>
               <h4 class="serif-font fw-bold mb-0 text-dark">{{ editingRoom ? 'Edit Room' : 'Add Room' }}</h4>
               <p class="text-muted small mb-0">{{ editingRoom ? 'Changing Room #' + editingRoom.room_number : 'Add a new room to the system.' }}</p>
             </div>
          </div>
          <button class="btn-close-premium" @click="closeModal">
            <i class="bi bi-xl"></i>
          </button>
        </div>

        <!-- Modal Body with Layout Sidebar & Content -->
        <div class="modal-body-premium d-flex flex-grow-1 overflow-hidden">
          
          <!-- Modal Sidebar Tabs -->
          <div class="modal-sidebar bg-cream-subtle p-3 border-end d-none d-md-block" style="width: 220px;">
            <ul class="nav flex-column gap-2 list-unstyled">
              <li v-for="tab in formTabs" :key="tab.id">
                <button @click="activeTab = tab.id" 
                  class="nav-tab-btn py-3 px-3 rounded-4 w-100 d-flex align-items-center gap-3 transition-all"
                  :class="{'active-tab shadow-sm': activeTab === tab.id}">
                  <i :class="['bi', tab.icon, 'fs-5']"></i>
                  <span class="fw-bold small text-uppercase tracking-wider">{{ tab.name }}</span>
                </button>
              </li>
            </ul>

            <!-- Visual Preview Card Shortcut (Bottom of Sibebar) -->
            <div class="mt-auto p-3 bg-white rounded-4 border border-dashed border-gold-light opacity-75">
               <div class="small fw-bold text-gold text-uppercase mb-2 text-center" style="font-size: 0.6rem;">Live Specification</div>
               <div class="d-flex flex-column align-items-center">
                  <span class="h4 mb-0 fw-bold serif-font text-dark">{{ form.room_number || '---' }}</span>
                  <span class="small text-muted">{{ form.room_type.split(' ')[0] }}</span>
               </div>
            </div>
          </div>

          <!-- Main Form Content -->
          <div class="flex-grow-1 p-4 p-lg-5 overflow-y-auto bg-white custom-scrollbar">
            <form id="roomPremiumForm" @submit.prevent="saveRoom">
              
              <!-- TAB 1: BASIC INFORMATION -->
              <div v-show="activeTab === 'basic'" class="animate-tab-content">
                 <div class="section-title-premium mb-4">
                    <span class="badge bg-gold-subtle text-gold mb-2">Step 1</span>
                    <h5 class="fw-bold text-dark">Room Info</h5>
                    <p class="text-muted small">Enter the room number and type.</p>
                 </div>

                 <div class="row g-4">
                    <div class="col-md-6">
                       <div class="floating-group">
                          <input type="text" v-model="form.room_number" class="input-modern-xl" placeholder=" " required id="f_room_no">
                          <label for="f_room_no">Unit / Room Number</label>
                       </div>
                    </div>
                    <div class="col-md-6">
                       <div class="floating-group">
                          <select v-model="form.room_type" class="input-modern-xl" required id="f_room_type">
                             <option value="Standard Single">Standard Single</option>
                             <option value="Deluxe Double">Deluxe Double</option>
                             <option value="Family Suite">Family Suite</option>
                             <option value="Premium Suite">Premium Suite</option>
                          </select>
                          <label for="f_room_type">Room Type</label>
                       </div>
                    </div>
                    <div class="col-md-12">
                       <div class="floating-group">
                          <textarea v-model="form.description" class="input-modern-xl" rows="4" placeholder=" " id="f_desc"></textarea>
                          <label for="f_desc">Room Details</label>
                       </div>
                    </div>
                 </div>
              </div>

              <!-- TAB 2: PRICING & SPECS -->
              <div v-show="activeTab === 'specs'" class="animate-tab-content">
                 <div class="section-title-premium mb-4">
                    <span class="badge bg-gold-subtle text-gold mb-2">Step 2</span>
                    <h5 class="fw-bold text-dark">Price & Details</h5>
                    <p class="text-muted small">Set how much it costs and how many guests.</p>
                 </div>

                 <div class="row g-4">
                    <div class="col-md-6">
                       <div class="floating-group price-field">
                          <input type="number" v-model="form.price_per_night" class="input-modern-xl" placeholder=" " required id="f_price">
                          <label for="f_price">Nightly Rate (PHP)</label>
                          <span class="currency-prefix">₱</span>
                       </div>
                    </div>
                    <div class="col-md-6">
                       <div class="floating-group">
                          <input type="number" v-model="form.max_occupancy" class="input-modern-xl" placeholder=" " required id="f_max">
                          <label for="f_max">Maximum Guests</label>
                       </div>
                    </div>
                    <div class="col-md-6">
                       <div class="floating-group">
                          <input type="text" v-model="form.bed_type" class="input-modern-xl" placeholder=" " id="f_bed">
                          <label for="f_bed">Bed Configuration</label>
                       </div>
                    </div>
                    <div class="col-md-6">
                       <div class="floating-group">
                          <input type="text" v-model="form.room_size" class="input-modern-xl" placeholder=" " id="f_size">
                          <label for="f_size">Floor Area (sq.m)</label>
                       </div>
                    </div>
                    <div class="col-md-12">
                        <label class="small fw-bold text-uppercase text-muted mb-3 d-block tracking-widest">Available?</label>
                        <div class="status-selector-modern d-flex gap-3">
                           <div class="status-option flex-grow-1">
                              <input type="radio" class="btn-check" name="room_status" id="s_avail" value="available" v-model="form.status">
                              <label class="btn btn-status-modern w-100 py-3" for="s_avail">
                                 <i class="bi bi-check-circle-fill me-2"></i> Ready for Guests
                              </label>
                           </div>
                           <div class="status-option flex-grow-1">
                               <input type="radio" class="btn-check" name="room_status" id="s_maint" value="maintenance" v-model="form.status">
                               <label class="btn btn-status-modern maint w-100 py-3" for="s_maint">
                                  <i class="bi bi-tools me-2"></i> Maintenance
                               </label>
                            </div>
                        </div>
                    </div>
                 </div>
              </div>

              <!-- TAB 3: MEDIA & AMENITIES -->
              <div v-show="activeTab === 'media'" class="animate-tab-content">
                 <div class="section-title-premium mb-4">
                    <span class="badge bg-gold-subtle text-gold mb-2">Step 3</span>
                    <h5 class="fw-bold text-dark">Photos & Features</h5>
                    <p class="text-muted small">Add photos and what's inside the room.</p>
                 </div>

                  <div class="row g-4">
                    <div class="col-12">
                       <label class="small fw-bold text-uppercase text-muted mb-3 d-block tracking-widest">Main Room Image</label>
                       <div class="image-preview-slot rounded-5 border-2 border-dashed border-gold-light mb-4 overflow-hidden position-relative group cursor-pointer" @click="$refs.fileInput.click()">
                          <img v-if="imagePreview" :src="imagePreview" class="w-100 h-100 object-fit-cover">
                          <img v-else-if="form.image" :src="form.image" class="w-100 h-100 object-fit-cover">
                          <div v-else class="h-100 d-flex flex-column align-items-center justify-content-center text-muted py-5">
                             <i class="bi bi-cloud-arrow-up fs-1 opacity-25 mb-2"></i>
                             <span class="small fw-semibold">Click to Upload Main Photo</span>
                          </div>
                          
                          <!-- Remove Main Image Button -->
                          <button v-if="imagePreview || form.image" type="button" @click.stop="removeMainImage" class="btn-remove-img shadow-sm position-absolute top-0 end-0 m-3 z-3" title="Remove Main Image">
                             <i class="bi bi-x-lg"></i>
                          </button>

                          <div class="preview-overlay position-absolute inset-0 bg-dark bg-opacity-25 d-flex align-items-center justify-content-center opacity-0 group-hover-opacity-100 transition-all">
                             <button type="button" class="btn btn-light btn-sm rounded-pill px-3">
                                <i class="bi bi-camera-fill me-2"></i> Change Main Photo
                             </button>
                          </div>
                       </div>
                       <input type="file" ref="fileInput" class="d-none" @change="handleFileChange" accept="image/*">
                    </div>

                    <!-- Additional Images Section -->
                    <div class="col-12 mt-2">
                       <label class="small fw-bold text-uppercase text-muted mb-3 d-block tracking-widest d-flex justify-content-between">
                          Gallery Images
                          <span class="text-gold">{{ existingImages.length + multipleImagePreviews.length }} Total</span>
                       </label>
                       
                       <div class="gallery-upload-grid d-grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));">
                          <!-- Existing Images -->
                          <div v-for="img in existingImages" :key="img.id" class="gallery-item-premium rounded-4 overflow-hidden border position-relative shadow-sm aspect-square">
                             <img :src="img.image_path" class="w-100 h-100 object-fit-cover">
                             <button type="button" @click="removeExistingImage(img.id)" class="btn-remove-img shadow-sm position-absolute top-0 end-0 m-1">
                                <i class="bi bi-x"></i>
                             </button>
                          </div>

                          <!-- New Upload Previews -->
                          <div v-for="(preview, idx) in multipleImagePreviews" :key="idx" class="gallery-item-premium rounded-4 overflow-hidden border border-gold-subtle position-relative shadow-sm aspect-square animate-fade-in">
                             <img :src="preview" class="w-100 h-100 object-fit-cover">
                             <div class="new-badge position-absolute top-0 start-0 bg-gold text-white x-small px-2 py-0">NEW</div>
                             <button type="button" @click="removeNewImage(idx)" class="btn-remove-img shadow-sm position-absolute top-0 end-0 m-1">
                                <i class="bi bi-x"></i>
                             </button>
                          </div>

                          <!-- Add Button -->
                          <div class="gallery-add-card rounded-4 border-2 border-dashed border-gold-light d-flex flex-column align-items-center justify-content-center cursor-pointer hover-bg-light transition-all aspect-square" @click="$refs.multipleFileInput.click()">
                             <i class="bi bi-plus-circle text-gold fs-3 mb-1"></i>
                             <span class="x-small fw-bold text-muted text-uppercase">Add more</span>
                          </div>
                       </div>
                       <input type="file" ref="multipleFileInput" class="d-none" @change="handleMultipleFilesChange" accept="image/*" multiple>
                    </div>

                    <div class="col-12 mt-5">
                       <label class="small fw-bold text-uppercase text-muted mb-4 d-block tracking-widest d-flex justify-content-between">
                          Inclusive Amenities
                          <span class="text-gold">{{ form.amenities.length }} Selected</span>
                       </label>
                       <div class="row g-3">
                          <div v-for="amenity in allAmenities" :key="amenity.id" class="col-lg-4 col-sm-6">
                             <div class="amenity-checkbox-modern position-relative" :class="{'selected': form.amenities.includes(amenity.id)}">
                                 <input type="checkbox" :id="'am-'+amenity.id" :value="amenity.id" v-model="form.amenities" class="d-none">
                                 <label :for="'am-'+amenity.id" class="w-100 h-100 p-3 rounded-4 border transition-all d-flex align-items-center gap-3">
                                    <div class="amenity-icon-mini shadow-sm rounded-circle d-flex align-items-center justify-content-center">
                                       <i :class="['bi', formatIconClass(amenity.icon)]"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                       <span class="d-block fw-bold small text-truncate">{{ amenity.name }}</span>
                                    </div>
                                    <div class="ms-auto selected-dot shadow-sm"></div>
                                 </label>
                             </div>
                          </div>
                       </div>
                    </div>
                 </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Premium Modal Footer -->
        <div class="modal-footer-premium p-4 border-top bg-white d-flex justify-content-between align-items-center">
           <div class="d-flex gap-2 align-items-center overflow-hidden">
              <div class="progress-steps d-flex gap-2">
                 <div v-for="tab in formTabs" :key="tab.id" class="step-dot" :class="{'active-dot': activeTab === tab.id, 'done-dot': isTabFilled(tab.id)}"></div>
              </div>
              <span class="ms-2 small text-muted d-none d-sm-block">{{ activeTabLabel }}</span>
           </div>
           
           <div class="d-flex gap-3">
              <button type="button" class="btn btn-outline-modern px-4 py-2 fw-semibold rounded-pill" @click="closeModal">Discard</button>
              
              <button v-if="activeTab !== 'media'" @click="activeTab = getNextTab()" type="button" class="btn btn-dark-modern px-4 py-2 rounded-pill d-flex align-items-center gap-2">
                 <span>Next Section</span>
                 <i class="bi bi-arrow-right"></i>
              </button>
              
              <button v-else type="submit" form="roomPremiumForm" class="btn btn-gold px-5 py-2 rounded-pill shadow-gold d-flex align-items-center gap-2" :disabled="saving">
                 <span v-if="saving" class="spinner-border spinner-border-sm"></span>
                 <span class="fw-bold text-uppercase tracking-wider small">{{ editingRoom ? 'Save Changes' : 'Save Room' }}</span>
              </button>
           </div>
        </div>

      </div>
    </div>

    <!-- View Room Details Modal -->
    <div v-if="showViewModal" class="modal-overlay d-flex align-items-center justify-content-center p-3 animate-fade-in">
      <div class="modal-card-premium bg-white rounded-5 shadow-2xl w-100 d-flex flex-column overflow-hidden" style="max-width: 800px; max-height: 90vh;">
        <div class="modal-header-premium p-4 d-flex justify-content-between align-items-center border-bottom bg-white-glass">
          <div class="d-flex align-items-center gap-3">
             <div class="brand-icon-box bg-gold text-white rounded-circle d-flex align-items-center justify-content-center shadow-gold">
               <i class="bi bi-door-open fs-4"></i>
             </div>
             <div>
               <h4 class="serif-font fw-bold mb-0 text-dark">Room #{{ selectedRoom.room_number }}</h4>
               <p class="text-muted small mb-0">{{ selectedRoom.room_type }} Details</p>
             </div>
          </div>
          <button @click="showViewModal = false" class="btn-close shadow-none"></button>
        </div>
        <div class="modal-body-premium p-4 p-lg-5 overflow-y-auto">
          <div class="row g-4">
            <div class="col-md-5">
              <div class="rounded-4 overflow-hidden shadow-sm mb-4">
                <img :src="selectedRoom.image || getDefaultImage(selectedRoom.room_type)" class="w-100 object-fit-cover" style="height: 250px;">
              </div>
              
              <!-- Additional Images in View Modal -->
              <div v-if="selectedRoom.images && selectedRoom.images.length > 0" class="mb-4">
                 <h6 class="x-small fw-bold text-uppercase text-muted mb-2 letter-spacing-wide">Gallery</h6>
                 <div class="d-flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                    <div v-for="img in selectedRoom.images" :key="img.id" class="rounded-3 overflow-hidden flex-shrink-0" style="width: 80px; height: 60px;">
                       <img :src="img.image_path" class="w-100 h-100 object-fit-cover border">
                    </div>
                 </div>
              </div>

              <div class="status-indicator p-3 rounded-4" :class="statusBadgeClass(selectedRoom.status)">
                <div class="small fw-bold text-uppercase mb-1 opacity-75">Current Status</div>
                <div class="h5 mb-0 fw-bold">{{ selectedRoom.status.toUpperCase() }}</div>
              </div>
            </div>
            <div class="col-md-7">
               <div class="mb-4">
                 <h6 class="text-gold small fw-bold text-uppercase mb-2">Room Description</h6>
                 <p class="text-secondary-dark mb-0">{{ selectedRoom.description || 'No description provided.' }}</p>
               </div>
               
               <div class="row g-3">
                 <div class="col-6">
                   <div class="p-3 bg-light rounded-3">
                     <small class="text-muted d-block mb-1">Nightly Rate</small>
                     <span class="fw-bold h5 mb-0 text-secondary-dark">₱{{ formatPrice(selectedRoom.price_per_night) }}</span>
                   </div>
                 </div>
                 <div class="col-6">
                   <div class="p-3 bg-light rounded-3">
                     <small class="text-muted d-block mb-1">Max Occupancy</small>
                     <span class="fw-bold h5 mb-0 text-secondary-dark">{{ selectedRoom.max_occupancy }} Guests</span>
                   </div>
                 </div>
                 <div class="col-6">
                   <div class="p-3 bg-light rounded-3">
                     <small class="text-muted d-block mb-1">Room Size</small>
                     <span class="fw-bold h5 mb-0 text-secondary-dark">{{ selectedRoom.room_size || '20' }} sq.m</span>
                   </div>
                 </div>
                 <div class="col-6">
                   <div class="p-3 bg-light rounded-3">
                     <small class="text-muted d-block mb-1">Bed Configuration</small>
                     <span class="fw-bold h5 mb-0 text-secondary-dark text-truncate d-block">{{ selectedRoom.bed_type || 'Queen Size' }}</span>
                   </div>
                 </div>
               </div>

               <div class="mt-4">
                 <h6 class="text-gold-dark small fw-bold text-uppercase mb-3 letter-spacing-wide">Inclusive Features</h6>
                 <div class="d-flex flex-wrap gap-2">
                    <div v-for="amenity in selectedRoom.amenities" :key="amenity.id" class="feature-badge d-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm border">
                      <div class="feature-icon bg-gold-subtle text-gold rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                        <i :class="['bi', formatIconClass(amenity.icon), 'small']"></i>
                      </div>
                      <span class="fw-bold text-secondary-dark mb-0 ls-tight" style="font-size: 0.85rem;">{{ amenity.name }}</span>
                    </div>
                    <div v-if="!selectedRoom.amenities?.length" class="text-muted small italic p-2 bg-light rounded-3 w-100 border border-dashed">
                      <i class="bi bi-info-circle me-2"></i>No features listed for this room.
                    </div>
                 </div>
               </div>
            </div>
          </div>
        </div>
        <div class="modal-footer-premium p-4 border-top bg-light d-flex justify-content-end gap-3">
          <button @click="openEditModal(selectedRoom); showViewModal = false" class="btn btn-outline-modern px-4 py-2 fw-semibold rounded-pill">Edit Details</button>
          <button @click="showViewModal = false" class="btn btn-gold px-5 py-2 rounded-pill shadow-gold fw-bold text-uppercase">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive, watch } from 'vue';
import axios from 'axios';
import { notify, confirm } from '../../utils/sweetalert';
import AdminPagination from '../../components/AdminPagination.vue';

const rooms = ref([]);
const allAmenities = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const showModal = ref(false);
const editingRoom = ref(null);
const saving = ref(false);
const currentPage = ref(1);
const pageSize = ref(5);
const activeTab = ref('basic');
const fileInput = ref(null);
const imagePreview = ref(null);
const showViewModal = ref(false);
const selectedRoom = ref(null);

const formTabs = [
  { id: 'basic', name: 'Identity', icon: 'bi-tag-fill' },
  { id: 'specs', name: 'Specs & Price', icon: 'bi-gear-wide-connected' },
  { id: 'media', name: 'Media & Features', icon: 'bi-stars' }
];

const form = reactive({
  room_number: '',
  room_type: 'Standard Single',
  price_per_night: 1500,
  max_occupancy: 2,
  description: '',
  status: 'available',
  image: '',
  image_file: null,
  bed_type: 'Queen Size',
  room_size: '20',
  amenities: [],
  images: [], // File array for new uploads
  remove_images: [] // ID array for removals
});

const multipleImagePreviews = ref([]);
const existingImages = ref([]);

const fetchRooms = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/rooms');
    rooms.value = response.data;
  } catch (err) {
    console.error('Failed to fetch rooms', err);
    notify.error('Fetch Error', 'Failed to sync with room inventory.');
  } finally {
    loading.value = false;
  }
};

const fetchAllAmenities = async () => {
  try {
    const response = await axios.get('/api/amenities');
    allAmenities.value = response.data.filter(a => a.is_active);
  } catch (err) {
    console.error('Failed to fetch amenities', err);
  }
};

const availableRoomsCount = computed(() => {
  return rooms.value.filter(r => r.status === 'available').length;
});

const filteredRooms = computed(() => {
  if (!searchQuery.value) return rooms.value;
  const q = searchQuery.value.toLowerCase();
  return rooms.value.filter(r => 
    r.room_number.toString().includes(q) || 
    r.room_type.toLowerCase().includes(q)
  );
});

const paginatedRooms = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredRooms.value.slice(start, end);
});

const activeTabLabel = computed(() => {
  const tab = formTabs.find(t => t.id === activeTab.value);
  return tab ? tab.name : '';
});

watch(searchQuery, () => {
  currentPage.value = 1;
});

const openAddModal = () => {
  editingRoom.value = null;
  activeTab.value = 'basic';
  imagePreview.value = null;
  Object.assign(form, {
    room_number: '',
    room_type: 'Standard Single',
    price_per_night: 1500,
    max_occupancy: 2,
    description: '',
    status: 'available',
    image: '',
    image_file: null,
    bed_type: 'Queen Size',
    room_size: '20',
    amenities: [],
    images: [],
    remove_images: []
  });
  multipleImagePreviews.value = [];
  existingImages.value = [];
  showModal.value = true;
};

const openEditModal = (room) => {
  editingRoom.value = room;
  activeTab.value = 'basic';
  imagePreview.value = null;
  const currentAmenityIds = room.amenities ? room.amenities.map(a => a.id) : [];
  
  Object.assign(form, {
    room_number: room.room_number,
    room_type: room.room_type,
    price_per_night: room.price_per_night,
    max_occupancy: room.max_occupancy,
    description: room.description || '',
    status: room.status,
    image: room.image || '',
    image_file: null,
    bed_type: room.bed_type || 'King Size',
    room_size: room.room_size || '25',
    amenities: currentAmenityIds,
    images: [],
    remove_images: []
  });
  existingImages.value = room.images || [];
  multipleImagePreviews.value = [];
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const viewRoom = (room) => {
  selectedRoom.value = room;
  showViewModal.value = true;
};

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.image_file = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const handleMultipleFilesChange = (e) => {
  const files = Array.from(e.target.files);
  files.forEach(file => {
    form.images.push(file);
    multipleImagePreviews.value.push(URL.createObjectURL(file));
  });
};

const removeExistingImage = (id) => {
  existingImages.value = existingImages.value.filter(img => img.id !== id);
  form.remove_images.push(id);
};

const removeNewImage = (idx) => {
  form.images.splice(idx, 1);
  multipleImagePreviews.value.splice(idx, 1);
};

const removeMainImage = () => {
  form.image = '';
  form.image_file = null;
  imagePreview.value = null;
  if (fileInput.value) fileInput.value.value = '';
};

const saveRoom = async () => {
  saving.value = true;
  try {
    const formData = new FormData();
    Object.keys(form).forEach(key => {
      if (key === 'amenities') {
        formData.append(key, JSON.stringify(form[key]));
      } else if (key === 'remove_images') {
        formData.append(key, JSON.stringify(form[key]));
      } else if (key === 'images') {
        form[key].forEach((file) => {
          formData.append('images[]', file);
        });
      } else if (key === 'image_file' && form[key]) {
        formData.append(key, form[key]);
      } else {
        formData.append(key, form[key]);
      }
    });

    let res;
    if (editingRoom.value) {
      formData.append('_method', 'PUT');
      res = await axios.post(`/api/rooms/${editingRoom.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      
      const index = rooms.value.findIndex(r => r.id === editingRoom.value.id);
      if (index !== -1) {
        rooms.value[index] = res.data;
      }
      notify.success('Update Success', `Room #${form.room_number} data is now current.`);
    } else {
      res = await axios.post('/api/rooms', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      rooms.value.unshift(res.data);
      notify.success('Entry Recorded', `New Room #${form.room_number} added to the floor plan.`);
    }
    closeModal();
    fetchRooms();
  } catch (err) {
      const msg = err.response?.data?.message || 'Check form requirements.';
      notify.error('Operation Failed', msg);
  } finally {
    saving.value = false;
  }
};

const confirmDelete = async (id) => {
  const res = await confirm({
    title: 'Delete Room?',
    text: 'Are you sure you want to delete this room?',
    confirmText: 'Yes, Delete'
  });

  if (res.isConfirmed) {
    try {
      await axios.delete(`/api/rooms/${id}`);
      rooms.value = rooms.value.filter(r => r.id !== id);
      notify.success('Deleted', 'Room has been removed.');
    } catch (err) {
      notify.error('Restriction', 'Room cannot be deleted while reservations are active.');
    }
  }
};

const formatIconClass = (icon) => {
  if (!icon) return '';
  return icon.startsWith('bi-') ? icon : `bi-${icon}`;
};

const formatPrice = (price) => {
  return parseFloat(price || 0).toLocaleString();
};

const statusBadgeClass = (status) => {
  switch (status) {
    case 'available': return 'bg-success-subtle text-success';
    case 'occupied': return 'bg-danger-subtle text-danger';
    case 'maintenance': return 'bg-warning-subtle text-warning';
    default: return 'bg-secondary-subtle text-secondary';
  }
};

const getDefaultImage = (type) => {
    const images = {
        'Standard': '/images/unsplash/standard-room.jpg',
        'Deluxe': '/images/unsplash/deluxe-room.jpg',
        'Suite': '/images/unsplash/suite-room.jpg'
    };
    return images[type.split(' ')[0]] || images['Standard'];
};

const isTabFilled = (tabId) => {
    if (tabId === 'basic') return !!form.room_number && !!form.room_type;
    if (tabId === 'specs') return form.price_per_night > 0 && form.max_occupancy > 0;
    if (tabId === 'media') return !!form.image || !!form.image_file || form.amenities.length > 0;
    return false;
};

const getNextTab = () => {
    if (activeTab.value === 'basic') return 'specs';
    if (activeTab.value === 'specs') return 'media';
    return 'media';
};

onMounted(() => {
    fetchRooms();
    fetchAllAmenities();
});
</script>

<style scoped>
.stats-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.stats-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.06);
}

.icon-box {
  width: 54px;
  height: 54px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-icon {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-light-gold {
  background: var(--primary-gold-subtle);
  color: var(--primary-gold);
}

.btn-light-gold:hover {
  background: var(--primary-gold);
  color: white;
  transform: rotate(15deg);
}

.btn-light-danger {
  background: rgba(220, 53, 69, 0.08);
  color: #dc3545;
}

.btn-light-danger:hover {
  background: #dc3545;
  color: white;
  transform: scale(1.1);
}

/* PREMIUM MODAL STYLES */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.7);
  backdrop-filter: blur(10px);
  z-index: 10000;
}

.modal-card-premium {
  background: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.brand-icon-box {
  width: 48px;
  height: 48px;
}

.shadow-gold {
  box-shadow: 0 8px 16px rgba(188, 145, 81, 0.3);
}

.btn-close-premium {
  width: 40px; height: 40px;
  border-radius: 50%;
  border: none;
  background: #f1f5f9;
  color: #64748b;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}

.btn-close-premium:hover {
  background: #e2e8f0;
  color: #0f172a;
  transform: rotate(90deg);
}

/* Sidebar Tabs */
.nav-tab-btn {
  border: none;
  background: transparent;
  text-align: left;
  color: #64748b;
}

.nav-tab-btn:hover {
  background: rgba(212, 180, 131, 0.1);
  color: var(--primary-gold);
}

.active-tab {
  background: white !important;
  color: var(--primary-gold) !important;
}

.active-tab i {
  color: var(--primary-gold);
}

/* Modern Floating Inputs */
.floating-group {
  position: relative;
}

.input-modern-xl {
  width: 100%;
  padding: 1.25rem 1rem 0.75rem;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 16px;
  font-size: 1rem;
  font-weight: 500;
  transition: all 0.3s;
}

.input-modern-xl:focus {
  background: white;
  border-color: var(--primary-gold);
  box-shadow: 0 0 0 4px rgba(188, 145, 81, 0.08);
  outline: none;
}

.floating-group label {
  position: absolute;
  top: 1rem;
  left: 1rem;
  font-size: 0.9rem;
  color: #94a3b8;
  pointer-events: none;
  transition: all 0.2s;
}

.input-modern-xl:focus ~ label,
.input-modern-xl:not(:placeholder-shown) ~ label {
  top: 0.4rem;
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--primary-gold);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Price Prefix */
.price-field .input-modern-xl {
  padding-left: 2.5rem;
}

.currency-prefix {
  position: absolute;
  left: 1.25rem;
  top: 1.1rem;
  font-weight: 700;
  color: #94a3b8;
  transition: color 0.3s;
}

.input-modern-xl:focus ~ .currency-prefix {
  color: var(--primary-gold);
}

/* Status Selectors */
.btn-status-modern {
  border: 1.5px solid #e2e8f0;
  background: #f8fafc;
  color: #64748b;
  font-weight: 600;
  border-radius: 14px;
}

.btn-check:checked + .btn-status-modern {
  background: rgba(25, 135, 84, 0.1);
  border-color: #198754;
  color: #198754;
}

.btn-check:checked + .btn-status-modern.maint {
  background: rgba(255, 193, 7, 0.1);
  border-color: #ffc107;
  color: #ffc107;
}

/* Amenity Checkboxes */
.amenity-checkbox-modern label {
  cursor: pointer;
  background: white;
}

.amenity-checkbox-modern.selected label {
  background: rgba(188, 145, 81, 0.04);
  border-color: var(--primary-gold);
}

.amenity-icon-mini {
  width: 38px; height: 38px;
  background: #f8fafc;
  color: #94a3b8;
  transition: all 0.2s;
}

.amenity-checkbox-modern.selected .amenity-icon-mini {
  background: var(--primary-gold);
  color: white;
}

.selected-dot {
  width: 10px; height: 10px;
  border-radius: 50%;
  background: #e2e8f0;
  transition: all 0.2s;
}

.amenity-checkbox-modern.selected .selected-dot {
  background: var(--primary-gold);
  transform: scale(1.3);
}

/* Step Dots */
.step-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: #e2e8f0;
  transition: all 0.3s;
}

.active-dot {
  width: 24px;
  border-radius: 4px;
  background: var(--primary-gold);
}

.done-dot {
  background: #64748b;
}

/* General Animations */
.animate-tab-content {
  animation: slideLeft 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
}

@keyframes slideLeft {
  from { opacity: 0; transform: translateX(15px); }
  to { opacity: 1; transform: translateX(0); }
}

.animate-fade-in {
  animation: fadeIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}

.group:hover .preview-overlay {
   opacity: 1 !important;
}

.preview-overlay {
    transition: opacity 0.3s ease;
}

.z-2 { z-index: 2; }
.feature-badge {
  background: #ffffff;
  transition: all 0.2s ease;
  min-width: fit-content;
}
.feature-badge:hover {
  border-color: var(--primary-gold) !important;
  transform: translateY(-2px);
}
.feature-icon {
  flex-shrink: 0;
}
.text-gold-dark {
  color: #9A7640;
}
.ls-tight {
  letter-spacing: -0.2px;
}
.letter-spacing-wide {
  letter-spacing: 1px;
}
/* Gallery Image Management */
.gallery-upload-grid {
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
}

.aspect-square {
    aspect-ratio: 1 / 1;
}

.gallery-item-premium {
    transition: all 0.3s ease;
}

.gallery-item-premium:hover {
    transform: scale(1.05);
    z-index: 2;
}

.btn-remove-img {
    width: 24px;
    height: 24px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-remove-img:hover {
    background: #dc2626;
    transform: scale(1.2);
}

.gallery-add-card {
    background: #f8fafc;
    transition: all 0.3s;
}

.gallery-add-card:hover {
    background: var(--primary-gold-subtle);
    border-color: var(--primary-gold) !important;
}

.new-badge {
    border-bottom-right-radius: 8px;
    font-weight: 800;
    letter-spacing: 0.5px;
}
.z-3 { z-index: 3; }
</style>
