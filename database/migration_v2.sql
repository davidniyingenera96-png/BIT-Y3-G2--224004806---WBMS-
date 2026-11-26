-- Run once in phpMyAdmin (database water_bill_management). Keeps existing data.
USE water_bill_management;
CREATE TABLE IF NOT EXISTS site_settings (
  setting_key   VARCHAR(40) NOT NULL,
  setting_value TEXT NOT NULL,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Customer-submitted payments wait as Pending until staff confirm or reject them
ALTER TABLE payments MODIFY status ENUM('Completed','Voided','Pending','Rejected') NOT NULL DEFAULT 'Completed';
