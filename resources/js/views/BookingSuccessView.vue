<template>
  <div class="result-page bg-cream">
    <div class="container">
      <div class="result-card animate-fade-up">
        <div class="result-icon success">
          <i class="bi bi-check-lg"></i>
        </div>
        <h1 class="result-title">Booking Confirmed!</h1>
        <p class="result-message">
          Thank you for choosing EME's Apartelle. Your reservation has been successfully confirmed.<br>
          A confirmation email will be sent to you shortly with all the details.
        </p>
        <div class="result-actions">
          <router-link to="/" class="btn btn-outline-dark-custom">
            <i class="bi bi-house"></i> Go to Home
          </router-link>
          <router-link to="/rooms" class="btn btn-gold">
            <i class="bi bi-calendar-plus"></i> Book Another Room
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.result-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.result-card {
  background: white;
  border-radius: var(--radius-xl);
  padding: 4rem 3rem;
  text-align: center;
  max-width: 550px;
  width: 100%;
  box-shadow: var(--shadow-xl);
}

.result-icon {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 2rem;
}

.result-icon.success {
  background: #ECFDF5;
  color: #22C55E;
}

.result-icon i {
  font-size: 3rem;
}

.result-title {
  font-family: var(--font-serif);
  font-weight: 700;
  font-size: 2.5rem;
  margin-bottom: 1rem;
  color: var(--text-dark);
}

.result-message {
  color: var(--text-muted);
  font-size: 1.1rem;
  line-height: 1.7;
  margin-bottom: 2.5rem;
}

.result-actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.result-actions .btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.5rem;
}

@media (max-width: 575.98px) {
  .result-card {
    padding: 3rem 1.5rem;
  }
  
  .result-title {
    font-size: 2rem;
  }
  
  .result-actions {
    flex-direction: column;
  }
  
  .result-actions .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
