<?php

require('../assets/settings/admin.php');
require('../assets/settings/general.php');
require('../assets/settings/theme.php');
require('../assets/settings/ads.php');
$config['site_url'] = $site_url;
$config['theme_url'] = $site_url . '/themes/' . $config['theme'];

if (isset($_POST['update_site_settings']) && isset($_POST['keep_blank']) && empty($_POST['keep_blank']) && $logged_in == true) {
    $saved = false;
    
    if (isset($_POST['friends']) && !empty($_POST['site_name']) && !empty($_POST['site_title']) && !empty($_POST['site_email']) && isset($_POST['email_verification']) && isset($_POST['chat']) && isset($_POST['captcha']) && isset($_POST['language']) && isset($_POST['smooth_links']) && isset($_POST['censored_words'])) {
        $friends = SK_secureEncode($_POST['friends']);
        $site_name = SK_secureEncode($_POST['site_name']);
        $site_title = SK_secureEncode($_POST['site_title']);
        $email = SK_secureEncode($_POST['site_email']);
        $email_verification = SK_secureEncode($_POST['email_verification']);
        $chat = SK_secureEncode($_POST['chat']);
        $captcha = SK_secureEncode($_POST['captcha']);
        $language = SK_secureEncode($_POST['language']);
        $smooth_links = SK_secureEncode($_POST['smooth_links']);
        $censored_words = $_POST['censored_words'];
        $clear_follow_table = false;

        if ($config['friends'] != true && $friends == 1) {
            $clear_follow_table = true;
        } elseif ($config['friends'] == true && $friends != 1) {
            $clear_follow_table = true;
        }

        if ($friends == 1) {
            $friends = 'true';
        } else {
            $friends = 'false';
        }

        if ($email_verification == 1) {
            $email_verification = 'true';
        } else {
            $email_verification = 'false';
        }

        if ($chat == 1) {
            $chat = 'true';
        } else {
            $chat = 'false';
        }

        if ($captcha == 1) {
            $captcha = 'true';
        } else {
            $captcha = 'false';
        }

        if ($smooth_links == 1) {
            $smooth_links = 'true';
        } else {
            $smooth_links = 'false';
        }

        $story_character_limit = 0;
        $comment_character_limit = 0;
        $message_character_limit = 0;

        if (!empty($_POST['story_character_limit']) && is_numeric($_POST['story_character_limit']) && $_POST['story_character_limit'] > 0) {
            $story_character_limit = SK_secureEncode($_POST['story_character_limit']);
        }

        if (!empty($_POST['comment_character_limit']) && is_numeric($_POST['comment_character_limit']) && $_POST['comment_character_limit'] > 0) {
            $comment_character_limit = SK_secureEncode($_POST['comment_character_limit']);
        }

        if (!empty($_POST['message_character_limit']) && is_numeric($_POST['message_character_limit']) && $_POST['message_character_limit'] > 0) {
            $message_character_limit = SK_secureEncode($_POST['message_character_limit']);
        }

        $reg_req_birthday = 'false';
        $reg_req_currentcity = 'false';
        $reg_req_hometown = 'false';
        $reg_req_about = 'false';

        if (!empty($_POST['reg_req_birthday'])) {
            $reg_req_birthday = 'true';
        }

        if (!empty($_POST['reg_req_currentcity'])) {
            $reg_req_currentcity = 'true';
        }

        if (!empty($_POST['reg_req_hometown'])) {
            $reg_req_hometown = 'true';
        }

        if (!empty($_POST['reg_req_about'])) {
            $reg_req_about = 'true';
        }

        $file_content = '<?php
$config[\'friends\'] = ' . $friends . ';
$config[\'site_name\'] = \'' . $site_name . '\';
$config[\'site_title\'] = \'' . $site_title . '\';
$config[\'email\'] = \'' . $email . '\';
$config[\'email_verification\'] = ' . $email_verification . ';
$config[\'chat\'] = ' . $chat . ';
$config[\'captcha\'] = ' . $captcha . ';
$config[\'language\'] = \'' . $language . '\';
$config[\'smooth_links\'] = ' . $smooth_links . ';
$config[\'censored_words\'] = \'' . $censored_words . '\';
$config[\'reg_req_birthday\'] = ' . $reg_req_birthday . ';
$config[\'reg_req_currentcity\'] = ' . $reg_req_currentcity . ';
$config[\'reg_req_hometown\'] = ' . $reg_req_hometown . ';
$config[\'reg_req_about\'] = ' . $reg_req_about . ';
$config[\'story_character_limit\'] = ' . $story_character_limit . ';
$config[\'comment_character_limit\'] = ' . $comment_character_limit . ';
$config[\'message_character_limit\'] = ' . $message_character_limit . ';
';
        
        $process = file_put_contents('../assets/settings/general.php', $file_content);
        
        if ($process) {
            $saved = true;

            if ($clear_follow_table == true) {
                mysqli_query($dbConnect, "DELETE FROM " . DB_FOLLOWERS);
            }
        }
    }
    
    if ($saved == true) {
        $post_message = '<div class="post-success">Website settings updated!</div>';
    } else {
        $post_message = '<div class="post-failure">Failed to save changes. Please do not keep required fields empty.</div>';
    }
}
