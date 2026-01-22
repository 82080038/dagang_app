-- Inventory Management Tables

-- 1. Branch Inventory (Current Stock)
CREATE TABLE IF NOT EXISTS branch_inventory (
    id_inventory INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    min_stock DECIMAL(10,2) DEFAULT 0.00,
    location_in_store VARCHAR(100),
    last_restocked_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    UNIQUE KEY uk_branch_product (branch_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Stock Movements (History)
CREATE TABLE IF NOT EXISTS stock_movements (
    id_movement INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    product_id INT NOT NULL,
    type ENUM('in', 'out', 'adjustment', 'transfer_in', 'transfer_out', 'sale', 'return') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    previous_stock DECIMAL(10,2) NOT NULL,
    current_stock DECIMAL(10,2) NOT NULL,
    reference_type VARCHAR(50), -- 'transaction', 'transfer', 'adjustment'
    reference_id INT,
    notes TEXT,
    created_by INT NULL, -- User ID
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE,
    INDEX idx_movement_date (created_at),
    INDEX idx_movement_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Inventory Transfers (Between Branches)
CREATE TABLE IF NOT EXISTS inventory_transfers (
    id_transfer INT AUTO_INCREMENT PRIMARY KEY,
    source_branch_id INT NOT NULL,
    destination_branch_id INT NOT NULL,
    status ENUM('pending', 'approved', 'shipped', 'received', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_by INT NOT NULL,
    approved_by INT NULL,
    received_by INT NULL,
    shipped_at TIMESTAMP NULL,
    received_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (source_branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE,
    FOREIGN KEY (destination_branch_id) REFERENCES branches(id_branch) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Transfer Items
CREATE TABLE IF NOT EXISTS transfer_items (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    transfer_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity_requested DECIMAL(10,2) NOT NULL,
    quantity_sent DECIMAL(10,2) DEFAULT 0.00,
    quantity_received DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT,
    
    FOREIGN KEY (transfer_id) REFERENCES inventory_transfers(id_transfer) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id_product) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
