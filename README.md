# Water Bill Management System (vanilla PHP MVC + MySQL)
**Setup (XAMPP):** copy to `C:\xampp\htdocs\water-bill-management`; import `database/schema.sql` then `database/seed.sql`
(upgrading an existing database? import `database/migration_contact.sql` then `database/migration_v2.sql` instead). Open `http://localhost/water-bill-management/public/`.
Optional DB credentials: `app/config/database.local.php` (see `database.php`). Make sure `public/uploads/` is writable (logo uploads).
**Demo accounts** (password `Password@123`): `admin`, `staff.alice`, `jean.paul` (customer). Change them before real use.
**Roles & sidebars:** Admin - users, customers, readings, bills, payments, reports, messages, settings, profile. Staff - customers, readings, bills, payments, reports, profile. Customer - bills, payments, meter info, profile.
**Settings (admin):** logo upload, site name, tagline, contact details, billing rules. **Customer payments:** submitted with a transaction reference, kept Pending until staff/admin confirm (no payment gateway).
