-- Add optional image gallery JSON column (run once on existing installs).
-- Paths are relative strings like "uploads/articles/article-....jpg".

USE charged_articles;

ALTER TABLE articles
  ADD COLUMN gallery_images JSON NULL DEFAULT NULL
  AFTER featured_image;
