-- Kalitusi Nature Resort E-shop
-- Run this against the application's configured MySQL database.

CREATE TABLE IF NOT EXISTS shop_categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_shop_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shop_settings (
    setting_key VARCHAR(80) NOT NULL,
    setting_value VARCHAR(500) NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shop_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    description TEXT NULL,
    price DECIMAL(12,2) NOT NULL,
    image_url VARCHAR(500) NULL,
    stock_status ENUM('in_stock', 'out_of_stock') NOT NULL DEFAULT 'in_stock',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_shop_items_category_name (category_id, name),
    KEY idx_shop_items_category (category_id),
    KEY idx_shop_items_stock (stock_status),
    CONSTRAINT fk_shop_items_category
        FOREIGN KEY (category_id) REFERENCES shop_categories (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_shop_items_price CHECK (price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_name VARCHAR(160) NOT NULL,
    customer_email VARCHAR(190) NULL,
    customer_phone VARCHAR(40) NOT NULL,
    payment_method ENUM('onsite', 'mobile_money') NOT NULL,
    payment_provider ENUM('MTN', 'Airtel') NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_orders_created_at (created_at),
    KEY idx_orders_status (status),
    CONSTRAINT chk_orders_total CHECK (total_amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    shop_item_id INT UNSIGNED NOT NULL,
    item_name VARCHAR(180) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_order_items_order (order_id),
    KEY idx_order_items_shop_item (shop_item_id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_items_shop_item
        FOREIGN KEY (shop_item_id) REFERENCES shop_items (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_order_items_quantity CHECK (quantity > 0),
    CONSTRAINT chk_order_items_subtotal CHECK (subtotal >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO shop_categories (name) VALUES
    ('Pastries'), ('Crafts'), ('Potted Plants'), ('Groceries')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO shop_settings (setting_key, setting_value) VALUES
    ('shop_eyebrow', 'Kalitusi Nature Resort'),
    ('shop_title', 'E-shop & Gift Shop'),
    ('shop_tagline', 'A little piece of Kalitusi to enjoy here or take home.')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO shop_items (category_id, name, description, price, image_url, stock_status)
SELECT c.id, demo.name, demo.description, demo.price, demo.image_url, 'in_stock'
FROM (
    SELECT 'Pastries' AS category_name, 'Fresh Banana Bread' AS name, 'House-baked banana bread, made fresh for your stay.' AS description, 18000.00 AS price, 'assets/images/food1.webp' AS image_url
    UNION ALL SELECT 'Pastries', 'Pineapple Cake Slice', 'A soft, tropical cake slice from the resort kitchen.', 12000.00, 'assets/images/food4.webp'
    UNION ALL SELECT 'Crafts', 'Handwoven Coaster Set', 'A set of locally made woven coasters to take home.', 25000.00, 'assets/images/unnamed (9).webp'
    UNION ALL SELECT 'Crafts', 'Beaded Bracelet', 'A colorful handmade bracelet from a local craft maker.', 18000.00, 'assets/images/unnamed (11).webp'
    UNION ALL SELECT 'Potted Plants', 'African Violet', 'A flowering potted plant, ready for a sunny windowsill.', 25000.00, 'assets/images/2021-10-31.webp'
    UNION ALL SELECT 'Potted Plants', 'Garden Succulent', 'An easy-care little plant in a simple nursery pot.', 20000.00, 'assets/images/unnamed (14).webp'
    UNION ALL SELECT 'Groceries', 'Rwenzori Forest Honey', 'Locally sourced honey in a take-home jar.', 28000.00, 'assets/images/food2.webp'
    UNION ALL SELECT 'Groceries', 'Ugandan Arabica Coffee', 'Freshly roasted Arabica coffee from western Uganda.', 35000.00, 'assets/images/food3.webp'
) AS demo
INNER JOIN shop_categories c ON c.name = demo.category_name
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    price = VALUES(price),
    image_url = VALUES(image_url),
    stock_status = VALUES(stock_status);