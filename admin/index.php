<?php
session_start();
date_default_timezone_set('Europe/London');

require('../assets/includes/tables.php');
require('../assets/includes/config.php');

$dbConnect = mysqli_connect($sql_host, $sql_user, $sql_pass, $sql_name);

$function_files = glob('functions/*.php');

foreach ($function_files as $func_file) {
    require($func_file);
}

$logged_in = false;

if (FA_verifyLogin()) {
    $logged_in = true;
}

$post_message = null;
$request_files = glob('requests/*.php');

foreach ($request_files as $req_file) {
    require($req_file);
}

if (!isset($_GET['tab1']) or empty($_GET['tab1'])) {
    $_GET['tab1'] = 'general_settings';
}

if ($_GET['tab1'] == "logout") {
    $logged_in = false;
    
    if (isset($_SESSION['admin_id'])) {
        unset($_SESSION['admin_id']);
    }
    
    if (isset($_SESSION['admin_password'])) {
        unset($_SESSION['admin_password']);
    }
}

require('../assets/settings/admin.php');
require('../assets/settings/general.php');
require('../assets/settings/theme.php');
require('../assets/settings/ads.php');
$config['site_url'] = $site_url;
$config['theme_url'] = $site_url . '/themes/' . $config['theme'];

$tab_files = glob('tabs/*.php');

foreach ($tab_files as $tab_file) {
    require($tab_file);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $config['site_name']; ?> - Admin Area</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link href="style/style.css" rel="stylesheet">
    <?php if (!$logged_in) { ?>
    <link href="style/welcome.css" rel="stylesheet">
    <?php } ?>
</head>
<body<?php if (!$logged_in) echo ' class="welcome"'; ?>>
    <div class="header-wrapper">
        <div class="header-content">
            <div class="float-left">
                <table border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="header-site-logo" align="left" valign="middle">
                        <a class="header-link" href="<?php echo $config['site_url']; ?>/">
                            <img src="<?php echo $config['theme_url']; ?>/logo.png" alt="Logo">
                        </a>
                    </td>
                </tr>
                </table>
            </div>
            <div class="float-clear"></div>
        </div>
    </div>
    
    <div class="page-wrapper">
        <div class="page-content">
            <?php
            if (!$logged_in) {
            admin_login();
            } else {
            $new_reports = mysqli_num_rows(mysqli_query($dbConnect, "SELECT * FROM " . DB_REPORTS . " WHERE active=1 AND status=0"));
            ?>
            <div class="float-left span25">
                <div class="list-container">
                    <div class="list-header">Menu</div>
                    <a href="?tab1=general_settings" class="list-wrapper">General Settings</a>
                    <a href="?tab1=user_settings" class="list-wrapper">User Settings</a>
                    <a href="?tab1=page_settings" class="list-wrapper">Page Settings</a>
                    <a href="?tab1=group_settings" class="list-wrapper">Group Settings</a>
                    <a href="?tab1=announcements" class="list-wrapper">Announcements</a>
                    <a href="?tab1=themes" class="list-wrapper">Themes</a>
                    <a href="?tab1=statistics" class="list-wrapper">Statistics</a>
                    <a href="?tab1=manage_users" class="list-wrapper">Manage Users</a>
                    <a href="?tab1=manage_pages" class="list-wrapper">Manage Pages</a>
                    <a href="?tab1=manage_groups" class="list-wrapper">Manage Groups</a>
                    <a href="?tab1=manage_reports" class="list-wrapper">Manage Reports <?php if ($new_reports > 0) echo '<span class="update-alert">' . $new_reports . '</span>'; ?></a>
                    <a href="?tab1=manage_ads" class="list-wrapper">Manage Ads</a>
                    <a href="?tab1=manage_admin_login" class="list-wrapper">Manage Admin Login</a>
                    <a href="?tab1=logout" class="list-wrapper">Log Out</a>
                </div>
            </div>
            <div class="float-right span75">
                <?php
                echo $post_message;
                
                switch($_GET['tab1']) {
                    case 'general_settings':
                    general_settings();
                    break;
                    
                    case 'user_settings':
                    user_settings();
                    break;
                    
                    case 'page_settings':
                    page_settings();
                    break;
                    
                    case 'group_settings':
                    group_settings();
                    break;
                    
                    case 'announcements':
                    announcements();
                    break;

                    case 'themes':
                    themes();
                    break;
                    
                    case 'statistics':
                    statistics();
                    break;
                    
                    case 'manage_users':
                    manage_users();
                    break;
                    
                    case 'manage_pages':
                    manage_pages();
                    break;
                    
                    case 'manage_groups':
                    manage_groups();
                    break;
                    
                    case 'manage_reports':
                    manage_reports();
                    break;
                    
                    case 'manage_ads':
                    manage_ads();
                    break;
                    
                    case 'manage_admin_login':
                    manage_admin_login();
                    break;
                    
                    case 'edit_user':
                    edit_user();
                    break;
                    
                    case 'delete_user':
                    delete_user();
                    break;
                    
                    case 'edit_group':
                    edit_group();
                    break;
                    
                    case 'delete_group':
                    delete_group();
                    break;
                    
                    case 'edit_page':
                    edit_page();
                    break;
                    
                    case 'delete_page':
                    delete_page();
                    break;
                }
                ?>
            </div>
            <div class="float-clear"></div>
            <?php } ?>
        </div>
    </div>
    <div class="footer-wrapper">
        <div class="footer-content" align="center">
            <div class="footer-line">
                Copyright &#169; <?php echo date('Y') . ' ' . $config['site_name']; ?>. All rights reserved. Powered by <a href="http://pradyogeek.com/">Pradyogeek</a>.
            </div>
        </div>
    </div>
</body>