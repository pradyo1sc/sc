<?php
/* * * * * * * * * * * * * *
Gang Group
Copyright (c) 2014

Author Fa
Date: 27/Nov/2014
* * * * * * * * * * * * * */

require_once('assets/includes/core.php');

$t = '';
$a = '';

if (isset($_GET['t'])) {
    $t = SK_secureEncode($_GET['t']);
}

if (isset($_GET['a'])) {
    $a = SK_secureEncode($_GET['a']);
}

$data = array(
    'status' => 417
);

// Login & Verify User
if ($t == "login") {
    $data['error_message'] = $lang['error_empty_login'];
    
    if (!empty($_POST['login_id']) && !empty($_POST['login_password'])) {
        
        $login_id = SK_secureEncode($_POST['login_id']);
        $login_password = trim($_POST['login_password']);
        $login_password_md5 = md5($login_password);
        
        if (preg_match('/@/', $login_id)) {
            $db_query_part = "email='$login_id'";
        } elseif (preg_match('/^[0-9]+$/', $login_id)) {
            $db_query_part = "id=$login_id";
        } else {
            $db_query_part = "username='$login_id'";
        }
        
        $query_one = "SELECT id FROM " . DB_ACCOUNTS . " WHERE $db_query_part AND password='$login_password_md5' AND type='user' AND active=1";
        $sql_query_one = mysqli_query($dbConnect, $query_one);
        $data['error_message'] = $lang['error_bad_login'];
        
        if (($sql_numrows_one = mysqli_num_rows($sql_query_one)) == 1) {
            $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
            
            $query_two = "SELECT username,email_verified FROM " . DB_ACCOUNTS . " WHERE id=" . $sql_fetch_one['id'] . " AND password='$login_password_md5' AND type='user' AND active=1";
            $sql_query_two = mysqli_query($dbConnect, $query_two);
            
            if (($sql_numrows_two = mysqli_num_rows($sql_query_two)) == 1) {
                $sql_fetch_two = mysqli_fetch_assoc($sql_query_two);
                $continue = true;
                
                if ($config['email_verification'] == 1 && $sql_fetch_two['email_verified'] == 0) {
                    $continue = false;
                    $data['error_message'] = $lang['error_verify_email'];
                }
                
                if ($continue == true) {
                    $_SESSION['user_id'] = $sql_fetch_one['id'];
                    $_SESSION['user_pass'] = $login_password_md5;
                    
                    if (!empty($_POST['keep_logged_in']) && $_POST['keep_logged_in'] == 1) {
                        setcookie('sk_u_i', $_SESSION['user_id'], time() + (60 * 60 * 24 * 7));
                        setcookie('sk_u_p', $_SESSION['user_pass'], time() + (60 * 60 * 24 * 7));
                    }
                    
                    $data['status'] = 200;
                    $data['redirect_url'] = SK_smoothLink('index.php?tab1=home');
                }
            }
        }
    }
    
    header("Content-type: application/json");
    echo json_encode($data);
    mysqli_close($dbConnect);
    exit();
}

// Register User
if ($t == "register") {
    $proceed = false;
    $data['error_message'] = $lang['error_bad_captcha'];
    
    if ($config['captcha'] == false) {
        $proceed = true;
    } elseif (!empty($_POST['captcha']) && $_POST['captcha'] == $_SESSION['captcha_key']) {
        $proceed = true;
    }
    
    if ($proceed == true) {
        $data['error_message'] = $lang['error_empty_registration'];
        
        if (($register = SK_registerUser($_POST)) != false) {
            $register['verification_link'] = $config['site_url'] . '/?tab1=email-verification&email=' . $register['email'] . '&key=' . $register['email_verification_key'];
            $sk['mail'] = $register;
            
            $to = $register['email'];
            $subject = $config['site_name'] . ' - Email Verification';
            
            $headers = "From: " . $config['email'] . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            
            $message = SK_getPage('emails/email-verification');
            mail($to, $subject, $message, $headers);
            
            if ($config['email_verification'] == 0) {
                $_SESSION['user_id'] = $register['id'];
                $_SESSION['user_pass'] = md5(trim($_POST['password']));
                
                $data['status'] = 200;
                $data['redirect_url'] = SK_smoothLink('index.php?tab1=home');
            } else {
                $data['error_message'] = $lang['verification_email_sent'];
            }
        }
    }
    
    header("Content-type: application/json");
    echo json_encode($data);
    mysqli_close($dbConnect);
    exit();
}

// Forgot Password E-mail sender
if ($t == "forgot_password") {
    $data['message'] = $lang['password_reset_mail_unknown'];
    
    if (!empty($_POST['forgotpass_id']) && !SK_isLogged()) {
        $forgotpass_id = SK_secureEncode($_POST['forgotpass_id']);
        
        if (preg_match('/@/', $forgotpass_id)) {
            $db_query_part = "email='$forgotpass_id'";
        } elseif (preg_match('/^[0-9]+$/', $forgotpass_id)) {
            $db_query_part = "id=$forgotpass_id";
        } else {
            $db_query_part = "username='$forgotpass_id'";
        }
        
        $query_one = "SELECT id,password,username,email,name FROM " . DB_ACCOUNTS . " WHERE $db_query_part AND type='user' AND active=1";
        $sql_query_one = mysqli_query($dbConnect, $query_one);
        
        if (($sql_numrows_one = mysqli_num_rows($sql_query_one)) == 1) {
            $forgotpass_user = mysqli_fetch_assoc($sql_query_one);
            
            if (isset($forgotpass_user['id'])) {
                $forgotpass_user['url'] = SK_smoothLink('index.php?tab1=welcome&tab2=password_reset&id=' . $forgotpass_user['id'] . '_' . $forgotpass_user['password']);
                $sk['mail'] = $forgotpass_user;
                $to = $forgotpass_user['email'];
                $subject = $config['site_name'] . ' - Password Reset';
                
                $headers = "From: " . $config['email'] . "\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                
                $message = SK_getPage('emails/password-reset-email');
                
                if (mail($to, $subject, $message, $headers)) {
                    $data = array(
                        'status' => 200,
                        'message' => $lang['password_reset_mail_confirm']
                    );
                }
            }
        }
    }
    
    header("Content-type: application/json");
    echo json_encode($data);
    mysqli_close($dbConnect);
    exit();
}

if ($t == "reset_password") {
    
    if (!empty($_POST['pr_password']) && !empty($_POST['pr_token']) && !SK_isLogged()) {
        $password = trim($_POST['pr_password']);
        $token = SK_isValidPasswordResetToken($_POST['pr_token']);
        
        if ($token != false && is_array($token)) {
            $md5_password = md5($password);
            
            $query_one = "UPDATE " . DB_ACCOUNTS . " SET password='$md5_password' WHERE id=" . $token['id'];
            $sql_query_one = mysqli_query($dbConnect, $query_one);
            
            if ($sql_query_one) {
                $data = array(
                    'status' => 200,
                    'url' => SK_smoothLink('index.php?tab1=welcome')
                );
            }
        }
    }
    
    header("Content-type: application/json");
    echo json_encode($data);
    mysqli_close($dbConnect);
    exit();
}

// Check username availability
if ($t == "username") {
    
    if ($a == "check") {
        
        if (!empty($_GET['q'])) {
            $timeline_id = 0;
            
            if (!empty($_GET['timeline_id']) && is_numeric($_GET['timeline_id'])) {
                $timeline_id = $_GET['timeline_id'];
            }
            
            if (($status = SK_getUsernameStatus($_GET['q'], $timeline_id))) {
                $data = array(
                    'status' => $status
                );
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
}

// Search requests
if ($t == "search") {
    
    // Header search
    if ($a == "header") {
        $search_query = '';
        $html = '';
        
        if (!empty($_GET['q'])) {
            $search_query = SK_secureEncode($_GET['q']);
        }
        
        $hashtag_results = SK_getHashtagSearch($search_query, 4);
        $user_results = SK_getSearch($search_query, 0, 7);
        
        if (is_array($hashtag_results)) {
            
            foreach ($hashtag_results as $sk['hashtag']) {
                $sk['hashtag']['url'] = SK_smoothLink('index.php?tab1=hashtag&query=' . $sk['hashtag']['tag']);
                $html .= SK_getPage('header/hashtag-result');
            }
        }
        
        if (is_array($user_results)) {
            
            foreach ($user_results as $sk['list']) {
                $html .= SK_getPage('header/search-result');
            }
        }
        
        $data = array(
            'status' => 200,
            'html' => $html,
            'link' => SK_smoothLink('index.php?tab1=search&query=' . $search_query)
        );
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    if (!SK_isLogged()) {
        mysqli_close($dbConnect);
        exit("Please log in to continue!");
    }
    
    // Search onlines
    if ($a == "online") {
        $search_query = '';
        $html = '';
        
        if (!empty($_GET['q'])) {
            $search_query = SK_secureEncode($_GET['q']);
        }
        
        foreach (SK_getOnlines(0, $search_query) as $sk['list']) {
            $html .= SK_getPage('chat/online-column');
        }
        
        $data = array(
            'status' => 200,
            'html' => $html
        );
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Search suggestions
    if ($a == "suggestions") {
        $search_query = '';
        $html = '';
        
        if (!empty($_GET['q'])) {
            $search_query = SK_secureEncode($_GET['q']);
        }
        
        foreach (SK_getFollowSuggestions($search_query) as $sk['suggestion']) {
            $html .= SK_getPage('home/suggestion-wrap');
        }
        
        $data = array(
            'status' => 200,
            'html' => $html
        );
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Search followers
    if ($a == "followers") {
        $search_query = '';
        $html = '';
        
        if (!empty($_GET['q'])) {
            $search_query = SK_secureEncode($_GET['q']);
        }
        
        foreach (SK_getFollowers($user['id'], $search_query) as $sk['list']) {
            $html .= SK_getPage('lists/followers');
        }
        
        $data = array(
            'status' => 200,
            'html' => $html
        );
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    if ($a == "recipients") {
        $search_query = '';
        $html = '';
        
        if (!empty($_GET['q'])) {
            $search_query = SK_secureEncode($_GET['q']);
        }
        
        foreach (SK_getMessageRecipients(0, $search_query) as $sk['recipient']) {
            $html .= SK_getPage('message/recipient-list');
        }
        
        $data = array(
            'status' => 200,
            'html' => $html
        );
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
}

if ($t == "announcements") {

    if ($a == "read") {
        $query_one = "SELECT id FROM " . DB_ANNOUNCEMENTS . " WHERE id NOT IN (SELECT announcement_id FROM " . DB_ANNOUNCEMENT_VIEWS . " WHERE account_id=" . $user['id'] . ")";
        $sql_query_one = mysqli_query($dbConnect, $query_one);

        while ($sql_fetch_one = mysqli_fetch_assoc($sql_query_one)) {
            $query_two = "INSERT INTO " . DB_ANNOUNCEMENT_VIEWS . " (account_id,announcement_id) VALUES (" . $user['id'] . "," . $sql_fetch_one['id'] . ")";
            $sql_query_two = mysqli_query($dbConnect, $query_two);
        }
    }
}

// Posts requests [story, comment]
if ($t == "post") {
    
    // Filter stories
    if ($a == "filter") {
        $html = '';
        $data_array = array();
        
        $data_array['type'] = 'all';
        $data_array['limit'] = 10;
        $data_array['exclude_activity'] = false;
        
        if (!empty($_GET['type'])) {
            $data_array['type'] = $_GET['type'];
        }
        
        if (!empty($_GET['after_id']) && is_numeric($_GET['after_id'])) {
            $data_array['after_post_id'] = $_GET['after_id'];
        } elseif (!empty($_GET['before_id']) && is_numeric($_GET['before_id'])) {
            $data_array['before_post_id'] = $_GET['before_id'];
        }
        
        if (!empty($_GET['timeline_id']) && is_numeric($_GET['timeline_id'])) {
            $data_array['publisher_id'] = $_GET['timeline_id'];
        }

        if (isset($_GET['exclude_activity'])) {
            $data_array['exclude_activity'] = true;
        }
        
        $stories = SK_getStories($data_array);
        
        if (is_array($stories) && count($stories) > 0) {
            
            foreach ($stories as $sk['story']) {
                $html .= SK_getPage('story/content');
            }
        }
        
        if (!empty($html)) {
            $data = array(
                'status' => 200,
                'html' => $html
            );
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // New story
    if ($a == "new" && SK_isLogged()) {
        $parameters = array(
            'timeline_id',
            'recipient_id',
            'text',
            'soundcloud_title',
            'soundcloud_uri',
            'youtube_title',
            'youtube_video_id',
            'google_map_name'
        );
        $array = array();
        $array['type'] = 'story';
        
        foreach ($parameters as $v) {
            
            if (!empty($_POST[$v])) {
                $array[$v] = $_POST[$v];
            }
        }
        
        if (isset($_FILES['photos']['name'])) {
            $array['photos'] = $_FILES['photos'];
        }
        
        $post_id = SK_registerPost($array);
        
        if (!empty($post_id)) {
            $sk['story'] = SK_getStory($post_id);
            $html = SK_getPage('story/content');
            
            $data = array(
                'status' => 200,
                'html' => $html
            );
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Post-specific requests
    if (!empty($_GET['post_id']) && is_numeric($_GET['post_id']) && $_GET['post_id'] > 0) {
        $post_id = SK_secureEncode($_GET['post_id']);
        $query_one = "SELECT timeline_id,recipient_id,media_id,type1,type2 FROM " . DB_POSTS . " WHERE id=$post_id AND type1='story' AND type2 IN ('comment','none') AND active=1";
        $sql_query_one = mysqli_query($dbConnect, $query_one);
        
        if (mysqli_num_rows($sql_query_one) != 1) {
            mysqli_close($dbConnect);
            exit();
        }
        
        $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
        $sql_fetch_one['type'] = 'story';
        
        if ($sql_fetch_one['type2'] == "comment") {
            $sql_fetch_one['type'] = 'comment';
        }
        
        if (isset($sql_fetch_one['timeline_id']) && $sql_fetch_one['timeline_id'] > 0) {
            
            if ($a == "lightbox" && $sql_fetch_one['media_id'] > 0 && $sql_fetch_one['type'] == "story") {
                $sk['lb_story'] = SK_getStory($post_id);

                if (is_array($sk['lb_story'])) {
                    $html = SK_getPage('lightbox/content');
                    
                    if (!empty($html)) {
                        $data = array(
                            'status' => 200,
                            'html' => $html
                        );
                    }
                }
                
                header("Content-type: application/json");
                echo json_encode($data);
                mysqli_close($dbConnect);
                exit();
            }

            // Like post
            if ($a == "like" && SK_isLogged()) {
                SK_registerPostLike($post_id);
                $data = array(
                    'status' => 200,
                    'button_html' => SK_getPostLikeButton($post_id),
                    'activity_html' => SK_getPostLikeActivityButton($post_id)
                );
                
                header("Content-type: application/json");
                echo json_encode($data);
                
                if (SK_isPostLiked($post_id)) {
                    $notification_data_array = array(
                        'recipient_id' => $sql_fetch_one['timeline_id'],
                        'post_id' => $post_id,
                        'type' => 'like',
                        'url' => 'index.php?tab1=story&id=' . $post_id
                    );
                    SK_registerNotification($notification_data_array);
                }
                
                mysqli_close($dbConnect);
                exit();
            }
            
            // Share post
            if ($a == "share" && SK_isLogged()) {
                SK_registerPostShare($post_id);
                $data = array(
                    'status' => 200,
                    'button_html' => SK_getPostShareButton($post_id),
                    'activity_html' => SK_getPostShareActivityButton($post_id)
                );
                
                header("Content-type: application/json");
                echo json_encode($data);
                
                if (SK_isPostShared($post_id)) {
                    $notification_data_array = array(
                        'recipient_id' => $sql_fetch_one['timeline_id'],
                        'post_id' => $post_id,
                        'type' => 'share',
                        'url' => 'index.php?tab1=story&id=' . $post_id
                    );
                    SK_registerNotification($notification_data_array);
                }
                
                mysqli_close($dbConnect);
                exit();
            }
            
            // Follow post
            if ($a == "follow" && SK_isLogged()) {
                SK_registerPostFollow($post_id);
                $data = array(
                    'status' => 200,
                    'button_html' => SK_getPostFollowButton($post_id),
                    'activity_html' => SK_getPostFollowActivityButton($post_id)
                );
                
                header("Content-type: application/json");
                echo json_encode($data);
                mysqli_close($dbConnect);
                exit();
            }
            
            // Comment on post
            if ($a == "comment" && $sql_fetch_one['type'] == "story" && !empty($_POST['text']) && SK_isLogged()) {
                
                $timeline_id = $user['id'];
                
                if (!empty($_POST['timeline_id']) && is_numeric($_POST['timeline_id']) && $_POST['timeline_id'] > 0) {
                    $timeline_id = $_POST['timeline_id'];
                }
                
                $return_data = SK_registerPostComment($post_id, $timeline_id, $_POST['text']);
                
                if (isset($return_data) && is_numeric($return_data)) {
                    $sk['comment'] = SK_getComment($return_data);
                    $html = SK_getPage('comment/content');
                    $data = array(
                        'status' => 200,
                        'html' => $html,
                        'activity_html' => SK_getPostCommentActivityButton($post_id)
                    );
                    $notification_data_array = array(
                        'recipient_id' => $sql_fetch_one['timeline_id'],
                        'post_id' => $post_id,
                        'type' => 'comment',
                        'url' => 'index.php?tab1=story&id=' . $post_id
                    );
                    SK_registerNotification($notification_data_array);
                }
                
                header("Content-type: application/json");
                echo json_encode($data);
                mysqli_close($dbConnect);
                exit();
            }
            
            // Load old comments
            if ($a == "load_all_comments") {
                $html = '';
                
                foreach (SK_getComments($post_id, SK_countPostComments($post_id)) as $sk['comment']) {
                    $html .= SK_getPage('comment/content');
                }
                
                $data = array(
                    'status' => 200,
                    'html' => $html
                );
                
                header("Content-type: application/json");
                echo json_encode($data);
                mysqli_close($dbConnect);
                exit();
            }
            
            // View post likes window (popup)
            if ($a == "like_window") {
                $sk['post']['id'] = $post_id;
                $html = SK_getPage('window/post-likes');
                
                if (!empty($html)) {
                    $data = array(
                        'status' => 200,
                        'html' => $html
                    );
                }
                
                header("Content-type: application/json");
                echo json_encode($data);
                mysqli_close($dbConnect);
                exit();
            }
            
            // View post shares window
            if ($a == "share_window" && $sql_fetch_one['type'] == "story") {
                $sk['post']['id'] = $post_id;
                $html = SK_getPage('window/post-shares');
                
                if (!empty($html)) {
                    $data = array(
                        'status' => 200,
                        'html' => $html
                    );
                }
                
                header("Content-type: application/json");
                echo json_encode($data);
                mysqli_close($dbConnect);
                exit();
            }
            
            // View post delete window
            if ($a == "delete_window" && SK_isLogged()) {
                $sk['post']['id'] = $post_id;
                $html = SK_getPage('window/remove-post');
                
                if (!empty($html)) {
                    $data = array(
                        'status' => 200,
                        'html' => $html
                    );
                }
                
                header("Content-type: application/json");
                echo json_encode($data);
                mysqli_close($dbConnect);
                exit();
            }
            
            if ($a == "report" && SK_isLogged()) {
                
                if (!SK_isPostReported($post_id)) {
                    $query_three = "INSERT INTO " . DB_REPORTS . " (active,post_id,reporter_id) VALUES (1,$post_id," . $user['id'] . ")";
                    $sql_query_three = mysqli_query($dbConnect, $query_three);
                    
                    if ($sql_query_three) {
                        $data = array(
                            'status' => 200
                        );
                    }
                }
                
                header("Content-type: application/json");
                echo json_encode($data);
                mysqli_close($dbConnect);
                exit();
            }
            
            if ($a == "delete" && SK_isLogged()) {
                $continue = false;
                $post_timeline = SK_getUser($sql_fetch_one['timeline_id']);
                
                if (isset($post_timeline['id'])) {
                    
                    if ($post_timeline['type'] == "user" && $post_timeline['id'] == $user['id']) {
                        $continue = true;
                    } elseif ($post_timeline['type'] == "page" && SK_isPageAdmin($post_timeline['id'])) {
                        $continue = true;
                    } elseif ($post_timeline['type'] == "group" && SK_isGroupAdmin($post_timeline['id'])) {
                        $continue = true;
                    }
                }
                
                $post_recipient = SK_getUser($sql_fetch_one['recipient_id']);
                
                if (isset($post_recipient['id'])) {
                    
                    if ($post_recipient['type'] == "user" && $post_recipient['id'] == $user['id']) {
                        $continue = true;
                    } elseif ($post_recipient['type'] == "page" && SK_isPageAdmin($post_recipient['id'])) {
                        $continue = true;
                    } elseif ($post_recipient['type'] == "group" && SK_isGroupAdmin($post_recipient['id'])) {
                        $continue = true;
                    }
                }
                
                if ($continue == true) {
                
                    if ($sql_fetch_one['type'] == "story") {
                        $query_two = "DELETE FROM " . DB_POSTS . " WHERE post_id=$post_id";
                        $sql_query_two = mysqli_query($dbConnect, $query_two);
                        $sql_fetch_three = SK_getMedia($sql_fetch_one['media_id']);

                        if (is_array($sql_fetch_three)) {

                            if ($sql_fetch_three['type'] == "album") {

                                if ($sql_fetch_three['temp'] == 1) {
                                    $sql_fetch_four = SK_getAlbumPhotos($sql_fetch_three['id']);

                                    foreach ($sql_fetch_four as $photo) {
                                        $query_five = "DELETE FROM " . DB_MEDIA . " WHERE id=" . $photo['id'] . " AND type='photo'";
                                        $sql_query_five = mysqli_query($dbConnect, $query_five);

                                        $query_six = "DELETE FROM " . DB_POSTS . " WHERE media_id=" . $photo['id'];
                                        $sql_query_six = mysqli_query($dbConnect, $query_six);

                                        $dirimages = glob($photo['url'] . '*');

                                        foreach ($dirimages as $dirimg) {
                                            unlink($dirimg);
                                        }
                                    }
                                }
                            } else {
                                $query_four = "DELETE FROM " . DB_MEDIA . " WHERE id=" . $sql_fetch_three['id'] . " AND type='photo'";
                                $sql_query_four = mysqli_query($dbConnect, $query_four);

                                $query_five = "DELETE FROM " . DB_POSTS . " WHERE media_id=" . $sql_fetch_three['id'];
                                $sql_query_five = mysqli_query($dbConnect, $query_five);

                                $dirimages = glob($sql_fetch_three['url'] . '*');

                                foreach ($dirimages as $dirimg) {
                                    unlink($dirimg);
                                }
                            }
                        }
                    } elseif ($sql_fetch_one['type'] == "comment") {
                        $query_two = "DELETE FROM " . DB_POSTS . " WHERE id=$post_id";
                        $sql_query_two = mysqli_query($dbConnect, $query_two);
                    }
                    
                    if (isset($sql_query_two) && $sql_query_two) {
                        $data = array(
                            'status' => 200,
                            'post_type' => $sql_fetch_one['type']
                        );
                    }
                }
                
                header("Content-type: application/json");
                echo json_encode($data);
                mysqli_close($dbConnect);
                exit();
            }
        }
    }
}

if (!SK_isLogged()) {
    mysqli_close($dbConnect);
    exit("Please log in to continue!");
}

// Ajax intervals
if ($t == "interval") {
    $data['status'] = 200;
    $data['notifications'] = SK_countNotifications(
        array(
            'unread' => true
        )
    );
    $data['messages'] = SK_countMessages(
        array(
            'new' => true
        )
    );
    $data['follow_requests'] = SK_countFollowRequests();
    $data['chat'] = false;
    
    if (!empty($_GET['chat_recipient_id']) && is_numeric($_GET['chat_recipient_id']) && $_GET['chat_recipient_id'] > 0) {
        $data['chat'] = true;
        $html = '';
        $recipient_id = SK_secureEncode($_GET['chat_recipient_id']);
        $recipient = SK_getUser($recipient_id);
        
        if (isset($recipient['id'])) {
            $data['chat_recipient_online'] = $recipient['online'];
            $query_array = array(
                'new' => true,
                'recipient_id' => $recipient['id']
            );
            $messages_num = SK_countMessages($query_array);
            
            if (count($messages_num) > 0) {
                $messages = SK_getMessages($query_array);
                
                if (is_array($messages)) {
                    foreach ($messages as $sk['message']) {
                        $html .= SK_getPage('chat/incoming-text');
                    }
                }
                
                $data['chat_messages'] = $html;
            }
        }
    }
    
    header("Content-type: application/json");
    echo json_encode($data);
    mysqli_close($dbConnect);
    exit();
}

// Chat requests
if ($t == "chat" && $config['chat'] == 1) {
    
    // Send chat message
    if ($a == "send_message") {
        $array = array();
        $array['type'] = 'message';
        $parameters = array(
            'recipient_id',
            'text'
        );
        
        foreach ($parameters as $v) {
            
            if (!empty($_POST[$v])) {
                $array[$v] = $_POST[$v];
            }
        }
        
        $message_id = SK_registerPost($array);
        
        if (!empty($message_id)) {
            $html = '';
            $message = SK_getMessages(
                array(
                    'message_id' => $message_id,
                    'recipient_id' => $array['recipient_id']
                )
            );
            
            foreach ($message as $sk['message']) {
                $html .= SK_getPage('chat/outgoing-text');
            }
            
            $data = array(
                'status' => 200,
                'html' => $html
            );
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Load chat messages
    if ($a == "load_messages") {
        
        if (!empty($_GET['recipient_id']) && is_numeric($_GET['recipient_id']) && $_GET['recipient_id'] > 0) {
            $recipient_id = SK_secureEncode($_GET['recipient_id']);
            $recipient = SK_getUser($recipient_id);
            
            if (isset($recipient['id'])) {
                $sk['chat']['recipient'] = $recipient;
                $data = array(
                    'status' => 200,
                    'html' => SK_getPage('chat/content'),
                    'online' => $recipient['online']
                );
                $_SESSION['chat_recipient_id'] = $recipient['id'];
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    if ($a == "load_new_messages") {
        
        if (!empty($_GET['recipient_id']) && is_numeric($_GET['recipient_id']) && $_GET['recipient_id'] > 0) {
            $html = '';
            $recipient_id = SK_secureEncode($_GET['recipient_id']);
            $recipient = SK_getUser($recipient_id);
            
            if (isset($recipient['id'])) {
                $query_array = array(
                    'new' => true,
                    'recipient_id' => $recipient['id']
                );
                $messages_num = SK_countMessages($query_array);
                
                if ($messages_num > 0) {
                    $messages = SK_getMessages($query_array);
                    
                    if (is_array($messages) && count($messages) > 0) {
                        
                        foreach ($messages as $sk['message']) {
                            $html .= SK_getPage('chat/incoming-text');
                        }
                    }
                    
                    $data = array(
                        'status' => 200,
                        'html' => $html
                    );
                }
                
                $data['online'] = $recipient['online'];
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Close chat
    if ($a == "close") {
        unset($_SESSION['chat_recipient_id']);
        mysqli_close($dbConnect);
        exit();
    }
}

// User requests
if ($t == "user") {
    
    // Update user settings
    if ($a == "settings") {
        
        if (SK_updateTimeline($_POST)) {
            $data = array(
                'status' => 200
            );
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Accept follow request
    if ($a == "accept_request") {
        
        if (!empty($_POST['follower_id']) && is_numeric($_POST['follower_id']) && $_POST['follower_id'] > 0) {
            $follower_id = SK_secureEncode($_POST['follower_id']);
            
            if (SK_isFollowRequested($user['id'], $follower_id)) {
                $query = "UPDATE " . DB_FOLLOWERS . " SET active=1 WHERE follower_id=$follower_id AND following_id=" . $user['id'] . " AND active=0";
                $sql_query = mysqli_query($dbConnect, $query);
                
                if ($sql_query) {

                    if ($config['friends'] == true) {
                        $query_two = "INSERT INTO " . DB_FOLLOWERS . " (active,follower_id,following_id,time) VALUES (1," . $user['id'] . ",$follower_id," . time() . ")";
                        $sql_query_two = mysqli_query($dbConnect, $query_two);

                        $notification_data_array = array(
                            'recipient_id' => $follower_id,
                            'type' => 'accepted_friend_request',
                            'text' => $lang['accepted_friend_request'],
                            'url' => 'index.php?tab1=timeline&id=' . $user['username']
                        );
                        SK_registerNotification($notification_data_array);
                    }

                    $data = array(
                        'status' => 200
                    );
                }
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Decline follow request
    if ($a == "decline_request") {
        
        if (!empty($_POST['follower_id']) && is_numeric($_POST['follower_id']) && $_POST['follower_id'] > 0) {
            $follower_id = SK_secureEncode($_POST['follower_id']);
            
            if (SK_isFollowRequested($user['id'], $follower_id)) {
                $query = "DELETE FROM " . DB_FOLLOWERS . " WHERE follower_id=$follower_id AND following_id=" . $user['id'] . " AND active=0";
                $sql_query = mysqli_query($dbConnect, $query);
                
                if ($sql_query) {
                    $data = array(
                        'status' => 200
                    );
                }
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
}

// Pages requests
if ($t == "page") {
    
    // Create page
    if ($a == "create") {
        
        if (!empty($_POST['page_name']) && !empty($_POST['page_username']) && !empty($_POST['page_about']) && !empty($_POST['page_category_id'])) {
            $registerArray = array(
                'name' => $_POST['page_name'],
                'username' => $_POST['page_username'],
                'about' => $_POST['page_about'],
                'category_id' => $_POST['page_category_id']
            );
            $register = SK_registerPage($registerArray);
            
            if ($register) {
                $group = SK_getUser($register['id']);
                $data = array(
                    'status' => 200,
                    'url' => $group['url']
                );
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Update page settings & info
    if ($a == "update") {
        
        if (!empty($_POST['timeline_id']) && is_numeric($_POST['timeline_id']) && $_POST['timeline_id'] > 0) {
            $params = $_POST;
            
            if (SK_updateTimeline($params)) {
                $timeline = SK_getUser($_POST['timeline_id']);
                
                if (isset($timeline['id']) && $timeline['type'] == "page") {
                    $data = array(
                        'status' => 200,
                        'url' => $timeline['url']
                    );
                }
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Add page admins
    if ($a == "add_admin") {
        
        if (!empty($_POST['page_id']) && is_numeric($_POST['page_id']) && !empty($_POST['admin_id']) && is_numeric($_POST['admin_id']) && !empty($_POST['admin_role'])) {
            $page_id = SK_secureEncode($_POST['page_id']);
            $admin_id = SK_secureEncode($_POST['admin_id']);
            $admin_role = SK_secureEncode($_POST['admin_role']);
            
            if (SK_registerPageAdmin($page_id, $admin_id, $admin_role)) {
                $data = array(
                    'status' => 200
                );
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Remove page admins
    if ($a == "remove_admin") {
        
        if (!empty($_POST['page_id']) && is_numeric($_POST['page_id']) && !empty($_POST['admin_id']) && is_numeric($_POST['admin_id'])) {
            $page_id = SK_secureEncode($_POST['page_id']);
            $admin_id = SK_secureEncode($_POST['admin_id']);
            
            if (SK_deletePageAdmin($page_id, $admin_id)) {
                $data = array(
                    'status' => 200
                );
                
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // List page categories
    if ($a == "categories") {
        
        if (!empty($_GET['category_id'])) {
            $category_id = SK_secureEncode($_GET['category_id']);
            
            $data = array(
                'status' => 200,
                'content' => SK_getPageCategories($category_id)
            );
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
}

// Group requests
if ($t == "group") {
    
    // Create group
    if ($a == "create") {
        
        if (!empty($_POST['group_name']) && !empty($_POST['group_username']) && !empty($_POST['group_about']) && !empty($_POST['group_privacy'])) {
            $registerArray = array(
                'name' => $_POST['group_name'],
                'username' => $_POST['group_username'],
                'about' => $_POST['group_about'],
                'privacy' => $_POST['group_privacy']
            );
            $register = SK_registerGroup($registerArray);
            
            if ($register) {
                $group = SK_getUser($register['id']);
                $data = array(
                    'status' => 200,
                    'url' => $group['url']
                );
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Update group settings & info
    if ($a == "update_settings") {
        
        if (!empty($_POST['timeline_id']) && is_numeric($_POST['timeline_id']) && $_POST['timeline_id'] > 0) {
            
            if (SK_updateTimeline($_POST)) {
                $group = SK_getUser($_POST['timeline_id']);
                
                if (!empty($group['id']) && $group['type'] == "group") {
                    $data = array(
                        'status' => 200,
                        'url' => $group['url']
                    );
                }
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Add group member
    if ($a == "add_member") {
        
        if (!empty($_POST['group_id']) && is_numeric($_POST['group_id']) && !empty($_POST['member_id']) && is_numeric($_POST['member_id'])) {
            
            if (SK_registerGroupMember($_POST['group_id'], $_POST['member_id'])) {
                $data = array(
                    'status' => 200
                );
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Add group admin
    if ($a == "add_admin") {
        
        if (!empty($_POST['group_id']) && is_numeric($_POST['group_id']) && !empty($_POST['admin_id']) && is_numeric($_POST['admin_id'])) {
            
            if (SK_registerGroupAdmin($_POST['group_id'], $_POST['admin_id'])) {
                $data = array(
                    'status' => 200
                );
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Accept group member
    if ($a == "accept_member") {
        
        if(!empty($_POST['group_id']) && is_numeric($_POST['group_id']) && !empty($_POST['member_id']) && is_numeric($_POST['member_id'])) {
            
            if (SK_registerGroupMember($_POST['group_id'], $_POST['member_id'])) {
                $data = array(
                    'status' => 200
                );
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Remove group member
    if ($a == "remove_member") {
        
        if (!empty($_POST['group_id']) && is_numeric($_POST['group_id']) && !empty($_POST['member_id']) && is_numeric($_POST['member_id'])) {
            
            if (SK_deleteGroupMember($_POST['group_id'], $_POST['member_id'])) {
                $data = array(
                    'status' => 200
                );
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Remove group admin
    if ($a == "remove_admin") {
        
        if (!empty($_POST['group_id']) && is_numeric($_POST['group_id']) && !empty($_POST['admin_id']) && is_numeric($_POST['admin_id'])) {
            
            if (SK_deleteGroupAdmin($_POST['group_id'], $_POST['admin_id'])) {
                $data = array(
                    'status' => 200
                );
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
}

// Album requests
if ($t == "album") {

    if ($a == "create") {

        if (!empty($_POST['album_name']) && isset($_FILES['photos']['name'])) {
            $album_name = SK_secureEncode($_POST['album_name']);
            $album_descr = '';
            $query_one = "INSERT INTO " . DB_MEDIA . " (timeline_id,active,name,descr,type,temp) VALUES (" . $user['id'] . ",1,'$album_name','$album_descr','album',0)";
            $sql_query_one = mysqli_query($dbConnect, $query_one);

            if (!empty($_POST['album_descr'])) {
                $album_descr = SK_secureEncode($_POST['album_descr']);
            }

            if ($sql_query_one) {
                $album_id = mysqli_insert_id($dbConnect);
                $photos_count = count($_FILES['photos']['name']);

                for ($i = 0; $i < $photos_count; $i++) {
                    $photo_param = array(
                        'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                        'name' => $_FILES['photos']['name'][$i],
                        'size' => $_FILES['photos']['size'][$i]
                    );
                    $photo_data = SK_registerMedia($photo_param, $album_id);
                    
                    if (!empty($photo_data['id'])) {
                        $query_two = "INSERT INTO " . DB_POSTS . " (active,hidden,media_id,time,timeline_id,type1,type2) VALUES (1,1," . $photo_data['id'] . "," . time() . "," . $user['id'] . ",'story','none')";
                        $sql_query_two = mysqli_query($dbConnect, $query_two);
                        
                        if ($sql_query_two) {
                            $media_story_id = mysqli_insert_id($dbConnect);
                            
                            mysqli_query($dbConnect, "UPDATE " . DB_POSTS . " SET post_id=id WHERE id=$media_story_id");
                            mysqli_query($dbConnect, "UPDATE " . DB_MEDIA . " SET post_id=$media_story_id WHERE id=" . $photo_data['id']);
                            SK_registerPostFollow($media_story_id);
                        }
                    }
                }

                $activity_text = 'added ' . $photos_count . ' new photos to the album [album]' . $album_id . '[/album]';
                $query_three = "INSERT INTO " . DB_POSTS . " (active,media_id,activity_text,time,timeline_id,type1,type2) VALUES (1,$album_id,'$activity_text'," . time() . "," . $user['id'] . ",'story','none')";
                $sql_query_three = mysqli_query($dbConnect, $query_three);
                
                if ($sql_query_three) {
                    $post_id = mysqli_insert_id($dbConnect);
                    $query_four = "UPDATE " . DB_POSTS . " SET post_id=$post_id WHERE id=$post_id";
                    $sql_query_four = mysqli_query($dbConnect, $query_four);
                    SK_registerPostFollow($post_id);
                    
                    if ($sql_query_four) {
                        $data = array(
                            'status' => 200,
                            'url' => SK_smoothLink('index.php?tab1=album&tab2=' . $album_id)
                            );
                    }
                }
            }
        }

    header("Content-type: application/json");
    echo json_encode($data);
    mysqli_close($dbConnect);
    exit();
    }

    if ($a == "upload") {

        if (!empty($_POST['album_id']) && isset($_FILES['photos']['name'])) {
            $album_id = SK_secureEncode($_POST['album_id']);
            
            if (is_numeric($album_id) && $album_id > 0) {
                $query_one = "SELECT COUNT(id) AS count FROM " . DB_MEDIA . " WHERE id=$album_id AND timeline_id=" . $user['id'] . " AND type='album' AND temp=0 AND active=1";
                $sql_query_one = mysqli_query($dbConnect, $query_one);
                
                if ($sql_query_one) {
                    $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
                    
                    if ($sql_fetch_one['count'] == 1) {
                        $photos_count = count($_FILES['photos']['name']);
                        $html = '';

                        for ($i = 0; $i < $photos_count; $i++) {
                            $photo_param = array(
                                'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                                'name' => $_FILES['photos']['name'][$i],
                                'size' => $_FILES['photos']['size'][$i]
                            );
                            $photo_data = SK_registerMedia($photo_param, $album_id);
                            
                            if (!empty($photo_data['id'])) {
                                $query_two = "INSERT INTO " . DB_POSTS . " (active,hidden,media_id,time,timeline_id,type1,type2) VALUES (1,1," . $photo_data['id'] . "," . time() . "," . $user['id'] . ",'story','none')";
                                $sql_query_two = mysqli_query($dbConnect, $query_two);
                                
                                if ($sql_query_two) {
                                    $media_story_id = mysqli_insert_id($dbConnect);
                                    
                                    mysqli_query($dbConnect, "UPDATE " . DB_POSTS . " SET post_id=id WHERE id=$media_story_id");
                                    mysqli_query($dbConnect, "UPDATE " . DB_MEDIA . " SET post_id=$media_story_id WHERE id=" . $photo_data['id']);
                                    SK_registerPostFollow($media_story_id);
                                }
                                $sk['photo'] = $photo_data;
                                $sk['photo']['post_id'] = $media_story_id;
                                $sk['photo']['timeline_id'] = $user['id'];

                                $html .= SK_getPage('album/photo');
                            }
                        }

                        $data = array(
                            'status' => 200,
                            'html' => $html
                        );

                        $query_three = "DELETE FROM " . DB_POSTS . " WHERE media_id=$album_id AND timeline_id=" . $user['id'];
                        $sql_query_three = mysqli_query($dbConnect, $query_three);
                        
                        $activity_text = 'added ' . $photos_count . ' new photos to the album [album]' . $album_id . '[/album]';
                        $query_four = "INSERT INTO " . DB_POSTS . " (active,media_id,activity_text,time,timeline_id,type1,type2) VALUES (1,$album_id,'$activity_text'," . time() . "," . $user['id'] . ",'story','none')";
                        $sql_query_four = mysqli_query($dbConnect, $query_four);
                        
                        if ($sql_query_four) {
                            $post_id = mysqli_insert_id($dbConnect);
                            $query_five = "UPDATE " . DB_POSTS . " SET post_id=$post_id WHERE id=$post_id";
                            $sql_query_five = mysqli_query($dbConnect, $query_five);
                            SK_registerPostFollow($post_id);
                        }
                    }
                }
            }
        }

    header("Content-type: application/json");
    echo json_encode($data);
    mysqli_close($dbConnect);
    exit();
    }

    // View album delete window
    if ($a == "delete_window" && SK_isLogged()) {
        
        if (!empty($_GET['album_id'])) {
            $album_id = SK_secureEncode($_GET['album_id']);
            
            if (is_numeric($album_id) && $album_id > 0) {
                $query_one = "SELECT COUNT(id) AS count FROM " . DB_MEDIA . " WHERE id=$album_id AND timeline_id=" . $user['id'] . " AND type='album' AND temp=0 AND active=1";
                $sql_query_one = mysqli_query($dbConnect, $query_one);
                
                if ($sql_query_one) {
                    $sk['album']['id'] = $album_id;
                    $html = SK_getPage('window/delete-album');
                    
                    if (!empty($html)) {
                        $data = array(
                            'status' => 200,
                            'html' => $html
                        );
                    }
                }
            }
        }
        
    header("Content-type: application/json");
    echo json_encode($data);
    mysqli_close($dbConnect);
    exit();
    }

    // Delete album
    if ($a == "delete" && SK_isLogged()) {
        
        if (!empty($_GET['album_id'])) {
            $album_id = SK_secureEncode($_GET['album_id']);
            
            if (is_numeric($album_id) && $album_id > 0) {
                $query_one = "SELECT COUNT(id) AS count FROM " . DB_MEDIA . " WHERE id=$album_id AND timeline_id=" . $user['id'] . " AND type='album' AND temp=0 AND active=1";
                $sql_query_one = mysqli_query($dbConnect, $query_one);
                
                if ($sql_query_one) {
                    $sql_fetch_two = SK_getAlbumPhotos($album_id);

                    foreach ($sql_fetch_two as $photo) {
                        $query_three = "DELETE FROM " . DB_MEDIA . " WHERE id=" . $photo['id'];
                        $sql_query_three = mysqli_query($dbConnect, $query_three);
                        $query_four = "DELETE FROM " . DB_POSTS . " WHERE media_id=" . $photo['id'];
                        $sql_query_four = mysqli_query($dbConnect, $query_four);
                        $dirimages = glob($photo['url'] . '*');

                        foreach ($dirimages as $dirimg) {
                            unlink($dirimg);
                        }
                    }

                    $query_five = "DELETE FROM " . DB_MEDIA . " WHERE id=$album_id";
                    $sql_query_five = mysqli_query($dbConnect, $query_five);

                    $query_six = "DELETE FROM " . DB_POSTS . " WHERE media_id=$album_id";
                    $sql_query_six = mysqli_query($dbConnect, $query_six);
                    
                    $data = array(
                        'status' => 200,
                        'location' => SK_smoothLink('index.php?tab1=timeline&id=' . $user['username'])
                    );
                }
            }
        }
        
    header("Content-type: application/json");
    echo json_encode($data);
    mysqli_close($dbConnect);
    exit();
    }
}

// Notification requests
if ($t == "notifications") {
    $data = array(
        'status' => 200,
        'html' => ''
    );
    $notifications = SK_getNotifications();
    
    if (count($notifications) > 0) {
        
        foreach ($notifications as $sk['notification']) {
            $data['html'] .= SK_getPage('header/notification-list');
            
            if ($sk['notification']['seen'] == 0) {
                $query = "UPDATE " . DB_NOTIFICATIONS . " SET seen=" . time() . " WHERE id=" . $sk['notification']['id'];
                $sql_query = mysqli_query($dbConnect, $query);
            }
        }
    }
    
    header("Content-type: application/json");
    echo json_encode($data);
    mysqli_close($dbConnect);
    exit();
}

// Messages requests
if ($t == "message") {
    
    // Send new message
    if ($a == "send_message") {
        
        if (!empty($_POST['timeline_id']) && is_numeric($_POST['timeline_id']) && $_POST['timeline_id'] > 0 && !empty($_POST['recipient_id']) && is_numeric($_POST['recipient_id']) && $_POST['recipient_id'] > 0) {
            $array = array();
            $array['type'] = 'message';
            $array['timeline_id'] = SK_secureEncode($_POST['timeline_id']);
            $array['recipient_id'] = SK_secureEncode($_POST['recipient_id']);
            $continue = false;
            
            if (isset($_FILES['photos']['name'])) {
                $array['photos'] = $_FILES['photos'];
                $continue = true;
            }
            
            if (!empty($_POST['text'])) {
                $array['text'] = $_POST['text'];
                $continue = true;
            }
            
            if ($continue == true) {
                $post_id = SK_registerPost($array);
                
                if (!empty($post_id)) {
                    $html = '';
                    $array_data = array(
                        'message_id' => $post_id,
                        'timeline_id' => $array['timeline_id'],
                        'recipient_id' => $array['recipient_id']
                    );
                    
                    foreach (SK_getMessages($array_data) as $sk['message']) {
                        $html .= SK_getPage('message/text-list');
                    }
                    
                    $data = array(
                        'status' => 200,
                        'html' => $html
                    );
                }
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Load messages
    if ($a == "load_messages") {
        $html = '';
        
        if (!empty($_GET['recipient_id']) && is_numeric($_GET['recipient_id']) && $_GET['recipient_id'] > 0) {
            $recipient_id = SK_secureEncode($_GET['recipient_id']);
            $recipient = SK_getUser($_GET['recipient_id']);
            $timeline_id = $user['id'];
            $reply_ability = true;
            
            if (!empty($_GET['timeline_id']) && is_numeric($_GET['timeline_id']) && $_GET['timeline_id'] > 0) {
                $timeline_id = SK_secureEncode($_GET['timeline_id']);
            }
            
            $messages = SK_getMessages(
                array(
                    'recipient_id' => $recipient_id,
                    'timeline_id' => $timeline_id
                )
            );
            
            if (!empty($recipient['id']) && $recipient['type'] == "user" && $recipient['message_privacy'] == "following") {
                
                if (!SK_isFollowing($timeline_id, $recipient['id'])) {
                    $reply_ability = false;
                }
            } elseif ($recipient['type'] == "page" && $recipient['message_privacy'] != "everyone") {
                $reply_ability = false;
            }
            
            if (is_array($messages)) {
                
                foreach ($messages as $sk['message']) {
                    $html .= SK_getPage('message/text-list');
                }
            }
            
            $data = array(
                'status' => 200,
                'html' => $html,
                'reply_ability_status' => $reply_ability
            );
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Load new messages
    if ($a == "load_new_messages") {
        $html = '';
        
        if (!empty($_GET['recipient_id'])) {
            $recipient_id = $_GET['recipient_id'];
            $timeline_id = $user['id'];
            
            if (!empty($_GET['timeline_id'])) {
                $timeline_id = $_GET['timeline_id'];
            }
            
            $messages = SK_getMessages(
                array(
                    'new' => true,
                    'recipient_id' => $recipient_id,
                    'timeline_id' => $timeline_id
                )
            );
            
            if (is_array($messages) && count($messages) > 0) {
                
                foreach ($messages as $sk['message']) {
                    $html .= SK_getPage('message/text-list');
                }
                
                $data = array(
                    'status' => 200,
                    'html' => $html
                );
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    if ($a == "load_previous_messages") {
        $html = '';
        
        if (!empty($_GET['recipient_id']) && !empty($_GET['before_message_id'])) {
            $recipient_id = $_GET['recipient_id'];
            $before_message_id = $_GET['before_message_id'];
            $timeline_id = $user['id'];
            
            if (!empty($_GET['timeline_id'])) {
                $timeline_id = $_GET['timeline_id'];
            }
            
            $messages = SK_getMessages(array(
                'recipient_id' => $recipient_id,
                'before_message_id' => $before_message_id,
                'timeline_id' => $timeline_id
            ));
            
            if (is_array($messages) && count($messages) > 0) {
                
                foreach ($messages as $sk['message']) {
                    $html .= SK_getPage('message/text-list');
                }
                
                $data = array(
                    'status' => 200,
                    'html' => $html
                );
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    if ($a == "delete") {
        
        if (!empty($_POST['message_id']) && is_numeric($_POST['message_id']) && $_POST['message_id'] > 0) {
            $message_id = SK_secureEncode($_POST['message_id']);
            $query_one = "SELECT id,timeline_id FROM " . DB_POSTS . " WHERE id=$message_id AND type1='message' AND active=1";
            $sql_query_one = mysqli_query($dbConnect, $query_one);
            
            if (mysqli_num_rows($sql_query_one) == 1) {
                $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
                $timeline_fetch = SK_getUser($sql_fetch_one['timeline_id']);
                $continue = false;

                if ($timeline_fetch['type'] == "user" && $timeline_fetch['id'] == $user['id']) {
                    $continue = true;
                } elseif ($timeline_fetch['type'] == "page" && SK_isPageAdmin($timeline_fetch['id'])) {
                    $continue = true;
                } elseif ($timeline_fetch['type'] == "group" && SK_isGroupAdmin($timeline_fetch['id'])) {
                    $continue = true;
                }

                if ($continue == true) {
                    $query_two = "DELETE FROM " . DB_POSTS . " WHERE id=$message_id AND type1='message' AND active=1";
                    $sql_query_two = mysqli_query($dbConnect, $query_two);
                    
                    if ($sql_query_two) {
                        $data = array(
                            'status' => 200
                        );
                    }
                }
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
}

// Avatar requests
if ($t == "avatar") {
    
    // Update avatar
    if ($a == "new" && !empty($_POST['timeline_id']) && is_numeric($_POST['timeline_id']) && $_POST['timeline_id'] > 0) {
        $continue = false;
        
        if ($_POST['timeline_id'] == $user['id']) {
            $timeline = $user;
            $continue = true;
        } else {
            $timeline_id = SK_secureEncode($_POST['timeline_id']);
            $timeline = SK_getUser($timeline_id);
        }
        
        if ($continue == false && isset($timeline['id'])) {
            
            if ($timeline['type'] == "page" && SK_isPageAdmin($timeline['id'])) {
                $continue = true;
            } elseif ($timeline['type'] == "group" && SK_isGroupAdmin($timeline['id'])) {
                $continue = true;
            }
        }
        
        if (isset($_FILES['image']['tmp_name']) && $continue == true) {
            $image = $_FILES['image'];
            $avatar = SK_registerMedia($image);
            
            if (isset($avatar['id'])) {
                $query_one = "UPDATE " . DB_ACCOUNTS . " SET avatar_id=" . $avatar['id'] . " WHERE id=" . $timeline['id'];
                $sql_query_one = mysqli_query($dbConnect, $query_one);
                
                if ($sql_query_one) {
                    $data = array(
                        'status' => 200,
                        'avatar_url' => $sk['config']['site_url'] . '/' . $avatar['url'] . '_100x100.' . $avatar['extension']
                    );
                }
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Update avatar
    if ($a == "post_upload" && !empty($_POST['timeline_id']) && is_numeric($_POST['timeline_id']) && $_POST['timeline_id'] > 0) {
        $continue = false;
        $processed = false;
        
        if ($_POST['timeline_id'] == $user['id']) {
            $timeline = $user;
            $continue = true;
        } else {
            $timeline_id = SK_secureEncode($_POST['timeline_id']);
            $timeline = SK_getUser($timeline_id);
        }
        
        if ($continue == false && isset($timeline['id'])) {
            
            if ($timeline['type'] == "page" && SK_isPageAdmin($timeline['id'])) {
                $continue = true;
            } elseif ($timeline['type'] == "group" && SK_isGroupAdmin($timeline['id'])) {
                $continue = true;
            }
        }
        
        if (isset($_FILES['image']['tmp_name']) && $continue == true) {
            $image = $_FILES['image'];
            $avatar = SK_registerMedia($image);
            
            if (isset($avatar['id'])) {
                $query_one = "UPDATE " . DB_ACCOUNTS . " SET avatar_id=" . $avatar['id'] . " WHERE id=" . $timeline['id'];
                $sql_query_one = mysqli_query($dbConnect, $query_one);
                
                if ($sql_query_one) {
                    $processed = true;
                }
            }
        }
        
        if (!empty($_POST['redirect_url'])) {
            $redirect_url = SK_secureEncode($_POST['redirect_url']);
            header('Location: ' . $redirect_url);
        } else {
            
            if ($processed == true) {
                header('Location: ' . SK_smoothLink('index.php?tab1=timeline&id=' . $timeline['username']));
            } else {
                header('Location: ' . SK_smoothLink('index.php?tab1=settings&tab2=avatar'));
            }
        }
    }
}

// Cover requests
if ($t == "cover") {
    
    // Update cover
    if ($a == "new" && isset($_FILES['image']['tmp_name'])) {
        $cover_image = $_FILES['image'];
        $continue = false;

        if (!empty($_POST['timeline_id'])) {
            
            if (is_numeric($_POST['timeline_id']) && $_POST['timeline_id'] > 0) {
                $timeline_id = SK_secureEncode($_POST['timeline_id']);
                $timeline = SK_getUser($timeline_id);
                
                if (isset($timeline['id'])) {
                    $timeline_id = $timeline['id'];
                    
                    if ($timeline['id'] == $user['id']) {
                        $continue = true;
                    } elseif ($timeline['type'] == "page" && SK_isPageAdmin($timeline['id'])) {
                        $continue = true;
                    } elseif ($timeline['type'] == "group" && SK_isGroupAdmin($timeline['id'])) {
                        $continue = true;
                    }
                }
            }
        }
        
        if ($continue == true) {
            $cover_data = SK_registerCoverImage($cover_image);
            
            if (isset($cover_data['id'])) {
                $query = "UPDATE " . DB_ACCOUNTS . " SET cover_id=" . $cover_data['id'] . ",cover_position=0 WHERE id=" . $timeline_id;
                $sql_query = mysqli_query($dbConnect, $query);
                
                if ($sql_query) {
                    $data = array(
                        'status' => 200,
                        'cover_url' => $sk['config']['site_url'] . '/' . $cover_data['cover_url'],
                        'actual_cover_url' => $sk['config']['site_url'] . '/' . $cover_data['url']. '.' . $cover_data['extension']
                    );
                }
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // Update cover
    if ($a == "post_upload" && isset($_FILES['image']['tmp_name'])) {
        $cover_image = $_FILES['image'];
        $continue = false;
        $processed = false;
        
        if (!empty($_POST['timeline_id'])) {
            
            if (is_numeric($_POST['timeline_id']) && $_POST['timeline_id'] > 0) {
                $timeline_id = SK_secureEncode($_POST['timeline_id']);
                $timeline = SK_getUser($timeline_id);
                
                if (isset($timeline['id'])) {
                    $timeline_id = $timeline['id'];
                    
                    if ($timeline['id'] == $user['id']) {
                        $continue = true;
                    } elseif ($timeline['type'] == "page" && SK_isPageAdmin($timeline['id'])) {
                        $continue = true;
                    } elseif ($timeline['type'] == "group" && SK_isGroupAdmin($timeline['id'])) {
                        $continue = true;
                    }
                }
            }
        }
        
        if ($continue == true) {
            $cover_data = SK_registerCoverImage($cover_image);
            
            if (isset($cover_data['id'])) {
                $query = "UPDATE " . DB_ACCOUNTS . " SET cover_id=" . $cover_data['id'] . ",cover_position=0 WHERE id=" . $timeline_id;
                $sql_query = mysqli_query($dbConnect, $query);
                
                if ($sql_query) {
                    $processed = true;
                }
            }
        }
        
        if (!empty($_POST['redirect_url'])) {
            $redirect_url = SK_secureEncode($_POST['redirect_url']);
            header('Location: ' . $redirect_url);
        } else {
            
            if ($processed == true) {
                header('Location: ' . SK_smoothLink('index.php?tab1=timeline&id=' . $timeline['username']));
            } else {
                header('Location: ' . SK_smoothLink('index.php?tab1=settings&tab2=avatar'));
            }
        }
    }
    
    // Reposition cover
    if ($a == "reposition") {
        
        if (!empty($_POST['pos']) && is_numeric($_POST['pos'])) {
            $_POST['pos'] = SK_secureEncode($_POST['pos']);
            $reposition = false;
            $position = preg_replace('/[^0-9]/', '', $_POST['pos']);
            $width = 920;
            
            if (!empty($_POST['width']) && is_numeric($_POST['width']) && $_POST['width'] > 0) {
                $width = SK_secureEncode($_POST['width']);
            }
            
            if (!empty($_POST['timeline_id']) && is_numeric($_POST['timeline_id']) && $_POST['timeline_id'] > 0) {
                $timeline_id = SK_secureEncode($_POST['timeline_id']);
                
                if (is_numeric($_POST['timeline_id']) && $_POST['timeline_id'] > 0) {
                    $timeline = SK_getUser($timeline_id, true);
                    
                    if (isset($timeline['id'])) {
                        $cover_id = $timeline['cover']['id'];
                        
                        if ($timeline['id'] == $user['id']) {
                            $reposition = true;
                        } elseif ($timeline['type'] == "page") {
                            
                            if (SK_isPageAdmin($timeline['id'])) {
                                $reposition = true;
                            }
                        } elseif ($timeline['type'] == "group") {
                            
                            if (SK_isGroupAdmin($timeline['id'])) {
                                $reposition = true;
                            }
                        }
                    }
                }
            }
            
            if ($reposition == true) {
                $cover_url = SK_createCover($cover_id, ($position / $width));
                
                if ($cover_url) {
                    $query = "UPDATE " . DB_ACCOUNTS . " SET cover_position=$position WHERE id=$timeline_id AND active=1";
                    $sql_query = mysqli_query($dbConnect, $query);
                    
                    if ($sql_query) {
                        $data = array(
                            'status' => 200,
                            'url' => $sk['config']['site_url'] . '/' . $cover_url
                        );
                    }
                }
            }
            
            header("Content-type: application/json");
            echo json_encode($data);
            mysqli_close($dbConnect);
            exit();
        }
    }
}

// Follow requests
if ($t == "follow") {
    
    // Follow user | Like page | Join group
    if ($a == "follow") {
        
        if (!empty($_POST['following_id'])) {
            $following_id = SK_secureEncode($_POST['following_id']);
            
            if (SK_isFollowing($following_id) or SK_isFollowRequested($following_id)) {
                $follow = SK_deleteFollow($following_id);
            } else {
                $follow = SK_registerFollow($following_id);
            }
            
            if ($follow) {
                $data = array(
                    'status' => 200,
                    'html' => SK_getFollowButton($following_id)
                );
            }
        }
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
    
    // List followers
    if ($a == "followers_list") {
        
        if (!empty($_GET['q'])) {
            $content = SK_getFollowers($user['id'], SK_secureEncode($_GET['q']));
        } else {
            $content = SK_getFollowers($user['id']);
        }
        
        foreach ($content as $each) {
            $one[] = array(
                'id' => $each['id'],
                'name' => $each['name']
            );
        }
        
        $data = array(
            'status' => 200,
            'data' => $one
        );
        
        header("Content-type: application/json");
        echo json_encode($data);
        mysqli_close($dbConnect);
        exit();
    }
}

// Soundcloud search
if ($t == "soundcloud_search") {
    
    if (!empty($_GET['q'])) {
        
        if (preg_match('/^(soundcloud\.com)/', $_GET['q'])) {
            $data = array(
                'status' => 200,
                'type' => 'embed',
                'sc_uri' => $_GET['q']
            );
            
            if (!preg_match('/^(http\:\/\/|https\:\/\/)/', $_GET['q'])) {
                $data['sc_uri'] = 'https://' . $data['sc_uri'];
            }
        } else {
            $api_url = 'http://api.soundcloud.com/tracks.json?client_id=4346c8125f4f5c40ad666bacd8e96498&q=' . urlencode($_GET['q']);
            $api_content = @file_get_contents($api_url);
            $html = '';
            
            if (!$api_content) {
                mysqli_close($dbConnect);
                exit();
            }
            
            $api_content_array = json_decode($api_content, true);
            
            if (!is_array($api_content_array) or count($api_content_array) < 1) {
                mysqli_close($dbConnect);
                exit();
            }
            
            foreach ($api_content_array as $sk['api']) {
                $html .= SK_getPage('story/publisher-box/soundcloud-search');
            }
            
            if (!empty($html)) {
                $data = array(
                    'status' => 200,
                    'type' => 'api',
                    'html' => $html
                );
            }
        }
    }
    
    header("Content-type: application/json");
    echo json_encode($data);
    mysqli_close($dbConnect);
    exit();
}

// YouTube Search
if ($t == "youtube_search") {
    
    if (!empty($_GET['q'])) {
        
        if (preg_match('/^(http\:\/\/|https\:\/\/|www\.|youtube\.com|youtu\.be)/', $_GET['q'])) {
            $data = array(
                'status' => 200,
                'type' => 'embed'
            );
        } else {
            $api_url = 'http://gdata.youtube.com/feeds/api/videos?q=' . urlencode($_GET['q']) . '&max-results=30&orderby=relevance&alt=json&format=5&v=2';
            $api_content = @file_get_contents($api_url);
            $html = '';
            
            if (!$api_content) {
                mysqli_close($dbConnect);
                exit();
            }
            
            $api_content_array = json_decode($api_content, true);
            
            if (!is_array($api_content_array)) {
                mysqli_close($dbConnect);
                exit();
            }
            
            if (!is_array($api_content_array['feed']['entry']) or count($api_content_array['feed']['entry']) < 1) {
                mysqli_close($dbConnect);
                exit();
            }
            
            foreach ($api_content_array['feed']['entry'] as $sk['api']) {
                $html .= SK_getPage('story/publisher-box/youtube-search');
            }
            
            if (!empty($html)) {
                $data = array(
                    'status' => 200,
                    'type' => 'api',
                    'html' => $html
                );
            }
        }
    }
    
    header("Content-type: application/json");
    echo json_encode($data);
    mysqli_close($dbConnect);
    exit();
}
