<?php
if (!empty($_POST['admin_login']) && isset($_POST['keep_blank']) && empty($_POST['keep_blank'])) {
    if (!empty($_POST['admin_username']) && !empty($_POST['admin_password'])) {
        $admin_username = SK_secureEncode($_POST['admin_username']);
        $admin_password = SK_secureEncode($_POST['admin_password']);
        $md5_admin_password = md5($admin_password);
        
        require('../assets/settings/admin.php');
        
        if ($admin_username == $config['admin_username'] && $md5_admin_password == $config['admin_password']) {
            $_SESSION['admin_id'] = $admin_username;
            $_SESSION['admin_password'] = $md5_admin_password;
            header('Location: ?logged_in');
        }
    }
}
