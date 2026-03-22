# Product Requirements Document (PRD)
## Project: EME’s Apartelle Web-Based Reservation System

### 1. Executive Summary
EME’s Apartelle requires a robust web-based reservation system to automate and manage its room bookings, guest records, and financial transactions. The system is designed to provide a seamless experience for both staff and customers, replacing manual processes with a digital platform.

---

### 2. Problem Statement
Currently, EME’s Apartelle manages reservations and billing through manual or fragmented processes. This leads to several challenges:
*   **Risk of Double Bookings:** Without a centralized real-time calendar, tracking availability is prone to errors.
*   **Inefficient Billing:** Manual invoice generation is time-consuming and subject to calculation errors.
*   **Limited Online Presence:** Guests lack a direct, interactive way to view availability and book rooms online with secure payment options.
*   **Difficult Guest Tracking:** Storing and retrieving guest history and stay details is cumbersome without a dedicated database.

---

### 3. Project Goals & Objectives
The primary goal is to deliver a reliable, secure, and user-friendly platform that:
*   **Automates Bookings:** Streamlines the reservation process through an interactive calendar.
*   **Improves Efficiency:** Reduces manual administrative work for staff.
*   **Enhances Guest Experience:** Provides clear information, easy booking, and secure digital payment options.
*   **Centralizes Data:** Ensures guest, room, and payment data are stored securely and are easily accessible.

---

### 4. Target Users
*   **Guests:** Users who visit the website to view room information, check availability, and make reservations.
*   **Staff/Receptionists:** Internal users who manage check-ins, check-outs, and manual booking updates.
*   **Administrators:** Top-level users with control over room pricing, inventory, staff accounts, and financial reports.

---

### 5. Functional Requirements

#### 5.1 Room Availability Management
*   Display real-time availability of rooms.
*   Detailed room descriptions (type, capacity, amenities).
*   Dynamic pricing management by Admin.

#### 5.2 Reservation Module
*   **Interactive Calendar UI:** Users can select dates and view occupied/available slots.
*   **Booking Form:** Collect necessary guest details for the reservation.
*   **Conflict Prevention:** Logic to prevent double booking of the same room for overlapping dates.

#### 5.3 Guest Record Management
*   Profiles for returning guests.
*   Searchable booking history.
*   Storage of stay details and special requests.

#### 5.4 Automated Billing & Invoicing
*   Auto-calculation of totals based on room rate and stay duration.
*   Automatic generation of PDF or digital invoices.
*   Support for additional charges (e.g., extra person, early check-in).

#### 5.5 Payment Integration
*   Integrated **PayMongo** gateway for secure online transactions.
*   Real-time transaction tracking and status updates (Succeeded, Pending, Failed).

#### 5.6 Admin Dashboard
*   Overview of current day's check-ins and check-outs.
*   Management tools for Rooms, Reservations, and Guest files.
*   Financial reporting and transaction logs.

---

### 6. Non-Functional Requirements
*   **Responsive UI/UX:** The system must be fully functional and aesthetically pleasing on Desktop, Tablet, and Mobile devices.
*   **Security:** Secure handling of guest data and integration with SSL.
*   **Reliability:** High availability to ensure guests can book at any time.
*   **Performance:** Fast page load times and real-time calendar updates using Vue.js.

---

### 7. Technology Stack
*   **Backend:** Laravel (PHP)
*   **Frontend:** Vue.js, HTML, CSS, JavaScript
*   **UI Framework:** Bootstrap
*   **Database:** MySQL
*   **Payment Gateway:** PayMongo

---

### 8. Scope & Delimitations
*   **Scope:** Specifically designed for EME’s Apartelle inventory and internal operations.
*   **Delimitations:** 
    *   Does NOT aggregate rooms from external platforms (Agoda, Airbnb, etc.).
    *   Does NOT include staff payroll management.
    *   Offline bookings must be manually entered into the system by staff to maintain real-time accuracy.

---

### 9. User Flow
1.  **Guest Flow:** Browse Rooms → Select Date on Calendar → Fill Booking Details → Proceed to PayMongo Payment → Receive Confirmation & Invoice.
2.  **Admin Flow:** Login → View Dashboard → Manage Reservations → Update Room Status/Pricing → Generate Reports.

---

### 10. Data Requirements
*   **Room Data:** Room number, type, base price, max occupancy, status (Available, Maintenance, Occupied).
*   **Reservation Data:** Guest ID, Room ID, Check-in date, Check-out date, Total price, Payment status, Reference number.
*   **Guest Data:** Full name, Email, Contact number, Address, ID scan/reference, Booking history.
*   **Payment Data:** Transaction ID, Gateway (PayMongo), Amount, Timestamp, Status.

---

### 11. System Architecture
*   **Layered Architecture:**
    *   **Presentation Layer:** Vue.js + Bootstrap (Responsive Web App).
    *   **Application Layer:** Laravel (RESTful API / Web Controllers).
    *   **Data Layer:** MySQL Database.
    *   **External Services:** PayMongo API integration for payments.

---

### 12. Assumptions, Constraints & Risks
*   **Assumptions:**
    *   Users have a stable internet connection for online payments.
    *   Admin will keep room inventory updated for offline walk-ins.
*   **Constraints:**
    *   The system only handles EME's Apartelle inventories.
    *   Limited to specified tech stack (Laravel, Vue.js).
*   **Risks:**
    *   **Database Sync:** Risk of data loss if the server is not backed up.
    *   **Payment Gateway Downtime:** Reliance on PayMongo's uptime for online bookings.
    *   **Concurrency:** Handling simultaneous booking attempts for the last available room.

---

### 13. Success Metrics
*   **Zero Double Bookings:** Successful implementation of the calendar scheduling logic.
*   **Reduced Check-in Time:** Measured by the time taken to retrieve guest records and generate invoices.
*   **Online Booking Adoption:** Percentage of total bookings made through the web application.
*   **Payment Success Rate:** High percentage of successful PayMongo transactions.
