<?php
require __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    check_csrf();
    $_SESSION = array();                  
    session_regenerate_id(true);
    set_message('success', 'You have been logged out.');
}
go_to('login.php');
