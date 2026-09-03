CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(80) NOT NULL,
    status ENUM('open', 'preparing', 'ready', 'closed', 'cancelled') NOT NULL DEFAULT 'open',
    total_value DECIMAL(10,2) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    opened_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_orders_status (status),
    INDEX idx_orders_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
