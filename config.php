<?php
// config.php

// Define base path
define('BASE_PATH', __DIR__);

// Database Configuration
define('DB_PATH', BASE_PATH . '/database.sqlite');

function getDb() {
    try {
        // Create (connect to) SQLite database in file
        $pdo = new PDO('sqlite:' . DB_PATH);
        // Set errormode to exceptions
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Global functions or constants can go here
date_default_timezone_set('America/Sao_Paulo');
