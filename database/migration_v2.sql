-- Migration script for Conference System v2.0.0
-- Adds password reset functionality to users table

USE `conference_db`;

-- Add password reset columns if they don't exist
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `reset_token` VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `reset_token_expiry` DATETIME DEFAULT NULL;

-- Add index for faster token lookups
ALTER TABLE `users` 
ADD INDEX IF NOT EXISTS `reset_token` (`reset_token`);

-- Verify the changes
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'conference_db' 
AND TABLE_NAME = 'users'
AND COLUMN_NAME IN ('reset_token', 'reset_token_expiry');
