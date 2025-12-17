-- ELGROW Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS `db_ELGROW`;
USE `db_ELGROW`;

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` INT PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Habits Table
CREATE TABLE IF NOT EXISTS `habits` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,  
  `user_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `frequency` VARCHAR(50) DEFAULT 'daily',
  `category` VARCHAR(50),
  `daily_days` VARCHAR(50) DEFAULT NULL,
  `weekly_count` INT DEFAULT NULL,
  `streak` INT DEFAULT 0,
  `is_active` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `end_date` DATE NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id_user`) ON DELETE CASCADE,
  INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Habit Completions Table
CREATE TABLE IF NOT EXISTS `habit_completions` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `habit_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `completion_date` DATE NOT NULL,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `streak` INT DEFAULT 0,
  FOREIGN KEY (`habit_id`) REFERENCES `habits`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id_user`) ON DELETE CASCADE,
  UNIQUE KEY `unique_habit_date` (`habit_id`, `completion_date`),
  INDEX (`user_id`),
  INDEX (`completion_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Habit Goals Table
CREATE TABLE IF NOT EXISTS goals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    target_date DATE NULL,
    is_complete TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(user_id),
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;