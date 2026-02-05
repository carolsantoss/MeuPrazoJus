<?php
// test_db.php
require_once 'config.php';
require_once 'src/Database.php';

echo "<h3>Diagnostic Report</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO Drivers: " . implode(", ", PDO::getAvailableDrivers()) . "<br><br>";

try {
    echo "Attempting to connect to " . DB_HOST . "...<br>";
    $db = Database::getInstance()->getConnection();
    echo "<span style='color:green'>Connection successful!</span><br>";
    
    $stmt = $db->query("SELECT 1");
    echo "Query test successful!";
} catch (Exception $e) {
    echo "<span style='color:red'><b>Error:</b> " . $e->getMessage() . "</span><br>";
    echo "Check if the IP " . DB_HOST . " allows external connections and if the user/password are correct.";
}
