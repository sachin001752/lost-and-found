-- Update schema for admin system
-- Add status column to items table

USE lost_and_found;

-- Add status column if it doesn't exist
ALTER TABLE items 
ADD COLUMN IF NOT EXISTS status ENUM('Pending', 'Resolved', 'Closed') DEFAULT 'Pending' AFTER created_at;

-- Add index for faster filtering
ALTER TABLE items 
ADD INDEX IF NOT EXISTS idx_status (status);

-- Optional: Create admin_users table for multiple admin accounts
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin1234)
INSERT INTO admin_users (email, password) 
VALUES ('admin@me.com', '$2y$10$rB3qX7QZGkJd5FWx8vY9/.vHxK5nYqGZqKwJQwZ8YqHfZqKw8YqZq')
ON DUPLICATE KEY UPDATE email = email;

-- Add user verification column
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS verified TINYINT(1) DEFAULT 0 AFTER address;
