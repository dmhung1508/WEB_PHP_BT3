-- Tạo cơ sở dữ liệu
CREATE DATABASE IF NOT EXISTS ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce_db;

-- Bảng người dùng
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng danh mục
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng sản phẩm
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10, 0) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng đơn hàng
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_price DECIMAL(10, 0) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50) NOT NULL,
    shipping_address TEXT NOT NULL,
    shipping_phone VARCHAR(20) NOT NULL,
    shipping_name VARCHAR(100) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng chi tiết đơn hàng
CREATE TABLE IF NOT EXISTS order_items (
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 0) NOT NULL,
    PRIMARY KEY (order_id, product_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng đánh giá
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    user_id INT,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    status ENUM('approved', 'pending', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu: Tài khoản admin
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); -- password: password

-- Thêm dữ liệu mẫu: Danh mục
INSERT INTO categories (name, slug) VALUES 
('Điện thoại', 'dien-thoai'),
('Laptop', 'laptop'),
('Máy tính bảng', 'may-tinh-bang'),
('Phụ kiện', 'phu-kien'),
('Đồng hồ thông minh', 'dong-ho-thong-minh');

-- Thêm dữ liệu mẫu: Sản phẩm
INSERT INTO products (name, slug, description, price, stock, image, category_id) VALUES 
('iPhone 13 Pro Max', 'iphone-13-pro-max', 'iPhone 13 Pro Max với màn hình Super Retina XDR 6.7 inch và chip A15 Bionic mạnh mẽ.', 28990000, 50, 'iphone13promax.jpg', 1),
('Samsung Galaxy S21 Ultra', 'samsung-galaxy-s21-ultra', 'Samsung Galaxy S21 Ultra với camera 108MP và màn hình Dynamic AMOLED 2X.', 25990000, 30, 'samsungs21ultra.jpg', 1),
('MacBook Pro M1', 'macbook-pro-m1', 'MacBook Pro với chip M1, 8GB RAM và 256GB SSD.', 32990000, 20, 'macbookprom1.jpg', 2),
('Dell XPS 13', 'dell-xps-13', 'Dell XPS 13 với màn hình InfinityEdge và bộ vi xử lý Intel Core i7.', 29990000, 15, 'dellxps13.jpg', 2),
('iPad Pro 12.9', 'ipad-pro-12-9', 'iPad Pro 12.9 inch với chip M1 và màn hình Liquid Retina XDR.', 26990000, 25, 'ipadpro129.jpg', 3),
('Samsung Galaxy Tab S7+', 'samsung-galaxy-tab-s7-plus', 'Samsung Galaxy Tab S7+ với màn hình Super AMOLED 12.4 inch.', 19990000, 20, 'samsungtabs7plus.jpg', 3),
('Apple Watch Series 7', 'apple-watch-series-7', 'Apple Watch Series 7 với màn hình Retina luôn bật và khả năng chống nước.', 10990000, 40, 'applewatchseries7.jpg', 5),
('Samsung Galaxy Watch 4', 'samsung-galaxy-watch-4', 'Samsung Galaxy Watch 4 với hệ điều hành Wear OS và tính năng theo dõi sức khỏe.', 6990000, 35, 'samsungwatch4.jpg', 5),
('AirPods Pro', 'airpods-pro', 'AirPods Pro với khả năng chống ồn chủ động và chống nước.', 5990000, 60, 'airpodspro.jpg', 4),
('Samsung Galaxy Buds Pro', 'samsung-galaxy-buds-pro', 'Samsung Galaxy Buds Pro với âm thanh 360 độ và khả năng chống ồn.', 4990000, 45, 'samsungbudspro.jpg', 4);

-- Thêm dữ liệu mẫu: Người dùng
INSERT INTO users (name, email, password, phone, address, role) VALUES 
('Nguyễn Văn A', 'nguyenvana@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234567', 'Quận 1, TP. Hồ Chí Minh', 'customer'),
('Trần Thị B', 'tranthib@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'Quận Cầu Giấy, Hà Nội', 'customer'),
('Lê Văn C', 'levanc@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0923456789', 'Quận Hải Châu, Đà Nẵng', 'customer');

-- Thêm dữ liệu mẫu: Đơn hàng
INSERT INTO orders (user_id, total_price, status, payment_method, shipping_address, shipping_phone, shipping_name, notes, created_at) VALUES 
(2, 28  payment_method, shipping_address, shipping_phone, shipping_name, notes, created_at) VALUES 
(2, 28990000, 'delivered', 'COD', 'Quận Cầu Giấy, Hà Nội', '0912345678', 'Trần Thị B', 'Giao hàng giờ hành chính', '2023-06-15 10:30:00'),
(3, 32990000, 'shipped', 'Banking', 'Quận Hải Châu, Đà Nẵng', '0923456789', 'Lê Văn C', 'Gọi trước khi giao', '2023-06-20 14:45:00'),
(2, 10990000, 'pending', 'COD', 'Quận Cầu Giấy, Hà Nội', '0912345678', 'Trần Thị B', NULL, '2023-06-25 09:15:00');

-- Thêm dữ liệu mẫu: Chi tiết đơn hàng
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES 
(1, 1, 1, 28990000),
(2, 3, 1, 32990000),
(3, 7, 1, 10990000);

-- Thêm dữ liệu mẫu: Đánh giá
INSERT INTO reviews (product_id, user_id, rating, comment, status, created_at) VALUES 
(1, 2, 5, 'Sản phẩm rất tốt, đúng như mô tả.', 'approved', '2023-06-18 11:20:00'),
(3, 3, 4, 'Máy chạy rất mượt, pin trâu.', 'approved', '2023-06-22 16:30:00'),
(7, 2, 5, 'Đồng hồ đẹp, nhiều tính năng hữu ích.', 'pending', '2023-06-26 10:45:00');
