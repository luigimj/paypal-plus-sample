<?php

session_start();
session_unset();
$_SESSION = array();
$_SESSION['define'] = false ;

// set timzone
date_default_timezone_set('UCT');

// site bast dir and url
$BASE_HTTP = 'http://';
$_SESSION['BASE_DIR'] =dirname($_SERVER['PHP_SELF']);
$_SESSION['BASE_URL'] = $BASE_HTTP.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);

include( 'paypal/html/header.phtml');
include( 'paypal/html/home.phtml');
include( 'paypal/html/footer.phtml');
?>