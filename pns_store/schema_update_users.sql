ALTER TABLE users 
  MODIFY COLUMN role ENUM('user', 'org', 'admin') NOT NULL DEFAULT 'user',
  ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  ADD COLUMN IF NOT EXISTS first_name VARCHAR(100) AFTER name,
  ADD COLUMN IF NOT EXISTS last_name VARCHAR(100) AFTER first_name;

-- Update existing users to split name into first_name and last_name
UPDATE users 
SET first_name = SUBSTRING_INDEX(name, ' ', 1),
    last_name = TRIM(SUBSTR(name, LOCATE(' ', name)));

-- Convert any 'student' roles to 'user'
UPDATE users SET role = 'user' WHERE role = 'student';