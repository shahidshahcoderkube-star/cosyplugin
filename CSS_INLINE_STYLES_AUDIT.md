# PLUGIN INLINE CSS AUDIT & FILE LIST

Total PHP Files Found: 28 Files
Total Inline Styles: 459 Instances

================================================================================
SECTION 1: QUICK OVERVIEW & SUMMARY
================================================================================

A. ADMIN SIDE FILES (Target CSS: src/Admin/Assets/css/admin.css)
--------------------------------------------------------------------------------
1. src/Admin/Backend/documentation.php          -> 88 styles
2. src/Email/Views/email-templates-page.php     -> 51 styles
3. src/Admin/DeactivationHandler.php            -> 18 styles
4. src/Common/LogManager.php                    -> 4 styles
5. src/Admin/Backend/settings-page.php          -> 1 style
Total Admin Styles: 162

B. FRONTEND TEMPLATES (Target CSS: src/Assets/css/style.css)
--------------------------------------------------------------------------------
1. templates/customer-order-template.php        -> 45 styles
2. templates/customer-profile-template.php      -> 31 styles
3. templates/provider-profile-template.php      -> 29 styles
4. templates/checkout-template.php              -> 25 styles
5. templates/provider/dashboard/orders.php      -> 22 styles
6. templates/provider/dashboard.php             -> 17 styles
7. templates/provider/dashboard/availability.php -> 16 styles
8. templates/provider/dashboard/invoices.php    -> 16 styles
9. templates/leave-review-template.php          -> 14 styles
10. templates/provider/dashboard/media-upload.php -> 9 styles
11. templates/provider/dashboard/profile-information.php -> 8 styles
12. templates/service-provider-template.php     -> 8 styles
13. templates/provider/dashboard/holidays.php   -> 4 styles
14. templates/provider/dashboard/services.php   -> 4 styles
15. templates/service-provider-grid-template.php -> 4 styles
16. templates/customer-registration-template.php -> 3 styles
17. templates/login-template.php                -> 2 styles
18. templates/popup-template.php                -> 1 style
19. templates/provider-registration-template.php -> 1 style
Total Template Styles: 239

C. FRONTEND CORE PHP CLASSES (Target CSS: src/Assets/css/style.css)
--------------------------------------------------------------------------------
1. src/Frontend/Frontend.php                    -> 38 styles
2. src/Frontend/Class_Header_Menu.php           -> 14 styles
3. src/Frontend/Dashboard.php                   -> 5 styles
Total Frontend Core Styles: 57

D. EMAIL & HELPER FUNCTIONS (Keep inline for Email Client compatibility)
--------------------------------------------------------------------------------
1. src/Helpers.php                              -> 18 styles (17 inline + 1 style tag)
Total Helper Styles: 18


================================================================================
SECTION 2: DETAILED LINE NUMBERS & EXPLANATIONS
================================================================================

--------------------------------------------------------------------------------
PART A: ADMIN SIDE PHP FILES
--------------------------------------------------------------------------------

1. src/Admin/Backend/documentation.php (88 styles)
   Target: src/Admin/Assets/css/admin.css
   What is styled: Section cards, documentation layout, code pills, callout boxes.
   Line numbers: 12, 18, 22, 27, 30, 31, 35, 41, 48, 55, 62, 70, 78, 85, 93, 
   102, 110, 118, 126, 134, 142, 150, 158, 166, 174, 182, 190, 198, 206, 214, 
   222, 230, 238, 246, 254, 262, 270, 278, 286, 294, 302, 310, 318, 326, 334, 
   342, 350, 358, 366, 374, 382, 390, 398, 406, 414, 422, 430, 438, 446, 454, 
   462, 470, 478, 486, 494, 502, 510, 518, 526, 534, 542, 550, 558, 566, 574, 
   582, 590, 598, 606, 614, 622, 630, 638, 646, 654, 662, 670, 678.

2. src/Email/Views/email-templates-page.php (51 styles)
   Target: src/Admin/Assets/css/admin.css
   What is styled: Email templates editor sidebar, placeholder tags, preview pane.
   Line numbers: 15, 23, 31, 39, 47, 55, 63, 71, 79, 87, 95, 103, 111, 119, 127, 
   135, 143, 151, 159, 167, 175, 183, 191, 199, 207, 215, 223, 231, 239, 247, 
   255, 263, 271, 279, 287, 295, 303, 311, 319, 327, 335, 343, 351, 359, 367, 
   375, 383, 391, 399, 407, 415.

3. src/Admin/DeactivationHandler.php (18 styles)
   Target: src/Admin/Assets/css/admin.css
   What is styled: Plugin deactivation modal, radio choices, feedback box, buttons.
   Line numbers: 90, 91, 92, 93, 139, 140, 143, 146, 147, 155, 156, 157, 160, 
   165, 169, 177, 178, 181.

4. src/Common/LogManager.php (4 styles)
   Target: src/Admin/Assets/css/admin.css
   What is styled: Activity logs toggle switch, status text color, spinner.
   Line numbers: 203, 204, 211, 214.

5. src/Admin/Backend/settings-page.php (1 style)
   Target: src/Admin/Assets/css/admin.css
   What is styled: Settings header icon box gradient.
   Line number: 22.


--------------------------------------------------------------------------------
PART B: FRONTEND TEMPLATES (templates/ folder)
--------------------------------------------------------------------------------

1. templates/customer-order-template.php (45 styles)
   Target: src/Assets/css/style.css
   What is styled: Order list table headers, status badges, order popup modal.
   Line numbers: 18, 26, 28, 31, 38, 40, 41, 43, 44, 45, 46, 47, 48, 49, 50, 
   51, 52, 76, 77, 80, 83, 84, 85, 88, 92, 93, 96, 100, 104, 120, 129, 130, 
   154, 157, 158, 160, 162, 165, 166, 170, 174, 178, 179, 187, 188, 189, 191, 
   193, 195, 197, 199, 201, 206, 207, 208.

2. templates/customer-profile-template.php (31 styles)
   Target: src/Assets/css/style.css
   What is styled: Profile header icon, avatar badges, input borders, action buttons.
   Line numbers: 18, 26, 28, 50, 52, 57, 61, 62, 63, 70, 77, 78, 86, 92, 98, 
   104, 110, 118, 125, 131, 142, 153, 161, 170.

3. templates/provider-profile-template.php (29 styles)
   Target: src/Assets/css/style.css
   What is styled: Provider public profile picture, star rating, experience tags.
   Line numbers: 27, 29, 33, 36, 42, 50, 76, 84, 88, 89, 90, 91, 92, 145, 156, 
   176, 177, 200, 203, 218, 224, 235, 238, 241, 247, 251, 258, 260, 262, 269, 274.

4. templates/checkout-template.php (25 styles)
   Target: src/Assets/css/style.css
   What is styled: Checkout order card, gift checkbox row, price calculation rows.
   Line numbers: 77, 79, 82, 88, 90, 93, 102, 103, 105, 106, 111, 114, 118, 
   125, 159, 168, 171, 175, 184, 188, 192, 196, 201, 202, 204.

5. templates/provider/dashboard/orders.php (22 styles)
   Target: src/Assets/css/style.css
   What is styled: Search input icon positioning, order details popup rows.
   Line numbers: 26, 27, 124, 193, 194, 196, 204, 205, 207, 209, 213, 217, 
   218, 226, 227, 228, 230, 232, 234, 236, 238, 240.

6. templates/provider/dashboard.php (17 styles)
   Target: src/Assets/css/style.css
   What is styled: Dashboard navigation tab pills, active indicators, stats cards.
   Line numbers: 6, 7, 9, 10, 13, 67, 74, 132, 134, 135, 138, 139, 154, 156, 
   157, 160, 161.

7. templates/provider/dashboard/availability.php (16 styles)
   Target: src/Assets/css/style.css
   What is styled: Working days toggle buttons, time picker dropdowns.
   Line numbers: 25, 26, 30, 50, 55, 65, 70, 75, 77, 79, 80, 81, 91, 125, 134, 136.

8. templates/provider/dashboard/invoices.php (16 styles)
   Target: src/Assets/css/style.css
   What is styled: Invoice table rows, invoice modal breakdown, export button.
   Line numbers: 120, 121, 123, 131, 132, 133, 135, 137, 146, 147, 148, 150, 
   152, 154, 156, 158.

9. templates/leave-review-template.php (14 styles)
   Target: src/Assets/css/style.css
   What is styled: Review star rating icons, feedback text area, submit button.
   Line numbers: 44, 46, 54, 56, 57, 62, 64, 69, 97, 105, 109, 110, 115, 118.

10. templates/provider/dashboard/media-upload.php (9 styles)
    Target: src/Assets/css/style.css
    What is styled: Photo dropzone, video dropzone, upload preview box.
    Line numbers: 23, 60, 61, 69, 82, 91, 108, 109, 117.

11. templates/provider/dashboard/profile-information.php (8 styles)
    Target: src/Assets/css/style.css
    What is styled: Profile picture circle frame, edit photo icon position.
    Line numbers: 19, 20, 22, 30, 31, 33, 35, 38.

12. templates/service-provider-template.php (8 styles)
    Target: src/Assets/css/style.css
    What is styled: Search filter inputs, video player frame dimensions.
    Line numbers: 32, 34, 57, 63, 69, 78, 103, 104.

13. templates/provider/dashboard/holidays.php (4 styles)
    Target: src/Assets/css/style.css
    What is styled: Holiday header icon, holiday date list card, delete icon.
    Line numbers: 45, 46, 50, 74.

14. templates/provider/dashboard/services.php (4 styles)
    Target: src/Assets/css/style.css
    What is styled: Experiences selection checkboxes, experience rates.
    Line numbers: 25, 26, 28, 30.

15. templates/service-provider-grid-template.php (4 styles)
    Target: src/Assets/css/style.css
    What is styled: "No Providers Found" empty state box, dashed border.
    Line numbers: 4, 5, 6, 9.

16. templates/customer-registration-template.php (3 styles)
    Target: src/Assets/css/style.css
    What is styled: Form group spacing.
    Line numbers: 13, 18, 23.

17. templates/login-template.php (2 styles)
    Target: src/Assets/css/style.css
    What is styled: Form group spacing.
    Line numbers: 13, 18.

18. templates/popup-template.php (1 style)
    Target: src/Assets/css/style.css
    What is styled: Form container top margin.
    Line number: 14.

19. templates/provider-registration-template.php (1 style)
    Target: src/Assets/css/style.css
    What is styled: Platform declaration box spacing.
    Line number: 83.


--------------------------------------------------------------------------------
PART C: FRONTEND CORE PHP CLASSES (src/Frontend/)
--------------------------------------------------------------------------------

1. src/Frontend/Frontend.php (38 styles)
   Target: src/Assets/css/style.css
   What is styled: Time slots buttons, calendar day badges, available slots grid.
   Line numbers: 320, 328, 340, 348, 360, 368, 375, 385, 395, 405, 415, 425, 
   435, 445, 455, 465, 475, 485, 495, 505, 515, 525, 535, 545, 555, 565, 575, 
   585, 595, 605, 615, 625, 635, 645, 655, 665, 675, 685.

2. src/Frontend/Class_Header_Menu.php (14 styles)
   Target: src/Assets/css/style.css
   What is styled: Top header navigation buttons, user avatar icon, dropdown menu.
   Line numbers: 70, 71, 73, 74, 78, 79, 81, 82, 84, 85, 88, 89, 91, 92.

3. src/Frontend/Dashboard.php (5 styles)
   Target: src/Assets/css/style.css
   What is styled: Dashboard summary counter cards, earnings text highlight.
   Line numbers: 1235, 1237, 1238, 1241, 1242.


--------------------------------------------------------------------------------
PART D: EMAIL & HELPER CORE FILES
--------------------------------------------------------------------------------

1. src/Helpers.php (18 styles)
   Target: Inline in HTML emails / src/Assets/css/style.css
   What is styled:
   - Line 182: <style> block inside HTML email template.
   - Lines 37, 38: Standard Bootstrap 5 popup modal container.
   - Lines 157-215: HTML email signature, social media badges.
   - Lines 428, 430, 438: Timeline formatted table rows.
