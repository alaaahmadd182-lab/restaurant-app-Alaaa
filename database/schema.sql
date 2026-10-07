USE restaurant_app;

-- =============================================
-- USERS
-- =============================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,

    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- CATEGORIES
-- =============================================
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- RESTAURANTS
-- =============================================
CREATE TABLE restaurants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    category_id INT UNSIGNED NOT NULL,

    name VARCHAR(150) NOT NULL,
    description TEXT,
    address VARCHAR(255) NOT NULL,
    image VARCHAR(255),

    latitude DECIMAL(10, 7),
    longitude DECIMAL(10, 7),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_restaurant_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- =============================================
-- MENUS
-- =============================================
CREATE TABLE menus (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    restaurant_id INT UNSIGNED NOT NULL,

    name VARCHAR(150) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_menu_restaurant
        FOREIGN KEY (restaurant_id)
        REFERENCES restaurants(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- =============================================
-- MENU ITEMS
-- =============================================
CREATE TABLE menu_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    menu_id INT UNSIGNED NOT NULL,

    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_menu_item_menu
        FOREIGN KEY (menu_id)
        REFERENCES menus(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT chk_menu_item_price
        CHECK (price >= 0)
);

-- =============================================
-- INDEXES
-- =============================================

CREATE INDEX idx_restaurants_category
ON restaurants(category_id);

CREATE INDEX idx_restaurants_name
ON restaurants(name);

CREATE INDEX idx_menus_restaurant
ON menus(restaurant_id);

CREATE INDEX idx_menu_items_menu
ON menu_items(menu_id);



-- =============================================
-- INITIAL CATEGORIES
-- =============================================

INSERT INTO categories (name)
VALUES
    ('Lebanese'),
    ('Italian'),
    ('Japanese'),
    ('Fast Food'),
    ('Cafe'),
    ('Desserts');


CREATE TABLE user_otps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_otp_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE INDEX idx_user_otps_user
ON user_otps(user_id);


ALTER TABLE user_otps
ADD COLUMN attempts INT NOT NULL DEFAULT 0;