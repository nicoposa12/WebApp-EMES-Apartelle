# Lacking Features and Discrepancies

Based on a comparison between the **Research Paper** (`docs/research.md`) and the **Current System Implementation**, the following items are identified as lacking, incomplete, or mismatched:

## 1. Tech Stack Mismatch (Critical)
- **Research Paper Specification:** Specifies a stack consisting of **Next.js 14**, **Node.js**, **MongoDB**, and **Prisma ORM**.
- **Current Implementation:** Built using **Laravel 12 (PHP)**, **MySQL**, and **Vite**.
- **Status:** Complete architectural mismatch with the documentation.

## 2. One-Time Password (OTP) Authentication
- **Requirement:** The paper emphasizes secure access via **One-Time Password (OTP)** verification for both guests and administrators.
- **Current Implementation:** Only standard email and password authentication is implemented (`AuthController.php`). No OTP logic or fields exist in the `User` model.
- **Status:** Not Yet Implemented.

## 3. SMS Integration
- **Requirement:** The paper mentions that OTP verification and notifications rely on **telecommunication provider’s SMS delivery**.
- **Current Implementation:** The system currently relies on Laravel's internal database/email notifications. There is no integration with an SMS gateway (e.g., Twilio, Semaphore, or iManila).
- **Status:** Not Yet Implemented.

## 4. Advanced Amenities Filtering
- **Requirement:** Guests should be able to "**Filter rooms based on selected amenities**" to make informed decisions.
- **Current Implementation:** While amenities are displayed for rooms, the `RoomController@index` only filters by date range. There is no logic to filter the room list by one or more amenity IDs.
- **Status:** Partially Implemented (Data exists, but filter logic is missing).

## 5. Real-time WebSocket Features (Broadcasting)
- **Requirement:** The paper implies real-time updates for room availability, notifications, and live chat.
- **Current Implementation:** Although **Laravel Reverb** has been installed, the controllers and models do not yet trigger `Broadcast` events to push updates to the frontend in real-time.
- **Status:** Infrastructure installed; Logic not yet integrated.

## 6. Comprehensive Administrative Reporting
- **Requirement:** The paper specifies monitoring of "**occupancy rates**" and analysis of "**booking trends**" to help management monitor business performance.
- **Current Implementation:** Basic statistics and revenue reports are available in `AdminController@getReports`, but a dedicated occupancy rate calculation (percentage of rooms filled over time) and robust trend visualization data are lacking.
- **Status:** Partially Implemented.

## 7. Multi-currency Support (Optional but Noted)
- **Observation:** The paper lists the lack of multi-currency support as a limitation (PHP only).
- **Current Implementation:** Matches the paper (supports PHP only via Xendit).
- **Status:** Compliant with paper limitations.
