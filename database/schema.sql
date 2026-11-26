-- Water Bill Management System - schema (XAMPP / MySQL 8 / MariaDB 10.4+)
-- Re-runnable: drops existing WBMS tables first (development use), creates all tables,
-- then adds foreign keys at the end so table order can never cause error 1005 / errno 150.
CREATE DATABASE IF NOT EXISTS water_bill_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE water_bill_management;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS site_settings, contact_messages, audit_logs, billing_settings, payments, bills, meter_readings, customers, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  user_id       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username      VARCHAR(50)  NOT NULL,
  email         VARCHAR(120) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','staff','customer') NOT NULL DEFAULT 'customer',
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE customers (
  customer_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     INT UNSIGNED NULL,                    -- NULL until the customer creates an online account
  full_name   VARCHAR(120) NOT NULL,
  meter_no    VARCHAR(30)  NOT NULL,
  address     VARCHAR(255) NOT NULL,
  phone       VARCHAR(25)  NULL,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,        -- deactivate instead of delete (financial history)
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (customer_id),
  UNIQUE KEY uq_customers_user (user_id),
  UNIQUE KEY uq_customers_meter (meter_no),
  KEY idx_customers_name (full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE meter_readings (
  reading_id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_id  INT UNSIGNED NOT NULL,
  reading_date DATE NOT NULL,
  meter_value  DECIMAL(12,2) NOT NULL,              -- cumulative meter value
  units_used   DECIMAL(12,2) NOT NULL DEFAULT 0,    -- = meter_value - previous accepted reading
  reader_id    INT UNSIGNED NOT NULL,
  status       ENUM('Accepted','Voided') NOT NULL DEFAULT 'Accepted',
  note         VARCHAR(255) NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (reading_id),
  KEY idx_readings_customer_date (customer_id, reading_date),
  KEY idx_readings_reader (reader_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bills (
  bill_id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_id      INT UNSIGNED NOT NULL,
  reading_id       INT UNSIGNED NOT NULL,
  billing_month    CHAR(7) NOT NULL,                -- 'YYYY-MM'
  previous_reading DECIMAL(12,2) NOT NULL,          -- snapshot of values used (audit-safe)
  current_reading  DECIMAL(12,2) NOT NULL,
  units_used       DECIMAL(12,2) NOT NULL,
  unit_rate        DECIMAL(12,2) NOT NULL,          -- snapshot of rate applied
  fixed_charge     DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax_amount       DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_amount     DECIMAL(12,2) NOT NULL,
  amount_paid      DECIMAL(12,2) NOT NULL DEFAULT 0,
  status           ENUM('Unpaid','Partially Paid','Paid','Cancelled') NOT NULL DEFAULT 'Unpaid',
  due_date         DATE NOT NULL,
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (bill_id),
  UNIQUE KEY uq_bill_customer_month (customer_id, billing_month),   -- no duplicate bills
  KEY idx_bills_reading (reading_id),
  KEY idx_bills_status (status),
  KEY idx_bills_due (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payments (
  payment_id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  bill_id               INT UNSIGNED NOT NULL,
  amount_paid           DECIMAL(12,2) NOT NULL,
  payment_date          DATE NOT NULL,
  payment_method        ENUM('Cash','Mobile Money','Bank Transfer','Card') NOT NULL,
  transaction_reference VARCHAR(60) NULL,
  recorded_by           INT UNSIGNED NOT NULL,
  status                ENUM('Completed','Voided','Pending','Rejected') NOT NULL DEFAULT 'Completed',  -- never hard-delete
  created_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (payment_id),
  UNIQUE KEY uq_txn_ref (transaction_reference),
  KEY idx_payments_bill (bill_id),
  KEY idx_payments_user (recorded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE billing_settings (
  setting_id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  unit_rate    DECIMAL(12,2) NOT NULL,
  fixed_charge DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax_rate     DECIMAL(5,2)  NOT NULL DEFAULT 0,    -- percent, applied to water charge + fixed charge
  due_days     SMALLINT UNSIGNED NOT NULL DEFAULT 15,
  currency     CHAR(3) NOT NULL DEFAULT 'RWF',
  updated_by   INT UNSIGNED NULL,
  updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (setting_id),
  KEY idx_settings_user (updated_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
  log_id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NULL,
  action     VARCHAR(60) NOT NULL,
  entity     VARCHAR(40) NOT NULL,
  entity_id  INT UNSIGNED NULL,
  details    TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (log_id),
  KEY idx_audit_created (created_at),
  KEY idx_audit_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
  message_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(80)  NOT NULL,
  email      VARCHAR(120) NOT NULL,
  subject    VARCHAR(120) NOT NULL,
  message    TEXT NOT NULL,
  ip_address VARCHAR(45) NULL,
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (message_id),
  KEY idx_msg_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE site_settings (
  setting_key   VARCHAR(40) NOT NULL,
  setting_value TEXT NOT NULL,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Foreign keys (added last; every referenced table already exists, all ID columns are INT UNSIGNED)
ALTER TABLE customers      ADD CONSTRAINT fk_customers_user    FOREIGN KEY (user_id)     REFERENCES users(user_id)           ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE meter_readings ADD CONSTRAINT fk_readings_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id)   ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE meter_readings ADD CONSTRAINT fk_readings_reader   FOREIGN KEY (reader_id)   REFERENCES users(user_id)           ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE bills          ADD CONSTRAINT fk_bills_customer    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)   ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE bills          ADD CONSTRAINT fk_bills_reading     FOREIGN KEY (reading_id)  REFERENCES meter_readings(reading_id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE payments       ADD CONSTRAINT fk_payments_bill     FOREIGN KEY (bill_id)     REFERENCES bills(bill_id)           ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE payments       ADD CONSTRAINT fk_payments_user     FOREIGN KEY (recorded_by) REFERENCES users(user_id)           ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE billing_settings ADD CONSTRAINT fk_settings_user   FOREIGN KEY (updated_by)  REFERENCES users(user_id)           ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE audit_logs     ADD CONSTRAINT fk_audit_user        FOREIGN KEY (user_id)     REFERENCES users(user_id)           ON DELETE SET NULL ON UPDATE CASCADE;

-- Data-integrity checks (enforced on MySQL 8 / MariaDB 10.2+)
ALTER TABLE meter_readings ADD CONSTRAINT chk_reading_value CHECK (meter_value >= 0 AND units_used >= 0);
ALTER TABLE payments       ADD CONSTRAINT chk_payment_amount CHECK (amount_paid > 0);
