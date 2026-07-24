# Client Requirements - Cosy Appointments Plugin

This document serves as the official client requirements list gathered from recent chat feedback and document screenshots. We will use this to track modifications and implement changes.

---

## 1. UI & Layout Simplification ("Less is More")

### 📂 Categories vs AI Search
* **Requirement:** Remove all category selection cards/tabs from the home and landing pages.
* **Replacement:** Add an **AI Search bar** as the primary navigation tool.
* **Placeholder text:** `"What would you like to talk about"`

### 👤 Profile Layout Minimalization
Simplify the provider profiles to only show essential info:
1. **📷 Photo:** Make the profile photo larger and more prominent.
2. **Name:** Display provider display name.
3. **Rating (★★★★★):** Show star rating **only if** the provider has reviews. Do not display blank or zero stars.
4. **🎥 Video:** Introduction video player.
5. **📖 Story:** Provider bio/description section.
6. **Book Conversation:** Direct booking button.
7. **❌ Blue Verification Tick:** Remove this badge completely from profiles.
8. **❌ Reviews Section:** Hide this section completely if the provider has no reviews.

---

## 2. Booking Flow & Payment Optimizations

### 📅 Booking Steps
* **No Service Selection:** Remove the "Select Service" dropdown from the checkout. The conversation itself is the service, and customers do not need to specify or define it beforehand.
* **Flat Rate Pricing:** Implement a single, unified flat rate (£) for all provider conversations instead of per-service prices.
* **Step-by-Step UI Transition:** Once a user clicks/selects a date on the calendar, the screen should transition/smooth-scroll directly to the **"Call Schedule"** section to capture user attention and commit them to the booking (rather than keeping the entire profile and calendar visible simultaneously).

### 💳 Stripe Alternatives
* Keep payment gateways modular. Add support for other payment gateways (like PayPal Commerce, Mollie, Braintree, or Worldpay) as alternatives to Stripe due to client request.

---

## 3. Review Management Restructuring

* **Provider Restrictions:** Strip review deletion/rejection rights from the Provider Dashboard. Providers can no longer delete reviews.
* **Admin-Only Deletions:** Only Site Administrators retain the ability to delete reviews.
* **Provider Replies:** Replace the "Delete" button in the Provider Dashboard with a **"Respond to Review"** textarea to let providers post public replies underneath customer feedback.

---

## 4. Hosting, Infrastructure & Bug Fixes

### 🌐 Domain & Hosting Setup
* Deploy the final site directly under `CosyChats.com` to eliminate dependencies and redirects from the old `helpisonly` domain.

### 🐛 Critical Bug Fix (Immediate Priority)
* **Calendar Unavailable Days:** Investigate and fix the issue where Service Providers cannot successfully mark specific days as "unavailable" or "holidays" in their dashboard calendars. Currently, this option is broken and not disabling dates for booking.
