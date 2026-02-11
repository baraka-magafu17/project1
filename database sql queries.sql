
CREATE DATABASE IF NOT EXISTS mycycle
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE mycycle;

CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'student',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS groups_table (
  group_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT NULL,
  created_by INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_group_creator FOREIGN KEY (created_by)
    REFERENCES users(user_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS group_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT NOT NULL,
  user_id INT NOT NULL,
  joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_member (group_id, user_id),
  CONSTRAINT fk_member_group FOREIGN KEY (group_id)
    REFERENCES groups_table(group_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_member_user FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS files (
  file_id INT AUTO_INCREMENT PRIMARY KEY,
  file_name VARCHAR(255) NOT NULL,        -- stored filename on server
  original_name VARCHAR(255) NOT NULL,    -- original uploaded name
  uploaded_by INT NULL,                   -- must allow NULL for ON DELETE SET NULL
  group_id INT NOT NULL,
  upload_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  file_size BIGINT NOT NULL DEFAULT 0,
  mime_type VARCHAR(150) NULL,
  CONSTRAINT fk_file_uploader FOREIGN KEY (uploaded_by)
    REFERENCES users(user_id)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT fk_file_group FOREIGN KEY (group_id)
    REFERENCES groups_table(group_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  INDEX (group_id),
  INDEX (uploaded_by)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS messages (
  message_id INT AUTO_INCREMENT PRIMARY KEY,
  content TEXT NOT NULL,
  sender_id INT NULL,                     -- must allow NULL for ON DELETE SET NULL
  group_id INT NOT NULL,
  sent_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_msg_sender FOREIGN KEY (sender_id)
    REFERENCES users(user_id)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT fk_msg_group FOREIGN KEY (group_id)
    REFERENCES groups_table(group_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  INDEX (group_id),
  INDEX (sender_id)
) ENGINE=InnoDB;

