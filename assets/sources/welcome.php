<?php
if (!empty($_COOKIE['sk_u_i']) && !empty($_COOKIE['sk_u_p'])) {
    $u_i = FA_secureEncode($_COOKIE['sk_u_i']);
    $u_p = FA_secureEncode($_COOKIE['sk_u_p']);
    
    $_SESSION['user_id'] = $u_i;
    $_SESSION['user_pass'] = $u_p;
}

if ($logged == true) {
    header('Location: ' . FA_smoothLink('index.php?tab1=home'));
}

/* Core */
require_once("assets/includes/core.php");

/* Facebook Developer API */
require_once("assets/imports/facebook/facebook.php");

$fb_config = array(
    'appId' => $fb_app_id,
    'secret' => $fb_app_secret,
    'fileUpload' => false,
    'allowSignedRequest' => false,
);

$facebook = new Facebook($fb_config);
$params = array(
  'scope' => 'email',
  'redirect_uri' => $config['site_url'] . '/import.php?type=facebook'
);

$fb_login_url = $facebook->getLoginUrl($params);

$sk['fb_login_url'] = $fb_login_url;

$sk['content'] = FA_getPage('welcome/content');
