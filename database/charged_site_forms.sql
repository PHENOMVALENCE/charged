-- CHARGED — public form submissions (newsletter + partner inquiries)
-- Run once on an existing database if you already imported an older charged_articles.sql:
--   mysql -u root charged_articles < database/charged_site_forms.sql
-- Fresh installs: table is included in database/charged_articles.sql (no need to run this file).

USE charged_articles;

CREATE TABLE IF NOT EXISTS site_form_submissions (
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
