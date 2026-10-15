CREATE TABLE dining_tables (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    area ENUM('salao', 'varanda', 'balcao') NOT NULL DEFAULT 'salao',
    seats INT NOT NULL DEFAULT 4,
    status ENUM('free', 'occupied', 'reserved') NOT NULL DEFAULT 'free',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dining_tables_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
