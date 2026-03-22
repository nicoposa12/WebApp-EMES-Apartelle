<template>
  <nav class="d-flex justify-content-between align-items-center px-4 py-3 border-top bg-white pagination-container">
    <div class="d-flex align-items-center gap-4">
      <!-- Rows Per Page Selector (Inspiration Design) -->
      <div class="rows-selector-wrapper position-relative" v-click-outside="() => showDropdown = false">
        <button @click="showDropdown = !showDropdown" class="btn-rows-selector d-flex align-items-center justify-content-between gap-3 px-3 py-2 rounded-3 border transition-all">
          <span class="small fw-semibold text-secondary-dark">{{ pageSize }} rows per page</span>
          <i class="bi bi-chevron-down small transition-all" :class="{ 'rotate-180': showDropdown }"></i>
        </button>
        
        <transition name="fade-slide">
          <div v-if="showDropdown" class="rows-dropdown shadow-lg rounded-4 border bg-white position-absolute bottom-100 mb-2 py-2 overflow-hidden">
            <button v-for="option in [5, 10, 20, 50]" :key="option" 
              @click="selectPageSize(option)"
              class="dropdown-item d-flex align-items-center gap-3 px-3 py-2.5 transition-all"
              :class="{ 'active-option': pageSize === option }">
              <i class="bi bi-check2 fw-bold fs-5 invisible" :class="{ 'visible': pageSize === option }"></i>
              <span class="small fw-bold text-secondary-dark">{{ option }} rows per page</span>
            </button>
          </div>
        </transition>
      </div>

      <div class="text-muted small d-none d-md-block">
        Showing <span class="fw-bold text-secondary-dark">{{ startRange }}</span> to <span class="fw-bold text-secondary-dark">{{ endRange }}</span> of <span class="fw-bold text-secondary-dark">{{ totalItems }}</span> entries
      </div>
    </div>

    <ul v-if="totalPages > 1" class="pagination pagination-sm mb-0 gap-2">
      <li class="page-item" :class="{ disabled: currentPage === 1 }">
        <button class="page-link rounded-circle d-flex align-items-center justify-content-center border-0 bg-light shadow-sm" @click="$emit('change', currentPage - 1)" style="width: 32px; height: 32px;">
          <i class="bi bi-chevron-left"></i>
        </button>
      </li>
      
      <li v-for="page in pages" :key="page" class="page-item" :class="{ active: currentPage === page }">
        <button class="page-link rounded-circle d-flex align-items-center justify-content-center border-0 shadow-sm" @click="$emit('change', page)" style="width: 32px; height: 32px;">
          {{ page }}
        </button>
      </li>

      <li class="page-item" :class="{ disabled: currentPage === totalPages }">
        <button class="page-link rounded-circle d-flex align-items-center justify-content-center border-0 bg-light shadow-sm" @click="$emit('change', currentPage + 1)" style="width: 32px; height: 32px;">
          <i class="bi bi-chevron-right"></i>
        </button>
      </li>
    </ul>
  </nav>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  currentPage: { type: Number, required: true },
  totalItems: { type: Number, required: true },
  pageSize: { type: Number, required: true }
});

const emit = defineEmits(['change', 'pageSizeChange']);

const showDropdown = ref(false);

const totalPages = computed(() => Math.ceil(props.totalItems / props.pageSize));

const startRange = computed(() => props.totalItems === 0 ? 0 : ((props.currentPage - 1) * props.pageSize) + 1);
const endRange = computed(() => Math.min(props.currentPage * props.pageSize, props.totalItems));

const pages = computed(() => {
  const range = [];
  const maxPagesToShow = 5;
  let startPage = Math.max(1, props.currentPage - 2);
  let endPage = Math.min(totalPages.value, startPage + maxPagesToShow - 1);
  
  if (endPage - startPage + 1 < maxPagesToShow) {
    startPage = Math.max(1, endPage - maxPagesToShow + 1);
  }

  for (let i = startPage; i <= endPage; i++) {
    range.push(i);
  }
  return range;
});

const selectPageSize = (size) => {
  emit('pageSizeChange', size);
  showDropdown.value = false;
};

// Simple click-outside directive logic for the component
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event);
      }
    };
    document.addEventListener('click', el.clickOutsideEvent);
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent);
  },
};
</script>

<style scoped>
.pagination-container {
  min-height: 72px;
}

.btn-rows-selector {
  background: white;
  border-color: #e2e8f0 !important;
  color: #475569;
  min-width: 170px;
}

.btn-rows-selector:hover {
  border-color: var(--primary-gold) !important;
  background-color: #f8fafc;
}

.btn-rows-selector:focus {
  border-color: var(--primary-gold) !important;
  box-shadow: 0 0 0 3px rgba(188, 145, 81, 0.1);
}

.rows-dropdown {
  min-width: 220px;
  left: 0;
  z-index: 1000;
  background: white;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
}

.dropdown-item {
  border: none;
  background: transparent;
  width: 100%;
  text-align: left;
  color: #475569;
}

.dropdown-item:hover {
  background-color: #f1f5f9;
}

.active-option {
  background-color: var(--primary-gold-subtle);
}

.active-option span {
  color: var(--primary-gold) !important;
}

.active-option i {
  color: var(--primary-gold) !important;
}

.rotate-180 {
  transform: rotate(180deg);
}

.page-link {
  color: var(--text-dark);
  font-weight: 600;
  font-size: 0.8rem;
  transition: all 0.3s ease;
}

.page-item.active .page-link {
  background-color: var(--primary-gold) !important;
  color: white !important;
  box-shadow: 0 4px 10px rgba(188, 145, 81, 0.3) !important;
}

.page-item.disabled .page-link {
  opacity: 0.5;
  background-color: #f8f9fa !important;
}

.page-link:hover:not(.disabled) {
  transform: translateY(-2px);
  background-color: var(--primary-gold-subtle);
  color: var(--primary-gold);
}

.fade-slide-enter-active, .fade-slide-leave-active {
  transition: all 0.2s ease-out;
}

.fade-slide-enter-from, .fade-slide-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
