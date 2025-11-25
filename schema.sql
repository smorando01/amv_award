-- Esquema AMV Store Award v2 (MySQL 8+)
SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  vote_weight TINYINT UNSIGNED NOT NULL DEFAULT 1,
  can_be_voted TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ci VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  sector VARCHAR(80) DEFAULT NULL,
  role_id INT UNSIGNED NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE auth_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  expires_at DATETIME NOT NULL,
  last_used_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_auth_tokens_hash (token_hash),
  CONSTRAINT fk_auth_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE votes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  voter_id BIGINT UNSIGNED NOT NULL,
  candidate_id BIGINT UNSIGNED NOT NULL,
  weight TINYINT UNSIGNED NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_votes_voter (voter_id),
  KEY idx_votes_candidate (candidate_id),
  CONSTRAINT fk_votes_voter FOREIGN KEY (voter_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_votes_candidate FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos base
INSERT INTO roles (name, vote_weight, can_be_voted) VALUES
  ('empleado', 1, 1),
  ('encargado', 2, 0);

-- Usuarios de ejemplo (contraseña: Passw0rd!)
INSERT INTO users (ci, name, email, password_hash, role_id, sector, is_active) VALUES
  ('10000001', 'Encargado Demo', 'encargado@amvstore.com.uy', '$2y$10$DlsXs6FNJjd6NjIKtrVB/ODwpFL8.WhQwUEL.iTSU3TXbN7Hak0.q', (SELECT id FROM roles WHERE name='encargado'), 'Operaciones', 1),
  ('20000002', 'Empleado Demo', 'empleado@amvstore.com.uy', '$2y$10$DlsXs6FNJjd6NjIKtrVB/ODwpFL8.WhQwUEL.iTSU3TXbN7Hak0.q', (SELECT id FROM roles WHERE name='empleado'), 'Ventas', 1);

