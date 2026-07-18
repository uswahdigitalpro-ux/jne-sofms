-- JNE Smart Operations & Financial Management System
-- Database Schema
-- Created: 2026-07-18

SET FOREIGN_KEY_CHECKS=0;

-- =============================================
-- 1. USERS & AUTHENTICATION
-- =============================================

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('owner', 'staff') DEFAULT 'staff',
    phone VARCHAR(20),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    KEY idx_outlet_id (outlet_id),
    KEY idx_role (role),
    KEY idx_status (status)
);

-- =============================================
-- 2. OUTLET / BRANCH
-- =============================================

CREATE TABLE IF NOT EXISTS outlets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) UNIQUE,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    logo_path VARCHAR(255),
    timezone VARCHAR(50) DEFAULT 'Asia/Jakarta',
    daily_target DECIMAL(15,2) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_owner_id (owner_id),
    KEY idx_status (status)
);

-- =============================================
-- 3. SHIFT MANAGEMENT
-- =============================================

CREATE TABLE IF NOT EXISTS shifts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_shift (outlet_id, name),
    KEY idx_outlet_id (outlet_id),
    KEY idx_status (status)
);

-- =============================================
-- 4. OPENING CASH / MODAL AWAL
-- =============================================

CREATE TABLE IF NOT EXISTS opening_cash (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    staff_id INT NOT NULL,
    shift_id INT NOT NULL,
    initial_capital DECIMAL(15,2) NOT NULL,
    notes TEXT,
    opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL,
    status ENUM('open', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    KEY idx_outlet_id (outlet_id),
    KEY idx_staff_id (staff_id),
    KEY idx_shift_id (shift_id),
    KEY idx_status (status),
    KEY idx_opened_at (opened_at)
);

-- =============================================
-- 5. INCOME CATEGORIES
-- =============================================

CREATE TABLE IF NOT EXISTS income_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50),
    description TEXT,
    icon VARCHAR(50),
    color_code VARCHAR(7),
    order_index INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_category (outlet_id, name),
    KEY idx_outlet_id (outlet_id),
    KEY idx_status (status)
);

-- =============================================
-- 6. PAYMENT METHODS
-- =============================================

CREATE TABLE IF NOT EXISTS payment_methods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50),
    description TEXT,
    icon VARCHAR(50),
    color_code VARCHAR(7),
    order_index INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_method (outlet_id, name),
    KEY idx_outlet_id (outlet_id),
    KEY idx_status (status)
);

-- =============================================
-- 7. INCOME / PENDAPATAN
-- =============================================

CREATE TABLE IF NOT EXISTS income (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    opening_cash_id INT NOT NULL,
    staff_id INT NOT NULL,
    income_category_id INT NOT NULL,
    payment_method_id INT NOT NULL,
    description VARCHAR(255),
    amount DECIMAL(15,2) NOT NULL,
    proof_image_path VARCHAR(255),
    notes TEXT,
    is_corrected BOOLEAN DEFAULT FALSE,
    corrected_by INT,
    correction_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    KEY idx_outlet_id (outlet_id),
    KEY idx_opening_cash_id (opening_cash_id),
    KEY idx_staff_id (staff_id),
    KEY idx_income_category_id (income_category_id),
    KEY idx_payment_method_id (payment_method_id),
    KEY idx_created_at (created_at),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (opening_cash_id) REFERENCES opening_cash(id),
    FOREIGN KEY (staff_id) REFERENCES users(id),
    FOREIGN KEY (income_category_id) REFERENCES income_categories(id),
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id)
);

-- =============================================
-- 8. EXPENSE CATEGORIES
-- =============================================

CREATE TABLE IF NOT EXISTS expense_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50),
    description TEXT,
    icon VARCHAR(50),
    color_code VARCHAR(7),
    order_index INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_category (outlet_id, name),
    KEY idx_outlet_id (outlet_id),
    KEY idx_status (status)
);

-- =============================================
-- 9. EXPENSE / PENGELUARAN
-- =============================================

CREATE TABLE IF NOT EXISTS expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    opening_cash_id INT NOT NULL,
    staff_id INT NOT NULL,
    expense_category_id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    receipt_image_path VARCHAR(255),
    notes TEXT,
    requires_approval BOOLEAN DEFAULT FALSE,
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT,
    approval_notes TEXT,
    is_corrected BOOLEAN DEFAULT FALSE,
    corrected_by INT,
    correction_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    KEY idx_outlet_id (outlet_id),
    KEY idx_opening_cash_id (opening_cash_id),
    KEY idx_staff_id (staff_id),
    KEY idx_expense_category_id (expense_category_id),
    KEY idx_approval_status (approval_status),
    KEY idx_created_at (created_at),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (opening_cash_id) REFERENCES opening_cash(id),
    FOREIGN KEY (staff_id) REFERENCES users(id),
    FOREIGN KEY (expense_category_id) REFERENCES expense_categories(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- =============================================
-- 10. CLOSING CASH / PENUTUPAN KAS
-- =============================================

CREATE TABLE IF NOT EXISTS closing_cash (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    opening_cash_id INT NOT NULL,
    staff_id INT NOT NULL,
    physical_cash DECIMAL(15,2) NOT NULL,
    notes TEXT,
    photo_path VARCHAR(255),
    closed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_outlet_id (outlet_id),
    KEY idx_opening_cash_id (opening_cash_id),
    KEY idx_staff_id (staff_id),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (opening_cash_id) REFERENCES opening_cash(id),
    FOREIGN KEY (staff_id) REFERENCES users(id)
);

-- =============================================
-- 11. CASH RECONCILIATION
-- =============================================

CREATE TABLE IF NOT EXISTS cash_reconciliation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    opening_cash_id INT NOT NULL,
    closing_cash_id INT,
    initial_capital DECIMAL(15,2) NOT NULL,
    cash_in DECIMAL(15,2) DEFAULT 0,
    cash_out DECIMAL(15,2) DEFAULT 0,
    expected_cash DECIMAL(15,2) NOT NULL,
    physical_cash DECIMAL(15,2),
    difference DECIMAL(15,2),
    difference_notes TEXT,
    reconciliation_status ENUM('balanced', 'unbalanced', 'pending') DEFAULT 'pending',
    reconciled_by INT,
    reconciled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_outlet_id (outlet_id),
    KEY idx_opening_cash_id (opening_cash_id),
    KEY idx_closing_cash_id (closing_cash_id),
    KEY idx_reconciliation_status (reconciliation_status),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (opening_cash_id) REFERENCES opening_cash(id),
    FOREIGN KEY (closing_cash_id) REFERENCES closing_cash(id),
    FOREIGN KEY (reconciled_by) REFERENCES users(id)
);

-- =============================================
-- 12. MULTI-PAYMENT TRACKING
-- =============================================

CREATE TABLE IF NOT EXISTS payment_tracking (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    opening_cash_id INT NOT NULL,
    payment_method_id INT NOT NULL,
    opening_balance DECIMAL(15,2) DEFAULT 0,
    total_in DECIMAL(15,2) DEFAULT 0,
    total_out DECIMAL(15,2) DEFAULT 0,
    closing_balance DECIMAL(15,2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_outlet_id (outlet_id),
    KEY idx_opening_cash_id (opening_cash_id),
    KEY idx_payment_method_id (payment_method_id),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (opening_cash_id) REFERENCES opening_cash(id),
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id)
);

-- =============================================
-- 13. APPROVALS
-- =============================================

CREATE TABLE IF NOT EXISTS approvals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    approver_id INT NOT NULL,
    expense_id INT,
    opening_cash_id INT,
    approval_type ENUM('expense', 'cash_reopening', 'transaction_correction') DEFAULT 'expense',
    reference_id INT,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approval_notes TEXT,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_outlet_id (outlet_id),
    KEY idx_approver_id (approver_id),
    KEY idx_status (status),
    KEY idx_approval_type (approval_type),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (approver_id) REFERENCES users(id),
    FOREIGN KEY (expense_id) REFERENCES expenses(id)
);

-- =============================================
-- 14. AUDIT LOG / AUDIT TRAIL
-- =============================================

CREATE TABLE IF NOT EXISTS audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_outlet_id (outlet_id),
    KEY idx_user_id (user_id),
    KEY idx_action (action),
    KEY idx_entity_type (entity_type),
    KEY idx_created_at (created_at),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =============================================
-- 15. SESSIONS
-- =============================================

CREATE TABLE IF NOT EXISTS sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    logged_in_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    logged_out_at TIMESTAMP NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    KEY idx_outlet_id (outlet_id),
    KEY idx_user_id (user_id),
    KEY idx_status (status),
    KEY idx_logged_in_at (logged_in_at),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =============================================
-- 16. TELEGRAM SETTINGS
-- =============================================

CREATE TABLE IF NOT EXISTS telegram_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    bot_token VARCHAR(255) NOT NULL,
    chat_id VARCHAR(50) NOT NULL,
    operational_group_id VARCHAR(50),
    order_group_id VARCHAR(50),
    send_login_logout BOOLEAN DEFAULT TRUE,
    send_cash_report BOOLEAN DEFAULT TRUE,
    send_daily_summary BOOLEAN DEFAULT TRUE,
    send_error_alert BOOLEAN DEFAULT TRUE,
    send_approval_request BOOLEAN DEFAULT TRUE,
    enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_outlet (outlet_id),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id)
);

-- =============================================
-- 17. NOTIFICATIONS
-- =============================================

CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'error', 'success') DEFAULT 'info',
    notification_type ENUM('login', 'logout', 'cash_opening', 'cash_closing', 'approval', 'error') DEFAULT 'info',
    telegram_sent BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_outlet_id (outlet_id),
    KEY idx_user_id (user_id),
    KEY idx_read_at (read_at),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =============================================
-- 18. BACKUP & LOGS
-- =============================================

CREATE TABLE IF NOT EXISTS backup_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    backup_file VARCHAR(255) NOT NULL,
    backup_type ENUM('automatic', 'manual') DEFAULT 'manual',
    backup_size INT,
    status ENUM('success', 'failed') DEFAULT 'success',
    error_message TEXT,
    backup_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_outlet_id (outlet_id),
    KEY idx_created_at (created_at),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (backup_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT,
    level ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    message TEXT NOT NULL,
    context JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_level (level),
    KEY idx_created_at (created_at)
);

-- =============================================
-- 19. SETTINGS
-- =============================================

CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value LONGTEXT,
    data_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_setting (outlet_id, setting_key),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id)
);

-- =============================================
-- 20. RECYCLE BIN / SOFT DELETE
-- =============================================

CREATE TABLE IF NOT EXISTS recycle_bin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    outlet_id INT NOT NULL,
    user_id INT NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NOT NULL,
    entity_data JSON NOT NULL,
    deleted_reason VARCHAR(255),
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    restored_at TIMESTAMP NULL,
    restored_by INT,
    permanent_delete_at TIMESTAMP NULL,
    KEY idx_outlet_id (outlet_id),
    KEY idx_entity_type (entity_type),
    KEY idx_deleted_at (deleted_at),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (restored_by) REFERENCES users(id)
);

-- =============================================
-- 21. DEFAULT DATA
-- =============================================

-- Insert default income categories
INSERT INTO income_categories (outlet_id, name, code, icon, color_code, order_index) VALUES
(1, 'JNE', 'jne', 'truck', '#3B82F6', 1),
(1, 'Pulsa', 'pulsa', 'phone', '#10B981', 2),
(1, 'Token Listrik', 'token', 'zap', '#F59E0B', 3),
(1, 'Layanan Lain', 'other', 'briefcase', '#8B5CF6', 4);

-- Insert default payment methods
INSERT INTO payment_methods (outlet_id, name, code, icon, color_code, order_index) VALUES
(1, 'Cash', 'cash', 'dollar-sign', '#059669', 1),
(1, 'Transfer', 'transfer', 'send', '#0891B2', 2),
(1, 'QRIS', 'qris', 'qrcode', '#7C3AED', 3),
(1, 'E-Wallet', 'ewallet', 'smartphone', '#EC4899', 4);

-- Insert default expense categories
INSERT INTO expense_categories (outlet_id, name, code, icon, color_code, order_index) VALUES
(1, 'BBM', 'fuel', 'fuel-pump', '#DC2626', 1),
(1, 'ATK', 'stationery', 'briefcase', '#EA580C', 2),
(1, 'Listrik', 'electricity', 'zap', '#F59E0B', 3),
(1, 'Internet', 'internet', 'wifi', '#06B6D4', 4),
(1, 'Konsumsi', 'consumption', 'coffee', '#8B5CF6', 5),
(1, 'Operasional', 'operational', 'settings', '#6B7280', 6),
(1, 'Lainnya', 'other', 'more-horizontal', '#9CA3AF', 7);

-- Insert default shifts
INSERT INTO shifts (outlet_id, name, start_time, end_time, description) VALUES
(1, 'Pagi', '06:00:00', '14:00:00', 'Shift Pagi'),
(1, 'Siang', '14:00:00', '22:00:00', 'Shift Siang'),
(1, 'Malam', '22:00:00', '06:00:00', 'Shift Malam');

SET FOREIGN_KEY_CHECKS=1;
