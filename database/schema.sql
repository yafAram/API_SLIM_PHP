-- Esquema de la base de datos (MySQL / MariaDB)
-- Uso: mysql -u root -p < database/schema.sql

CREATE DATABASE IF NOT EXISTS api_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE api_db;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username      VARCHAR(50)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB;
