<template>
  <div class="calendar-picker p-2 border rounded-4 bg-white shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-2 px-2">
      <button @click="changeMonth(-1)" type="button" class="btn btn-sm btn-light rounded-circle calend-nav-btn">
        <i class="bi bi-chevron-left small"></i>
      </button>
      <span class="fw-bold fs-6 serif-font text-secondary-dark">{{ monthName }}</span>
      <button @click="changeMonth(1)" type="button" class="btn btn-sm btn-light rounded-circle calend-nav-btn">
        <i class="bi bi-chevron-right small"></i>
      </button>
    </div>

    <div class="calendar-grid">
      <div v-for="day in days" :key="day" class="calendar-head">{{ day }}</div>
      <div
        v-for="(dateObj, i) in calendarDays"
        :key="i"
        class="calendar-date"
        :class="{
          'selected': isSelected(dateObj),
          'disabled': isDisabled(dateObj),
          'empty': !dateObj,
          'today': isToday(dateObj),
          'booked': isBooked(dateObj)
        }"
        @click="selectDate(dateObj)"
      >
        <span v-if="dateObj">{{ dateObj.day }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  modelValue: String, // YYYY-MM-DD
  disabledDates: {
    type: Array, // Array of { start: 'YYYY-MM-DD', end: 'YYYY-MM-DD' }
    default: () => []
  },
  minDate: {
    type: String, // YYYY-MM-DD
    default: () => new Date().toISOString().split('T')[0]
  },
  maxDate: {
    type: String,
    default: null
  },
  isCheckout: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);

const days = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
const currentViewDate = ref(props.modelValue ? new Date(props.modelValue) : new Date());

// Ensure currentViewDate is valid
if (isNaN(currentViewDate.value.getTime())) {
  currentViewDate.value = new Date();
}

const monthName = computed(() => {
  return currentViewDate.value.toLocaleString('default', { month: 'long', year: 'numeric' });
});

const calendarDays = computed(() => {
  const year = currentViewDate.value.getFullYear();
  const month = currentViewDate.value.getMonth();
  const firstDay = new Date(year, month, 1).getDay();
  const lastDate = new Date(year, month + 1, 0).getDate();

  const d = [];
  // Padding for empty start
  for (let i = 0; i < firstDay; i++) d.push(null);
  // Real days
  for (let i = 1; i <= lastDate; i++) {
    d.push({
      day: i,
      fullDate: new Date(year, month, i)
    });
  }
  return d;
});

const changeMonth = (direction) => {
  const newDate = new Date(currentViewDate.value);
  newDate.setMonth(newDate.getMonth() + direction);
  currentViewDate.value = newDate;
};

const formatToYMD = (date) => {
  if (!date) return '';
  const d = new Date(date);
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
};

const selectDate = (dateObj) => {
  if (!dateObj || isDisabled(dateObj)) return;
  emit('update:modelValue', formatToYMD(dateObj.fullDate));
};

const isSelected = (dateObj) => {
  if (!dateObj || !props.modelValue) return false;
  return formatToYMD(dateObj.fullDate) === props.modelValue;
};

const isToday = (dateObj) => {
  if (!dateObj) return false;
  return formatToYMD(dateObj.fullDate) === formatToYMD(new Date());
};

const isDisabled = (dateObj) => {
  if (!dateObj) return true;
  const dStr = formatToYMD(dateObj.fullDate);
  
  // Check min date
  if (props.minDate && dStr < props.minDate) return true;
  
  // Check max date
  if (props.maxDate && dStr > props.maxDate) return true;
  
  // Check booked dates
  return isBooked(dateObj);
};

const isBooked = (dateObj) => {
  if (!dateObj || !props.disabledDates.length) return false;
  const d = dateObj.fullDate;
  d.setHours(0, 0, 0, 0);

  return props.disabledDates.some(range => {
    const start = new Date(range.check_in || range.start);
    const end = new Date(range.check_out || range.end);
    start.setHours(0, 0, 0, 0);
    end.setHours(0, 0, 0, 0);
    
    if (props.isCheckout) {
      // For check-out, a date is disabled if the night before it is occupied
      return d > start && d <= end;
    } else {
      // For check-in, a date is disabled if the night itself is occupied
      return d >= start && d < end;
    }
  });
};

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    const newDate = new Date(newVal);
    if (!isNaN(newDate.getTime())) {
      // Only change view if it's a different month
      if (newDate.getMonth() !== currentViewDate.value.getMonth() || 
          newDate.getFullYear() !== currentViewDate.value.getFullYear()) {
        currentViewDate.value = newDate;
      }
    }
  }
});
</script>

<style scoped>
.calendar-picker {
  min-width: 280px;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
}

.calendar-head {
  text-align: center;
  font-size: 0.65rem;
  font-weight: 800;
  color: #94A3B8; /* Slate 400 */
  padding: 4px 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.calendar-date {
  text-align: center;
  padding: 6px 0;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  border-radius: 12px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  user-select: none;
  color: #1E293B; /* Slate 800 */
  aspect-ratio: 1 / 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.calendar-date:hover:not(.empty):not(.disabled) {
  background-color: var(--primary-gold-subtle);
  color: var(--primary-gold);
}

.calendar-date.selected {
  background-color: var(--primary-gold) !important;
  color: white !important;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(188, 145, 81, 0.3);
}

.calendar-date.disabled {
  color: #CBD5E1; /* Slate 300 */
  cursor: not-allowed;
  font-weight: 400;
}

.calendar-date.booked {
  color: #F87171; /* Red 400 */
  background-color: #FEF2F2; /* Red 50 */
  text-decoration: line-through;
}

.calendar-date.today:not(.selected) {
  border: 2px solid var(--primary-gold-subtle);
  color: var(--primary-gold);
}

.calendar-date.empty {
  cursor: default;
}

.calend-nav-btn {
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.calend-nav-btn:hover {
  background-color: var(--primary-gold-subtle);
  color: var(--primary-gold);
  transform: scale(1.1);
}
</style>
