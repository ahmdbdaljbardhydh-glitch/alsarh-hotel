-- =========================================================
-- قاعدة بيانات موقع فندق "الصرح الذهبي"
-- =========================================================
CREATE DATABASE IF NOT EXISTS alsarh_hotel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE alsarh_hotel;

-- ---------------------------------------------------------
-- جدول المستخدمين
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    gender ENUM('ذكر','انثى') DEFAULT NULL,
    image VARCHAR(255) DEFAULT 'default.png',
    birthdate DATE DEFAULT NULL,
    user_type ENUM('admin','user') NOT NULL DEFAULT 'user',
    status ENUM('pending','active','blocked') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول أنواع الغرف
-- ---------------------------------------------------------
CREATE TABLE room_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول الغرف
-- ---------------------------------------------------------
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_type_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    capacity INT NOT NULL DEFAULT 2,
    image VARCHAR(255) DEFAULT 'room-default.jpg',
    status ENUM('available','unavailable') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول الحجوزات
-- ---------------------------------------------------------
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول الخدمات الإضافية للفندق
-- ---------------------------------------------------------
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    icon VARCHAR(100) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول معرض الصور
-- ---------------------------------------------------------
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image VARCHAR(255) NOT NULL,
    caption VARCHAR(200) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول آراء الزوار (Testimonials)
-- ---------------------------------------------------------
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    comment TEXT,
    status ENUM('pending','approved') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- جدول رسائل التواصل
-- ---------------------------------------------------------
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- بيانات أولية
-- ---------------------------------------------------------

-- حساب أدمن افتراضي (كلمة المرور: Admin@123 ) مشفرة بـ password_hash
INSERT INTO users (full_name, email, password, user_type, status, image)
VALUES ('مدير الموقع', 'admin@alsarh.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 'default.png');
-- ملاحظة: هذا الهاش يعادل كلمة المرور "password" -- يجب تغييره بعد أول تسجيل دخول، راجع ملف README

INSERT INTO room_types (name, description) VALUES
('غرفة ديلوكس', 'غرفة فاخرة بإطلالة رائعة وتجهيزات عصرية'),
('جناح ملكي', 'جناح واسع بتصميم ملكي وخدمة خاصة'),
('غرفة عائلية', 'مساحة واسعة تناسب العائلات');

INSERT INTO rooms (room_type_id, title, description, price, capacity, image) VALUES
(1, 'غرفة ديلوكس بإطلالة على البحر', 'غرفة أنيقة مع شرفة خاصة وإطلالة بانورامية', 120.00, 2, 'room-default.jpg'),
(2, 'الجناح الملكي', 'جناح فاخر مع صالة استقبال خاصة وجاكوزي', 350.00, 4, 'room-default.jpg'),
(3, 'غرفة عائلية كبيرة', 'غرفتان متصلتان تناسب العائلات الكبيرة', 200.00, 6, 'room-default.jpg');

INSERT INTO services (title, description, icon) VALUES
('مسبح خارجي', 'مسبح فاخر مفتوح على مدار اليوم', 'fa-water-ladder'),
('سبا ومنتجع صحي', 'خدمات استرخاء وتدليك احترافية', 'fa-spa'),
('مطاعم عالمية', 'تشكيلة واسعة من المأكولات العالمية', 'fa-utensils'),
('صالة رياضية', 'أحدث الأجهزة الرياضية على مدار الساعة', 'fa-dumbbell');

-- ---------------------------------------------------------
-- جدول خلفيات رأس الصفحات (Hero Banners)
-- يخزن صورة خلفية لكل صفحة رئيسية بالموقع، يتحكم بها الأدمن
-- من لوحة التحكم (الصورة تظهر خلف عنوان الصفحة كخلفية متحركة الشعور بصريًا)
-- ---------------------------------------------------------
CREATE TABLE page_banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_key VARCHAR(50) NOT NULL UNIQUE, -- معرف الصفحة: home, rooms, services, gallery, about, contact
    page_label VARCHAR(100) NOT NULL,     -- اسم الصفحة بالعربية لعرضه في لوحة التحكم
    image VARCHAR(255) DEFAULT NULL,      -- اسم ملف الصورة داخل assets/img/banners
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO page_banners (page_key, page_label, image) VALUES
('home', 'الصفحة الرئيسية', NULL),
('rooms', 'الغرف والأجنحة', NULL),
('services', 'الخدمات', NULL),
('gallery', 'معرض الصور', NULL),
('about', 'من نحن', NULL),
('contact', 'تواصل معنا', NULL);
