# System Entities and Roles

This document defines the three primary entities that interact with the **EME’s Apartelle Digital Transformation** system.

## 1. Guest
The **Guest** is the external end-user of the system. Their primary interaction is with the public-facing booking portal.

*   **Objectives**: To browse available accommodations, check real-time availability, and secure a room through online booking and payment.
*   **Key Responsibilities**:
    *   Providing accurate personal and payment information during the booking process.
    *   Managing their own reservations and reviewing booking details.
    *   Interacting with the AI-assisted chatbot for immediate inquiries or support.
    *   Filing dispute reports if issues arise with their stay or billing.
*   **Access Level**: Limited to public room listings, personal booking history, and support interfaces.

## 2. Owner / System Administrator
The **Owner / System Administrator** is the primary operational user of the system, responsible for the day-to-day management of EME’s Apartelle.

*   **Objectives**: To maintain accurate room inventory, optimize pricing, manage guest bookings, and ensure smooth business operations.
*   **Key Responsibilities**:
    *   Managing room listings, descriptions, and high-quality visual content.
    *   Updating room status (e.g., setting a room to 'Maintenance' or 'Occupied').
    *   Setting and modifying pricing rules based on seasons or demand.
    *   Reviewing and responding to booking requests and guest inquiries.
    *   Generating operational reports (occupancy rates, revenue trends) to monitor business performance.
    *   Resolving guest disputes and managing staff/receptionist accounts.
*   **Access Level**: Full operational access to the administrative dashboard, excluding core system configuration and developer-level settings.

## 3. Super Administrator (Developer)
The **Super Administrator** is the highest-level entity, typically representing the development team or the platform's technical owner.

*   **Objectives**: To ensure the system’s technical integrity, security, and continuous operation.
*   **Key Responsibilities**:
    *   Managing high-level system configurations and environment settings.
    *   Creating, suspending, or restoring Administrator and Owner accounts.
    *   Performing database maintenance, backups, and security audits.
    *   Monitoring system logs and performance metrics to prevent downtime.
    *   Implementing and updating core features, such as the Chatbot logic or Payment Gateway integrations.
    *   Resetting user passwords and handling critical technical failures.
*   **Access Level**: Unrestricted access to all system modules, database records, and technical configurations.
