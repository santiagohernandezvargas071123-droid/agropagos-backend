<?php
// Ejecutar una sola vez para crear tablas. Protegido con token.
if (($_GET['token'] ?? '') !== 'agropagos_install_2026') {
    http_response_code(403); die('Forbidden');
}

require_once __DIR__ . '/config/database.php';
$database = new Database();
$db = $database->getConnection();

if (!$db) { die('No se pudo conectar a la base de datos'); }

$sqls = [
"CREATE TABLE IF NOT EXISTS workers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    id_number VARCHAR(50) NOT NULL UNIQUE,
    address VARCHAR(255),
    position VARCHAR(100),
    lote VARCHAR(100),
    status VARCHAR(20) DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    worker_id INT NOT NULL,
    payment_date DATE NOT NULL,
    week_worked VARCHAR(100) NOT NULL,
    days_worked DECIMAL(4,1) NOT NULL DEFAULT 0,
    amount_earned DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE
)",
"CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_name VARCHAR(100) NOT NULL DEFAULT 'Admin',
    admin_email VARCHAR(100) NOT NULL DEFAULT 'admin@agropagos.com',
    daily_rate DECIMAL(10,2) NOT NULL DEFAULT 60000.00,
    currency VARCHAR(50) NOT NULL DEFAULT 'Peso Colombiano (COP)'
)",
"CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"CREATE TABLE IF NOT EXISTS lot_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_task DATE NOT NULL,
    lot_name VARCHAR(100) NOT NULL,
    task_name VARCHAR(255) NOT NULL,
    workers_desc VARCHAR(255) NOT NULL,
    calculated_value DECIMAL(12,2) NOT NULL,
    original_value_notes VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"INSERT INTO settings (admin_name,admin_email,daily_rate,currency)
 SELECT 'Admin Chucho','admin@agropagos.com',60000.00,'Peso Colombiano (COP)'
 WHERE NOT EXISTS (SELECT 1 FROM settings)",
"INSERT INTO users (username,password_hash)
 SELECT 'admin','\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
 WHERE NOT EXISTS (SELECT 1 FROM users WHERE username='admin')",
];

echo "<pre>Instalando...\n";
foreach ($sqls as $sql) {
    try { $db->exec($sql); echo "OK\n"; }
    catch(PDOException $e) { echo "ERROR: " . $e->getMessage() . "\n"; }
}

$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "\nTablas creadas:\n";
foreach ($tables as $t) echo "  - $t\n";
echo "\nListo. Usuario: admin / Contrasena: admin123\n";
echo "BORRA ESTE ARCHIVO despues de usarlo.\n</pre>";
