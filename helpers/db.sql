-- 1. Create DB
CREATE DATABASE IF NOT EXISTS `htzone_php_test-main_v1`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- 2. Use DB
USE `htzone_php_test-main_v1`;

-- =========================
-- 3. Table categories
-- =========================
CREATE TABLE IF NOT EXISTS `categories` (
                                            `category_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                            `parent_id` INT UNSIGNED DEFAULT NULL,
                                            `title` VARCHAR(255) NOT NULL,
    `level` TINYINT UNSIGNED DEFAULT 1,
    `type_id` INT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP NULL,
    FOREIGN KEY (`parent_id`) REFERENCES `categories`(`category_id`)
                          ON DELETE SET NULL
                          ON UPDATE CASCADE
    );

-- =========================
-- 4. Table items
-- =========================
CREATE TABLE IF NOT EXISTS `items` (
                                       `item_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                       `item_api_id` VARCHAR(50) NOT NULL COMMENT 'ID from API',

    `category_id` INT UNSIGNED NOT NULL COMMENT 'Link to categories',
    `active` TINYINT(1) DEFAULT 1 COMMENT '1 = on, 0 = off',
    `title` VARCHAR(255) NOT NULL,
    `sub_title` VARCHAR(255) DEFAULT NULL,

    `brand_id` INT DEFAULT NULL,
    `brand_title` VARCHAR(255) DEFAULT NULL,

    `expiration_date` INT DEFAULT NULL COMMENT 'unix_timestamp/int',
    `supplier_id` INT DEFAULT NULL,
    `supplier_title` VARCHAR(255) DEFAULT NULL,

    `price` DECIMAL(10,2) DEFAULT 0,
    `price_before_discount` DECIMAL(10,2) DEFAULT 0,
    `price_supplier` DECIMAL(10,2) DEFAULT 0,

    `brief` TEXT,
    `description_json` JSON COMMENT 'Complex descriptions, delivery_info, etc.',

    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP NULL,

    FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`)
                          ON DELETE CASCADE
                          ON UPDATE CASCADE
    );

-- =========================
-- 5. Table item_images
-- =========================
CREATE TABLE IF NOT EXISTS `item_images` (
                                             `image_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                             `item_id` INT UNSIGNED NOT NULL,
                                             `img_url` VARCHAR(500) NOT NULL,
    `sort_order` INT UNSIGNED DEFAULT 0,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
    );

-- =========================
-- 6. Table item_deliveries
-- =========================
CREATE TABLE IF NOT EXISTS `item_deliveries` (
                                                 `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                                 `item_id` INT UNSIGNED NOT NULL,
                                                 `delivery_id` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `sum` DECIMAL(10,2) DEFAULT 0,
    `supplier_cost` DECIMAL(10,2) DEFAULT 0,
    `is_home_delivery` TINYINT(1) DEFAULT 0,
    `is_self_pickup` TINYINT(1) DEFAULT 0,
    `is_quick` TINYINT(1) DEFAULT 0,
    `is_special` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
    );

-- =========================
-- 7. Table item_branches
-- =========================
CREATE TABLE IF NOT EXISTS `item_branches` (
                                               `branch_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                               `item_id` INT UNSIGNED NOT NULL,
                                               `title` VARCHAR(255) NOT NULL,
    `address` VARCHAR(255) DEFAULT NULL,
    `city_id` INT DEFAULT NULL,
    `gps_point` VARCHAR(100) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
    );

-- =========================
-- 8. Table item_features
-- =========================
CREATE TABLE IF NOT EXISTS `item_features` (
                                               `feature_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                               `item_id` INT UNSIGNED NOT NULL,
                                               `feature_key` VARCHAR(100) NOT NULL,
    `feature_value` VARCHAR(500) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
    );


