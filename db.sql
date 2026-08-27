-- Script para crear la base de datos y tablas básicas
CREATE DATABASE IF NOT EXISTS actividad_fisica DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE actividad_fisica;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS activities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  activity_date DATE NOT NULL,
  activity_type VARCHAR(100) NOT NULL,
  duration_minutes INT NOT NULL,
  calories INT DEFAULT NULL,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Usuario de prueba (password: demo1234)
INSERT INTO users (name, email, password) VALUES
('Usuario Demo', 'demo@example.com', '$2y$10$zX0ZxgW3pQmQW8vQk7nYduz5eXgWc1F6BxVQKXKq9VYwQ8k0s1aG'); -- demo1234
