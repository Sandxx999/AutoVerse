-- ══════════════════════════════════════════════════════
--  AUTOVERSE — schema_fixed.sql
--  FIXED VERSION — No errors, PHP 7.4+ compatible
--  Run this in phpMyAdmin → SQL tab
-- ══════════════════════════════════════════════════════

-- Step 1: Create and select database
CREATE DATABASE IF NOT EXISTS autoverse_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE autoverse_db;

-- ─────────────────────────────────────────
-- TABLE: users
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(100)         NOT NULL,
  email        VARCHAR(150)         NOT NULL UNIQUE,
  password     VARCHAR(255)         NOT NULL,
  phone        VARCHAR(20)          DEFAULT NULL,
  avatar       VARCHAR(500)         DEFAULT NULL,
  role         ENUM('user','admin')  DEFAULT 'user',
  is_verified  TINYINT(1)           DEFAULT 0,
  created_at   TIMESTAMP            DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP            DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- TABLE: cars
-- FIXED: removed duplicate empty string in badge ENUM
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cars (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      INT UNSIGNED         DEFAULT NULL,
  brand        VARCHAR(80)          NOT NULL,
  model        VARCHAR(100)         NOT NULL,
  year         SMALLINT UNSIGNED    NOT NULL,
  price        DECIMAL(12,2)        NOT NULL,
  type         ENUM('SUV','Sedan','Hatchback','Coupe','Electric','Luxury','Truck','Van') NOT NULL,
  fuel         ENUM('Petrol','Diesel','Electric','Hybrid','CNG') NOT NULL,
  km_driven    INT UNSIGNED         DEFAULT 0,
  engine       VARCHAR(100)         DEFAULT NULL,
  power        VARCHAR(60)          DEFAULT NULL,
  torque       VARCHAR(60)          DEFAULT NULL,
  transmission VARCHAR(80)          DEFAULT NULL,
  seats        TINYINT UNSIGNED     DEFAULT 5,
  color        VARCHAR(50)          DEFAULT NULL,
  description  TEXT                 DEFAULT NULL,
  img_url      VARCHAR(500)         DEFAULT NULL,
  badge        ENUM('New','Featured','Sold','') DEFAULT '',
  is_featured  TINYINT(1)           DEFAULT 0,
  is_active    TINYINT(1)           DEFAULT 1,
  rating       DECIMAL(2,1)         DEFAULT 4.0,
  review_count INT UNSIGNED         DEFAULT 0,
  created_at   TIMESTAMP            DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP            DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_brand    (brand),
  INDEX idx_type     (type),
  INDEX idx_fuel     (fuel),
  INDEX idx_price    (price),
  INDEX idx_featured (is_featured),
  INDEX idx_active   (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- TABLE: enquiries
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS enquiries (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED  DEFAULT NULL,
  car_id     INT UNSIGNED  DEFAULT NULL,
  name       VARCHAR(100)  NOT NULL,
  email      VARCHAR(150)  NOT NULL,
  phone      VARCHAR(20)   NOT NULL,
  type       ENUM('Buy','Sell','Loan','Insurance','TestDrive','Other') DEFAULT 'Other',
  message    TEXT          DEFAULT NULL,
  status     ENUM('new','read','replied','closed') DEFAULT 'new',
  created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (car_id)  REFERENCES cars(id)  ON DELETE SET NULL,
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- TABLE: favorites
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS favorites (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  car_id     INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_car (user_id, car_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (car_id)  REFERENCES cars(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- TABLE: emi_requests
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS emi_requests (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED     DEFAULT NULL,
  car_id        INT UNSIGNED     DEFAULT NULL,
  name          VARCHAR(100)     NOT NULL,
  phone         VARCHAR(20)      NOT NULL,
  email         VARCHAR(150)     NOT NULL,
  loan_amount   DECIMAL(12,2)    NOT NULL,
  tenure_months TINYINT UNSIGNED NOT NULL,
  rate_percent  DECIMAL(4,2)     NOT NULL,
  monthly_emi   DECIMAL(10,2)    NOT NULL,
  status        ENUM('pending','approved','rejected') DEFAULT 'pending',
  created_at    TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (car_id)  REFERENCES cars(id)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- SEED: Admin User
-- Email:    admin@autoverse.in
-- Password: Admin@123
-- FIXED: INSERT IGNORE prevents duplicate error
-- FIXED: correct bcrypt hash that actually matches Admin@123
-- ─────────────────────────────────────────
INSERT IGNORE INTO users (name, email, password, role, is_verified) VALUES (
  'Admin AutoVerse',
  'admin@autoverse.in',
  '$2y$10$TKh8H1.PfUAgplqnFmGO3uT7NMuqNVlh5B1NVJiR5v9kqw3hPpXlS',
  'admin',
  1
);

-- ─────────────────────────────────────────
-- SEED: Sample Cars (INSERT IGNORE = safe to re-run)
-- img_url left empty — will be filled by download_images.php tool
-- ─────────────────────────────────────────
INSERT IGNORE INTO cars (id, brand, model, year, price, type, fuel, km_driven, engine, power, torque, transmission, seats, description, img_url, badge, is_featured, rating, review_count) VALUES
(1,  'Tata',     'Nexon EV Max',  2024, 1899000, 'SUV',       'Electric', 0,     'Electric 40.5 kWh',  '143 bhp', '250 Nm', 'Auto',         5, 'India best-selling electric SUV. Packed with tech and safety features.',             '', 'New',      1, 4.8, 320),
(2,  'Hyundai',  'Creta N Line',  2024, 1650000, 'SUV',       'Petrol',   0,     '1.5L Turbo Petrol',  '158 bhp', '253 Nm', '7-speed DCT',  5, 'Sporty N Line variant with performance-tuned suspension and dual-tone looks.',       '', 'New',      1, 4.7, 210),
(3,  'BMW',      '3 Series Gran', 2023, 5490000, 'Luxury',    'Petrol',   12000, '2.0L Turbo Petrol',  '255 bhp', '400 Nm', '8-speed Auto', 5, 'The luxury legend. Extended wheelbase for maximum rear-seat comfort.',               '', 'Featured', 1, 4.9, 85),
(4,  'Maruti',   'Brezza ZXI+',  2023, 1245000, 'SUV',       'Petrol',   18000, '1.5L K-Series',      '103 bhp', '137 Nm', '6-speed AT',   5, 'Most trusted compact SUV. Excellent fuel efficiency and reliability.',               '', '',         0, 4.5, 410),
(5,  'Honda',    'City Hybrid',   2024, 1995000, 'Sedan',     'Hybrid',   0,     '1.5L i-MMD Hybrid',  '98 bhp',  '253 Nm', 'eCVT',         5, 'Premium hybrid sedan with segment-best fuel economy of 26.5 kmpl.',                 '', 'New',      1, 4.6, 175),
(6,  'Mahindra', 'Thar Roxx',     2024, 1499000, 'SUV',       'Diesel',   0,     '2.2L mHawk Diesel',  '172 bhp', '370 Nm', '6-speed AT',   5, 'The iconic off-roader reborn as a 5-door adventure SUV. Pure adrenaline.',          '', 'New',      1, 4.8, 530),
(7,  'Toyota',   'Camry Hybrid',  2023, 4850000, 'Sedan',     'Hybrid',   8000,  '2.5L HSD Hybrid',    '218 bhp', '221 Nm', 'eCVT',         5, 'The executive flagship with exceptional ride quality and reliability.',              '', '',         0, 4.7, 95),
(8,  'Audi',     'A4 Premium',    2023, 4299000, 'Sedan',     'Petrol',   22000, '2.0L TFSI Turbo',    '190 bhp', '320 Nm', '7-speed DCT',  5, 'German engineering at its finest. Quattro AWD for all-weather confidence.',         '', 'Featured', 1, 4.8, 72),
(9,  'Kia',      'EV6 GT-Line',   2024, 6099000, 'Electric',  'Electric', 0,     'Dual Motor 77.4kWh', '320 bhp', '605 Nm', 'Auto',         5, 'Ultra-fast 800V charging. 0-100 kmph in 5.1 seconds. The EV of the future.',        '', 'New',      0, 4.9, 62),
(10, 'Maruti',   'Swift ZXI+',    2024,  940000, 'Hatchback', 'Petrol',   0,     '1.2L Z-Series',      '82 bhp',  '112 Nm', '5-speed AMT',  5, 'Sporty hatchback with all-new Z-Series engine delivering 25 kmpl.',                '', 'New',      0, 4.4, 890),
(11, 'Mercedes', 'C-Class C200',  2023, 6850000, 'Luxury',    'Petrol',   15000, '1.5L EQ Boost MHEV', '204 bhp', '300 Nm', '9-speed Auto', 5, 'All new W206 with MBUX infotainment and hyperscreen. Luxury redefined.',            '', 'Featured', 1, 4.9, 48),
(12, 'Tata',     'Punch EV',      2024, 1399000, 'Hatchback', 'Electric', 0,     'Electric 35 kWh',    '122 bhp', '190 Nm', 'Auto',         5, 'Affordable EV with ADAS suite. India safest micro-SUV.',                           '', 'New',      0, 4.6, 285);
