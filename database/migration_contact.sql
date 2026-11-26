-- Run once in phpMyAdmin (database: water_bill_management). Safe to re-run; does not touch existing data.
USE water_bill_management;
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
