-- =====================================================
-- Medicare Plus Sri Lanka — Complete Database Schema
-- Run in phpMyAdmin against: medicare_plus_db
-- Default XAMPP port: 3306
-- =====================================================

CREATE DATABASE IF NOT EXISTS medicare_plus_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medicare_plus_db;

-- USERS
CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name      VARCHAR(80)  NOT NULL,
    last_name       VARCHAR(80)  NOT NULL,
    email           VARCHAR(180) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('patient','doctor','admin') NOT NULL DEFAULT 'patient',
    phone           VARCHAR(20),
    address         VARCHAR(255),
    city            VARCHAR(100),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- PATIENTS
CREATE TABLE IF NOT EXISTS patients (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL UNIQUE,
    date_of_birth   DATE,
    gender          ENUM('male','female','other'),
    blood_type      VARCHAR(5),
    emergency_contact VARCHAR(80),
    emergency_phone VARCHAR(20),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- DOCTORS
CREATE TABLE IF NOT EXISTS doctors (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             INT UNSIGNED NOT NULL UNIQUE,
    specialization      VARCHAR(120) NOT NULL,
    qualification       VARCHAR(255),
    hospital            VARCHAR(180),
    location            VARCHAR(180),
    consultation_fee    DECIMAL(10,2) NOT NULL DEFAULT 1500.00,
    experience_years    TINYINT UNSIGNED DEFAULT 0,
    rating              DECIMAL(3,2) DEFAULT 4.00,
    availability        VARCHAR(255),
    bio                 TEXT,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- APPOINTMENTS
CREATE TABLE IF NOT EXISTS appointments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id      INT UNSIGNED NOT NULL,
    doctor_id       INT UNSIGNED NOT NULL,
    appointment_dt  DATETIME NOT NULL,
    status          ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id)  REFERENCES doctors(id)  ON DELETE CASCADE
);

-- MEDICAL REPORTS
CREATE TABLE IF NOT EXISTS medical_reports (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id      INT UNSIGNED NOT NULL,
    appointment_id  INT UNSIGNED,
    uploaded_by     INT UNSIGNED NOT NULL,
    file_name       VARCHAR(255) NOT NULL,
    file_path       VARCHAR(500) NOT NULL,
    description     TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id)     REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by)    REFERENCES users(id) ON DELETE CASCADE
);

-- PAYMENTS
CREATE TABLE IF NOT EXISTS payments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id  INT UNSIGNED NOT NULL UNIQUE,
    amount          DECIMAL(10,2) NOT NULL,
    status          ENUM('pending','paid','refunded') NOT NULL DEFAULT 'pending',
    payment_method  VARCHAR(50),
    transaction_ref VARCHAR(100),
    paid_at         TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
);

-- NOTIFICATIONS
CREATE TABLE IF NOT EXISTS notifications (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    message     TEXT NOT NULL,
    is_read     TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- MESSAGES
CREATE TABLE IF NOT EXISTS messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id   INT UNSIGNED NOT NULL,
    receiver_id INT UNSIGNED NOT NULL,
    message     TEXT NOT NULL,
    is_read     TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- BLOG POSTS
CREATE TABLE IF NOT EXISTS blog_posts (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id   INT UNSIGNED,
    title       VARCHAR(255) NOT NULL,
    excerpt     TEXT,
    content     LONGTEXT,
    category    VARCHAR(80),
    published   TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- SERVICES
CREATE TABLE IF NOT EXISTS services (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    description TEXT,
    icon        VARCHAR(80),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- SEED DATA
-- Passwords:
--   Doctors  → Doctor123!
--   Admin    → Admin123!
--   Patient  → Patient123!
-- =====================================================

INSERT INTO users (first_name, last_name, email, password_hash, role, phone, city) VALUES
('Nuwan',     'Perera',         'nuwan.perera@medicareplusslk.lk',       '$2y$12$rPDQknTi.cgAL2Zgxyw3UOQL0PLtUbShTvqJRf55ytT9tefqakjtS', 'doctor',  '0771234567', 'Colombo'),
('Dilhani',   'Wickramasinghe', 'dilhani.wickrama@medicareplusslk.lk',   '$2y$12$UpG133q91In.wRekm7VbEOnlLsGvsSv59D.VphprCbTKR6kNphxYG', 'doctor',  '0772345678', 'Kandy'),
('Chaminda',  'Fernando',       'chaminda.fer@medicareplusslk.lk',       '$2y$12$jDalPLv0eFaJojh5cPT28u/o.WVg8nuu8dedVnM3p7LS4H8aHheLK', 'doctor',  '0773456789', 'Galle'),
('Priyanka',  'Jayawardena',    'priyanka.jaya@medicareplusslk.lk',      '$2y$12$Bggx3gv/PWG/Lb0AzZWq4u.lKhbgbMe3wo8h/eLVa3UNC7UkrNuBa', 'doctor',  '0774567890', 'Colombo'),
('Ruwan',     'Bandara',        'ruwan.bandara@medicareplusslk.lk',      '$2y$12$dNxbh9qA7UX8A5gp8AjzH./1wiP9umkMuYVCZkGami2WeuwIw.OeS', 'doctor',  '0775678901', 'Negombo'),
('Sanduni',   'Rathnayake',     'sanduni.rath@medicareplusslk.lk',       '$2y$12$1K1sYIvTvYmL1YU4Vliuh.rmZs5ttt5Y5yhIQ.qDJ9UWTiMuJGKta', 'doctor',  '0776789012', 'Matara'),
('Lasith',    'Gunawardena',    'lasith.guna@medicareplusslk.lk',        '$2y$12$UreIvXJzvQYCVRM2hslgAOgilWyRO7rGXdrvf6ribayH/B2lgHVAK', 'doctor',  '0777890123', 'Kurunegala'),
('Thilini',   'Karunarathna',   'thilini.karu@medicareplusslk.lk',       '$2y$12$tia0JaRDkxcceNRSAmXnTuaGsitNUIHudvV5XniUNXgzhEP7G3Dzi', 'doctor',  '0778901234', 'Colombo'),
('Admin',     'Medicare',       'admin@medicareplusslk.lk',              '$2y$12$PCwqcM8yzpO0TnlFjhLwrO/YH8blqe9vomhnGVvIIlTglI66NvJEC', 'admin',   '0112140000', 'Colombo'),
('Kasun',     'Silva',          'kasun.silva@gmail.com',                 '$2y$12$MOkTfKKuPVMfn/mfUdpNEu2Ha1p/5BBDNLS3TWQLF2Yppw4C0RxDO', 'patient', '0712345678', 'Colombo');

INSERT INTO doctors (user_id, specialization, qualification, hospital, location, consultation_fee, experience_years, rating, availability, bio) VALUES
(1, 'Cardiology',       'MBBS, MD (Cardiology), MRCP',    'National Hospital of Sri Lanka',    'Colombo 10', 3500.00, 14, 4.9, 'Mon–Fri 9am–1pm',    'Dr. Perera is a leading cardiologist with 14 years at National Hospital.'),
(2, 'Neurology',        'MBBS, MD (Neurology)',            'Kandy Teaching Hospital',           'Kandy',      3000.00, 10, 4.8, 'Tue–Sat 10am–2pm',   'Dr. Wickramasinghe specialises in neurological disorders.'),
(3, 'Orthopaedics',     'MBBS, MS (Ortho), FRCS',         'Karapitiya Teaching Hospital',      'Galle',      2800.00, 16, 4.7, 'Mon–Thu 8am–12pm',   'Dr. Fernando is a senior orthopaedic surgeon with expertise in joint replacement.'),
(4, 'Paediatrics',      'MBBS, DCH, MD (Paeds)',          'Lady Ridgeway Children Hospital',   'Colombo 8',  2500.00, 11, 4.9, 'Mon–Fri 2pm–5pm',    'Dr. Jayawardena is a compassionate paediatrician at Lady Ridgeway.'),
(5, 'General Practice', 'MBBS, DRCOG',                    'Negombo District General Hospital', 'Negombo',    1500.00,  8, 4.6, 'Daily 8am–4pm',      'Dr. Bandara provides comprehensive primary care services.'),
(6, 'Gynaecology',      'MBBS, MD (Obs & Gynae)',         'Matara Teaching Hospital',          'Matara',     2800.00, 12, 4.8, 'Mon/Wed/Fri 9am–1pm','Dr. Rathnayake is a respected gynaecologist serving the Southern Province.'),
(7, 'Dermatology',      'MBBS, MD (Dermatology)',         'Kurunegala Teaching Hospital',      'Kurunegala', 2200.00,  9, 4.7, 'Tue–Fri 3pm–6pm',    'Dr. Gunawardena treats a wide range of skin conditions.'),
(8, 'Endocrinology',    'MBBS, MD (Endocrinology), MRCP', 'Colombo South Teaching Hospital',  'Dehiwala',   3200.00, 13, 4.8, 'Mon–Thu 10am–1pm',   'Dr. Karunarathna specialises in diabetes and thyroid disorders.');

INSERT INTO patients (user_id) VALUES (10);

INSERT INTO blog_posts (author_id, title, excerpt, content, category, published) VALUES
(1, 'Understanding Heart Health: Warning Signs You Should Never Ignore',
   'Early detection of heart disease can save your life. Here''s what our cardiologists want you to know.',
   'Heart disease remains the leading cause of death in Sri Lanka. Early recognition of warning signs is critical. Chest pain, shortness of breath, and unusual fatigue are key indicators that require immediate medical attention.',
   'Cardiology', 1),
(4, 'Children''s Vaccinations: A Complete Schedule for Sri Lankan Parents',
   'Keeping up with your child''s vaccination calendar is one of the most important things you can do.',
   'Vaccinations protect children from serious diseases. The Sri Lankan National Immunisation Programme covers 13 vaccine-preventable diseases. Consult your paediatrician to ensure your child is up to date.',
   'Paediatrics', 1),
(8, 'Managing Diabetes Through Diet: A Practical Guide',
   'Dietary changes remain the single most effective tool in managing type 2 diabetes.',
   'A balanced diet low in refined carbohydrates and high in fibre can significantly improve blood sugar control. Our endocrinologist shares practical meal planning strategies suitable for Sri Lankan cuisine.',
   'Endocrinology', 1);
