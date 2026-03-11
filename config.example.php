<?php

define('IS_LOCAL', true);

if (IS_LOCAL) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'prazolegal');
} else {
    define('DB_HOST', 'production_host');
    define('DB_USER', 'production_user');
    define('DB_PASS', 'production_pass');
    define('DB_NAME', 'production_db');
}

define('ASAAS_API_KEY', 'YOUR_ASAAS_API_KEY');
define('ASAAS_URL', 'https://sandbox.asaas.com/api/v3');
