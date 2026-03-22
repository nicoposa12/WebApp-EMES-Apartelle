# System Design Document (SDD)
## Project: EME’s Apartelle Web-Based Reservation System

### 1. Introduction
This document outlines the technical design for the EME’s Apartelle Reservation System. It focuses on the database schema, API structure, and frontend architecture.

### 2. Database Schema (MySQL)

#### 2.1 Table: `users`
*   `id` (PK)
*   `name`
*   `email` (Unique)
*   `password`
*   `role` (Enum: 'admin', 'staff', 'guest')
*   `created_at` / `updated_at`

#### 2.2 Table: `rooms`
*   `id` (PK)
*   `room_number` (String, Unique)
*   `room_type` (String: Single, Double, Suite)
*   `description` (Text)
*   `price_per_night` (Decimal 10,2)
*   `max_occupancy` (Integer)
*   `status` (Enum: 'available', 'unavailable', 'maintenance')
*   `created_at` / `updated_at`

#### 2.3 Table: `reservations`
*   `id` (PK)
*   `user_id` (FK to users)
*   `room_id` (FK to rooms)
*   `check_in` (Date)
*   `check_out` (Date)
*   `total_amount` (Decimal 10,2)
*   `status` (Enum: 'pending', 'confirmed', 'cancelled', 'completed')
*   `payment_status` (Enum: 'unpaid', 'paid', 'refunded')
*   `created_at` / `updated_at`

#### 2.4 Table: `payments`
*   `id` (PK)
*   `reservation_id` (FK to reservations)
*   `paymongo_payment_id` (String: External Ref)
*   `amount` (Decimal 10,2)
*   `method` (String: Card, GCash, etc.)
*   `status` (String: Succeeded, Failed)
*   `created_at`

---

### 3. API Endpoints (Laravel)

#### 3.1 Public/Guest Endpoints
*   `GET /api/rooms` - Fetch all rooms with filters.
*   `GET /api/rooms/{id}/availability` - Check room availability for dates.
*   `POST /api/bookings` - Create a temporary booking and initiate PayMongo.

#### 3.2 Admin/Staff Endpoints
*   `GET /api/admin/reservations` - View all bookings.
*   `POST /api/admin/rooms` - Add/Update room info.
*   `PUT /api/admin/reservations/{id}/status` - Update booking status.

---

### 4. Frontend Architecture (Vue.js)
*   **Routing:** Vue Router for navigating between Landing, Rooms, and Admin Dashboard.
*   **State Management:** Pinia or Vue Reactive Store for handling user sessions and cart-like booking state.
*   **Components:**
    *   `RoomCard.vue`: Displays room info.
    *   `BookingCalendar.vue`: FullCalendar or custom date picker.
    *   `PaymentForm.vue`: PayMongo integration logic.
*   **UI Framework:** Bootstrap 5 for responsive layout and pre-built components.

---

### 5. Integration: PayMongo Workflow
1.  User selects room and dates.
2.  Frontend sends data to `/api/bookings`.
3.  Laravel creates `Pending` reservation and calls PayMongo `Checkout Session` API.
4.  Laravel returns `checkout_url` to Frontend.
5.  User completes payment on PayMongo site.
6.  PayMongo Webhook notifies Laravel `/api/paymongo/webhook`.
7.  Laravel updates reservation status to `Confirmed`.
