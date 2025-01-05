-- Create database if not exists
CREATE DATABASE IF NOT EXISTS htzone_php_test_main_v1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE htzone_php_test_main_v1;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    category_id INT PRIMARY KEY,
    parent_id INT,
    title VARCHAR(255) NOT NULL,
    level INT NOT NULL,
    type_id INT,
    INDEX (parent_id),
    INDEX (type_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Items table with JSON support
CREATE TABLE IF NOT EXISTS items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_api_id VARCHAR(50) NOT NULL UNIQUE,
    category_id INT NOT NULL,
    active TINYINT(1) DEFAULT 1,
    title VARCHAR(255) NOT NULL,
    sub_title VARCHAR(255),
    brand_title VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    price_before_discount DECIMAL(10,2),
    brief TEXT,
    description_json JSON,
    INDEX (category_id),
    INDEX (item_api_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Item images table
CREATE TABLE IF NOT EXISTS item_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    img_url VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    INDEX (item_id),
    INDEX (sort_order)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Add foreign keys
ALTER TABLE items
ADD CONSTRAINT fk_items_category
FOREIGN KEY (category_id) REFERENCES categories(category_id)
ON DELETE CASCADE;

ALTER TABLE item_images
ADD CONSTRAINT fk_images_item
FOREIGN KEY (item_id) REFERENCES items(item_id)
ON DELETE CASCADE;


