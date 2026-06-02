<?php
// Automatic environment detection (Local vs Live Hosting)
$isLocal = ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1');

if ($isLocal) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'ehr_db');
    define('DB_USER', 'root');
    define('DB_PASS', ''); 
} else {
    // live server burdan cikacak / hassas bilgiler
    
}
?>