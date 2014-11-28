<?php
function FA_verifyLogin() {
    global $config;
    require_once('../assets/settings/admin.php');
    
    if (!empty($_SESSION['admin_id']) && !empty($_SESSION['admin_password'])) {
        
        if ($_SESSION['admin_id'] == $config['admin_username'] && $_SESSION['admin_password'] == $config['admin_password']) {
            return true;
        }
    }
}
