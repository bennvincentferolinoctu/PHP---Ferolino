<?php
session_name('agrisess');
session_set_cookie_params(array('httponly' => true, 'samesite' => 'Lax'));
session_start();

// Log the user out after 30 minutes of no activity
if (isset($_SESSION['last_seen']) && time() - $_SESSION['last_seen'] > 1800) {
    $_SESSION = array();
    $_SESSION['messages'][] = array('warning', 'Your session expired. Please log in again.');
}
$_SESSION['last_seen'] = time();

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';
