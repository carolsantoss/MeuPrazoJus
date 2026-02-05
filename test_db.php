<?php
// test_db.php
require_once 'src/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Connection successful!<br>";
    
    $stmt = $db->query("SELECT 1");
    echo "Query successful!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
