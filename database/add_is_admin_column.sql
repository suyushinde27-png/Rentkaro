-- Migration: Add is_admin column to users table
-- Run this on existing databases to add admin role support

ALTER TABLE `users` ADD `is_admin` tinyint(1) DEFAULT 0 AFTER `reward_points`;

-- Make the first user (user_id = 1) an admin by default
UPDATE `users` SET `is_admin` = 1 WHERE `user_id` = 1;
