<?php

require_once 'actions.php';

session_start();

$action = function_exists($_GET['action'])? $_GET['action'] : 'index';
$action();
