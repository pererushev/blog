-- Устанавливаем кодировку
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ===========================================
-- Таблица категорий
-- ===========================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `slug` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Уникальный индекс для ЧПУ
    UNIQUE KEY `idx_categories_slug` (`slug`),
    
    -- Индекс для сортировки по дате
    INDEX `idx_categories_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Таблица статей
-- ===========================================
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `image` VARCHAR(500),
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `content` LONGTEXT NOT NULL,
    `views` INT UNSIGNED DEFAULT 0,
    `slug` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Уникальный индекс для ЧПУ
    UNIQUE KEY `idx_posts_slug` (`slug`),
    
    -- Индексы для сортировки (используются на странице категории)
    INDEX `idx_posts_views` (`views`),
    INDEX `idx_posts_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Связующая таблица (Many-to-Many)
-- ===========================================
CREATE TABLE IF NOT EXISTS `post_categories` (
    `post_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    
    -- Составной первичный ключ
    PRIMARY KEY (`post_id`, `category_id`),
    
    -- Внешние ключи с каскадным удалением
    CONSTRAINT `fk_post_categories_post` 
        FOREIGN KEY (`post_id`) 
        REFERENCES `posts` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    CONSTRAINT `fk_post_categories_category` 
        FOREIGN KEY (`category_id`) 
        REFERENCES `categories` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    
    -- Индекс для быстрого поиска категорий поста
    INDEX `idx_post_categories_category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;