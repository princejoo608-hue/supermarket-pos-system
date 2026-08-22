-- =========================================================
-- Ajimi POS — نظام إدارة السوبر ماركت (سوبر ماركت البركة)
-- استوردها مباشرة عبر phpMyAdmin > Import (تُنشئ القاعدة تلقائياً)
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS supermarket_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE supermarket_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin','cashier') DEFAULT 'cashier',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    address VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    is_default TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category_id INT NULL,
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    cost_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    quantity INT NOT NULL DEFAULT 0,
    min_quantity INT NOT NULL DEFAULT 10,
    expiry_date DATE NOT NULL,
    barcode VARCHAR(50),
    description TEXT,
    image VARCHAR(255),
    is_favorite TINYINT(1) DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    supplier_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    global_invoice_no INT NOT NULL,
    daily_invoice_no INT NOT NULL,
    user_id INT NOT NULL,
    customer_id INT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    sale_date DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_at_sale DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sale_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    payment_method_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS refunds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    refund_date DATETIME NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS refund_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    refund_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_at_refund DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (refund_id) REFERENCES refunds(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- بيانات مبدئية جاهزة للتجربة فوراً
-- =========================================================

INSERT INTO users (username, password, full_name, role, active) VALUES
('admin', '$2b$12$l51biXKTq3A3m9YIec9nN.jQ/uX9lMGY/RJcUpnuLi80JMXj2cb7i', 'مدير السوبر ماركت', 'admin', 1),
('cashier1', '$2b$12$lvD/EkvcmDzJASljzrTufO/EvLd8nciAOAD3wRBApCZA6jYWU9cPm', 'أحمد الكاشير', 'cashier', 1);

INSERT INTO payment_methods (name) VALUES
('نقدي'), ('بنكك'), ('أوكاش'), ('فوري');

INSERT INTO customers (name, phone, is_default) VALUES
('عميل بيع عام', NULL, 1);

INSERT INTO categories (name) VALUES
('مواد غذائية'), ('ألبان ومشتقاتها'), ('مشروبات'), ('منظفات'),
('عناية شخصية'), ('خضروات وفواكه'), ('معلبات');

INSERT INTO suppliers (name, phone, address) VALUES
('شركة النيل الأزرق للمواد الغذائية', '0912345678', 'الخرطوم - شارع الجمهورية'),
('مجموعة الوطنية للتوزيع', '0923456789', 'أم درمان - السوق المحلي'),
('شركة البركة للمستلزمات المنزلية', '0934567890', 'بحري - الحرية');

INSERT INTO products (name, category_id, unit_price, cost_price, quantity, min_quantity, expiry_date, barcode, supplier_id, is_favorite) VALUES
('أرز أبيض 5 كجم', 1, 45.00, 32.00, 200, 30, '2027-06-30', '20001', 1, 1),
('سكر أبيض 1 كجم', 1, 12.00, 8.00, 300, 40, '2027-12-15', '20002', 1, 1),
('زيت طبخ 1.5 لتر', 1, 28.00, 20.00, 120, 20, '2027-03-10', '20003', 1, 0),
('لبن كامل الدسم 1 لتر', 2, 9.00, 6.00, 80, 25, '2026-09-15', '20004', 2, 1),
('جبنة بيضاء 400 جم', 2, 22.00, 15.00, 40, 15, '2026-09-20', '20005', 2, 0),
('عصير برتقال 1 لتر', 3, 15.00, 9.00, 100, 20, '2027-01-25', '20006', 2, 0),
('مياه معدنية 1.5 لتر', 3, 5.00, 3.00, 5, 30, '2027-06-01', '20007', 2, 0),
('صابون غسيل 1 كجم', 4, 18.00, 11.00, 60, 15, '2028-01-01', '20008', 3, 0),
('معجون طماطم 400 جم', 7, 8.00, 5.00, 90, 20, '2027-04-10', '20009', 3, 0),
('شامبو 400 مل', 5, 32.00, 20.00, 8, 15, '2027-11-01', '20010', 3, 0);

INSERT INTO settings (setting_key, setting_value) VALUES
('store_name', 'سوبر ماركت البركة'),
('phone', '0912000000'),
('address', 'الخرطوم - السودان'),
('currency', 'ج.س'),
('paper_size', '80mm'),
('footer_message', 'شكراً لزيارتكم - Ajimi POS'),
('print_logo', '1'),
('print_phone', '1'),
('print_address', '1');

SET FOREIGN_KEY_CHECKS = 1;
