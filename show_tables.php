<?php
require 'src/Database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query('SHOW TABLES'); 
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
