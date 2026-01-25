<?php
$pdo = new PDO("mysql:host=localhost;dbname=perdagangan_system", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('ALTER TABLE suppliers ADD COLUMN business_category ENUM("retail", "wholesale", "manufacturing", "agriculture", "services", "distribution", "import_export") DEFAULT "wholesale" AFTER supplier_type');
echo '✅ business_category column added successfully';
?>
