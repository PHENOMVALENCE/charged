-- CHARGED — articles CMS schema (plain PHP + MySQL)
-- Import via phpMyAdmin or: mysql -u root < database/charged_articles.sql
--
-- Default admin: admin@charged.com / admin123
-- Password hash was generated with: php -r "echo password_hash('admin123', PASSWORD_DEFAULT);"
-- If you change the password, run that command and UPDATE admins SET password = '...' WHERE email = 'admin@charged.com';

CREATE DATABASE IF NOT EXISTS charged_articles
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE charged_articles;

-- Clean slate when re-importing (removes data).
DROP TABLE IF EXISTS site_form_submissions;
DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE articles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL,
  excerpt TEXT NULL,
  content LONGTEXT NOT NULL,
  featured_image VARCHAR(500) NULL DEFAULT NULL,
  status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  author_id INT UNSIGNED NOT NULL,
  published_at DATETIME NULL DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_articles_slug (slug),
  KEY idx_articles_status_published (status, published_at),
  KEY idx_articles_author (author_id),
  CONSTRAINT fk_articles_author FOREIGN KEY (author_id) REFERENCES admins (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE site_form_submissions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  form_type ENUM('subscribe', 'partner') NOT NULL,
  email VARCHAR(190) NOT NULL,
  name VARCHAR(200) NULL,
  message TEXT NULL,
  source_page VARCHAR(500) NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_site_forms_type_created (form_type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admins (name, email, password, created_at) VALUES (
  'Administrator',
  'admin@charged.com',
  '$2y$10$QXscg9CURGs.pHm2AAmMEOaITvANk4gOqsP8eM65Kgs85Ovle7TWe',
  NOW()
);
