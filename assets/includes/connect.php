<?php
session_cache_limiter('none');
session_start();

// Include 'config.php' file
require('assets/includes/config.php');

// Connect to SQL Server
$dbConnect = mysqli_connect($sql_host, $sql_user, $sql_pass, $sql_name);

// Check connection
if (mysqli_connect_errno($dbConnect)) {
    exit(mysqli_connect_error());
}

// Assign database table names to constants
require('tables.php');

// Fetch site configurations
require('assets/settings/general.php');
require('assets/settings/theme.php');
require('assets/settings/ads.php');
$config['site_url'] = $site_url;
$config['theme_url'] = $site_url . '/themes/' . $config['theme'];
$config['script_path'] = str_replace('index.php', '', $_SERVER['PHP_SELF']);
$config['ajax_path'] = $config['script_path'] . 'request.php';
$config['page_path'] = $config['script_path'] . 'page.php';


if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = $config['language'];
}

include_once('themes/' . $config['theme'] . '/emoticons/process.php');

// Stores site configurations to variables for later use
$sk = array();
$sk['config'] = $config;

// Login verification and user stats update
$logged = false;
$user = null;

if (SK_isLogged()) {
    $user = SK_getUser($_SESSION['user_id'], true);
    
    if (!empty($user['id']) && $user['type'] == "user") {
        $sk['user'] = $user;
        $logged = true;
        
        $query_two = "UPDATE " . DB_ACCOUNTS . " SET last_logged=" . time() . " WHERE id=" . $user['id'];
        $sql_query_two = mysqli_query($dbConnect, $query_two);
        
        if (!empty($user['language'])) {
            $_SESSION['language'] = $user['language'];
        }
        
        if (!SK_isFollowing($user['id'], $user['id'])) {
            $query_three = "DELETE FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND following_id=" . $user['id'];
            $sql_query_three = mysqli_query($dbConnect, $query_three);
            
            $query_four = "INSERT INTO " . DB_FOLLOWERS . " (active,follower_id,following_id,time) VALUES (1," . $user['id'] . "," . $user['id'] . "," . time() . ")";
            $sql_query_four = mysqli_query($dbConnect, $query_four);
        }
    }
}

$sk['logged'] = $logged;

// Fetch preferred language
if (!empty($_GET['lang'])) {
    
    if (file_exists('assets/languages/' . $_GET['lang'] . '.php')) {
        $config['language'] = $_GET['lang'];
        $_SESSION['language'] = $_GET['lang'];
        
        if ($logged == true) {
            mysqli_query($dbConnect, "UPDATE " . DB_ACCOUNTS . " SET language='" . $_GET['lang'] . "' WHERE id=" . $user['id']);
        }
    }
}

require_once('assets/languages/' . $_SESSION['language'] . '.php');

// Removes session and unnecessary variables if user verification fails
if ($logged == false) {
    unset($_SESSION['user_id']);
    unset($user);
}