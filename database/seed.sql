-- Sample data. ALL sample accounts use the password: Password@123  (change after first login)
USE water_bill_management;

INSERT INTO users (user_id, username, email, password_hash, role) VALUES
 (1,'admin','admin@waterbill.test','$2y$10$No8Eywts2RtVQsrupiRRpOneolZUJoLNjBKPig7Lfxa9FldsKblxW','admin'),
 (2,'staff.alice','alice@waterbill.test','$2y$10$No8Eywts2RtVQsrupiRRpOneolZUJoLNjBKPig7Lfxa9FldsKblxW','staff'),
 (3,'staff.eric','eric@waterbill.test','$2y$10$No8Eywts2RtVQsrupiRRpOneolZUJoLNjBKPig7Lfxa9FldsKblxW','staff'),
 (4,'jean.paul','jean@waterbill.test','$2y$10$No8Eywts2RtVQsrupiRRpOneolZUJoLNjBKPig7Lfxa9FldsKblxW','customer'),
 (5,'marie.claire','marie@waterbill.test','$2y$10$No8Eywts2RtVQsrupiRRpOneolZUJoLNjBKPig7Lfxa9FldsKblxW','customer'),
 (6,'emmanuel','emmanuel@waterbill.test','$2y$10$No8Eywts2RtVQsrupiRRpOneolZUJoLNjBKPig7Lfxa9FldsKblxW','customer');

-- Customer 4 has no online account yet (registered by staff)
INSERT INTO customers (customer_id, user_id, full_name, meter_no, address, phone) VALUES
 (1,4,'Jean Paul','MT-001','KG 11 Ave, Kigali','0788000001'),
 (2,5,'Marie Claire','MT-002','KN 5 St, Kigali','0788000002'),
 (3,6,'Emmanuel','MT-003','KK 15 Rd, Kigali','0788000003'),
 (4,NULL,'Grace Uwase','MT-004','KG 201 St, Kigali','0788000004');

-- Cumulative meter values: April = baseline, May = current
INSERT INTO meter_readings (reading_id, customer_id, reading_date, meter_value, units_used, reader_id) VALUES
 (1,1,'2025-04-01',1000,0,2),(2,2,'2025-04-01',500,0,2),(3,3,'2025-04-01',200,0,3),(4,4,'2025-04-01',50,0,3),
 (5,1,'2025-05-01',1025,25,2),(6,2,'2025-05-01',530,30,2),(7,3,'2025-05-01',220,20,3),(8,4,'2025-05-01',70,20,3);

-- SAMPLE rates for demonstration only - NOT an official tariff; the admin configures real values
INSERT INTO billing_settings (unit_rate, fixed_charge, tax_rate, due_days, currency, updated_by)
VALUES (1500.00, 2000.00, 18.00, 15, 'RWF', 1);

-- total = (units * rate + fixed) * (1 + tax%)
INSERT INTO bills (bill_id, customer_id, reading_id, billing_month, previous_reading, current_reading, units_used, unit_rate, fixed_charge, tax_amount, total_amount, amount_paid, status, due_date) VALUES
 (1,1,5,'2025-05',1000,1025,25,1500,2000,7110,46610,46610,'Paid','2025-05-31'),
 (2,2,6,'2025-05',500,530,30,1500,2000,8460,55460,30000,'Partially Paid','2025-05-31'),
 (3,3,7,'2025-05',200,220,20,1500,2000,5760,37760,0,'Unpaid','2025-05-31'),
 (4,4,8,'2025-05',50,70,20,1500,2000,5760,37760,0,'Unpaid','2025-05-31');

INSERT INTO payments (bill_id, amount_paid, payment_date, payment_method, transaction_reference, recorded_by) VALUES
 (1,46610,'2025-05-10','Mobile Money','TXN-0001',2),
 (2,30000,'2025-05-12','Cash','TXN-0002',2);

INSERT INTO audit_logs (user_id, action, entity, entity_id, details) VALUES
 (2,'BILL_GENERATED','bill',1,'May 2025 bill for MT-001'),
 (2,'PAYMENT_RECORDED','payment',1,'46610 via Mobile Money');
