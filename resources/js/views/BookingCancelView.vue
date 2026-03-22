<template>
  <div class="result-page bg-cream">
    <div class="container">
      <div class="result-card animate-fade-up">
        <div class="result-icon error">
          <i class="bi bi-x-lg"></i>
        </div>
        <h1 class="result-title">Payment Cancelled</h1>
        <p class="result-message">
          The payment process was cancelled or could not be completed.<br>
          Your reservation is pending. Please try again or contact our staff for assistance.
        </p>
        <div class="result-actions">
          <router-link to="/rooms" class="btn btn-gold">
            <i class="bi bi-arrow-left"></i> Return to Rooms
          </router-link>
        </div>
        <div class="result-help">
          <p>Need help? Call us at <a href="tel:+639123456789">+63 912 345 6789</a></p>
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

.result-icon.error {
  background: #FEF2F2;
  color: #EF4444;
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
  margin-bottom: 2rem;
}

.result-actions .btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.5rem;
}

.result-help {
  padding-top: 1.5rem;
  border-top: 1px solid #F0F0F0;
}

.result-help p {
  color: var(--text-muted);
  margin: 0;
}

.result-help a {
  color: var(--primary-gold);
  font-weight: 600;
  text-decoration: none;
}

.result-help a:hover {
  text-decoration: underline;
}

@media (max-width: 575.98px) {
  .result-card {
    padding: 3rem 1.5rem;
  }
  
  .result-title {
    font-size: 2rem;
  }
}
</style>
