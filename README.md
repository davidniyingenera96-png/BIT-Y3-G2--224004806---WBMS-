
# Water Bill Management System (vanilla PHP MVC + MySQL)
**Setup (XAMPP):** copy to `C:\xampp\htdocs\water-bill-management`; import `database/schema.sql` then `database/seed.sql`
(upgrading an existing database? import `database/migration_contact.sql` then `database/migration_v2.sql` instead). Open `http://localhost/water-bill-management/public/`.
Optional DB credentials: `app/config/database.local.php` (see `database.php`). Make sure `public/uploads/` is writable (logo uploads).
**Demo accounts** (password `Password@123`): `admin`, `staff.alice`, `jean.paul` (customer). Change them before real use.
**Roles & sidebars:** Admin - users, customers, readings, bills, payments, reports, messages, settings, profile. Staff - customers, readings, bills, payments, reports, profile. Customer - bills, payments, meter info, profile.
**Settings (admin):** logo upload, site name, tagline, contact details, billing rules. **Customer payments:** submitted with a transaction reference, kept Pending until staff/admin confirm (no payment gateway).

<img width="1902" height="1079" alt="Screenshot 2026-11-25 182903" src="https://github.com/user-attachments/assets/dd8f0234-0200-4899-85f2-ff1904a9037c" />


<img width="1906" height="600" alt="Screenshot 2026-11-25 181906" src="https://github.com/user-attachments/assets/65e24497-ff29-4ddc-a779-edc94e1c916d" />


    
# BIT-Y3-G2--224004806---WBMS-
Water Bill Management System handles this serv Customers view meters, bills and payments, staff record readings. 

