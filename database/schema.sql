-- TravelVista - database structure.
--
-- Creates the database and all seven tables with their keys and foreign
-- keys. Import this first, then database/seed.sql for the demo content.
--
--   mysql -u root -p < database/schema.sql
--
-- Tables are created parent-first (users, then posts, then everything that
-- points at them) so the import works in phpMyAdmin as well as on the
-- command line, without relying on foreign key checks being switched off.

CREATE DATABASE IF NOT EXISTS travelvista
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE travelvista;

SET FOREIGN_KEY_CHECKS = 0;

-- Dropped child-first, the reverse of the creation order below.
DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `reset_requests`;
DROP TABLE IF EXISTS `post_requests`;
DROP TABLE IF EXISTS `cost_estimates`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- users - referenced by every other table.
-- ---------------------------------------------------------------------
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','scout','user') NOT NULL DEFAULT 'user',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `profile_picture` varchar(255) DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- posts - the published destinations.
-- ---------------------------------------------------------------------
CREATE TABLE `posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `scout_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `short_history` text NOT NULL,
  `country` varchar(90) NOT NULL,
  `country_representation` text DEFAULT NULL,
  `genre` varchar(40) NOT NULL,
  `cost_level` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `travel_medium_info` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','hidden') NOT NULL DEFAULT 'approved',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_posts_scout` (`scout_id`),
  KEY `idx_posts_status` (`status`),
  KEY `idx_posts_country` (`country`),
  KEY `idx_posts_genre` (`genre`),
  KEY `idx_posts_cost` (`cost_level`),
  CONSTRAINT `fk_posts_scout` FOREIGN KEY (`scout_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- comments - one user's comment on one post.
-- ---------------------------------------------------------------------
CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_comments_post` (`post_id`),
  KEY `idx_comments_user` (`user_id`),
  CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- cost_estimates - the base trip cost stored per post.
-- ---------------------------------------------------------------------
CREATE TABLE `cost_estimates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(10) unsigned NOT NULL,
  `base_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` char(3) NOT NULL DEFAULT 'USD',
  `last_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cost_post` (`post_id`),
  CONSTRAINT `fk_cost_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- post_requests - a scout's submission waiting on admin review.
-- ---------------------------------------------------------------------
CREATE TABLE `post_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `scout_id` int(10) unsigned NOT NULL,
  `original_post_id` int(10) unsigned DEFAULT NULL,
  `post_data` longtext NOT NULL,
  `admin_note` varchar(255) DEFAULT NULL,
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `idx_requests_scout` (`scout_id`),
  KEY `idx_requests_status` (`status`),
  KEY `fk_requests_post` (`original_post_id`),
  CONSTRAINT `fk_requests_post` FOREIGN KEY (`original_post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_requests_scout` FOREIGN KEY (`scout_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- reset_requests - a user asking an admin for a password reset.
-- ---------------------------------------------------------------------
CREATE TABLE `reset_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `status` enum('pending','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reset_user` (`user_id`),
  CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- wishlist - saved destinations, one row per user and post.
-- ---------------------------------------------------------------------
CREATE TABLE `wishlist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `post_id` int(10) unsigned NOT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wishlist_pair` (`user_id`,`post_id`),
  KEY `idx_wishlist_post` (`post_id`),
  CONSTRAINT `fk_wishlist_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
