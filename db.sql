-- SQL Schema for GoLocal - A Micro-Adventure Planning App
-- This schema defines the necessary tables and relationships to support
-- -----------------------------------------------------

-- Drop tables in reverse order of creation to avoid foreign key errors
DROP TABLE IF EXISTS `photo_recipients`;
DROP TABLE IF EXISTS `photos`;
DROP TABLE IF EXISTS `chat_messages`;
DROP TABLE IF EXISTS `checklists`;
DROP TABLE IF EXISTS `trip_participants`;
DROP TABLE IF EXISTS `trips`;
DROP TABLE IF EXISTS `users`;


-- Table structure for `users`
-- Stores essential user profile information.
CREATE TABLE `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `profile_picture_url` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Table structure for `trips`
-- The main table that holds the details for each micro-adventure.
CREATE TABLE `trips` (
    `trip_id` INT AUTO_INCREMENT PRIMARY KEY,
    `trip_name` VARCHAR(255) NOT NULL,
    `location` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `estimated_cost` DECIMAL(10, 2),
    `start_datetime` DATETIME NOT NULL,
    `end_datetime` DATETIME NOT NULL,
    `admin_id` INT NOT NULL,
    `co_admin_id` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `users`(`user_id`),
    FOREIGN KEY (`co_admin_id`) REFERENCES `users`(`user_id`)
);


-- Table structure for `trip_participants`
-- Connects users to trips and tracks their participation status.
CREATE TABLE `trip_participants` (
  `participant_id` INT AUTO_INCREMENT PRIMARY KEY,
  `trip_id` INT NOT NULL,
  `user_id` INT NULL,
  `guest_name` VARCHAR(255) NULL,
  `guest_email` VARCHAR(255) NULL,
  `status` ENUM('invited', 'accepted', 'declined', 'completed', 'did_not_attend') NOT NULL DEFAULT 'invited',
  `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`trip_id`) REFERENCES `trips`(`trip_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  UNIQUE KEY (`trip_id`, `user_id`), -- A user can't be in the same trip twice
  UNIQUE KEY (`trip_id`, `guest_email`) -- A guest email can't be invited twice
);


-- Table structure for `checklists`
-- Holds all the individual to-do items for a specific trip.
CREATE TABLE `checklists` (
    `item_id` INT AUTO_INCREMENT PRIMARY KEY,
    `trip_id` INT NOT NULL,
    `item_description` VARCHAR(255) NOT NULL,
    `is_completed` BOOLEAN NOT NULL DEFAULT FALSE,
    `created_by_user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`trip_id`) REFERENCES `trips`(`trip_id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by_user_id`) REFERENCES `users`(`user_id`)
);


-- Table structure for `photos`
-- Manages photo sharing for a trip. Privacy is handled by the `photo_recipients` table.
CREATE TABLE `photos` (
    `photo_id` INT AUTO_INCREMENT PRIMARY KEY,
    `trip_id` INT NOT NULL,
    `uploaded_by_user_id` INT NOT NULL,
    `photo_url` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(255),
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`trip_id`) REFERENCES `trips`(`trip_id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by_user_id`) REFERENCES `users`(`user_id`)
);


-- Table structure for `photo_recipients`
-- If a photo is private, this table links it to users who can see it.
CREATE TABLE `photo_recipients` (
    `recipient_id` INT AUTO_INCREMENT PRIMARY KEY,
    `photo_id` INT NOT NULL,
    `recipient_user_id` INT NOT NULL,
    FOREIGN KEY (`photo_id`) REFERENCES `photos`(`photo_id`) ON DELETE CASCADE,
    FOREIGN KEY (`recipient_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    UNIQUE KEY (`photo_id`, `recipient_user_id`)
);


-- Table structure for `chat_messages`
-- Stores all messages sent in a trip's chatbox, with categories.
CREATE TABLE `chat_messages` (
    `message_id` INT AUTO_INCREMENT PRIMARY KEY,
    `trip_id` INT NOT NULL,
    `sender_user_id` INT NOT NULL,
    `message_text` TEXT NOT NULL,
    `category` ENUM('normal', 'plan_change', 'notice', 'expense') NOT NULL DEFAULT 'normal',
    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`trip_id`) REFERENCES `trips`(`trip_id`) ON DELETE CASCADE,
    FOREIGN KEY (`sender_user_id`) REFERENCES `users`(`user_id`)
);