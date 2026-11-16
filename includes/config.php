<?php
// Default values
$config = array(
    'DB_HOST'    => 'localhost',
    'DB_PORT'    => '5432',
    'DB_NAME'    => 'postgres',
    'DB_USER'    => '',
    'DB_PASSWORD'=> '',
    'DB_SSLMODE' => 'require',
    'APP_TZ'     => 'Asia/Manila'
);

$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line == '' || substr($line, 0, 1) == '#') {
            continue;   // skip empty lines and comments
        }
        $parts = explode('=', $line, 2);
        if (count($parts) == 2) {
            $config[trim($parts[0])] = trim($parts[1], " \t\"'");
        }
    }
}

foreach ($config as $name => $value) {
    $from_env = getenv($name);
    if ($from_env !== false && $from_env != '') {
        $config[$name] = $from_env;
    }
}

date_default_timezone_set($config['APP_TZ']);
ini_set('display_errors', '0');   
ini_set('log_errors', '1');       


try {
    $dsn = 'pgsql:host=' . $config['DB_HOST'] . ';port=' . $config['DB_PORT'] .
           ';dbname=' . $config['DB_NAME'] . ';sslmode=' . $config['DB_SSLMODE'];
    $pdo = new PDO($dsn, $config['DB_USER'], $config['DB_PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
} catch (PDOException $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(503);
    echo '<h2>Service temporarily unavailable</h2>';
    echo '<p>The system could not reach its database. Please try again later.</p>';
    exit;
}
