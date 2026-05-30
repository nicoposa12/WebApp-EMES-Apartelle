# Data Flow Diagram (DFD) - Context Diagram

This document presents the Context Diagram (Level 0 DFD) for the **EME’s Apartelle Digital Transformation** system, as verified against the research proposal and system requirements.

## DFD Overview

The diagram illustrates the high-level interactions between the central system and its three primary external entities: **Owner**, **System Administrator**, and **Guest**.

### Level 0: Context Diagram

```mermaid
graph TD
    %% Entities
    Owner([Owner])
    Admin([System Administrator])
    Guest([Guest])

    %% Central Process
    System(("(0)<br/>EME’s Apartelle Digital Transformation:<br/>An Automated Booking and Billing Management System<br/>with Chatbot-Assisted Guest Support"))

    %% Owner Flows
    Owner -- "Owner Data Input<br/>Room Listing Input<br/>Pricing Data Input<br/>Amenity Management Input<br/>Payment Account Input<br/>Room Status Update Input<br/>Booking Request Response Input<br/>Listing Update Input<br/>Listing Removal Input<br/>Dispute Response Input" --> System
    System -- "Registration Confirmation Notification<br/>Login Success Notification<br/>Room Listing Approval Notification<br/>Listing Rejection Notification<br/>Payment Notification<br/>Booking Request Notification<br/>Request Acceptance Acknowledgement<br/>Room Status Update Confirmation<br/>Listing Update Notification<br/>Listing Removal Notification<br/>Dispute Resolution Notification" --> Owner

    %% Admin Flows
    Admin -- "Add / Update Admin Account<br/>Approve / Reject Room Listing<br/>Delete User Account<br/>Suspend / Restore User Accounts<br/>Manage Reports And Disputes<br/>Modify Pricing Rules<br/>Add / Remove Amenities Categories<br/>Reset User Password" --> System
    System -- "View User Account Details<br/>View Amenities Details<br/>View Booking Activities<br/>View Reports and Disputes<br/>Room Listing Approval / Rejection<br/>System Report Display" --> Admin

    %% Guest Flows
    Guest -- "Guest Data Input<br/>Search Criteria Input<br/>Room Selection<br/>Online Payment Data Input<br/>Booking Request Input<br/>Booking Confirmation Review<br/>Amenity Selection & Filtering<br/>Guest Inquiry<br/>Dispute Report Input" --> System
    System -- "Registration Confirmation Notification<br/>Login Success Notification<br/>Search Result Display<br/>Room Availability Display<br/>Payment Acknowledgement<br/>Booking Request Acknowledgement<br/>Booking Details Display<br/>Amenities Display<br/>Chatbot / Staff Response<br/>Return Confirmation Notification<br/>Review Submission Acknowledgement<br/>Dispute Report Acknowledgement" --> Guest
```

## Verification Analysis

Based on the [Research Proposal](file:///c:/Users/basco/WebApp-EMEs-Apartelle/docs/research.md), the flow illustrated in the diagram is **CORRECT** and aligns with the following system requirements:

1.  **Process Identity**: The process name matches the official project title exactly.
2.  **Entity Roles**:
    *   **Owner**: Correctly handles establishment-side operations (Listings, Pricing, Disputes).
    *   **System Administrator**: Correctly handles platform-level management (User accounts, Global rules).
    *   **Guest**: Correctly handles the consumer journey (Search, Booking, Payment, Inquiry).
3.  **Chatbot Integration**: The inclusion of "Guest Inquiry" and "Chatbot / Staff Response" flows accurately reflects the "Chatbot-Assisted Guest Support" objective.
4.  **Dispute Management**: The diagram accounts for dispute reporting and resolution, which is essential for a comprehensive billing management system.
5.  **Amenities Filtering**: The "Amenity Selection & Filtering" input flow for Guests aligns with Objective 8 of the research paper.

## Conclusion
The DFD provided is an accurate representation of the system's functional scope and external interactions as defined in the project documentation.
