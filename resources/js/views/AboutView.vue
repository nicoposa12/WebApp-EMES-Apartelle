<script setup>
import { onMounted } from 'vue';

onMounted(() => {
  window.scrollTo(0, 0);
});

const stats = [
  { icon: 'bi-calendar-event', value: '10+', label: 'Years of Service' },
  { icon: 'bi-people', value: '1,000+', label: 'Happy Guests' },
  { icon: 'bi-award', value: '90%', label: 'Satisfaction Rate' },
  { icon: 'bi-person-badge', value: '15', label: 'Team Members' }
];

const values = [
  {
    title: 'Genuine Hospitality',
    description: 'We treat every guest like family, ensuring a warm and welcoming experience from check-in to check-out.'
  },
  {
    title: 'Comfort First',
    description: 'Every room is designed with your comfort in mind, from quality bedding to modern amenities.'
  },
  {
    title: 'Value for Money',
    description: 'We believe everyone deserves quality accommodation at fair prices, without hidden fees.'
  }
];
</script>

<template>
  <div class="about-page bg-cream min-vh-100">
    <!-- Hero Section -->
    <section class="page-header contrast-overlay">
      <div class="container position-relative z-1 text-center animate-fade-up">
        <span class="section-label-alt text-gold mb-3 d-block">OUR STORY</span>
        <h1 class="display-3 fw-bold serif-font mb-4 text-white">About EME's Apartelle</h1>
        <p class="section-description mx-auto text-white-50 fs-5" style="max-width: 600px;">
          A place that you can call home while having adventures in Siargao.
        </p>
      </div>
    </section>

    <!-- Main Content Container with Overlap -->
    <div class="container position-relative z-2 mt-n5">
      <div class="bg-white rounded-5 shadow-lg p-5">
        
        <!-- Our Journey Section -->
        <section class="journey-section py-4">
          <div class="row align-items-center g-5 animate-fade-up delay-1">
            <div class="col-lg-6">
              <div class="journey-image-wrapper">
                <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=80" alt="EME's Apartelle Journey" class="img-fluid rounded-4 shadow-lg">
              </div>
            </div>
            <div class="col-lg-6">
              <h2 class="serif-font display-5 mb-4 text-secondary-dark">Our Journey</h2>
              <div class="journey-text text-muted">
                <p class="mb-4">
                  Founded in 2015, EME's Apartelle started as a small family venture with just four rooms. What began as a dream to provide affordable yet comfortable accommodations has grown into a trusted name in the hospitality industry.
                </p>
                <p>
                  Today, we offer various room types from economy to family suites, each designed with the comfort and convenience of our guests in mind. Our commitment to genuine Filipino hospitality remains at the heart of everything we do.
                </p>
              </div>
            </div>
          </div>
        </section>

      </div>
    </div>

    <!-- Stats Section -->
    <section class="stats-section py-5 mt-5">
      <div class="container">
        <div class="row g-4 justify-content-center">
          <div v-for="(stat, index) in stats" :key="index" class="col-lg-3 col-md-6 animate-fade-up" :style="{ animationDelay: index * 100 + 'ms' }">
            <div class="stat-card p-4 d-flex align-items-center gap-4">
              <div class="stat-icon-box shadow-sm">
                <i :class="['bi', stat.icon]"></i>
              </div>
              <div class="stat-info">
                <h3 class="stat-value mb-0 serif-font text-gold">{{ stat.value }}</h3>
                <p class="stat-label mb-0 text-muted small fw-bold text-uppercase letter-spacing-wide">{{ stat.label }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Our Values Section -->
    <section class="values-section py-5 bg-white-50">
      <div class="container py-5">
        <div class="text-center mb-5 animate-fade-up">
          <h2 class="serif-font display-5 mb-3 text-secondary-dark">Our Values</h2>
          <p class="text-muted">These principles guide everything we do at EME's Apartelle.</p>
        </div>
        <div class="row g-4">
          <div v-for="(value, index) in values" :key="index" class="col-lg-4 animate-fade-up" :style="{ animationDelay: index * 100 + 'ms' }">
            <div class="value-card p-5 h-100 text-center shadow-hover transition-normal rounded-4">
              <h4 class="serif-font mb-4 text-secondary-dark">{{ value.title }}</h4>
              <p class="text-muted mb-0 lh-lg">
                {{ value.description }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="about-cta py-5">
      <div class="container py-5 animate-fade-up">
        <div class="cta-inner text-center">
          <h2 class="display-4 fw-bold serif-font mb-4 text-secondary-dark">Experience EME's Apartelle</h2>
          <p class="text-muted fs-5 mb-5 mx-auto" style="max-width: 600px;">
            Ready to experience our warm hospitality? Book your stay today.
          </p>
          <router-link to="/book-now" class="btn btn-gold btn-lg py-3 px-5 fw-bold text-uppercase letter-spacing-wide shadow-lg d-inline-flex align-items-center gap-2">
            <i class="bi bi-calendar-check"></i> Book Your Stay
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.page-header {
  background: url('https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=1920&q=80') center/center no-repeat;
  position: relative;
  padding: 12rem 0 10rem;
  background-attachment: fixed;
  margin-bottom: 0;
}

.contrast-overlay::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(rgba(26, 38, 52, 0.7), rgba(26, 38, 52, 0.9));
}

.mt-n5 {
  margin-top: -6rem;
}

.section-label-alt {
  color: var(--primary-gold);
  letter-spacing: 4px;
  font-size: 0.85rem;
  font-weight: 700;
}

.stat-card {
  background: white;
  border-radius: var(--radius-lg);
  transition: transform var(--transition-normal);
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-icon-box {
  width: 60px;
  height: 60px;
  background-color: var(--primary-gold-subtle);
  color: var(--primary-gold);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
}

.value-card {
  background: white;
  border: 1px solid rgba(0,0,0,0.03);
}

.shadow-hover:hover {
  box-shadow: var(--shadow-xl);
  border-color: transparent;
}

.text-gold {
  color: var(--primary-gold) !important;
}

.letter-spacing-wide {
  letter-spacing: 1px;
}
</style>
