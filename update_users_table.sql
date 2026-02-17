-- Add photo column to users table
USE lost_and_found;

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS photo VARCHAR(255) AFTER gender,
ADD COLUMN IF NOT EXISTS otp VARCHAR(6) AFTER photo,
ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) DEFAULT 0 AFTER otp;
