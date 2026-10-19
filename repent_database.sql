-- ============================================================
-- REPENT† — Database (schema + sample data)
-- File: repent_database.sql
--
-- Works on MySQL 5.7 / 8.x and MariaDB 10.3+ (XAMPP, WAMP, MAMP).
-- Re-running this file rebuilds the database from scratch.
--
-- HOW TO USE
--   phpMyAdmin : Import tab -> choose this file -> Go
--   command    : mysql -u root -p < repent_database.sql
--
-- TABLES (all have 10+ rows of sample data)
--   users                   registered customers and the admin
--   products                clothing catalogue
--   orders                  one row per purchase          -> users
--   order_items             the lines inside each order   -> orders, products
--   prayer_requests         prayer requests from the site/app
--   newsletter_subscribers  email subscribers
--   devotionals             devotional articles
--
-- SAMPLE LOGINS
--   admin@repent.co.za  / admin123      (administrator)
--   user@repent.co.za   / user123       (customer)
--   every other seeded customer         / Demo@1234
-- Passwords are stored as bcrypt hashes, never as plain text.
-- ============================================================

CREATE DATABASE IF NOT EXISTS repent_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE repent_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS prayer_requests;
DROP TABLE IF EXISTS devotionals;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- users: customers and admins. Google accounts are linked by google_sub.
-- ------------------------------------------------------------
CREATE TABLE users (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    fullname           VARCHAR(100) NOT NULL,
    email              VARCHAR(150) NOT NULL UNIQUE,
    password           VARCHAR(255) NOT NULL,
    role               ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    auth_provider      ENUM('local','google') NOT NULL DEFAULT 'local',
    google_sub         VARCHAR(64) NULL UNIQUE,
    phone              VARCHAR(30) NULL,
    address            VARCHAR(255) NULL,
    city               VARCHAR(100) NULL,
    postal_code        VARCHAR(20) NULL,
    preferred_category ENUM('all','tshirts','hoodies','caps') NOT NULL DEFAULT 'all',
    token_version      INT NOT NULL DEFAULT 0,
    created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- products: the shop catalogue (prices in South African Rand)
-- ------------------------------------------------------------
CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    description TEXT NULL,
    price       DECIMAL(10,2) NOT NULL CHECK (price >= 0),
    category    ENUM('tshirts','hoodies','caps') NOT NULL,
    image       VARCHAR(255) NULL,
    stock       INT NOT NULL DEFAULT 0 CHECK (stock >= 0),
    badge       VARCHAR(50) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- orders: one row per purchase. total = items + shipping.
-- ------------------------------------------------------------
CREATE TABLE orders (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    total            DECIMAL(10,2) NOT NULL CHECK (total >= 0),
    shipping         DECIMAL(10,2) NOT NULL DEFAULT 0 CHECK (shipping >= 0),
    status           ENUM('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    shipping_address VARCHAR(255) NOT NULL,
    city             VARCHAR(100) NOT NULL,
    postal_code      VARCHAR(20) NOT NULL,
    phone            VARCHAR(30) NOT NULL,
    payment_method   ENUM('eft','card') NOT NULL DEFAULT 'eft',
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- order_items: the products inside an order. The name and price are
-- copied at purchase time so old orders stay correct if a product changes.
-- ------------------------------------------------------------
CREATE TABLE order_items (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    order_id     INT NOT NULL,
    product_id   INT NULL,
    product_name VARCHAR(150) NOT NULL,
    price        DECIMAL(10,2) NOT NULL CHECK (price >= 0),
    quantity     INT NOT NULL DEFAULT 1 CHECK (quantity > 0),
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- prayer_requests
-- ------------------------------------------------------------
CREATE TABLE prayer_requests (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NULL,
    subject      VARCHAR(100) NULL,
    prayer       TEXT NOT NULL,
    is_private   TINYINT(1) NOT NULL DEFAULT 0,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- newsletter_subscribers
-- ------------------------------------------------------------
CREATE TABLE newsletter_subscribers (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    frequency     ENUM('daily','weekly','monthly') NOT NULL DEFAULT 'weekly',
    subscribed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- devotionals
-- ------------------------------------------------------------
CREATE TABLE devotionals (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(200) NOT NULL,
    verse      TEXT NOT NULL,
    reference  VARCHAR(100) NULL,
    content    TEXT NOT NULL,
    topic      VARCHAR(100) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_orders_user     ON orders(user_id);
CREATE INDEX idx_orders_status   ON orders(status);
CREATE INDEX idx_items_order     ON order_items(order_id);
CREATE INDEX idx_products_cat    ON products(category);

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- users (12)
INSERT INTO users (id, fullname, email, password, role, auth_provider, google_sub, phone, address, city, postal_code, preferred_category, token_version, created_at) VALUES
  (1, 'Admin User', 'admin@repent.co.za', '$2y$10$hCdWqyHOVz457oKueucFFuI5TYxmXtoksDoJD5hH0yVTEyIlzxMxu', 'admin', 'local', NULL, '012 555 0100', '1 Church Street', 'Pretoria', '0002', 'all', 0, '2026-07-01 08:00:00'),
  (2, 'Demo User', 'user@repent.co.za', '$2y$10$NlhJ9RWmpWyLzil0GL0vcOr6KWCe0Llm/46kilm5MlHn33XiV9Kae', 'customer', 'local', NULL, '082 555 0102', '24 Jorissen Street', 'Johannesburg', '2017', 'tshirts', 0, '2026-07-02 09:30:00'),
  (3, 'Thandeka Mokoena', 'thandeka.mokoena@example.com', '$2y$10$pPL7EkIZ/6u1qggtGzKkregaf4WIOh8krWkqNHbkdsVxVqI.8WwCG', 'customer', 'local', NULL, '071 555 0103', '8 Rissik Street', 'Pretoria', '0001', 'hoodies', 0, '2026-07-05 11:15:00'),
  (4, 'Sipho Khumalo', 'sipho.khumalo@example.com', '$2y$10$pPL7EkIZ/6u1qggtGzKkregaf4WIOh8krWkqNHbkdsVxVqI.8WwCG', 'customer', 'local', NULL, '083 555 0104', '15 Florida Road', 'Durban', '4001', 'caps', 0, '2026-07-09 14:02:00'),
  (5, 'Grace Nkosi', 'grace.nkosi@example.com', '$2y$10$pPL7EkIZ/6u1qggtGzKkregaf4WIOh8krWkqNHbkdsVxVqI.8WwCG', 'customer', 'local', NULL, '072 555 0105', '3 Long Street', 'Cape Town', '8001', 'tshirts', 0, '2026-07-14 10:45:00'),
  (6, 'Thabo Molefe', 'thabo.molefe@example.com', '$2y$10$pPL7EkIZ/6u1qggtGzKkregaf4WIOh8krWkqNHbkdsVxVqI.8WwCG', 'customer', 'local', NULL, '084 555 0106', '77 Nelson Mandela Dr', 'Bloemfontein', '9301', 'all', 0, '2026-07-18 16:20:00'),
  (7, 'Lindiwe Dube', 'lindiwe.dube@example.com', '$2y$10$pPL7EkIZ/6u1qggtGzKkregaf4WIOh8krWkqNHbkdsVxVqI.8WwCG', 'customer', 'local', NULL, '073 555 0107', '21 Church Street', 'Polokwane', '0700', 'hoodies', 0, '2026-07-23 09:10:00'),
  (8, 'Kagiso Sithole', 'kagiso.sithole@example.com', '$2y$10$pPL7EkIZ/6u1qggtGzKkregaf4WIOh8krWkqNHbkdsVxVqI.8WwCG', 'customer', 'local', NULL, '081 555 0108', '9 Kerk Street', 'Nelspruit', '1200', 'caps', 0, '2026-07-27 13:35:00'),
  (9, 'Naledi Mahlangu', 'naledi.mahlangu@example.com', '$2y$10$pPL7EkIZ/6u1qggtGzKkregaf4WIOh8krWkqNHbkdsVxVqI.8WwCG', 'customer', 'local', NULL, '076 555 0109', '46 Main Road', 'Gqeberha', '6001', 'tshirts', 0, '2026-08-01 17:50:00'),
  (10, 'Mpho Radebe', 'mpho.radebe@example.com', '$2y$10$pPL7EkIZ/6u1qggtGzKkregaf4WIOh8krWkqNHbkdsVxVqI.8WwCG', 'customer', 'local', NULL, '082 555 0110', '5 Vilakazi Street', 'Johannesburg', '1804', 'all', 0, '2026-08-06 12:05:00'),
  (11, 'Ayanda Zulu', 'ayanda.zulu@example.com', '$2y$10$pPL7EkIZ/6u1qggtGzKkregaf4WIOh8krWkqNHbkdsVxVqI.8WwCG', 'customer', 'local', NULL, '079 555 0111', '33 Smith Street', 'Durban', '4001', 'hoodies', 0, '2026-08-11 08:40:00'),
  (12, 'Zanele Pillay', 'zanele.pillay@example.com', '$2y$10$pPL7EkIZ/6u1qggtGzKkregaf4WIOh8krWkqNHbkdsVxVqI.8WwCG', 'customer', 'local', NULL, '060 555 0112', '12 Bree Street', 'Cape Town', '8001', 'caps', 0, '2026-08-16 15:25:00');

-- products (12). Stock is the starting stock minus what the sample orders below have sold.
INSERT INTO products (id, name, description, price, category, image, stock, badge, created_at) VALUES
  (1, 'Disciples Tee', 'Go and Make Disciples of All Nations — Matthew 28:19.', '350.00', 'tshirts', 'tshirt-white.jpg', 17, 'New', '2026-07-01 08:30:00'),
  (2, 'Be Still Tee (Pink)', 'Be Still and Know That I Am God — Psalm 46:10.', '320.00', 'tshirts', 'products-collection.jpg', 12, 'Popular', '2026-07-01 08:30:00'),
  (3, 'Be Still Tee (Blue)', 'Be Still and Know That I Am God — Psalm 46:10.', '320.00', 'tshirts', 'products-flat.jpg', 10, NULL, '2026-07-01 08:30:00'),
  (4, 'REPENT† Classic Tee', 'REPENT† classic bold statement tee.', '320.00', 'tshirts', 'people-wearing.jpg', 16, NULL, '2026-07-01 08:30:00'),
  (5, 'REPENT† Hoodie (Red)', 'Heavyweight hoodie with globe print on back.', '550.00', 'hoodies', 'hoodies-collection.jpg', 8, 'Bestseller', '2026-07-01 08:30:00'),
  (6, 'REPENT† Hoodie (Pink)', 'Soft pink hoodie with REPENT† logo on chest.', '550.00', 'hoodies', 'hoodies-collection.jpg', 7, NULL, '2026-07-01 08:30:00'),
  (7, 'REPENT† Crewneck', 'Red crewneck sweatshirt in multiple colours.', '480.00', 'hoodies', 'hoodies-collection.jpg', 13, NULL, '2026-07-01 08:30:00'),
  (8, 'Cap — Coral', 'Washed coral dad cap with embroidered REPENT† logo.', '280.00', 'caps', 'cap-coral.jpg', 23, NULL, '2026-07-01 08:30:00'),
  (9, 'Cap — Olive', 'Washed olive/khaki dad cap.', '280.00', 'caps', 'cap-olive.jpg', 20, NULL, '2026-07-01 08:30:00'),
  (10, 'Cap — Yellow', 'Vintage yellow washed dad cap.', '280.00', 'caps', 'cap-yellow.jpg', 18, NULL, '2026-07-01 08:30:00'),
  (11, 'Cap — Mint', 'Fresh mint/teal washed dad cap.', '280.00', 'caps', 'cap-mint.jpg', 17, 'New', '2026-07-01 08:30:00'),
  (12, 'Cap — Khaki', 'Stone washed khaki dad cap.', '280.00', 'caps', 'cap-khaki.jpg', 23, NULL, '2026-07-01 08:30:00');

-- orders (12). total = sum of the order's items + shipping (free over R800, otherwise R95).
INSERT INTO orders (id, user_id, total, shipping, status, shipping_address, city, postal_code, phone, payment_method, created_at) VALUES
  (1, 2, '725.00', '95.00', 'delivered', '24 Jorissen Street', 'Johannesburg', '2017', '082 555 0102', 'eft', '2026-08-03 10:12:00'),
  (2, 3, '645.00', '95.00', 'delivered', '8 Rissik Street', 'Pretoria', '0001', '071 555 0103', 'card', '2026-08-07 14:40:00'),
  (3, 4, '920.00', '0.00', 'delivered', '15 Florida Road', 'Durban', '4001', '083 555 0104', 'eft', '2026-08-12 09:05:00'),
  (4, 5, '855.00', '95.00', 'delivered', '3 Long Street', 'Cape Town', '8001', '072 555 0105', 'card', '2026-08-19 18:22:00'),
  (5, 6, '415.00', '95.00', 'delivered', '77 Nelson Mandela Dr', 'Bloemfontein', '9301', '084 555 0106', 'eft', '2026-08-27 11:30:00'),
  (6, 7, '870.00', '0.00', 'shipped', '21 Church Street', 'Polokwane', '0700', '073 555 0107', 'card', '2026-09-02 16:48:00'),
  (7, 8, '655.00', '95.00', 'shipped', '9 Kerk Street', 'Nelspruit', '1200', '081 555 0108', 'eft', '2026-09-09 08:15:00'),
  (8, 9, '980.00', '0.00', 'processing', '46 Main Road', 'Gqeberha', '6001', '076 555 0109', 'card', '2026-09-15 13:55:00'),
  (9, 10, '830.00', '0.00', 'processing', '5 Vilakazi Street', 'Johannesburg', '1804', '082 555 0110', 'eft', '2026-09-21 20:10:00'),
  (10, 11, '415.00', '95.00', 'pending', '33 Smith Street', 'Durban', '4001', '079 555 0111', 'eft', '2026-09-28 12:00:00'),
  (11, 12, '655.00', '95.00', 'cancelled', '12 Bree Street', 'Cape Town', '8001', '060 555 0112', 'card', '2026-10-05 09:35:00'),
  (12, 3, '920.00', '0.00', 'processing', '8 Rissik Street', 'Pretoria', '0001', '071 555 0103', 'eft', '2026-10-12 15:20:00');

-- order_items (20)
INSERT INTO order_items (id, order_id, product_id, product_name, price, quantity) VALUES
  (1, 1, 1, 'Disciples Tee', '350.00', 1),
  (2, 1, 8, 'Cap — Coral', '280.00', 1),
  (3, 2, 5, 'REPENT† Hoodie (Red)', '550.00', 1),
  (4, 3, 2, 'Be Still Tee (Pink)', '320.00', 2),
  (5, 3, 11, 'Cap — Mint', '280.00', 1),
  (6, 4, 7, 'REPENT† Crewneck', '480.00', 1),
  (7, 4, 9, 'Cap — Olive', '280.00', 1),
  (8, 5, 4, 'REPENT† Classic Tee', '320.00', 1),
  (9, 6, 6, 'REPENT† Hoodie (Pink)', '550.00', 1),
  (10, 6, 3, 'Be Still Tee (Blue)', '320.00', 1),
  (11, 7, 10, 'Cap — Yellow', '280.00', 2),
  (12, 8, 1, 'Disciples Tee', '350.00', 2),
  (13, 8, 12, 'Cap — Khaki', '280.00', 1),
  (14, 9, 5, 'REPENT† Hoodie (Red)', '550.00', 1),
  (15, 9, 8, 'Cap — Coral', '280.00', 1),
  (16, 10, 3, 'Be Still Tee (Blue)', '320.00', 1),
  (17, 11, 11, 'Cap — Mint', '280.00', 2),
  (18, 12, 2, 'Be Still Tee (Pink)', '320.00', 1),
  (19, 12, 4, 'REPENT† Classic Tee', '320.00', 1),
  (20, 12, 9, 'Cap — Olive', '280.00', 1);

-- prayer_requests (12)
INSERT INTO prayer_requests (id, name, email, subject, prayer, is_private, submitted_at) VALUES
  (1, 'Thandeka', 'thandeka.mokoena@example.com', 'Health & Healing', 'Please pray for my grandmother who is recovering in hospital. We are trusting God for her strength.', 0, '2026-08-04 09:20:00'),
  (2, 'Anonymous', NULL, 'Family & Relationships', 'Pray for peace and understanding in our home as we navigate a difficult season together.', 1, '2026-08-09 21:05:00'),
  (3, 'Sipho', 'sipho.khumalo@example.com', 'Financial Breakthrough', 'I have been looking for work for three months. Please pray that God opens the right door.', 0, '2026-08-15 07:45:00'),
  (4, 'Grace', NULL, 'Salvation of a loved one', 'My younger brother is far from God. Pray that his heart would soften and he would come home.', 0, '2026-08-22 18:30:00'),
  (5, 'Lindiwe', 'lindiwe.dube@example.com', 'Spiritual Growth', 'I want to build a consistent habit of prayer and Bible reading. Please pray for discipline.', 0, '2026-08-29 06:50:00'),
  (6, 'Kagiso', NULL, 'Direction & Purpose', 'I am about to choose a university course and need clear direction from God.', 0, '2026-09-03 16:10:00'),
  (7, 'Anonymous', NULL, 'Depression & Anxiety', 'Please pray for calm and rest. Some days feel very heavy and I need hope.', 1, '2026-09-08 22:40:00'),
  (8, 'Naledi', 'naledi.mahlangu@example.com', 'Praise & Thanksgiving', 'Praising God for a safe delivery of our baby girl. Thank you to everyone who prayed.', 0, '2026-09-13 11:25:00'),
  (9, 'Mpho', NULL, 'Other', 'Pray for our youth group retreat next month, for safety, unity and hearts open to God.', 0, '2026-09-19 14:00:00'),
  (10, 'Ayanda', 'ayanda.zulu@example.com', 'Health & Healing', 'Please keep my father in your prayers as he starts treatment next week.', 1, '2026-09-26 08:15:00'),
  (11, 'Zanele', NULL, 'Family & Relationships', 'Pray for our family business and for harmony between my siblings.', 0, '2026-10-02 19:35:00'),
  (12, 'Thabo', 'thabo.molefe@example.com', 'Financial Breakthrough', 'Pray for wisdom as we plan our finances and for provision for the months ahead.', 0, '2026-10-09 10:55:00');

-- newsletter_subscribers (12)
INSERT INTO newsletter_subscribers (id, name, email, frequency, subscribed_at) VALUES
  (1, 'Thandeka Mokoena', 'thandeka.mokoena@example.com', 'weekly', '2026-07-06 10:00:00'),
  (2, 'Sipho Khumalo', 'sipho.khumalo@example.com', 'daily', '2026-07-10 08:30:00'),
  (3, 'Grace Nkosi', 'grace.nkosi@example.com', 'weekly', '2026-07-15 12:10:00'),
  (4, 'Thabo Molefe', 'thabo.molefe@example.com', 'monthly', '2026-07-19 17:45:00'),
  (5, 'Lindiwe Dube', 'lindiwe.dube@example.com', 'weekly', '2026-07-24 09:55:00'),
  (6, 'Kagiso Sithole', 'kagiso.sithole@example.com', 'daily', '2026-07-28 14:20:00'),
  (7, 'Naledi Mahlangu', 'naledi.mahlangu@example.com', 'weekly', '2026-08-02 11:05:00'),
  (8, 'Mpho Radebe', 'mpho.radebe@example.com', 'monthly', '2026-08-07 19:30:00'),
  (9, 'Ayanda Zulu', 'ayanda.zulu@example.com', 'weekly', '2026-08-12 07:40:00'),
  (10, 'Zanele Pillay', 'zanele.pillay@example.com', 'daily', '2026-08-17 16:15:00'),
  (11, 'Pastor John Baloyi', 'john.baloyi@example.com', 'weekly', '2026-08-24 10:20:00'),
  (12, 'Faith Maseko', 'faith.maseko@example.com', 'monthly', '2026-09-01 13:00:00');

-- devotionals (12)
INSERT INTO devotionals (id, title, verse, reference, content, topic, created_at) VALUES
  (1, 'Strength in Surrender', 'I can do all things through Christ who strengthens me.', 'Philippians 4:13', 'True strength is not found in our own abilities, but in yielding ourselves entirely to God. When we acknowledge our weakness, His power is made perfect in us. Paul wrote this verse from a prison cell, not a stage. Whatever you are facing today, Christ''s strength is available to you.', 'Strength', '2026-07-01 06:00:00'),
  (2, 'The Call to Repentance', 'Repent, for the kingdom of heaven has come near.', 'Matthew 4:17', 'Repentance is not simply feeling sorry for sin; it is a decisive turn. A 180-degree change in direction. Jesus begins His ministry not with a self-help programme but with a call to repent. This is the foundation of the Christian life: acknowledging that our way is broken and choosing God''s way instead.', 'Repentance', '2026-07-02 06:00:00'),
  (3, 'Finding Peace in the Storm', 'Be still, and know that I am God.', 'Psalm 46:10', 'In a world that rewards busyness and productivity, God invites us into stillness. Not passivity, but a confident rest in the knowledge that He holds everything in His hands. When life feels overwhelming, we are not called to figure it all out. We are called to be still and trust.', 'Peace', '2026-07-03 06:00:00'),
  (4, 'You Are Made New', 'Therefore, if anyone is in Christ, he is a new creation.', '2 Corinthians 5:17', 'The gospel is not a self-improvement plan; it is a new birth. When we come to Christ, the old is gone. We do not carry our old identity into the new life. God declares us new creations. Not improved versions of ourselves, but entirely new people, reborn in His image.', 'Identity', '2026-07-04 06:00:00'),
  (5, 'Living on Mission', 'Go and make disciples of all nations.', 'Matthew 28:19', 'The Great Commission is not just for missionaries and pastors. It is the calling of every believer. You are a missionary in your school, workplace, family and community. God sends us not just to places on a map, but to the people in our everyday lives who need to hear about His love.', 'Mission', '2026-07-05 06:00:00'),
  (6, 'Walking by Faith', 'Now faith is the substance of things hoped for, the evidence of things not seen.', 'Hebrews 11:1', 'Faith is not wishful thinking. It is a confident trust in a God who has proven Himself faithful throughout history and throughout your own life. Every step of obedience, even when the outcome is unclear, is an act of faith that pleases God and honours His name.', 'Faith', '2026-07-06 06:00:00'),
  (7, 'Trusting When You Cannot See', 'Trust in the LORD with all thine heart; and lean not unto thine own understanding.', 'Proverbs 3:5-6', 'We like plans we can fully see before we start. God often asks for trust one step at a time. Leaning on our own understanding means treating our limited view as the whole picture. Trust says: I do not see the road ahead, but I know the One who does, and He will direct my paths.', 'Trust', '2026-07-07 06:00:00'),
  (8, 'Do Not Fear', 'Fear thou not; for I am with thee: be not dismayed; for I am thy God: I will strengthen thee; yea, I will help thee.', 'Isaiah 41:10', 'Fear grows when we feel alone. God answers fear with presence: I am with you. He does not promise that nothing hard will happen; He promises to strengthen, help and uphold us through it. Today, name what you are afraid of and place it in the hands of the One who is with you.', 'Comfort', '2026-07-08 06:00:00'),
  (9, 'All Things Work Together', 'And we know that all things work together for good to them that love God, to them who are the called according to his purpose.', 'Romans 8:28', 'This verse does not say that everything is good. It says that God is at work in everything for the good of those who love Him. The setbacks, the waiting and the surprises are not wasted. Hold on to the truth that your story is in the hands of a God who redeems.', 'Hope', '2026-07-09 06:00:00'),
  (10, 'Come and Rest', 'Come unto me, all ye that labour and are heavy laden, and I will give you rest.', 'Matthew 11:28', 'Jesus does not say, "Fix yourself, then come." He says, "Come." The tired, the stretched and the burdened are invited first. Rest is not a reward for finishing everything; it is a gift received from Christ. Lay down what you are carrying today and accept His rest.', 'Rest', '2026-07-10 06:00:00'),
  (11, 'The Lord Is My Shepherd', 'The LORD is my shepherd; I shall not want.', 'Psalm 23:1', 'A shepherd knows his sheep, leads them to water and stays close in dark valleys. David could say "I shall not want" because he knew who was caring for him. When your needs feel bigger than your supply, remember that you belong to a Shepherd who provides.', 'Provision', '2026-07-11 06:00:00'),
  (12, 'Be Strong and Courageous', 'Have not I commanded thee? Be strong and of a good courage; be not afraid, neither be thou dismayed: for the LORD thy God is with thee whithersoever thou goest.', 'Joshua 1:9', 'Courage is not the absence of fear; it is moving forward because God is with you. Joshua was stepping into a new season with giant responsibilities, and God''s encouragement was simple: I am with you wherever you go. Take the next step today, knowing you do not walk alone.', 'Courage', '2026-07-12 06:00:00');
