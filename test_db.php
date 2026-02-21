<?php
try { 
    $db = new PDO('mysql:host=192.168.20.16;charset=utf8mb4', 'SRVDB', 'SRVFCa2026!@#'); 
    echo 'SUCCESS! Authenticated but no DB selected.'; 
} catch (PDOException $e) { 
    echo "Fail noDB: " . $e->getMessage() . "\n"; 
}
try { 
    $db = new PDO('mysql:host=192.168.20.16;dbname=meuprazojus;charset=utf8mb4', 'SRVDB', 'SRVFCa2026!@#'); 
    echo 'SUCCESS with DB!'; 
} catch (PDOException $e) { 
    echo "Fail withDB: " . $e->getMessage() . "\n"; 
}
