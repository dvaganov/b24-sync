<?php

require_once 'actions.php';

session_start();
$action = $_GET['action']?: 'index';

if (!function_exists($action)) {
    $action = 'index';
}

$action();
