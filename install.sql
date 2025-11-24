-- Run this in your cPanel MySQL (phpMyAdmin) after creating the database.

CREATE TABLE IF NOT EXISTS voters (
  ci VARCHAR(20) PRIMARY KEY,                 -- número de cédula sin puntos ni guiones
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) DEFAULT NULL,
  sector VARCHAR(80) DEFAULT NULL,
  role ENUM('empleado','encargado') NOT NULL, -- encargados: votan doble y NO pueden ser votados
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS votes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  voter_ci VARCHAR(20) NOT NULL,
  voted_ci VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_voter (voter_ci),
  KEY idx_voted (voted_ci),
  CONSTRAINT fk_votes_voter FOREIGN KEY (voter_ci) REFERENCES voters(ci) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_votes_voted FOREIGN KEY (voted_ci) REFERENCES voters(ci) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
