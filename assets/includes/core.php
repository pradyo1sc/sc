<?php
/* * * * * * * * * * * * * *
Gang Group
Copyright (c) 2014

Author Fa
Date: 27/Nov/2014
* * * * * * * * * * * * * */

require_once('connect.php');
require_once('timezones.php');

/* Check Functions */
function SK_isLogged() {
    global $dbConnect;
    
    if (!empty($_SESSION['user_id']) && is_numeric($_SESSION['user_id']) && $_SESSION['user_id'] > 0 && !empty($_SESSION['user_pass'])) {
        $user_id = SK_secureEncode($_SESSION['user_id']);
        $user_pass = SK_secureEncode($_SESSION['user_pass']);
        $query = "SELECT COUNT(id) AS count FROM " . DB_ACCOUNTS . " WHERE id=$user_id AND password='$user_pass' AND type='user' AND active=1";
        $sql_query = mysqli_query($dbConnect, $query);
        $sql_fetch = mysqli_fetch_assoc($sql_query);
        
        return $sql_fetch['count'];
    }
}

function SK_isPageAdmin($page_id=0, $admin_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (empty($page_id) or !is_numeric($page_id) or $page_id < 1) {
        return false;
    }
    
    if (empty($admin_id) or $admin_id == 0) {
        $admin_id = $user['id'];
        
        if (SK_isBlocked($page_id)) {
            return false;
        }
    }
    
    if (!is_numeric($admin_id) or $admin_id < 1) {
        return false;
    }
    
    $page_id = SK_secureEncode($page_id);
    $admin_id = SK_secureEncode($admin_id);
    
    $query_one = "SELECT id,role FROM " . DB_PAGE_ADMINS . " WHERE admin_id=$admin_id AND page_id=$page_id AND active=1";
    $sql_query = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query) == 1) {
        $sql_fetch = mysqli_fetch_assoc($sql_query);
        return $sql_fetch['role'];
    }
}

function SK_isGroupAdmin($group_id=0, $admin_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (empty($group_id) or !is_numeric($group_id) or $group_id < 1) {
        return false;
    }
    
    if (empty($admin_id) or $admin_id == 0) {
        $admin_id = $user['id'];
        
        if (SK_isBlocked($group_id)) {
            return false;
        }
    }
    
    if (!is_numeric($admin_id) or $admin_id < 1) {
        return false;
    }
    
    $group_id = SK_secureEncode($group_id);
    $admin_id = SK_secureEncode($admin_id);
    
    $query_one = "SELECT id FROM " . DB_GROUP_ADMINS . " WHERE admin_id=$admin_id AND group_id=$group_id AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        return true;
    }
}

function SK_isFollowing($following_id=0, $timeline_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (empty($following_id) or !is_numeric($following_id) or $following_id < 1) {
        return false;
    }
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
        
        if (SK_isBlocked($following_id)) {
            return false;
        }
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $following_id = SK_secureEncode($following_id);
    $timeline_id = SK_secureEncode($timeline_id);
    
    $query_text = "SELECT id FROM ". DB_FOLLOWERS ." WHERE follower_id=$timeline_id AND following_id=$following_id AND active=1";
    $sql_query = mysqli_query($dbConnect, $query_text);
    
    if (mysqli_num_rows($sql_query) > 0) {
        return true;
    }
}

function SK_isFollowRequested($following_id=0, $timeline_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (empty($following_id) or !is_numeric($following_id) or $following_id < 1) {
        return false;
    }
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
        
        if (SK_isBlocked($following_id)) {
            return false;
        }
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $following_id = SK_secureEncode($following_id);
    $timeline_id = SK_secureEncode($timeline_id);
    
    $query_text = "SELECT id FROM " . DB_FOLLOWERS . " WHERE follower_id=$timeline_id AND following_id=$following_id AND active=0";
    $sql_query = mysqli_query($dbConnect, $query_text);
    
    if (mysqli_num_rows($sql_query) > 0) {
        return true;
    }
}

function SK_isPostActive($post_id=0, $post_type='story') {
    global $dbConnect, $user;
    
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    if (empty($post_type)) {
       return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $post_type = SK_secureEncode($post_type);
    
    $query_text = "SELECT id,timeline_id FROM " . DB_POSTS . " WHERE post_id=$post_id AND type1='$post_type' AND type2='none' AND active=1";
    $sql_query = mysqli_query($dbConnect, $query_text);
    
    if (mysqli_num_rows($sql_query) == 1) {
        $sql_fetch = mysqli_fetch_assoc($sql_query);
        
        if (!SK_isBlocked($sql_fetch['timeline_id'])) {
            return true;
        }
    }
}

function SK_isPostLiked($post_id=0, $timeline_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (!preg_match('/(story|comment)/', SK_getPostType($post_id))) {
        return false;
    }
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $timeline_id = SK_secureEncode($timeline_id);
    
    $query_one = "SELECT id FROM " . DB_POSTS . " WHERE post_id=$post_id AND timeline_id=$timeline_id AND type2='like' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        return true;
    }
}

function SK_isPostCommented($post_id=0, $timeline_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (SK_getPostType($post_id) != "story") {
        return false;
    }
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $timeline_id = SK_secureEncode($timeline_id);
    
    $query_one = "SELECT id FROM " . DB_POSTS . " WHERE post_id=$post_id AND timeline_id=$timeline_id AND type1='story' AND type2='comment' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        return true;
    }
}

function SK_isPostShared($post_id=0, $timeline_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (SK_getPostType($post_id) != "story") {
        return false;
    }
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $timeline_id = SK_secureEncode($timeline_id);
    
    $query_one = "SELECT id FROM " . DB_POSTS . " WHERE post_id=$post_id AND timeline_id=$timeline_id AND type1='story' AND type2='share' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        return true;
    }
}

function SK_isPostFollowed($post_id=0, $timeline_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (SK_getPostType($post_id) != "story") {
        return false;
    }
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $timeline_id = SK_secureEncode($timeline_id);
    
    $query_one = "SELECT id FROM " . DB_POSTS . " WHERE post_id=$post_id AND timeline_id=". $timeline_id ." AND type1='story' AND type2='follow' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        return true;
    }
}

function SK_isPostReported($post_id=0, $timeline_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (!preg_match('/(story|comment)/', SK_getPostType($post_id))) {
        return false;
    }
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $timeline_id = SK_secureEncode($timeline_id);
    
    $query_one = "SELECT id FROM " . DB_REPORTS . " WHERE reporter_id=$timeline_id AND post_id=" . $post_id;
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        return true;
    }
}

function SK_isBlocked($blocked_id=0, $timeline_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (!is_numeric($blocked_id) or $blocked_id < 1) {
        return false;
    }
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $blocked_id = SK_secureEncode($blocked_id);
    $timeline_id = SK_secureEncode($timeline_id);
    
    $query_text = "SELECT id FROM " . DB_BLOCKERS . " WHERE ((blocker_id=$timeline_id AND blocked_id=$blocked_id AND active=1) OR (blocker_id=$blocked_id AND blocked_id=$timeline_id AND active=1)) AND active=1";
    $sql_query = mysqli_query($dbConnect, $query_text);
    
    if (mysqli_num_rows($sql_query) > 0) {
        return true;
    }
}

function SK_isValidPasswordResetToken($string) {
    global $dbConnect;
    
    $string_exp = explode('_', $string);
    $id = SK_secureEncode($string_exp[0]);
    $password = SK_secureEncode($string_exp[1]);
    
    if (empty($id) or !is_numeric($id) or $id < 1) {
        return false;
    }
    
    $query_one = "SELECT id FROM " . DB_ACCOUNTS . " WHERE id=$id AND password='$password' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        return array(
            'id' => $id,
            'password' => $password
        );
    } else {
        return false;
    }
}

function SK_validateEmail($string='') {
    $regex = '/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/';
    
    if (preg_match($regex, $string)) {
        return true;
    }
    
    return false;
}

function SK_validateUsername($query='') {
    if (strlen($query) > 3 && !is_numeric($query) && preg_match('/^[A-Za-z0-9_]+$/', $query)) {
        return true;
    }
}

function SK_secureEncode($string, $censorship=true) {
    global $dbConnect;
    $string = trim($string);
    $string = mysqli_real_escape_string($dbConnect, $string);
    $string = htmlspecialchars($string, ENT_QUOTES);
    $string = str_replace('\\r\\n', '<br>',$string);
    $string = str_replace('\\r', '<br>',$string);
    $string = str_replace('\\n\\n', '<br>',$string);
    $string = str_replace('\\n', '<br>',$string);
    $string = str_replace('\\n', '<br>',$string);
    $string = stripslashes($string);
    $string = str_replace('&amp;#', '&#',$string);
    
    if ($censorship == true) {
        global $config;
        $censored_words = explode(",", $config['censored_words']);
        
        foreach ($censored_words as $censored_word) {
            $censored_word = trim($censored_word);
            $string = str_replace($censored_word, '***', $string);
        }
    }
    
    return $string;
}

/* Get functions */
function SK_getAnnouncements() {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }

    global $dbConnect, $user;

    $get = array();
    $query = "SELECT * FROM " . DB_ANNOUNCEMENTS . " WHERE id NOT IN (SELECT announcement_id FROM " . DB_ANNOUNCEMENT_VIEWS . " WHERE account_id=" . $user['id'] . ") ORDER BY id DESC";
    $sql_query = mysqli_query($dbConnect, $query);

    while ($sql_fetch = mysqli_fetch_assoc($sql_query)) {
        $get[] = $sql_fetch;
    }

    return $get;
}

function SK_getPage($page_url='') {
    global $sk, $lang;
    
    $page = './themes/' . $sk['config']['theme'] . '/layout/' . $page_url . '.phtml';
    $page_content = '';
    
    ob_start();
    include($page);
    $page_content = ob_get_contents();
    ob_end_clean();
    
    return $page_content;
}

function SK_getLanguages() {
    global $sk;
    $get = array();
    $languages = glob('assets/languages/*.php');
    $languages_num = count($languages);
    $language_i = 0;
    
    foreach ($languages as $language) {
        $language = str_replace('assets/languages/', '', $language);
        $language = preg_replace('/([A-Za-z]+)\.php/i', '$1', $language);
        $language_i++;
        
        if ($sk['config']['smooth_links'] == 1) {
            $language_url = '?lang=' . $language;
        } else {
            $query_string = $_SERVER['QUERY_STRING'];
            $query_string = preg_replace('/(\&|)lang\=([A-Za-z0-9_]+)/i', '', $query_string);
            $language_url = 'index.php?' . $query_string . '&lang=' . $language;
            $language_url = SK_secureEncode(strip_tags($language_url));
        }
        
        $get[] = array(
            'name' => $language,
            'url' => $language_url
        );
    }
    
    return $get;
}

function SK_getChat() {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    if ($_GET['tab1'] == "messages") {
        return false;
    }
    
    if (empty($_SESSION['chat_recipient_id']) or !is_numeric($_SESSION['chat_recipient_id']) or $_SESSION['chat_recipient_id'] < 1) {
        return false;
    }
    
    $chat_recipient_id = SK_secureEncode($_SESSION['chat_recipient_id']);
    $chat_recipient = SK_getUser($chat_recipient_id);
    
    if (empty($chat_recipient['id'])) {
        return false;
    }
    
    return $chat_recipient;
}

function SK_getNotifications($data=array()) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    $get = array();
    
    if (!isset($data['account_id']) or empty($data['account_id'])) {
        $data['account_id'] = $user['id'];
    }
    
    if (!is_numeric($data['account_id']) or $data['account_id'] < 1) {
        return false;
    }
    
    if ($data['account_id'] == $user['id']) {
        $account = $user;
    } else {
        $data['account_id'] = SK_secureEncode($data['account_id']);
        $account = SK_getUser($data['account_id']);
    }
    
    if ($account['type'] == "user" && $account['id'] != $user['id']) {
        return false;
    } elseif ($account['type'] == "page" && !SK_isPageAdmin($account['id'])) {
        return false;
    } elseif ($account['type'] == "group") {
        return false;
    }
    
    $new_notif = SK_countNotifications(
        array(
            'unread' => true
        )
    );
    
    if ($new_notif > 0) {
        $query_one = "SELECT id,notifier_id,post_id,seen,text,time,timestamp,timeline_id,url FROM " . DB_NOTIFICATIONS . " WHERE timeline_id=" . $account['id'] . " AND active=1 AND seen=0 ORDER BY id DESC";
    } else {
        $query_one = "SELECT id,notifier_id,post_id,seen,text,time,timestamp,timeline_id,url FROM " . DB_NOTIFICATIONS . " WHERE timeline_id=" . $account['id'] . " AND active=1";
        
        if (isset($data['unread']) && $data['unread'] == true) {
            $query_one .= " AND seen=0";
        }
        
        $query_one .= " ORDER BY id DESC LIMIT 20";
    }
    
    if (isset($data['all']) && $data['all'] == true) {
        $query_one = "SELECT id,notifier_id,post_id,seen,text,time,timestamp,timeline_id,url FROM " . DB_NOTIFICATIONS . " WHERE timeline_id=" . $account['id'] . " AND active=1 AND seen=0 ORDER BY id DESC LIMIT 20";
    }
    
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) > 0) {
        
        while ($sql_fetch_one = mysqli_fetch_assoc($sql_query_one)) {
            $sql_fetch_one['notifier'] = SK_getUser($sql_fetch_one['notifier_id']);
            $sql_fetch_one['raw_url'] = $sql_fetch_one['url'];
            $sql_fetch_one['url'] = SK_smoothLink($sql_fetch_one['url']);
            $sql_fetch_one['text'] = preg_replace(
                '/\[b(| weight\=)(|[0-9]+)\](.*?)\[\/b\]/i',
                '<strong style="font-weight: $2;">$3</strong>',
                $sql_fetch_one['text']
            );
            $get[] = $sql_fetch_one;
        }
    }
    
    mysqli_query($dbConnect, "DELETE FROM " . DB_NOTIFICATIONS . " WHERE time<" . (time() - (60 * 60 * 24 * 5)) . " AND seen>0");
    return $get;
}

function SK_getAlbums($user_id=0, $limit=0) {
    global $dbConnect, $user;

    if (!is_numeric($user_id) or $user_id < 1) {
        return array();
    }

    $get = array();
    $query_one = "SELECT id,name,descr FROM " . DB_MEDIA . " WHERE timeline_id=$user_id AND temp=0 AND active=1";

    if (is_numeric($limit) && $limit > 0) {
        $query_one .= " LIMIT $limit";
    }

    $sql_query_one = mysqli_query($dbConnect, $query_one);

    while ($sql_fetch_one = mysqli_fetch_assoc($sql_query_one)) {
        $get[] = $sql_fetch_one;
    }

    return $get;
}

function SK_getAlbumPhotos($album_id=0) {
    global $dbConnect, $user;

    $get = array();
    $query_one = "SELECT id FROM " . DB_MEDIA . " WHERE album_id=$album_id AND type='photo' ORDER BY id DESC";
    $sql_query_one = mysqli_query($dbConnect, $query_one);

    if (mysqli_num_rows($sql_query_one) > 0) {

        while ($sql_fetch_one = mysqli_fetch_assoc($sql_query_one)) {
            $get[] = SK_getMedia($sql_fetch_one['id']);
        }
    }

    return $get;
}

function SK_getUsernameStatus($query='', $timeline_id=0) {
    global $dbConnect, $user;
    $query = SK_secureEncode($query);
    
    if (empty($query) or !SK_validateUsername($query)) {
        return 406;
    }
    
    if (strlen($query) < 4) {
        return 410;
    }
    
    if (empty($timeline_id) or $timeline_id < 1) {
        
        if ($GLOBALS['logged'] == true) {
            $timeline_id = $user['id'];
        }
    }
    
    if ($GLOBALS['logged'] == true) {
        
        if (!is_numeric($timeline_id) or $timeline_id < 1) {
            return false;
        }
        
        if ($timeline_id == $user['id']) {
            
            if ($query == $user['username']) {
                return 201;
            }
        }
        
        $timeline_id = SK_secureEncode($timeline_id);
        $timeline = SK_getUser($timeline_id);
        
        if (empty($timeline['id'])) {
            return false;
        }
        
        if ($timeline['type'] == "user" && $timeline['id'] != $user['id']) {
            return false;
        } elseif ($timeline['type'] == "page" && !SK_isPageAdmin($timeline['id'])) {
            return false;
        } elseif ($timeline['type'] == "group" && !SK_isGroupAdmin($timeline['id'])) {
            return false;
        }
        
        if ($query == $timeline['username']) {
            return 201;
        }
    }
    
    $query_one = "SELECT id FROM " . DB_ACCOUNTS . " WHERE username='$query'";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (($sql_numrows_one = mysqli_num_rows($sql_query_one)) == 0) {
        return 200;
    } else {
        return 410;
    }
}

function SK_getHashtag($tag='') {
    global $dbConnect;
    $create = false;
    
    if (empty($tag)) {
        return false;
    }
    
    $tag = SK_secureEncode($tag);
    
    if (is_numeric($tag)) {
        $query = "SELECT * FROM " . DB_HASHTAGS . " WHERE id=$tag";
    } else {
        $query = "SELECT * FROM " . DB_HASHTAGS . " WHERE tag='$tag'";
        $create = true;
    }
    
    $sql_query = mysqli_query($dbConnect, $query);
    $sql_numrows = mysqli_num_rows($sql_query);
    
    if ($sql_numrows == 1) {
        $sql_fetch = mysqli_fetch_assoc($sql_query);
        return $sql_fetch;
    } elseif ($sql_numrows == 0) {
        
        if ($create == true) {
            $hash = md5($tag);
            $query_two = "INSERT INTO " . DB_HASHTAGS . " (hash,tag,last_trend_time) VALUES ('$hash','$tag'," . time() . ")";
            $sql_query_two = mysqli_query($dbConnect, $query_two);
            
            if ($sql_query_two) {
                $sql_id = mysqli_insert_id($dbConnect);
                $get = array(
                    'id' => $sql_id,
                    'hash' => $hash,
                    'tag' => $tag,
                    'last_trend_time' => time(),
                    'trend_use_num' => 0
                );
                return $get;
            }
        }
    }
}

function SK_getHashtagSearch($tag='', $limit=4) {
    global $dbConnect;
    $get = array();
    
    if (empty($tag)) {
        return false;
    }
    
    if (empty($limit) or !is_numeric($limit) or $limit < 1) {
        $limit = 5;
    }
    
    $tag = SK_secureEncode($tag);
    
    if (is_numeric($tag)) {
        $query = "SELECT * FROM " . DB_HASHTAGS . " WHERE id=$tag LIMIT $limit";
    } else {
        $query = "SELECT * FROM " . DB_HASHTAGS . " WHERE tag LIKE '%$tag%' LIMIT $limit";
    }
    
    $sql_query = mysqli_query($dbConnect, $query);
    $sql_numrows = mysqli_num_rows($sql_query);
    
    if ($sql_numrows > 0) {
        
        while ($sql_fetch = mysqli_fetch_assoc($sql_query)) {
            $get[] = $sql_fetch;
        }
        
        return $get;
    }
}

function SK_getTrendings($type='latest', $limit=5) {
    global $dbConnect;
    $get = array();
    
    if (empty($type)) {
        return false;
    }
    
    if (empty($limit) or !is_numeric($limit) or $limit < 1) {
        $limit = 5;
    }
    
    if ($type == "latest") {
        $query = "SELECT * FROM " . DB_HASHTAGS . " ORDER BY last_trend_time DESC LIMIT $limit";
    } elseif ($type == "popular") {
        $query = "SELECT * FROM " . DB_HASHTAGS . " ORDER BY trend_use_num DESC LIMIT $limit";
    }
    
    $sql_query = mysqli_query($dbConnect, $query);
    $sql_numrows = mysqli_num_rows($sql_query);
    
    if ($sql_numrows > 0) {
        
        while ($sql_fetch = mysqli_fetch_assoc($sql_query)) {
            $sql_fetch['url'] = SK_smoothLink('index.php?tab1=hashtag&query=' . $sql_fetch['tag']);
            $get[] = $sql_fetch;
        }
        
        return $get;
    }
}

function SK_getUser($timeline_id=0, $all=false) {
    global $dbConnect, $sk;
    $timeline_id = SK_secureEncode($timeline_id);
    $subquery_one = 'id,avatar_id,cover_id,name,type,username,last_logged';
    
    if (is_numeric($timeline_id)) {
        $check_query_part = "id=" . $timeline_id;
    } elseif (preg_match('/@/', $timeline_id)) {
        $check_query_part = "email='". $timeline_id ."'";
    } elseif (preg_match('/[A-Za-z0-9_]/', $timeline_id)) {
        $check_query_part = "username='". $timeline_id ."'";
    }
    
    if (empty($check_query_part)) {
        return false;
    }
    
    $query_one = "SELECT id FROM " . DB_ACCOUNTS . " WHERE $check_query_part AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) !== 1) {
        return false;
    }
    
    if ($all == true) {
        $subquery_one = '*';
    }
    
    $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
    $query_two = "SELECT $subquery_one FROM " . DB_ACCOUNTS . " WHERE id=" . $sql_fetch_one['id'] . " AND active=1";
    $sql_query_two = mysqli_query($dbConnect, $query_two);
    
    if (mysqli_num_rows($sql_query_two) == 1) {
        $sql_fetch_two = mysqli_fetch_assoc($sql_query_two);
        
        if (isset($sql_fetch_two['username'])) {
            $sql_fetch_two['url'] = SK_smoothLink('index.php?tab1=timeline&id=' . $sql_fetch_two['username']);
        }
        
        if (isset($sql_fetch_two['name'])) {
            $name_exp = explode(' ', $sql_fetch_two['name']);
            $sql_fetch_two['first_name'] = $name_exp[0];
            $sql_fetch_two['last_name'] = $name_exp[count($name_exp)-1];
        }
        
        if (isset($sql_fetch_two['password'])) {
            unset($sql_fetch_two['password']);
        }
        
        if (isset($sql_fetch_two['cover_id'])) {
            
            if ($sql_fetch_two['cover_id'] > 0) {
                $sql_fetch_two['cover'] = SK_getMedia($sql_fetch_two['cover_id']);
                $sql_fetch_two['actual_cover_url'] =  $sk['config']['site_url'] . '/' . $sql_fetch_two['cover']['url'] . '.' . $sql_fetch_two['cover']['extension'];
                $sql_fetch_two['cover_url'] =  $sk['config']['site_url'] . '/' . $sql_fetch_two['cover']['url'] . '_cover.' . $sql_fetch_two['cover']['extension'];
            }  else {
                $sql_fetch_two['actual_cover_url'] = $sql_fetch_two['cover_url'] =  $sk['config']['theme_url'] . '/images/default-cover.png';
            }
        }
        
        $no_avatar = false;
        
        if (isset($sql_fetch_two['avatar_id'])) {
            
            if ($sql_fetch_two['avatar_id'] > 0) {
                $sql_fetch_two['avatar'] = SK_getMedia($sql_fetch_two['avatar_id']);
                $sql_fetch_two['thumbnail_url'] =  $sk['config']['site_url'] . '/' . $sql_fetch_two['avatar']['url'] . '_thumb.' . $sql_fetch_two['avatar']['extension'];
                $sql_fetch_two['avatar_url'] =  $sk['config']['site_url'] . '/' . $sql_fetch_two['avatar']['url'] . '_100x100.' . $sql_fetch_two['avatar']['extension'];
            } else {
                $no_avatar = true;
            }
        }
        
        if (isset($sql_fetch_two['verified'])) {
            $sql_fetch_two['verified'] = ($sql_fetch_two['verified']==1) ? true : false;
        }
        
        if (isset($sql_fetch_two['last_logged'])) {
            $sql_fetch_two['online'] = false;
            
            if ($GLOBALS['logged'] == true && $sql_fetch_two['last_logged'] > (time()-15)) {
                $sql_fetch_two['online'] = true;
            }
        }
        
        if ($sql_fetch_two['type'] == "user") {
            $subquery_one = 'gender,comment_privacy,follow_privacy,message_privacy,post_privacy,confirm_followers';
            
            if ($all == true) {
                $subquery_one = '*';
            }
            
            $query_three = "SELECT $subquery_one FROM " . DB_USERS . " WHERE id=" . $sql_fetch_two['id'];
            $sql_query_three = mysqli_query($dbConnect, $query_three);
            
            if (mysqli_num_rows($sql_query_three) == 1) {
                $sql_fetch_three = mysqli_fetch_assoc($sql_query_three);
                
                if (!empty($sql_fetch_three['birthday'])) {
                    $sql_fetch_three['birth'] = explode('-', $sql_fetch_three['birthday']);
                    $sql_fetch_three['birth'] = array(
                        'date' => $sql_fetch_three['birth'][0],
                        'month' => $sql_fetch_three['birth'][1],
                        'year' => $sql_fetch_three['birth'][2]
                    );
                }
                
                if ($no_avatar == true) {
                    $sql_fetch_two['thumbnail_url'] = $sql_fetch_two['avatar_url'] = $sk['config']['theme_url'] . '/images/default-male-avatar.png';
                    
                    if (!empty($sql_fetch_three['gender'])) {
                        
                        if ($sql_fetch_three['gender'] == "female") {
                            $sql_fetch_two['thumbnail_url'] = $sql_fetch_two['avatar_url'] = $sk['config']['theme_url'] . '/images/default-female-avatar.png';
                        }
                    }
                }
                
                return array_merge($sql_fetch_two, $sql_fetch_three);
            }
        } elseif ($sql_fetch_two['type'] == "page") {
            $subquery_one = 'category_id,message_privacy';
            
            if ($all == true) {
                $subquery_one = '*';
            }
            
            $query_three = "SELECT $subquery_one FROM " . DB_PAGES . " WHERE id=" . $sql_fetch_two['id'];
            $sql_query_three = mysqli_query($dbConnect, $query_three);
            
            if (mysqli_num_rows($sql_query_three) == 1) {
                $sql_fetch_three = mysqli_fetch_assoc($sql_query_three);
                
                if ($no_avatar == true) {
                    $sql_fetch_two['thumbnail_url'] = $sql_fetch_two['avatar_url'] = $sk['config']['theme_url'] . '/images/default-page-avatar.png';
                }
                
                return array_merge($sql_fetch_two, $sql_fetch_three);
            }
        } elseif ($sql_fetch_two['type'] == "group") {
            $subquery_one = 'group_privacy';
            
            if ($all == true) {
                $subquery_one = '*';
            }
            
            $query_three = "SELECT $subquery_one FROM " . DB_GROUPS . " WHERE id=" . $sql_fetch_two['id'];
            $sql_query_three = mysqli_query($dbConnect, $query_three);
            
            if (mysqli_num_rows($sql_query_three) == 1) {
                $sql_fetch_three = mysqli_fetch_assoc($sql_query_three);
                $sql_fetch_two['thumbnail_url'] = $sql_fetch_two['avatar_url'] = $sk['config']['theme_url'] . '/images/default-group-avatar.png';
                
                return array_merge($sql_fetch_two, $sql_fetch_three);
            }
        }
    }
}

function SK_getFollowSuggestions($search_query='', $limit=5) {
    if ($GLOBALS['logged'] !== true) {
        return array();
    }
    
    global $dbConnect, $user;
    $get = array();
    
    if (!isset($limit) or empty($limit) or !is_numeric($limit) or $limit < 1) {
        $limit = 5;
    }
    
    $limit = SK_secureEncode($limit);
    $query_one = "SELECT id FROM " . DB_ACCOUNTS . " WHERE id NOT IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . ") AND (id IN (SELECT id FROM " . DB_USERS . " WHERE follow_privacy='everyone') OR id IN (SELECT id FROM " . DB_PAGES . ") OR id IN (SELECT id FROM " . DB_GROUPS . " WHERE group_privacy IN ('open','closed'))) AND type IN ('user','page','group') AND active=1";
    
    if (!empty($search_query)) {
        $search_query = SK_secureEncode($search_query);
        $query_one .= " AND name LIKE '%$search_query%'";
    }
    
    $query_one .= " ORDER BY RAND() LIMIT $limit";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    while ($sql_fetch_one = mysqli_fetch_assoc($sql_query_one)) {
        
        if (!SK_isBlocked($sql_fetch_one['id'])) {
            $get[] = SK_getUser($sql_fetch_one['id']);
        }
    }
    
    return $get;
}

function SK_getFollowing($timeline_id=0, $search_query='') {
    global $dbConnect, $user;
    $get = array();
    
    if (empty($timeline_id) or $timeline_id == 0) {
        
        if ($GLOBALS['logged'] !== true) {
            return false;
        }
        
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1 or SK_isBlocked($timeline_id)) {
        return false;
    }
    
    $timeline_id = SK_secureEncode($timeline_id);
    $query_text = "SELECT id FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=$timeline_id AND following_id<>$timeline_id AND active=1) AND type='user' AND active=1";
    
    if (!empty($search_query)) {
        $search_query = SK_secureEncode($search_query);
        $query_text .= " AND name LIKE '%$search_query%'";
    }
    
    $query_text .= " ORDER BY RAND()";
    $sql_query = mysqli_query($dbConnect, $query_text);
    
    while ($sql_fetch = mysqli_fetch_assoc($sql_query)) {
        $get[] = SK_getUser($sql_fetch['id']);
    }
    
    return $get;
}

function SK_getFollowers($timeline_id=0, $search_query='') {
    global $dbConnect, $user;
    $get = array();
    
    if (empty($timeline_id) or $timeline_id == 0) {
        
        if ($GLOBALS['logged'] !== true) {
            return false;
        }
        
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1 or SK_isBlocked($timeline_id)) {
        return false;
    }
    
    $timeline_id = SK_secureEncode($timeline_id);
    $query_text = "SELECT id FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT follower_id FROM " . DB_FOLLOWERS . " WHERE follower_id<>$timeline_id AND following_id=$timeline_id AND active=1) AND type='user' AND active=1";
    
    if (!empty($search_query)) {
        $search_query = SK_secureEncode($search_query);
        $query_text .= " AND name LIKE '%$search_query%'";
    }
    
    $query_text .= " ORDER BY RAND()";
    $sql_query = mysqli_query($dbConnect, $query_text);
    
    while ($sql_fetch = mysqli_fetch_assoc($sql_query)) {
        $get[] = SK_getUser($sql_fetch['id']);
    }
    
    return $get;
}

function SK_getLikedPages($timeline_id=0, $search_query='') {
    global $dbConnect, $user;
    $get = array();
    
    if (empty($timeline_id) or $timeline_id == 0) {
        
        if ($GLOBALS['logged'] !== true) {
            return false;
        }
        
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1 or SK_isBlocked($timeline_id)) {
        return false;
    }
    
    $timeline_id = SK_secureEncode($timeline_id);
    $query_text = "SELECT id FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=$timeline_id AND following_id<>$timeline_id AND active=1) AND type='page' AND active=1";
    
    if (!empty($search_query)) {
        $search_query = SK_secureEncode($search_query);
        $query_text .= " AND name LIKE '%$search_query%'";
    }
    
    $query_text .= " ORDER BY RAND()";
    $sql_query = mysqli_query($dbConnect, $query_text);
    
    while ($sql_fetch = mysqli_fetch_assoc($sql_query)) {
        $get[] = SK_getUser($sql_fetch['id']);
    }
    
    return $get;
}

function SK_getGroupsJoined($timeline_id=0, $search_query='') {
    global $dbConnect, $user;
    $get = array();
    
    if (empty($timeline_id) or $timeline_id == 0) {
        
        if ($GLOBALS['logged'] !== true) {
            return false;
        }
        
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $timeline_id = SK_secureEncode($timeline_id);
    $query_text = "SELECT id FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=$timeline_id AND following_id<>$timeline_id AND active=1) AND type='group' AND active=1";
    
    if (!empty($search_query)) {
        $search_query = SK_secureEncode($search_query);
        $query_text .= " AND name LIKE '%$search_query%'";
    }
    
    $query_text .= " ORDER BY RAND()";
    $sql_query = mysqli_query($dbConnect, $query_text);
    
    while ($sql_fetch = mysqli_fetch_assoc($sql_query)) {
        $get[] = SK_getUser($sql_fetch['id']);
    }
    
    return $get;
}

function SK_getFollowRequests($timeline_id=0, $search_query='') {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    $get = array();
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $timeline_id = SK_secureEncode($timeline_id);
    $query_text = "SELECT id FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT follower_id FROM " . DB_FOLLOWERS . " WHERE follower_id<>$timeline_id AND following_id=$timeline_id AND active=0) AND type='user' AND active=1";
    
    if (!empty($search_query)) {
        $search_query = SK_secureEncode($search_query);
        $query_text .= " AND name LIKE '%$search_query%'";
    }
    
    $query_text .= " ORDER BY RAND()";
    $sql_query = mysqli_query($dbConnect, $query_text);
    
    while ($sql_fetch = mysqli_fetch_assoc($sql_query)) {
        $get[] = SK_getUser($sql_fetch['id']);
    }
    
    return $get;
}

function SK_getPageAdmins($page_id=0, $search_query='') {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    $get = array();
    
    if (empty($page_id) or !is_numeric($page_id) or $page_id < 1) {
        return false;
    }
    
    $page_id = SK_secureEncode($page_id);
    
    if (!SK_isPageAdmin($page_id)) {
        return false;
    }
    
    $query_text = "SELECT id FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT admin_id FROM " . DB_PAGE_ADMINS . " WHERE page_id=$page_id AND active=1) AND active=1";
    
    if (!empty($search_query)) {
        $search_query = SK_secureEncode($search_query);
        $query_text .= " AND name LIKE '%$search_query%'";
    }
    
    $query_text .= " ORDER BY RAND()";
    $sql_query = mysqli_query($dbConnect, $query_text);
    
    while ($sql_fetch = mysqli_fetch_assoc($sql_query)) {
        $get[] = SK_getUser($sql_fetch['id']);
    }
    
    return $get;
}

function SK_getGroupAdmins($group_id=0, $search_query='') {
    global $dbConnect;
    $get = array();
    
    if (empty($group_id) or !is_numeric($group_id) or $group_id < 1) {
        return false;
    }
    
    $group_id = SK_secureEncode($group_id);
    $query_text = "SELECT id FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT admin_id FROM " . DB_GROUP_ADMINS . " WHERE group_id=$group_id AND active=1) AND active=1";
    
    if (!empty($search_query)) {
        $query_text .= " AND name LIKE '%$search_query%'";
    }
    
    $query_text .= " ORDER BY RAND()";
    $sql_query = mysqli_query($dbConnect, $query_text);
    
    while ($sql_fetch = mysqli_fetch_assoc($sql_query)) {
        $get[] = SK_getUser($sql_fetch['id']);
    }
    
    return $get;
}

function SK_getManagedPages() {
    if ($GLOBALS['logged'] !== true) {
        return array();
    }
    
    global $dbConnect, $user;
    $get = array();
    $query_one = "SELECT id FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT page_id FROM " . DB_PAGE_ADMINS . " WHERE admin_id=" . $user['id'] . " AND page_id IN (SELECT id FROM " . DB_PAGES .") AND active=1) AND type='page' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    while ($sql_fetch_one = mysqli_fetch_assoc($sql_query_one)) {
        $get[] = SK_getUser($sql_fetch_one['id']);
    }
    
    return $get;
}

function SK_getManagedGroups() {
    if ($GLOBALS['logged'] !== true) {
        return array();
    }
    
    global $dbConnect, $user;
    $get = array();
    $query_one = "SELECT id FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT group_id FROM " . DB_GROUP_ADMINS . " WHERE admin_id=" . $user['id'] . " AND group_id IN (SELECT id FROM " . DB_GROUPS .") AND active=1) AND type='group' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    while ($sql_fetch_one = mysqli_fetch_assoc($sql_query_one)) {
        $get[] = SK_getUser($sql_fetch_one['id']);
    }
    
    return $get;
}

function SK_getOnlines($timeline_id=0, $search_query='') {
    if ($GLOBALS['logged'] !== true) {
        return array();
    }
    
    global $dbConnect, $user;
    $get = array();
    $excludes = array();
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1 or SK_isBlocked($timeline_id)) {
        return false;
    }
    
    $query_text_one = "SELECT id FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=$timeline_id AND following_id<>$timeline_id AND active=1) AND type='user' AND last_logged>" . (time()-15) . " AND active=1";
    
    if (!empty($search_query)) {
        $query_text_one .= " AND name LIKE '%$search_query%'";
    }
    
    $query_text_one .= " ORDER BY last_logged DESC";
    $sql_query_one = mysqli_query($dbConnect, $query_text_one);
    
    while ($sql_fetch_one = mysqli_fetch_assoc($sql_query_one)) {
        $get[] = SK_getUser($sql_fetch_one['id']);
        $excludes[] = $sql_fetch_one['id'];
    }
    
    $exclude_query_string = 0;
    $exclude_i = 0;
    $excludes_num = count($excludes);
    
    if ($excludes_num > 0) {
        $exclude_query_string = '';
        
        foreach ($excludes as $exclude) {
            $exclude_i++;
            $exclude_query_string .= $exclude;
            
            if ($exclude_i != $excludes_num) {
                $exclude_query_string .= ',';
            }
        }
    }
    
    $query_two = "SELECT DISTINCT id FROM " . DB_ACCOUNTS . " WHERE id NOT IN ($exclude_query_string) AND id IN (SELECT timeline_id FROM " . DB_POSTS . " WHERE type1='message' AND recipient_id=$timeline_id AND active=1 AND seen=0 ORDER BY id DESC) AND active=1";
    
    if (!empty($search_query)) {
        $query_two .= " AND name LIKE '%$search_query%'";
    }
    
    $sql_query_two = mysqli_query($dbConnect, $query_two);
    
    while ($sql_fetch_two=mysqli_fetch_assoc($sql_query_two)) {
        $get[] = SK_getUser($sql_fetch_two['id']);
    }
    
    return $get;
}

function SK_getMessageRecipients($timeline_id=0, $search_query='', $new=false) {
    if ($GLOBALS['logged'] !== true) {
        return array();
    }
    
    global $dbConnect, $user;
    $get = array();
    $excludes = array();
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1 or SK_isBlocked($timeline_id)) {
        return false;
    }
    
    if (!empty($search_query)) {
        $search_query = SK_secureEncode($search_query);
        $query_one = "SELECT DISTINCT id FROM " . DB_ACCOUNTS . " WHERE (id IN (SELECT timeline_id FROM " . DB_POSTS . " WHERE type1='message' AND recipient_id=$timeline_id AND active=1";
        
        if (isset($new) && $new == true) {
            $query_one .= " AND seen=0";
        }
        
        $query_one .= " ORDER BY id DESC)";
        
        if (!isset($new) or $new == false) {
            $query_one .= " OR id IN (SELECT recipient_id FROM " . DB_POSTS . " WHERE type1='message' AND timeline_id=$timeline_id AND active=1 ORDER BY id DESC)";
        }
        
        $query_one .= ") AND id<>$timeline_id AND active=1 AND name LIKE '%$search_query%'";
    } else {
        $query_one = "SELECT DISTINCT id FROM " . DB_ACCOUNTS . " WHERE (id IN (SELECT timeline_id FROM " . DB_POSTS . " WHERE type1='message' AND recipient_id=$timeline_id AND active=1";
        
        if (isset($new) && $new == true) {
            $query_one .= " AND seen=0";
        }
        
        $query_one .= " ORDER BY id DESC)";
        
        if (!isset($new) or $new == false) {
            $query_one .= " OR id IN (SELECT recipient_id FROM " . DB_POSTS . " WHERE type1='message' AND timeline_id=$timeline_id AND active=1 ORDER BY id DESC)";
        }
        
        $query_one .= ") AND active=1";
    }
    
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) > 0) {
        
        while ($sql_fetch_one=mysqli_fetch_assoc($sql_query_one)) {
            $get[] = SK_getUser($sql_fetch_one['id']);
            $excludes[] = $sql_fetch_one['id'];
        }
    }
    
    $exclude_query_string = 0;
    $exclude_i = 0;
    $excludes_num = count($excludes);
    
    if ($excludes_num > 0) {
        $exclude_query_string = '';
        
        foreach ($excludes as $exclude) {
            $exclude_i++;
            $exclude_query_string .= $exclude;
            
            if ($exclude_i != $excludes_num) {
                $exclude_query_string .= ',';
            }
        }
    }
    
    $query_two = "SELECT id FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=$timeline_id AND following_id NOT IN ($timeline_id,$exclude_query_string) AND following_id IN (SELECT id FROM " . DB_USERS . ") AND active=1) AND active=1";
    
    if (!empty($search_query)) {
        $query_two .= " AND name LIKE '%$search_query%'";
    }
    
    $sql_query_two = mysqli_query($dbConnect, $query_two);
    
    while ($sql_fetch_two=mysqli_fetch_assoc($sql_query_two)) {
        $get[] = SK_getUser($sql_fetch_two['id']);
    }
    
    return $get;
}

function SK_getSearch($search_query='', $from_row=0, $limit=10) {
    global $dbConnect;
    $get = array();
    
    if (!isset($search_query) or empty($search_query)) {
        return $get;
    }
    
    if (!isset($from_row) or empty($from_row)) {
        $from_row = 0;
    }
    
    if (!is_numeric($from_row) or $from_row < 0) {
        return $get;
    }
    
    if (!isset($limit) or empty($limit)) {
        $limit = 10;
    }
    
    if (!is_numeric($limit) or $limit < 1) {
        return $get;
    }
    
    $search_query = SK_secureEncode($search_query);
    $from_row = SK_secureEncode($from_row);
    $limit = SK_secureEncode($limit);
    $query_one = "SELECT id FROM " . DB_ACCOUNTS . " WHERE name LIKE '%$search_query%' AND (id IN (SELECT id FROM " . DB_USERS . ") OR id IN (SELECT id FROM " . DB_PAGES . ") OR id IN (SELECT id FROM " . DB_GROUPS . " WHERE group_privacy IN ('open','closed'))) AND type IN ('user','page','group') AND active=1 ORDER BY name ASC LIMIT $from_row,$limit";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) > 0) {
        
        while ($sql_fetch_one = mysqli_fetch_assoc($sql_query_one)) {
            $get[] = SK_getUser($sql_fetch_one['id']);
        }
    }
    
    return $get;
}

function SK_getFollowButton($timeline_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user, $sk;
    
    if (!is_numeric($timeline_id) or $timeline_id < 0) {
        return false;
    }
    
    if ($timeline_id == $user['id'] or SK_isBlocked($timeline_id)) {
        return false;
    }
    
    $timeline_id = SK_secureEncode($timeline_id);
    $account = $sk['follow'] = SK_getUser($timeline_id);
    
    if (!isset($account['id'])) {
        return false;
    }

    if ($sk['config']['friends'] == true) {
        switch ($account['type']) {
            case 'user':
                $follow_button = 'global/buttons/add_as_friend';
                $unfollow_button = 'global/buttons/unfriend';
                $request_button = 'global/buttons/request-sent';
            break;
            
            case 'page':
                $follow_button = 'global/buttons/like';
                $unfollow_button = 'global/buttons/unlike';
                $request_button = 'global/buttons/request-sent';
            break;
            
            case 'group':
                $follow_button = 'global/buttons/join';
                $unfollow_button = 'global/buttons/leave';
                $request_button = 'global/buttons/request-sent';
            break;
        }
    } else {
        switch ($account['type']) {
            case 'user':
                $follow_button = 'global/buttons/follow';
                $unfollow_button = 'global/buttons/unfollow';
                $request_button = 'global/buttons/request-sent';
            break;
            
            case 'page':
                $follow_button = 'global/buttons/like';
                $unfollow_button = 'global/buttons/unlike';
                $request_button = 'global/buttons/request-sent';
            break;
            
            case 'group':
                $follow_button = 'global/buttons/join';
                $unfollow_button = 'global/buttons/leave';
                $request_button = 'global/buttons/request-sent';
            break;
        }
    }
    
    if (SK_isFollowing($timeline_id)) {
        return SK_getPage($unfollow_button);
    } else {
        
        if (SK_isFollowRequested($timeline_id)) {
            return SK_getPage($request_button);
        } else {
            
            if ($account['type'] == "user") {
                
                if ($account['follow_privacy'] == "following") {
                    
                    if (SK_isFollowing($user['id'], $timeline_id)) {
                        return SK_getPage($follow_button);
                    }
                } elseif ($account['follow_privacy'] == "everyone") {
                    return SK_getPage($follow_button);
                }
            } elseif ($account['type'] == "page") {
                return SK_getPage($follow_button);
            } elseif ($account['type'] == "group") {
                return SK_getPage($follow_button);
            }
        }
    }
}

function SK_getMessageButton($timeline_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (!is_numeric($timeline_id) or $timeline_id < 0 && SK_isBlocked($timeline_id)) {
        return false;
    }
    
    if ($timeline_id != $user['id']) {
        $account = SK_getUser($timeline_id);
        
        if (!isset($account['id'])) {
            return false;
        }
        
        if ($account['message_privacy'] == "following") {
            $query_one = "SELECT id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $account['id'] . " AND following_id=" . $user['id'] . " AND active=1";
            $sql_query_one = mysqli_query($dbConnect, $query);
            
            if (mysqli_num_rows($sql_query_one) == 1) {
                return SK_getPage('global/buttons/message');
            }
        } elseif ($account['message_privacy'] == "everyone") {
            return SK_getPage('global/buttons/message');
        }
    }
}

function SK_getMessages($data=array()) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    $get = array();
    
    if (empty($data['recipient_id']) or !is_numeric($data['recipient_id']) or $data['recipient_id'] < 1) {
        return false;
    }
    
    $data['recipient_id'] = SK_secureEncode($data['recipient_id']);
    $recipient = SK_getUser($data['recipient_id']);
    
    if (!isset($recipient['id'])) {
        return false;
    }
    
    $query_one = "SELECT id,active,media_id,recipient_id,seen,text,time,timeline_id,type1 FROM " . DB_POSTS . " WHERE active=1";
    
    if (!empty($data['message_id']) && is_numeric($data['message_id']) && $data['message_id'] > 0) {
        $data['message_id'] = SK_secureEncode($data['message_id']);
        $query_one .= " AND id=" . $data['message_id'];
    } elseif (!empty($data['before_message_id']) && is_numeric($data['before_message_id']) && $data['before_message_id'] > 0) {
        $data['before_message_id'] = SK_secureEncode($data['before_message_id']);
        $query_one .= " AND id<" . $data['before_message_id'];
    }
    
    if (empty($data['timeline_id']) or $data['timeline_id'] == 0) {
        $data['timeline_id'] = $user['id'];
    }
    
    if (!is_numeric($data['timeline_id']) or $data['timeline_id'] < 1) {
        return false;
    }
    
    if ($data['timeline_id'] == $user['id']) {
        $timeline = $user;
    } else {
        $data['timeline_id'] = SK_secureEncode($data['timeline_id']);
        $timeline = SK_getUser($data['timeline_id']);
    }
    
    if (!isset($timeline['id'])) {
        return false;
    }
    
    if ($timeline['type'] == "user" && $timeline['id'] != $user['id']) {
        return false;
    } elseif ($timeline['type'] == "page" && !SK_isPageAdmin($timeline['id'])) {
        return false;
    } elseif ($timeline['type'] == "group") {
        return false;
    }
    
    if ($timeline['id'] == $recipient['id']) {
        return false;
    }
    
    if (isset($data['new']) && $data['new'] == true) {
        $query_one .= " AND seen=0 AND timeline_id=" . $recipient['id'] . " AND recipient_id=" . $timeline['id'];
    } else {
        $query_one .= " AND ((timeline_id=" . $timeline['id'] . " AND recipient_id=" . $recipient['id'] . ") OR (timeline_id=" . $recipient['id'] . " AND recipient_id=" . $timeline['id'] . "))";
    }
    
    $query_one .= " AND type1='message'";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    $query_limit_from = mysqli_num_rows($sql_query_one) - 5;
    
    if ($query_limit_from < 1) {
        $query_limit_from = 0;
    }
    
    $query_one .= " ORDER BY id ASC LIMIT $query_limit_from,5";
    $sql_query_two = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_two) == 0) {
        return false;
    }
    
    while ($sql_fetch = mysqli_fetch_assoc($sql_query_two)) {
        $sql_fetch['account'] = SK_getUser($sql_fetch['timeline_id']);
        $sql_fetch['owner'] = false;
        
        if ($sql_fetch['account']['type'] == "user" && $sql_fetch['account']['id'] == $user['id']) {
            $sql_fetch['owner'] = true;
        } elseif ($sql_fetch['account']['type'] == "page" && SK_isPageAdmin($sql_fetch['account']['id'])) {
            $sql_fetch['owner'] = true;
        }
        
        $sql_fetch['text'] = SK_emoticonize($sql_fetch['text']);
        $sql_fetch['text'] = SK_getMarkup($sql_fetch['text']);
        
        if (!empty($sql_fetch['media_id']) && $sql_fetch['media_id'] > 0) {
            $sql_fetch['media'] = SK_getMedia($sql_fetch['media_id']);
        }
        
        if ($sql_fetch['recipient_id'] == $timeline['id'] && $sql_fetch['seen'] == 0) {
            mysqli_query($dbConnect, "UPDATE " . DB_POSTS . " SET seen=" . time() . " WHERE id=" . $sql_fetch['id']);
        }
        
        $get[] = $sql_fetch;
    }
    
    return $get;
}

function SK_getStoryPublisherBox($timeline_id=0, $recipient_id=0, $placeholder='') {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $sk, $lang, $user;
    $continue = true;
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    if ($timeline_id == $user['id']) {
        $timeline = $user;
    } else {
        $timeline_id = SK_secureEncode($timeline_id);
        $timeline = SK_getUser($timeline_id);
    }
    
    if (!isset($recipient_id) or empty($recipient_id)) {
        $recipient_id = 0;
    }
    
    if (!is_numeric($recipient_id) or $recipient_id < 0) {
        return false;
    }
    
    $recipient_id = SK_secureEncode($recipient_id);
    
    if (SK_isBlocked($recipient_id)) {
        return false;
    }
    
    if ($timeline_id == $recipient_id) {
        $recipient_id = 0;
    }
    
    if ($recipient_id > 0) {
        $recipient = SK_getUser($recipient_id, true);
        
        if (!isset($recipient['id'])) {
            return false;
        }
        
        if ($recipient['type'] == "user") {
            
            if ($recipient['timeline_post_privacy'] == "following") {
                
                if (!SK_isFollowing($user['id'], $recipient_id)) {
                    $continue = false;
                }
            } elseif ($recipient['timeline_post_privacy'] == "none") {
                $continue = false;
            }
        } elseif ($recipient['type'] == "page") {
            
            if ($recipient['timeline_post_privacy'] != "everyone") {
                
                if (!SK_isPageAdmin($recipient_id)) {
                    $continue = false;
                }
            }
        } elseif ($recipient['type'] == "group") {
            
            if ($recipient['timeline_post_privacy'] == "members") {
                
                if (!SK_isFollowing($recipient_id)) {
                    $continue = false;
                }
            } elseif ($recipient['timeline_post_privacy'] == "admins") {
                
                if (!SK_isGroupAdmin($recipient_id)) {
                    $continue = false;
                }
            }
        }
        
        $sk['input']['recipient'] = $recipient;
    }
    
    if (empty($placeholder)) {
        $placeholder = $lang['post_textarea_label'];
    }
    
    if ($continue == true) {
        $sk['input']['timeline'] = $timeline;
        $sk['input']['placeholder'] = $placeholder;
        return SK_getPage('story/publisher-box/content');
    }
}

function SK_getStories($data=array( 'type' => 'all', 'after_post_id' => 0, 'publisher_id' => 0, 'limit' => 5, 'exclude_activity' => false)) {
    global $dbConnect, $sk, $user;
    
    if (empty($data['type'])) {
        $data['type'] = 'all';
    }
    
    $subquery_one = "id>0";
    
    if (!empty($data['after_post_id']) && is_numeric($data['after_post_id']) && $data['after_post_id'] > 0) {
        $data['after_post_id'] = SK_secureEncode($data['after_post_id']);
        $subquery_one = "id<" . $data['after_post_id'] . " AND post_id<>" . $data['after_post_id'];
    } elseif (!empty($data['before_post_id']) && is_numeric($data['before_post_id']) && $data['before_post_id'] > 0) {
        $data['before_post_id'] = SK_secureEncode($data['before_post_id']);
        $subquery_one = "id>" . $data['before_post_id'] . " AND post_id<>" . $data['before_post_id'];
    }
    
    if (!empty($data['publisher_id']) && is_numeric($data['publisher_id']) && $data['publisher_id'] > 0) {
        $data['publisher_id'] = SK_secureEncode($data['publisher_id']);
        
        if (SK_isBlocked($data['publisher_id'])) {
            return array();
        }
        
        $sk_publisher = SK_getUser($data['publisher_id'], true);
    }
    
    $query_text = "SELECT id FROM " . DB_POSTS . " AS p1 WHERE " . $subquery_one;
    
    if (isset($sk_publisher['id'])) {
        
        if ($sk_publisher['type'] == "user") {

            if ($sk_publisher['post_privacy'] == "following" && $sk_publisher['id'] != $user['id'] && !SK_isFollowing($sk_publisher['id'])) {

                if ($GLOBALS['logged'] != true) {
                    return array();
                }

                switch ($data['type']) {
                    case 'texts':
                        $query_text .= " AND timeline_id=" . $user['id'] . " AND recipient_id=" . $data['publisher_id'] . " AND google_map_name='' AND media_id=0 AND soundcloud_uri='' AND youtube_video_id='' AND hidden=0 AND type1='story' AND type2='none'";
                    break;
                    
                    case 'photos':
                        $query_text .= " AND timeline_id=" . $user['id'] . " AND recipient_id=" . $data['publisher_id'] . " AND media_id>0 AND hidden=0 AND type1='story' AND type2='none'";
                    break;
                    
                    case 'videos':
                        $query_text .= " AND timeline_id=" . $user['id'] . " AND recipient_id=" . $data['publisher_id'] . " AND youtube_video_id<>'' AND hidden=0 AND type1='story' AND type2='none'";
                    break;
                    
                    case 'music':
                        $query_text .= " AND timeline_id=" . $user['id'] . " AND recipient_id=" . $data['publisher_id'] . " AND soundcloud_uri<>'' AND hidden=0 AND type1='story' AND type2='none'";
                    break;
                    
                    case 'places':
                        $query_text .= " AND timeline_id=" . $user['id'] . " AND recipient_id=" . $data['publisher_id'] . " AND google_map_name<>'' AND hidden=0 AND type1='story' AND type2='none'";
                    break;
                    
                    case 'likes':
                        $query_text .= " AND timeline_id=" . $user['id'] . " AND recipient_id=" . $data['publisher_id'] . " AND hidden=0 AND type1='story' AND type2='like'";
                    break;
                    
                    case 'shares':
                        $query_text .= " AND timeline_id=" . $user['id'] . " AND recipient_id=" . $data['publisher_id'] . " AND hidden=0 AND type1='story' AND type2='share'";
                    break;
                    
                    case 'timeline_post_by_others':
                        $query_text .= " AND timeline_id=" . $user['id'] . " AND recipient_id=" . $data['publisher_id'] . " AND type1='story' AND type2='none'";
                    break;
                    
                    default:
                        $query_text .= " AND timeline_id=" . $user['id'] . " AND recipient_id=" . $data['publisher_id'] . " AND hidden=0 AND type1='story' AND type2 IN ('none','share')";
                }
            } else {
                
                switch ($data['type']) {
                    case 'texts':
                        $query_text .= " AND timeline_id=" . $data['publisher_id'] . " AND recipient_id IN (0," . $data['publisher_id'] . ") AND google_map_name='' AND media_id=0 AND soundcloud_uri='' AND youtube_video_id='' AND hidden=0 AND type1='story' AND type2='none'";
                    break;
                    
                    case 'photos':
                        $query_text .= " AND timeline_id=" . $data['publisher_id'] . " AND recipient_id=0 AND media_id>0 AND hidden=0 AND type1='story' AND type2='none'";
                    break;
                    
                    case 'videos':
                        $query_text .= " AND timeline_id=" . $data['publisher_id'] . " AND recipient_id=0 AND youtube_video_id<>'' AND hidden=0 AND type1='story' AND type2='none'";
                    break;
                    
                    case 'music':
                        $query_text .= " AND timeline_id=" . $data['publisher_id'] . " AND recipient_id=0 AND soundcloud_uri<>'' AND hidden=0 AND type1='story' AND type2='none'";
                    break;
                    
                    case 'places':
                        $query_text .= " AND timeline_id=" . $data['publisher_id'] . " AND recipient_id=0 AND google_map_name<>'' AND hidden=0 AND type1='story' AND type2='none'";
                    break;
                    
                    case 'likes':
                        $query_text .= " AND timeline_id=" . $data['publisher_id'] . " AND recipient_id=0 AND hidden=0 AND type1='story' AND type2='like'";
                    break;
                    
                    case 'shares':
                        $query_text .= " AND timeline_id=" . $data['publisher_id'] . " AND recipient_id=0 AND hidden=0 AND type1='story' AND type2='share'";
                    break;
                    
                    case 'timeline_post_by_others':
                        $query_text .= " AND recipient_id=" . $data['publisher_id'] . " AND hidden=0 AND type1='story' AND type2='none'";
                    break;
                    
                    default:
                        $query_text .= " AND (timeline_id=" . $data['publisher_id'] . " OR recipient_id=" . $data['publisher_id'] . ") AND recipient_id NOT IN (SELECT id FROM " . DB_GROUPS . " WHERE group_privacy='secret') AND hidden=0 AND type1='story' AND type2 IN ('none','share')";
                }
            }
        } elseif ($sk_publisher['type'] == "page") {
            
            switch ($data['type']) {
                case 'texts':
                    $query_text .= " AND timeline_id=" . $data['publisher_id'] . " AND google_map_name='' AND media_id=0 AND soundcloud_uri='' AND youtube_video_id='' AND hidden=0 AND type1='story' AND type2='none'";
                break;
                
                case 'photos':
                    $query_text .= " AND timeline_id=" . $data['publisher_id'] . " AND media_id>0 AND hidden=0 AND type1='story' AND type2='none'";
                break;
                
                case 'videos':
                    $query_text .= " AND timeline_id=" . $data['publisher_id'] . " AND youtube_video_id<>'' AND hidden=0 AND type1='story' AND type2='none'";
                break;
                
                case 'music':
                    $query_text .= " AND timeline_id=" . $data['publisher_id'] . " AND soundcloud_uri<>'' AND hidden=0 AND type1='story' AND type2='none'";
                break;
                
                case 'places':
                    $query_text .= " AND timeline_id=" . $data['publisher_id'] . " AND google_map_name<>'' AND hidden=0 AND type1='story' AND type2='none'";
                break;
                
                case 'timeline_post_by_others':
                    $query_text .= " AND recipient_id=" . $data['publisher_id'] . " AND hidden=0 AND type1='story' AND type2='none'";
                break;
                
                default:
                    $query_text .= " AND timeline_id=" . $data['publisher_id'] . " AND hidden=0 AND type1='story' AND type2='none'";
            }
        } elseif ($sk_publisher['type'] == "group") {
            
            switch ($data['type']) {
                case 'texts':
                    $query_text .= " AND recipient_id=" . $data['publisher_id'] . " AND google_map_name='' AND media_id=0 AND soundcloud_uri='' AND youtube_video_id='' AND hidden=0 AND type1='story' AND type2='none'";
                break;
                
                case 'photos':
                    $query_text .= " AND recipient_id=" . $data['publisher_id'] . " AND media_id>0 AND hidden=0 AND type1='story' AND type2='none'";
                break;
                
                case 'videos':
                    $query_text .= " AND recipient_id=" . $data['publisher_id'] . " AND youtube_video_id<>'' AND hidden=0 AND type1='story' AND type2='none'";
                break;
                
                case 'music':
                    $query_text .= " AND recipient_id=" . $data['publisher_id'] . " AND soundcloud_uri<>'' AND hidden=0 AND type1='story' AND type2='none'";
                break;
                
                case 'places':
                    $query_text .= " AND recipient_id=" . $data['publisher_id'] . " AND google_map_name<>'' AND hidden=0 AND type1='story' AND type2='none'";
                break;
                
                default:
                    $query_text .= " AND recipient_id=" . $data['publisher_id'] . " AND hidden=0 AND type1='story' AND type2='none'";
            }
        }
    } else {
        
        if ($GLOBALS['logged'] !== true) {
            return false;
        }

        $default_type = "('none','share')";

        if ($data['exclude_activity'] == true) {
            $default_type = "('none')";
        }
        
        switch ($data['type']) {
            case 'texts':
                $query_text .= " AND (timeline_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND active=1) OR recipient_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND following_id IN (SELECT id FROM " . DB_GROUPS . "))) AND recipient_id NOT IN (SELECT id FROM " . DB_GROUPS . " WHERE group_privacy='secret') AND google_map_name='' AND media_id=0 AND soundcloud_uri='' AND youtube_video_id='' AND hidden=0 AND type1='story' AND type2='none'";
            break;
            
            case 'photos':
                $query_text .= " AND (timeline_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND active=1) OR recipient_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND following_id IN (SELECT id FROM " . DB_GROUPS . "))) AND recipient_id NOT IN (SELECT id FROM " . DB_GROUPS . " WHERE group_privacy='secret') AND media_id>0 AND hidden=0 AND type1='story' AND type2='none'";
            break;
            
            case 'videos':
                $query_text .= " AND (timeline_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND active=1) OR recipient_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND following_id IN (SELECT id FROM " . DB_GROUPS . "))) AND recipient_id NOT IN (SELECT id FROM " . DB_GROUPS . " WHERE group_privacy='secret') AND youtube_video_id<>'' AND hidden=0 AND type1='story' AND type2='none'";
            break;
            
            case 'music':
                $query_text .= " AND (timeline_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND active=1) OR recipient_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND following_id IN (SELECT id FROM " . DB_GROUPS . "))) AND recipient_id NOT IN (SELECT id FROM " . DB_GROUPS . " WHERE group_privacy='secret') AND soundcloud_uri<>'' AND hidden=0 AND type1='story' AND type2='none'";
            break;
            
            case 'places':
                $query_text .= " AND (timeline_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND active=1) OR recipient_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND following_id IN (SELECT id FROM " . DB_GROUPS . "))) AND recipient_id NOT IN (SELECT id FROM " . DB_GROUPS . " WHERE group_privacy='secret') AND google_map_name<>'' AND hidden=0 AND type1='story' AND type2='none'";
            break;
            
            case 'likes':
                $query_text .= " AND (timeline_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND active=1) OR recipient_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND following_id IN (SELECT id FROM " . DB_GROUPS . "))) AND recipient_id NOT IN (SELECT id FROM " . DB_GROUPS . " WHERE group_privacy='secret') AND hidden=0 AND type1='story' AND type2='like'";
            break;
            
            case 'shares':
                $query_text .= " AND (timeline_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND active=1) OR recipient_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND following_id IN (SELECT id FROM " . DB_GROUPS . "))) AND recipient_id NOT IN (SELECT id FROM " . DB_GROUPS . " WHERE group_privacy='secret') AND hidden=0 AND type1='story' AND type2='share'";
            break;
            
            default:
                $query_text .= " AND (timeline_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND active=1) OR recipient_id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND following_id IN (SELECT id FROM " . DB_GROUPS . "))) AND recipient_id NOT IN (SELECT id FROM " . DB_GROUPS . " WHERE group_privacy='secret') AND hidden=0 AND type1='story' AND type2 IN $default_type";
        }
    }
    
    if (empty($data['limit']) or !is_numeric($data['limit']) or $data['limit'] < 1) {
        $data['limit'] = 5;
    }
    
    $query_text .= " AND active=1 GROUP BY post_id ORDER BY id DESC LIMIT " . $data['limit'];
    
    if (isset($query_text))  {
        $get = array();
        $sql_query = mysqli_query($dbConnect, $query_text);
        
        while ($sql_fetch = mysqli_fetch_assoc($sql_query) ) {
            $story = SK_getStory($sql_fetch['id']);
            
            if (is_array($story)) {
                $get[] = $story;
            }
        }
    }
    
    return $get;
}

function SK_getStory($story_id=0, $view_all_comments=false) {
    global $dbConnect, $user, $sk;
    
    if (empty($story_id) or !is_numeric($story_id) or $story_id < 1) {
        return false;
    }
    
    $story_id = SK_secureEncode($story_id);
    $query_one = "SELECT id,active,activity_text,google_map_name,hidden,link_title,link_url,media_id,post_id,recipient_id,soundcloud_title,soundcloud_uri,text,time,timeline_id,type1,type2,youtube_video_id,youtube_title FROM " . DB_POSTS . " WHERE id=$story_id AND type1='story' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        $post = mysqli_fetch_assoc($sql_query_one);
        $post['publisher'] = SK_getUser($post['timeline_id']);
        
        if ( SK_isBlocked($post['timeline_id']) ) {
            return false;
        }
        
        if ($post['id'] == $post['post_id']) { 
            $story = $post;
        } else {
            $query_two = "SELECT id,active,activity_text,google_map_name,hidden,link_title,link_url,media_id,post_id,recipient_id,soundcloud_title,soundcloud_uri,text,time,timeline_id,type1,type2,youtube_video_id,youtube_title FROM " . DB_POSTS . " WHERE id=" . $post['post_id'] . " AND type1='story' AND type2='none' AND active=1";
            $sql_query_two = mysqli_query($dbConnect, $query_two);
            
            if (mysqli_num_rows($sql_query_two) != 1) {
                return false;
            }
            
            $sql_fetch_two = mysqli_fetch_assoc($sql_query_two);
            $story = $sql_fetch_two;
            $story['publisher'] = SK_getUser($story['timeline_id']);
        }

        if ($story['publisher']['type'] == "user") {

            if ($story['publisher']['post_privacy'] == "following") {

                if (!SK_isFollowing($user['id'], $story['publisher']['id'])) {
                    return false;
                }
            }
        }
        
        // Recipient, if applicable
        $story['recipient_exists'] = false;
        $story['recipient'] = '';
        
        if ($story['recipient_id'] > 0) {
            $story['recipient_exists'] = true;
            $story['recipient'] = SK_getUser($story['recipient_id']);
        }
        
        // Admin Rights
        $story['admin'] = false;
        
        if ($GLOBALS['logged'] == true) {
            
            // Publisher admin rights
            if ($story['publisher']['type'] == "user") {
                
                if ($story['publisher']['id'] == $user['id']) {
                    $story['admin'] = true;
                }
            } elseif ($story['publisher']['type'] == "page") {
                
                if (SK_isPageAdmin($story['publisher']['id'])) {
                    $story['admin'] = true;
                }
            } elseif ($story['publisher']['type'] == "group") {
                
                if (SK_isGroupAdmin($story['publisher']['id'])) {
                    $story['admin'] = true;
                }
            }
            
            // Recipient admin rights
            if ($story['recipient_exists'] == true) {
                
                if ($story['recipient']['type'] == "user") {
                    
                    if ($story['recipient']['id'] == $user['id']) {
                        $story['admin'] = true;
                    }
                } elseif ($story['recipient']['type'] == "page") {
                    
                    if (SK_isPageAdmin($story['recipient']['id'])) {
                        $story['admin'] = true;
                    }
                } elseif ($story['recipient']['type'] == "group") {
                    
                    if (SK_isGroupAdmin($story['recipient']['id'])) {
                        $story['admin'] = true;
                    }
                }
            }
        }

        // Activity Text
        if (!empty($story['activity_text'])) {
            preg_match('/\[album\]([0-9]+)\[\/album\]/i', $story['activity_text'], $activity_text_matches);

            $album_id = $activity_text_matches[1];
            $album_query = "SELECT id,name FROM " . DB_MEDIA . " WHERE id=" . $album_id;
            $album_sql_query = mysqli_query($dbConnect, $album_query);
            $album_sql_fetch = mysqli_fetch_assoc($album_sql_query);
            $activity_text_replace = '<a href="' . SK_smoothLink('index.php?tab1=album&tab2=' . $album_sql_fetch['id']) . '" data-href="?tab1=album&tab2=' . $album_sql_fetch['id'] . '">' . $album_sql_fetch['name'] . '</a>';
            $story['activity_text'] = str_replace($activity_text_matches[0], $activity_text_replace, $story['activity_text']);
        }
        
        // Emoticons
        $story['text'] = SK_emoticonize($story['text']);
        $story['text'] = SK_getMarkup($story['text']);
        
        // Media (Photos || Youtube || Soundcloud)
        $story['media_exists'] = false;
        $story['media_type'] = false;
        $query_three = "SELECT id FROM " . DB_MEDIA . " WHERE id=" . $story['media_id'] . " AND active=1";
        $sql_query_three = mysqli_query($dbConnect, $query_three);
        
        if ($story['media_id'] > 0 && mysqli_num_rows($sql_query_three) > 0) {
            $story['media_exists'] = true;
            $story['media_type'] = 'photos';
            
            $query_four = "SELECT id,type,temp FROM " . DB_MEDIA . " WHERE id=" . $story['media_id'] . " AND active=1";
            $sql_query_four = mysqli_query($dbConnect, $query_four);
            $sql_fetch_four = mysqli_fetch_assoc($sql_query_four);
            
            if ($sql_fetch_four['type'] == "photo") {
                $sql_fetch_four = SK_getMedia($sql_fetch_four['id']);
                $story['media_num'] = 1;
                
                $story['media'][] = array(
                    'id' => $sql_fetch_four['id'],
                    'url' => $sk['config']['site_url'] . '/' . $sql_fetch_four['url'] . '.' . $sql_fetch_four['extension'],
                    'post_id' => $story['id'],
                    'post_url' => SK_smoothLink('index.php?tab1=story&id=' . $story['id'])
                );
            } elseif ($sql_fetch_four['type'] == "album") {
                $query_five = "SELECT id FROM " . DB_MEDIA . " WHERE album_id=" . $sql_fetch_four['id'] . " AND active=1 ORDER BY id DESC";

                if ($sql_fetch_four['temp'] == 0) {
                    $query_five .= " LIMIT 6";
                }

                $sql_query_five = mysqli_query($dbConnect, $query_five);
                $sql_numrows_fives = mysqli_num_rows($sql_query_five);
                $story['media_num'] = $sql_numrows_fives;
                
                while ($sql_fetch_five = mysqli_fetch_assoc($sql_query_five) ) {
                    $sql_fetch_five = SK_getMedia($sql_fetch_five['id']);
                    
                    $story['media'][] = array(
                        'id' => $sql_fetch_five['id'],
                        'url' => $sk['config']['site_url'] . '/' . $sql_fetch_five['url'] . '_100x100.' . $sql_fetch_five['extension'],
                        'post_id' => $sql_fetch_five['post_id'],
                        'post_url' => $sql_fetch_five['post_url']
                    );
                }
            }
        } elseif (!empty($story['soundcloud_uri'])) {
            $story['media_exists'] = true;
            $story['media_type'] = 'soundcloud';
            $story['media']['url'] = $story['soundcloud_uri'];
        } elseif (!empty($story['youtube_video_id'])) {
            $story['media_exists'] = true;
            $story['media_type'] = 'youtube';
            $story['media']['id'] = $story['youtube_video_id'];
        }
        
        // Location
        $story['location_exists'] = false;
        
        if (!empty($story['google_map_name'])) {
            $story['location_exists'] = true;
            $story['location']['name'] = $story['google_map_name'];
        }
        
        // Via
        $story['via_type'] = '';
        
        if ($story['id'] != $post['id'] && $story['timeline_id'] != $post['timeline_id']) {
            $story['via_type'] = $post['type2'];
            
            if ($post['type2'] == "with") {
                $story['via_type'] = 'tag';
            }
            
            $story['via'] = $post['publisher'];
        }
        
        // View all comments link, if applicable
        $story['view_all_comments'] = false;
        $comment_num = SK_countPostComments($story_id);
        
        if ($view_all_comments == false) {
            
            if ($comment_num > 3) {
                $story['view_all_comments'] = true;
            }
            
            $comment_num = 3;
        }
        
        // Comments
        $comment_html = '';
        
        foreach (SK_getComments($story['id'], $comment_num) as $sk['comment']) {
            $comment_html .= SK_getPage('comment/content');
        }
        
        $story['comments'] = $comment_html;
        
        // Comment publisher box
        $show_comment_publisher_box = true;
        $story['comment']['publisher_box'] = '';
        
        if ($story['publisher']['type'] == "user") {
            
            if ($story['publisher']['comment_privacy'] == "following") {
                
                if (!SK_isFollowing($user['id'], $story['publisher']['id'])) {
                    $show_comment_publisher_box = false;
                }
            }
        } elseif ($story['publisher']['type'] == "group") {
            
            if (!SK_isFollowing($story['publisher']['id'], $user['id'])) {
                $show_comment_publisher_box = false;
            }
        }
        
        if ($show_comment_publisher_box == true) {
            
            if ($story['publisher']['type'] == "page" && SK_isPageAdmin($story['publisher']['id'])) {
                $story['comment']['publisher_box'] = SK_getCommentPublisherBox($story['id'], $story['publisher']['id']);
            } else {
                $story['comment']['publisher_box'] = SK_getCommentPublisherBox($story['id']);
            }
        }
        
        return $story;
    }
}

function SK_getPostType($post_id=0) {
    global $dbConnect;
    
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $query_one = "SELECT id,type1,type2 FROM " . DB_POSTS . " WHERE id=$post_id AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
        $type = $sql_fetch_one['type1'];
        
        if ($sql_fetch_one['type1'] == "story" && $sql_fetch_one['type2']=="comment") {
            $type = 'comment';
        }
        
        return $type;
    }
}

function SK_getPostTimelineId($post_id=0) {
    global $dbConnect;
    
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $query_one = "SELECT timeline_id FROM " . DB_POSTS . " WHERE id=$post_id AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
        return $sql_fetch_one['timeline_id'];
    }
}

function SK_getPostRecipientId($post_id=0) {
    global $dbConnect;
    
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $query_one = "SELECT recipient_id FROM " . DB_POSTS . " WHERE id=$post_id AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
        return $sql_fetch_one['recipient_id'];
    }
}

function SK_getPostLikes($post_id=0) {
    global $dbConnect;
    $get = array();
    
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $query_one = "SELECT id,timeline_id FROM " . DB_POSTS . " WHERE post_id=$post_id AND type1 IN ('story','comment') AND type2='like' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) > 0) {
        
        while ($sql_fetch_one = mysqli_fetch_assoc($sql_query_one)) {
            $get[] = SK_getUser($sql_fetch_one['timeline_id']);
        }
    }
    
    return $get;
}

function SK_getPostShares($post_id=0) {
    global $dbConnect;
    $get = array();
    
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $query_one = "SELECT id,timeline_id FROM " . DB_POSTS . " WHERE post_id=$post_id AND type1='story' AND type2='share' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) > 0) {
        
        while ($sql_fetch_one = mysqli_fetch_assoc($sql_query_one)) {
            $get[] = SK_getUser($sql_fetch_one['timeline_id']);
        }
    }
    
    return $get;
}

function SK_getPostFollowers($post_id=0) {
    global $dbConnect;
    $get = array();
    
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $query_one = "SELECT id,timeline_id FROM " . DB_POSTS . " WHERE post_id=$post_id AND type1='story' AND type2='follow' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) > 0) {
        
        while ($sql_fetch_one = mysqli_fetch_assoc($sql_query_one)) {
            $get[] = SK_getUser($sql_fetch_one['timeline_id']);
        }
    }
    
    return $get;
}

function SK_getComments($post_id=0, $limit=3) {
    global $dbConnect;
    $get = array();
    
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    if (!isset($limit) or empty($limit) or !is_numeric($limit) or $limit < 1) {
        $limit = 3;
    }
    
    $post_id = SK_secureEncode($post_id);
    $query_one = "SELECT id FROM " . DB_POSTS . " WHERE post_id=$post_id AND type1='story' AND type2='comment' AND active=1";
    
    if (($comments_num = SK_countPostComments($post_id)) > $limit) {
        $query_one .= " LIMIT " . ($comments_num-$limit) . ",$limit";
    }
    
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) > 0) {
        
        while ($sql_fetch_one = mysqli_fetch_assoc($sql_query_one)) {
            $get[] = SK_getComment($sql_fetch_one['id']);
        }
    }
    
    return $get;
}

function SK_getComment($comment_id=0) {
    global $dbConnect, $user;
    $get = array();
    
    if (empty($comment_id) or !is_numeric($comment_id) or $comment_id < 1) {
        return $get;
    }
    
    $comment_id = SK_secureEncode($comment_id);
    $query_one = "SELECT id,active,post_id,text,time,timeline_id,type1,type2 FROM " . DB_POSTS . " WHERE id=$comment_id AND type1='story' AND type2='comment' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
        $sql_fetch_one['publisher'] = SK_getUser($sql_fetch_one['timeline_id']);
        
        $sql_fetch_one['text'] = SK_emoticonize($sql_fetch_one['text']);
        $sql_fetch_one['text'] = SK_getMarkup($sql_fetch_one['text']);
        
        // Admin rights
        if ($GLOBALS['logged'] == true) {
            $sql_fetch_one['admin'] = false;
            
            if ($sql_fetch_one['publisher']['type'] == "user" && $sql_fetch_one['publisher']['id'] == $user['id']) {
                $sql_fetch_one['admin'] = true;
            } elseif ($sql_fetch_one['publisher']['type'] == "page" && SK_isPageAdmin($sql_fetch_one['publisher']['id'])) {
                $sql_fetch_one['admin'] = true;
            } elseif ($sql_fetch_one['publisher']['type'] == "group" && SK_isGroupAdmin($sql_fetch_one['publisher']['id'])) {
                $sql_fetch_one['admin'] = true;
            }
        }
        
        $get = $sql_fetch_one;
    }
    
    return $get;
}

function SK_getCommentPublisherBox($post_id=0, $timeline_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $sk, $user;
    $continue = true;
    $post_id = SK_secureEncode($post_id);
    
    if (($post_timeline_id = SK_getPostTimelineId($post_id)) == false) {
        return false;
    }
    
    if (SK_isBlocked($post_timeline_id)) {
        return false;
    }
    
    $post_timeline = SK_getUser($post_timeline_id);
    
    if ($post_timeline['type'] == "user" && $post_timeline['id'] != $user['id']) {
        
        if ($post_timeline['comment_privacy'] == "following") {
            
            if (!SK_isFollowing($post_timeline['id'], $user['id'])) {
                $continue = false;
            }
        }
    }
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    if ($timeline_id == $user['id']) {
        $timeline = $user;
    } else {
        $timeline = SK_getUser($timeline_id);
        
        if ($timeline['type'] == "page" && !SK_isPageAdmin($timeline['id'])) {
            $continue = false;
        } elseif ($timeline['type'] == "group" && !SK_isGroupAdmin($timeline['id'])) {
            $continue = false;
        }
    }
    
    if ($continue == false) {
        return false;
    }
    
    $sk['input']['post']['id'] = $post_id;
    $sk['input']['timeline'] = $timeline;
    return SK_getPage('comment/publisher-box/content');
}

function SK_getPostLikeButton($post_id=0) {
    global $sk;
    
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $story_type = SK_getPostType($post_id);
    
    if (!preg_match('/(story|comment)/', $story_type)) {
        return false;
    }
    
    if ($story_type == "story") {
        $sk['story']['id'] = $post_id;
        
        if (SK_isPostLiked($post_id)) {
            return SK_getPage('story/unlike-button');
        } else {
            return SK_getPage('story/like-button');
        }
    } elseif ($story_type == "comment") {
        $sk['comment']['id'] = $post_id;
        
        if (SK_isPostLiked($post_id)) {
            return SK_getPage('comment/unlike-button');
        } else {
            return SK_getPage('comment/like-button');
        }
    }
}

function SK_getPostShareButton($post_id=0) {
    global $sk;
    
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    
    if (SK_getPostType($post_id) != "story") {
        return false;
    }
    
    $sk['story']['id'] = $post_id;
    
    if (SK_isPostShared($post_id)) {
        return SK_getPage('story/unshare-button');
    } else {
        return SK_getPage('story/share-button');
    }
}

function SK_getPostFollowButton($post_id=0) {
    global $sk;
    
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    
    if (SK_getPostType($post_id) != "story") {
        return '';
    }
    
    $sk['story']['id'] = $post_id;
    
    if (SK_isPostFollowed($post_id) ) {
        return SK_getPage('story/unfollow-button');
    } else {
        return SK_getPage('story/follow-button');
    }
}

function SK_getPostLikeActivityButton($post_id=0) {
    global $sk;
    
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $story_type = SK_getPostType($post_id);
    
    if (!preg_match('/(story|comment)/', $story_type)) {
        return '';
    }
    
    if ($story_type == "story") {
        $sk['story']['id'] = $post_id;
        return SK_getPage('story/like-activity');
    } elseif ($story_type == "comment") {
        $sk['comment']['id'] = $post_id;
        return SK_getPage('comment/like-activity');
    }
}

function SK_getPostCommentActivityButton($post_id=0) {
    global $sk;
    
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    
    if (SK_getPostType($post_id) != "story") {
        return false;
    }
    
    $sk['story']['id'] = $post_id;
    return SK_getPage('story/comment-activity');
}

function SK_getPostShareActivityButton($post_id=0) {
    global $sk;
    
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    
    if (SK_getPostType($post_id) != "story") {
        return false;
    }
    
    $sk['story']['id'] = $post_id;
    return SK_getPage('story/share-activity');
}

function SK_getPostFollowActivityButton($post_id=0) {
    global $sk;
    
    if (!isset($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    
    if (SK_getPostType($post_id) != "story") {
        return false;
    }
    
    $sk['story']['id'] = $post_id;
    return SK_getPage('story/follow-activity');
}

function SK_getMedia($file_id=0) {
    global $dbConnect, $sk;
    
    if (empty($file_id) or !is_numeric($file_id) or $file_id < 1) {
        return false;
    }
    
    $file_id = SK_secureEncode($file_id);
    $query_one = "SELECT id,active,album_id,extension,name,post_id,temp,timeline_id,type,url FROM " . DB_MEDIA . " WHERE id=$file_id";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
        $sql_fetch_one['complete_url'] = $sk['config']['site_url'] . '/' . $sql_fetch_one['url'] . '.' . $sql_fetch_one['extension'];
        $sql_fetch_one['post_url'] = SK_smoothLink('index.php?tab1=story&id=' . $sql_fetch_one['post_id']);
        return $sql_fetch_one;
    }
}

function SK_getPageCategories($main_category=0, $check_only=false) {
    if ($GLOBALS['logged'] !== true) {
        return array();
    }
    
    global $dbConnect;
    $get = array();
    
    if (empty($main_category) or !is_numeric($main_category) or $main_category < 0) {
        $main_category = 0;
    }
    
    $main_category = SK_secureEncode($main_category);
    
    if ($check_only == true) {
        $query = "SELECT id FROM " . DB_PAGE_CATEGORIES . " WHERE id=$main_category AND active=1";
        $sql_query = mysqli_query($dbConnect, $query);
        return mysqli_num_rows($sql_query);
    } else {
        $query = "SELECT id,category_id,name FROM " . DB_PAGE_CATEGORIES . " WHERE category_id=$main_category AND active=1";
        $sql_query = mysqli_query($dbConnect, $query);
        
        while ($sql_fetch = mysqli_fetch_assoc($sql_query)) {
            $get[] = $sql_fetch;
        }
    }
    
    return $get;
}

function SK_getPageCategoryData($category_id=0) {
    global $dbConnect;
    
    if (empty($category_id) or !is_numeric($category_id) or $category_id < 1) {
        return false;
    }
    
    $category_id = SK_secureEncode($category_id);
    $query = "SELECT id,category_id,name FROM " . DB_PAGE_CATEGORIES . " WHERE id=$category_id AND active=1";
    $sql_query = mysqli_query($dbConnect, $query);
    
    if (mysqli_num_rows($sql_query) == 1) {
        $sql_fetch = mysqli_fetch_assoc($sql_query);
        return $sql_fetch;
    }
}

function SK_getMarkup($text, $link=true, $hashtag=true, $mention=true) {
    global $dbConnect;
    
    if ($link == true) {
        // Links
        $link_search = '/\[a\](.*?)\[\/a\]/i';
        
        if (preg_match_all($link_search, $text, $matches)) {
            
            foreach ($matches[1] as $match) {
                $match_decode = urldecode($match);
                $match_url = $match_decode;
                
                if (!preg_match("/http(|s)\:\/\//", $match_decode)) {
                    $match_url = 'http://' . $match_url;
                }
                
                $text = str_replace('[a]' . $match . '[/a]', '<a href="' . strip_tags($match_url) . '" target="_blank" rel="nofollow">' . $match_decode . '</a>', $text);
            }
        }
    }
    
    if ($hashtag == true) {
        // Hashtags
        $hashtag_regex = '/(#\[([0-9]+)\])/i';
        preg_match_all($hashtag_regex, $text, $matches);
        $match_i = 0;
        
        foreach ($matches[1] as $match) {
            $hashtag = $matches[1][$match_i];
            $hashkey = $matches[2][$match_i];
            $hashdata = SK_getHashtag($hashkey);
            
            if (is_array($hashdata)) {
                $hashlink = '<a href="' . SK_smoothLink('index.php?tab1=hashtag&query=' . $hashdata['tag']) . '" data-href="?tab1=hashtag&query=' . $hashdata['tag'] . '">#' . $hashdata['tag'] . '</a>';
                $text = str_replace($hashtag, $hashlink, $text);
            }
            
            $match_i++;
        }
    }
    
    if ($mention == true) {
        // @Mentions
        $mention_regex = '/@\[([0-9]+)\]/i';
        
        if (preg_match_all($mention_regex, $text, $matches)) {
            
            foreach ($matches[1] as $match) {
                $match = SK_secureEncode($match);
                $match_user = SK_getUser($match);
                
                $match_search = '@[' . $match . ']';
                $match_replace = '<a href="' . $match_user['url'] . '" data-href="?tab1=timeline&id=' . $match_user['username'] . '">' . $match_user['name'] . '</a>';
                
                if (isset($match_user['id'])) {
                    $text = str_replace($match_search, $match_replace, $text);
                }
            }
        }
    }
    
    return $text;
}

function SK_getEmoticons() {
    global $config, $emo;
    $emoticon = array();
    
    if (!isset($emo) or !is_array($emo)) {
        return false;
    }
    
    foreach ($emo as $code => $img) {
        $emoticon[addslashes($code)] = $config['theme_url'] . '/emoticons/' . $img;
    }
    
    return array_unique($emoticon);
}

function SK_getMonths() {
    global $lang;
    $months[1] = array('january', $lang['january']);
    $months[2] = array('february', $lang['february']);
    $months[3] = array('march', $lang['march']);
    $months[4] = array('april', $lang['april']);
    $months[5] = array('may', $lang['may']);
    $months[6] = array('june', $lang['june']);
    $months[7] = array('july', $lang['july']);
    $months[8] = array('august', $lang['august']);
    $months[9] = array('september', $lang['september']);
    $months[10] = array('october', $lang['october']);
    $months[11] = array('november', $lang['november']);
    $months[12] = array('december', $lang['december']);
    return $months;
}

function SK_countAnnouncements() {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }

    global $dbConnect, $user;

    $get = array();
    $query = "SELECT COUNT(id) AS count FROM " . DB_ANNOUNCEMENTS . " WHERE id NOT IN (SELECT announcement_id FROM " . DB_ANNOUNCEMENT_VIEWS . " WHERE account_id=" . $user['id'] . ")";
    $sql_query = mysqli_query($dbConnect, $query);
    $sql_fetch = mysqli_fetch_assoc($sql_query);

    return $sql_fetch['count'];
}

function SK_countNotifications($data=array()) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    $get = array();
    
    if (empty($data['account_id']) or $data['account_id'] == 0) {
        $data['account_id'] = $user['id'];
        $account = $user;
    }
    
    if (!is_numeric($data['account_id']) or $data['account_id'] < 1) {
        return false;
    }
    
    if ($data['account_id'] != $user['id']) {
        $data['account_id'] = SK_secureEncode($data['account_id']);
        $account = SK_getUser($data['account_id']);
    }
    
    $query_one = "SELECT COUNT(id) AS count FROM " . DB_NOTIFICATIONS . " WHERE timeline_id=" . $account['id'] . " AND active=1";
    
    if (isset($data['unread']) && $data['unread'] == true) {
        $query_one .= " AND seen=0";
    }
    
    $query_one .= " ORDER BY id DESC";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);

    return $sql_fetch_one['count'];
}

function SK_countFollowing($timeline_id=0) {
    global $dbConnect, $user;
    
    if (empty($timeline_id) or $timeline_id == 0) {
        
        if ($GLOBALS['logged'] == true) {
            $timeline_id = $user['id'];
        }
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $timeline_id = SK_secureEncode($timeline_id);
    $query = "SELECT COUNT(id) AS count FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=$timeline_id AND following_id<>$timeline_id AND active=1) AND type='user' AND active=1";
    $sql_query = mysqli_query($dbConnect, $query);
    $sql_fetch = mysqli_fetch_assoc($sql_query);

    return $sql_fetch['count'];
}

function SK_countFollowers($timeline_id=0) {
    global $dbConnect, $user;
    
    if (empty($timeline_id) or $timeline_id == 0) {
        
        if ($GLOBALS['logged'] == true) {
            $timeline_id = $user['id'];
        }
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $timeline_id = SK_secureEncode($timeline_id);
    $query = "SELECT COUNT(id) AS count FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT follower_id FROM " . DB_FOLLOWERS . " WHERE following_id=$timeline_id AND follower_id<>$timeline_id AND active=1) AND active=1";
    $sql_query = mysqli_query($dbConnect, $query);
    $sql_fetch = mysqli_fetch_assoc($sql_query);
    
    return $sql_fetch['count'];
}

function SK_countPageLikes($timeline_id=0) {
    global $dbConnect, $user;
    
    if (empty($timeline_id) or $timeline_id == 0) {
        
        if ($GLOBALS['logged'] == true) {
            $timeline_id = $user['id'];
        }
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $timeline_id = SK_secureEncode($timeline_id);
    $query = "SELECT COUNT(id) AS count FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=$timeline_id AND following_id<>$timeline_id AND active=1) AND type='page' AND active=1";
    $sql_query = mysqli_query($dbConnect, $query);
    $sql_fetch = mysqli_fetch_assoc($sql_query);
    
    return $sql_fetch['count'];
}

function SK_countGroupJoined($timeline_id=0) {
    global $dbConnect, $user;
    
    if (empty($timeline_id) or $timeline_id == 0) {
        
        if ($GLOBALS['logged'] == true) {
            $timeline_id = $user['id'];
        }
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $timeline_id = SK_secureEncode($timeline_id);
    $query = "SELECT COUNT(id) AS count FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=$timeline_id AND following_id<>$timeline_id AND active=1) AND type='group' AND active=1";
    $sql_query = mysqli_query($dbConnect, $query);
    $sql_fetch = mysqli_fetch_assoc($sql_query);
    
    return $sql_fetch['count'];
}

function SK_countFollowRequests($timeline_id=0) {
    global $dbConnect, $user;
    
    if (empty($timeline_id) or $timeline_id == 0) {
        
        if ($GLOBALS['logged'] == true) {
            $timeline_id = $user['id'];
        }
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $timeline_id = SK_secureEncode($timeline_id);
    $query = "SELECT COUNT(id) AS count FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT follower_id FROM " . DB_FOLLOWERS . " WHERE following_id=$timeline_id AND follower_id<>$timeline_id AND active=0) AND active=1";
    $sql_query = mysqli_query($dbConnect, $query);
    $sql_fetch = mysqli_fetch_assoc($sql_query);
    
    return $sql_fetch['count'];
}

function SK_countPageAdmins($page_id=0) {
    global $dbConnect;
    
    if (!isset($page_id) or empty($page_id) or !is_numeric($page_id) or $page_id < 1) {
        return false;
    }
    
    $page_id = SK_secureEncode($page_id);
    $query = "SELECT COUNT(DISTINCT admin_id) AS count FROM " . DB_PAGE_ADMINS . " WHERE page_id=$page_id AND active=1";
    $sql_query = mysqli_query($dbConnect, $query);
    $sql_fetch = mysqli_fetch_assoc($sql_query);
    
    return $sql_fetch['count'];
}

function SK_countGroupAdmins($group_id=0) {
    global $dbConnect;
    
    if (!isset($group_id) or empty($group_id) or !is_numeric($group_id) or $group_id < 1) {
        return false;
    }
    
    $group_id = SK_secureEncode($group_id);
    $query = "SELECT COUNT(DISTINCT admin_id) AS count FROM " . DB_GROUP_ADMINS . " WHERE group_id=$group_id AND active=1";
    $sql_query = mysqli_query($dbConnect, $query);
    $sql_fetch = mysqli_fetch_assoc($sql_query);
    
    return $sql_fetch['count'];
}

function SK_countManagedPages() {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    $get = array();
    
    $query_one = "SELECT COUNT(id) AS count FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT page_id FROM " . DB_PAGE_ADMINS . " WHERE admin_id=" . $user['id'] . " AND page_id IN (SELECT id FROM " . DB_PAGES .") AND active=1) AND type='page' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    $sql_fetch = mysqli_fetch_assoc($sql_query_one);
    
    return $sql_fetch['count'];
}

function SK_countManagedGroups() {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    $get = array();
    
    $query_one = "SELECT COUNT(id) AS count FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT group_id FROM " . DB_GROUP_ADMINS . " WHERE admin_id=" . $user['id'] . " AND group_id IN (SELECT id FROM " . DB_GROUPS .") AND active=1) AND type='group' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    $sql_fetch = mysqli_fetch_assoc($sql_query_one);
    
    return $sql_fetch['count'];
}

function SK_countOnlines($timeline_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    $data = array();
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $query_text = "SELECT COUNT(id) AS count FROM " . DB_ACCOUNTS . " WHERE id IN (SELECT following_id FROM " . DB_FOLLOWERS . " WHERE follower_id=$timeline_id AND following_id<>$timeline_id AND active=1) AND type='user' AND last_logged>" . (time()-15) . " AND active=1 ORDER BY last_logged DESC";
    $sql_query = mysqli_query($dbConnect, $query_text);
    $sql_fetch = mysqli_fetch_assoc($sql_query);
    
    return $sql_fetch['count'];
}

function SK_countMessages($data=array()) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (empty($data['timeline_id']) or $data['timeline_id'] == 0) {
        $data['timeline_id'] = $user['id'];
    }
    
    if (!is_numeric($data['timeline_id']) or $data['timeline_id'] < 1) {
        return false;
    }
    
    $data['timeline_id'] = SK_secureEncode($data['timeline_id']);
    $timeline = SK_getUser($data['timeline_id']);
    
    if (empty($timeline['id'])) {
        return false;
    }
    
    if ($timeline['type'] == "user" && $timeline['id'] != $user['id']) {
        return false;
    } elseif ($timeline['type'] == "page" && !SK_isPageAdmin($timeline['id'])) {
        return false;
    } elseif ($timeline['type'] == "group") {
        return false;
    }
    
    if (isset($data['recipient_id']) && is_numeric($data['recipient_id']) && $data['recipient_id'] > 0) {
        $data['recipient_id'] = SK_secureEncode($data['recipient_id']);
        
        if (isset($data['new']) && $data['new'] == true) {
            $query = "SELECT COUNT(id) AS count FROM " . DB_POSTS . " WHERE type1='message' AND active=1 AND timeline_id=" . $data['recipient_id'] . " AND recipient_id=" . $timeline['id'];
        } else {
            $query = "SELECT COUNT(id) AS count FROM " . DB_POSTS . " WHERE type1='message' AND active=1 AND ((timeline_id=" . $data['recipient_id'] . " AND recipient_id=" . $timeline['id'] . ") OR (timeline_id=" . $timeline['id'] . " AND recipient_id=" . $data['recipient_id'] . "))";
        }
    } else {
        $query = "SELECT COUNT(DISTINCT timeline_id) AS count FROM " . DB_POSTS . " WHERE type1='message' AND active=1 AND recipient_id=" . $timeline['id'];
    }
    
    if (isset($data['new']) && $data['new'] == true) {
        $query .= " AND seen=0";
    }
    
    $sql_query = mysqli_query($dbConnect, $query);
    $sql_fetch = mysqli_fetch_assoc($sql_query);
    
    return $sql_fetch['count'];
}

function SK_countPosts($timeline_id=0) {
    global $dbConnect, $user;
    
    if (empty($timeline_id) or $timeline_id == 0) {
        
        if ($GLOBALS['logged'] !== true) {
            return false;
        }
        
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    $timeline_id = SK_secureEncode($timeline_id);
    $timeline = SK_getUser($timeline_id);
    
    if (!isset($timeline['id'])) {
        return false;
    }
    
    $subquery = "timeline_id=$timeline_id AND recipient_id=0";
    
    if ($timeline['type'] == "group") {
        $subquery = "recipient_id=$timeline_id";
    }
    
    $query = "SELECT COUNT(id) AS count FROM " . DB_POSTS . " WHERE $subquery AND type1='story' AND type2='none' AND hidden=0 AND active=1";
    $sql_query = mysqli_query($dbConnect, $query);
    $sql_fetch = mysqli_fetch_assoc($sql_query);
    
    return $sql_fetch['count'];
}

function SK_countPostLikes($post_id=0) {
    global $dbConnect;
    $get = array();
    
    if (!preg_match('/(story|comment)/', SK_getPostType($post_id))) {
        return $get;
    }
    
    $query_one = "SELECT COUNT(id) AS count FROM " . DB_POSTS . " WHERE post_id=$post_id AND type2='like' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    $sql_fetch = mysqli_fetch_assoc($sql_query_one);
    
    return $sql_fetch['count'];
}

function SK_countPostComments($post_id=0) {
    global $dbConnect;
    $get = array();
    
    if (SK_getPostType($post_id) != "story") {
        return $get;
    }
    
    $query_one = "SELECT COUNT(id) AS count FROM ". DB_POSTS ." WHERE post_id=$post_id AND type1='story' AND type2='comment' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    $sql_fetch = mysqli_fetch_assoc($sql_query_one);
    
    return $sql_fetch['count'];
}

function SK_countPostCommenters($post_id=0) {
    global $dbConnect;
    $get = array();
    
    if (SK_getPostType($post_id) != "story") {
        return $get;
    }
    
    $query_one = "SELECT COUNT(DISTINCT timeline_id) AS count FROM ". DB_POSTS ." WHERE post_id=$post_id AND type1='story' AND type2='comment' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    $sql_fetch = mysqli_fetch_assoc($sql_query_one);
    
    return $sql_fetch['count'];
}

function SK_countPostShares($post_id=0) {
    global $dbConnect;
    $get = array();
    
    if (SK_getPostType($post_id) != "story") {
        return $get;
    }
    
    $query_one = "SELECT COUNT(id) AS count FROM " . DB_POSTS . " WHERE post_id=$post_id AND type1='story' AND type2='share' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    $sql_fetch = mysqli_fetch_assoc($sql_query_one);
    
    return $sql_fetch['count'];
}

function SK_countPostFollows($post_id=0) {
    global $dbConnect;
    $get = array();
    
    if (SK_getPostType($post_id) != "story") {
        return $get;
    }
    
    $query_one = "SELECT COUNT(id) AS count FROM " . DB_POSTS . " WHERE post_id=$post_id AND type1='story' AND type2='follow' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    $sql_fetch = mysqli_fetch_assoc($sql_query_one);
    
    return $sql_fetch['count'];
}

/* Register functions */
function SK_registerNotification($data=array()) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user, $lang;
    
    if (!isset($data['recipient_id']) or empty($data['recipient_id']) or !is_numeric($data['recipient_id']) or $data['recipient_id'] < 1) {
        return false;
    }
    
    if (!isset($data['post_id']) or empty($data['post_id'])) {
        $data['post_id'] = 0;
    }
    
    if (!is_numeric($data['post_id']) or $data['recipient_id'] < 0) {
        return false;
    }
    
    if (empty($data['notifier_id']) or $data['notifier_id'] == 0) {
        $data['notifier_id'] = $user['id'];
    }
    
    if (!is_numeric($data['notifier_id']) or $data['notifier_id'] < 1) {
        return false;
    }
    
    if ($data['notifier_id'] == $user['id']) {
        $notifier = $user;
    } else {
        $data['notifier_id'] = SK_secureEncode($data['notifier_id']);
        $notifier = SK_getUser($data['notifier_id']);
        
        if (!isset($notifier['id'])) {
            return false;
        }
    }
    
    if ($notifier['type'] == "user" && $notifier['id'] != $user['id']) {
        return false;
    } elseif ($notifier['type'] == "page" && !SK_isPageAdmin($notifier['id'])) {
        return false;
    } elseif ($notifier['type'] == "group" && !SK_isGroupAdmin($notifier['id'])) {
        return false;
    }
    
    if ($data['recipient_id'] == $data['notifier_id']) {
        return false;
    }
    
    if (!isset($data['text'])) {
        $data['text'] = '';
    }
    
    if (!isset($data['type']) or empty($data['type'])) {
        return false;
    }
    
    if(!isset($data['url']) or empty($data['url'])) {
        return false;
    }
    
    $data['recipient_id'] = SK_secureEncode($data['recipient_id']);
    $data['post_id'] = SK_secureEncode($data['post_id']);
    $data['notifier_id'] = SK_secureEncode($data['notifier_id']);
    $text = SK_secureEncode($data['text']);
    $type = SK_secureEncode($data['type']);
    $url = $data['url'];
    $recipient = SK_getUser($data['recipient_id']);
    
    if (!isset($recipient['id'])) {
        return false;
    }
    
    if ($data['post_id'] > 0) {
        $post = SK_getStory($data['post_id']);
        
        if (!preg_match('/(story|comment)/', SK_getPostType($post['id']))) {
            return false;
        }
    }
    
    if (empty($text)) {
        
        if (isset($post['id'])) {
            $post['text'] = SK_secureEncode(strip_tags($post['text']));
            
            if ($type == "like") {
                $count = SK_countPostLikes($post['id']);
                
                if (SK_isPostLiked($post['id'])) {
                    $count = $count - 1;
                }
                
                if ($count > 1) {
                    $text .= str_replace('{count}', ($count-1), $lang['notif_other_people']) . ' ';
                }
                
                if ($post['timeline_id'] == $recipient['id']) {
                    $text .= str_replace('{post}', substr($post['text'], 0, 45), $lang['likes_your_post']);
                }
            }
            
            if ($type == "comment") {
                $count = SK_countPostCommenters($post['id']);
                
                if ($count > 1) {
                    $text .= str_replace('{count}', ($count-1), $lang['notif_other_people']) . ' ';
                }
                
                if ($post['timeline_id'] == $recipient['id']) {
                    $text .= str_replace('{post}', substr($post['text'], 0, 45), $lang['commented_on_post']);
                } else {
                    $post['timeline'] = SK_getUser($post['timeline_id']);
                    $text .= str_replace(
                        array(
                            '{user}',
                            '{post}'
                            ),
                        array(
                            $post['timeline']['name'],
                            substr($post['text'], 0, 45)
                            ),
                        $lang['commented_on_user_post']
                        );
                }
            }
            
            if ($type == "share") {
                $count = SK_countPostShares($post['id']);
                
                if (SK_isPostShared($post['id'])) {
                    $count = $count - 1;
                }
                
                if ($count > 1) {
                    $text .= str_replace('{count}', ($count-1), $lang['notif_other_people']) . ' ';
                }
                
                if ($post['timeline_id'] == $recipient['id']) {
                    $text .= str_replace('{post}', substr($post['text'], 0, 45), $lang['shared_your_post']);
                }
            }
        } else {
            
            if ($type == "following") {
                $count = SK_countFollowers($recipient['id']);
                
                if ($count > 1) {
                    $text .= str_replace('{count}', ($count-1), $lang['following_you_plural']);
                } else {
                    $text .= $lang['following_you_singular'];
                }
            }
        }
    }
    
    if (empty($text)) {
        return false;
    }
    
    $query_one = "SELECT id FROM " . DB_NOTIFICATIONS . " WHERE timeline_id=" . $recipient['id'] . " AND post_id=" . $data['post_id'] . " AND type='$type' AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) > 0) {
        $query_two = "DELETE FROM " . DB_NOTIFICATIONS . " WHERE timeline_id=" . $recipient['id'] . " AND post_id=" . $data['post_id'] . " AND type='$type' AND active=1";
        $sql_query_two = mysqli_query($dbConnect, $query_two);
    }
    
    if (!isset($data['undo']) or $data['undo'] != true) {
        $query_three = "INSERT INTO " . DB_NOTIFICATIONS . " (timeline_id,active,notifier_id,post_id,text,time,type,url) VALUES (" . $recipient['id'] . ",1," . $notifier['id'] . "," . $data['post_id'] . ",'$text'," . time() . ",'$type','$url')";
        $sql_query_three = mysqli_query($dbConnect, $query_three);
        
        if ($sql_query_three) {
            return true;
        }
    }
}

function SK_registerUser($data=0) {
    if ($GLOBALS['logged'] == true) {
        return false;
    }
    
    global $dbConnect;
    
    if (!is_array($data)) {
        return false;
    }
    
    if (!empty($data['name']) && !empty($data['username']) && !empty($data['email']) && !empty($data['password']) && !empty($data['gender'])) {
        $name = SK_secureEncode($data['name']);
        $username = SK_secureEncode($data['username']);
        $email = SK_secureEncode($data['email']);
        $password = trim($data['password']);
        $md5_password = md5($password);
        $gender = SK_secureEncode($data['gender']);
        $birthday = '1-1-1990';
        $current_city = '';
        $hometown = '';
        $about = '';
        
        if (!SK_validateUsername($username)) {
            return false;
        }
        
        if (is_numeric($username)) {
            return false;
        }
        
        if (!SK_validateEmail($email)) {
            return false;
        }
        
        if (!preg_match('/(male|female)/', $gender)) {
            return false;
        }

        if (isset($data['birthday']) && is_array($data['birthday'])) {
            $birthday = SK_secureEncode(implode('-', $data['birthday']));
        }
        
        if (!empty($data['current_city'])) {
            $current_city = SK_secureEncode($data['current_city']);
        }

        if (!empty($data['hometown'])) {
            $hometown = SK_secureEncode($data['hometown']);
        }

        if (!empty($data['about'])) {
            $about = SK_secureEncode($data['about']);
        }

        $query_one = "INSERT INTO " . DB_ACCOUNTS . " (active,about,cover_id,email,email_verification_key,name,password,time,type,username) VALUES (1,'$about',0,'$email','" . md5(SK_generateKey()) . "','$name','$md5_password'," . time() . ",'user','$username')";
        $sql_query_one = mysqli_query($dbConnect, $query_one);
        
        if ($sql_query_one) {
            $user_id = mysqli_insert_id($dbConnect);
            $query_two = "INSERT INTO " . DB_USERS . " (id,birthday,gender,current_city,hometown) VALUES ($user_id,'$birthday','$gender','$current_city','$hometown')";
            $sql_query_two = mysqli_query($dbConnect, $query_two);
            
            if ($sql_query_two) {
                $get = SK_getUser($user_id, true);
                return $get;
            }
        }
    }
}

function SK_registerPage($data=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (!is_array($data)) {
        return false;
    }
    
    if (!empty($data['name']) && !empty($data['username']) &&!empty($data['about']) && !empty($data['category_id'])) {
        $name = SK_secureEncode($data['name']);
        $username = SK_secureEncode($data['username']);
        $about = SK_secureEncode($data['about']);
        $category_id = SK_secureEncode($data['category_id']);
        
        if (!SK_validateUsername($username)) {
            return false;
        }
        
        if (!SK_getPageCategories($category_id, true)) {
            return false;
        }
        
        $query_one = "INSERT INTO ". DB_ACCOUNTS ." (about,active,cover_id,email,name,password,time,type,username) VALUES ('$about',1,0,'$username','$name','" . md5(SK_generateKey()) . "'," . time() . ",'page','$username')";
        $sql_query_one = mysqli_query($dbConnect,$query_one);
        
        if ($sql_query_one) {
            $page_id = mysqli_insert_id($dbConnect);
            $query_two = "INSERT INTO " . DB_PAGES . " (id,category_id) VALUES ($page_id,$category_id)";
            $sql_query_two = mysqli_query($dbConnect, $query_two);
            
            if ($sql_query_two) {
                SK_registerFollow($page_id);
                $query_three = "INSERT INTO " . DB_PAGE_ADMINS . " (active,admin_id,page_id,role) VALUES (1," . $user['id'] . ",$page_id,'admin')";
                mysqli_query($dbConnect, $query_three);
                $get = array(
                    'id' => $page_id,
                    'username' => $username
                );
                return $get;
            }
        }
    }
}

function SK_registerGroup($data=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (!is_array($data)) {
        return false;
    }
    
    if (!empty($data['name']) && !empty($data['username']) && !empty($data['about']) && !empty($data['privacy'])) {
        $name = SK_secureEncode($data['name']);
        $username = SK_secureEncode($data['username']);
        $about = SK_secureEncode($data['about']);
        $privacy = SK_secureEncode($data['privacy']);
        
        if (!SK_validateUsername($username)) {
            return false;
        }
        
        if (!preg_match('/(open|closed|secret)/', $privacy)) {
            return false;
        }
        
        $query_one = "INSERT INTO " . DB_ACCOUNTS . " (about,active,cover_id,email,name,password,time,type,username) VALUES ('$about',1,0,'$username','$name','" . md5(SK_generateKey()) . "'," . time() . ",'group','$username')";
        $sql_query_one = mysqli_query($dbConnect, $query_one);
        
        if ($sql_query_one) {
            $group_id = mysqli_insert_id($dbConnect);
            $query_two = "INSERT INTO " . DB_GROUPS . " (id,group_privacy) VALUES ($group_id,'open')";
            $sql_query_two = mysqli_query($dbConnect,$query_two);
            
            if ($sql_query_two) {
                SK_registerFollow($group_id);
                $query_three = "INSERT INTO " . DB_GROUP_ADMINS . " (active,admin_id,group_id) VALUES (1," . $user['id'] . ",$group_id)";
                $sql_query_three = mysqli_query($dbConnect, $query_three);
                
                $query_four = "UPDATE " . DB_GROUPS . " SET group_privacy='$privacy' WHERE id=$group_id";
                $sql_query_four = mysqli_query($dbConnect, $query_four);
                $get = array(
                    'id' => $group_id,
                    'username' => $username
                );
                return $get;
            }
        }
    }
}

function SK_registerFollow($following_id=0, $timeline_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user, $config, $lang;
    
    if (!isset($following_id) or empty($following_id) or !is_numeric($following_id) or $following_id < 1) {
        return false;
    }
    
    $following_id = SK_secureEncode($following_id);
    $following = SK_getUser($following_id);
    
    if (!isset($following['id'])) {
        return false;
    }
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    if ($timeline_id == $user['id']) {
        $timeline = $user;
        
        if (SK_isBlocked($following_id)) {
            return false;
        }
    } else {
        $timeline_id = SK_secureEncode($timeline_id);
        $timeline = SK_getUser($timeline_id);
        
        if (!isset($timeline['id'])) {
            return false;
        }
    }
    
    if ($timeline['type'] == "user" && $timeline['id'] !== $user['id']) {
        return false;
    } elseif ($timeline['type'] == "page" && !SK_isPageAdmin($timeline['id'])) {
        return false;
    } elseif ($timeline['type'] == "group" && !SK_isGroupAdmin($timeline['id'])) {
        return false;
    }
    
    if (SK_isFollowing($following['id'], $timeline_id)) {
        return false;
    }

    $active = 1;
    $can_follow = true;
    
    if ($following['type'] == "user" && $following['follow_privacy'] == "following" && !SK_isFollowing($timeline['id'], $following['id']) ) {
        $can_follow = false;
    }
    
    if ($following['type'] == "user" && $following['confirm_followers'] == 1) {
        $active = 0;
    }

    if ($config['friends'] == true) {
        $active = 0;
    }

    if ($following['type'] == "page") {
        $active = 1;
    }

    if ($following['type'] == "group") {

        if ($following['group_privacy'] == "open") {
            $active = 1;
        }

        if ($following['group_privacy'] == "closed") {
            
            if (SK_isGroupAdmin($following['id'])) {
                $active = 1;
            } else {
                $active = 0;
            }
        }

        if ($following['group_privacy'] == "secret") {
            
            if (SK_isGroupAdmin($following['id'])) {
                $active = 1;
            } else {
                return false;
            }
        }
    }
    
    if ($can_follow == true) {
        $register_query = "INSERT INTO " . DB_FOLLOWERS . " (active,follower_id,following_id,time) VALUES ($active," . $timeline['id'] . "," . $following['id'] . "," . time() . ")";
        $sql_register_query = mysqli_query($dbConnect, $register_query);
        
        if ($sql_register_query) {
            
            if ($following['type'] == "user" && $active == 1) {
                $notification_data_array = array(
                    'recipient_id' => $following['id'],
                    'type' => 'following',
                    'url' => 'index.php?tab1=timeline&tab2=followers&id=' . $following['username']
                );

                SK_registerNotification($notification_data_array);
            }
            
            return true;
        }
    }
}

function SK_registerPageAdmin($page_id=0, $admin_id=0, $admin_role='') {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user, $lang;
    
    if (!isset($page_id) or empty($page_id) or !is_numeric($page_id) or $page_id < 1) {
        return false;
    }
    
    if (!isset($admin_id) or empty($admin_id) or !is_numeric($admin_id) or $admin_id < 1) {
        return false;
    }
    
    if (!isset($admin_role) or empty($admin_role)) {
        return false;
    }
    
    $page_id = SK_secureEncode($page_id);
    $admin_id = SK_secureEncode($admin_id);
    $admin_role = SK_secureEncode($admin_role);
    
    if (!preg_match('/(admin|editor)/', $admin_role)) {
        return false;
    }
    
    if (SK_isPageAdmin($page_id) != "admin") {
        return false;
    }
    
    if (!SK_isFollowing($user['id'], $admin_id)) {
        return false;
    }
    
    if (SK_isBlocked($page_id) or SK_isBlocked($admin_id)) {
        return false;
    }
    
    $page = SK_getUser($page_id);
    $admin = SK_getUser($admin_id);
    
    if (!isset($page['id']) or $page['type'] != "page") {
        return false;
    }
    
    if (!isset($admin['id']) or $admin['type'] != "user") {
        return false;
    }
    
    if (SK_isPageAdmin($page_id, $admin_id)) {
        $query_one = "UPDATE " . DB_PAGE_ADMINS . " SET role='$admin_role' WHERE page_id=$page_id AND admin_id=$admin_id AND active=1";
        $sql_query_one = mysqli_query($dbConnect, $query_one);
        
        if ($sql_query_one) {
            return true;
        }
    } else {
        $query_one = "INSERT INTO " . DB_PAGE_ADMINS . " (active,admin_id,page_id,role) VALUES (1,$admin_id,$page_id,'$admin_role')";
        $sql_query_one = mysqli_query($dbConnect, $query_one);
        
        if ($sql_query_one) {
            $notification_data_array = array(
                'recipient_id' => $admin_id,
                'notifier_id' => $page['id'],
                'type' => 'page_add_admin',
                'text' => $lang['made_page_admin'],
                'url' => 'index.php?tab1=timeline&id=' . $page['username']
            );
            SK_registerNotification($notification_data_array);
            
            return true;
        }
    }
}

function SK_registerGroupMember($group_id=0, $member_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user, $lang;
    
    if (!isset($group_id) or empty($group_id) or !is_numeric($group_id) or $group_id < 1) {
        return false;
    }
    
    if (!isset($member_id) or !is_numeric($member_id) or $member_id < 1) {
        return false;
    }
    
    $group_id = SK_secureEncode($group_id);
    $member_id = SK_secureEncode($member_id);
    $group = SK_getUser($group_id, true);
    $continue = false;
    
    if (!isset($group['id']) or $group['type'] != "group") {
        return false;
    }
    
    if ($member_id == $user['id']) {
        $member = $user;
    } else {
        $member = SK_getUser($member_id);
    }
    
    if (!isset($member['id']) or $member['type'] != "user") {
        return false;
    }
    
    if (SK_isFollowing($group['id'], $member['id'])) {
        return false;
    }
    
    if ($group['add_privacy'] == "admins") {
        
        if (SK_isGroupAdmin($group['id'])) {
            $continue = true;
        }
    } elseif ($group['add_privacy'] == "members") {
        
        if (SK_isFollowing($group['id'])) {
            $continue = true;
        }
    }
    
    if ($continue == true) {
        
        if (SK_isFollowRequested($group['id'], $member['id'])) {
            $query_one = "UPDATE " . DB_FOLLOWERS . " SET active=1 WHERE follower_id=" . $member['id'] . " AND following_id=" . $group['id'] . " AND active=0";
            $sql_query_one = mysqli_query($dbConnect, $query_one);
            
            if ($sql_query_one) {
                $notification_data_array = array(
                    'recipient_id' => $member['id'],
                    'text' => str_replace('{group_name}', '[b weight=500]'. $group['name'] .'[/b]', $lang['accepted_group_join_request']),
                    'type' => 'accepted_group_member',
                    'url' => 'index.php?tab1=timeline&id=' . $group['username']
                );
                SK_registerNotification($notification_data_array);
                
                return true;
            }
        } else {
            $query_one = "INSERT INTO " . DB_FOLLOWERS . " (active,follower_id,following_id,time) VALUES (1," . $member['id'] . "," . $group['id'] . "," . time() . ")";
            $sql_query_one = mysqli_query($dbConnect, $query_one);
            
            if ($sql_query_one) {
                $notification_data_array = array(
                    'recipient_id' => $member['id'],
                    'text' => str_replace('{group_name}', '[b weight=500]'. $group['name'] .'[/b]', $lang['added_to_group']),
                    'type' => 'made_group_member',
                    'url' => 'index.php?tab1=timeline&id=' . $group['username']
                );
                SK_registerNotification($notification_data_array);
                
                return true;
            }
        }
    }
}

function SK_registerGroupAdmin($group_id=0, $admin_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user, $lang;
    
    if (!isset($group_id) or empty($group_id) or !is_numeric($group_id) or $group_id < 1) {
        return false;
    }
    
    if (!isset($admin_id) or empty($admin_id) or !is_numeric($admin_id) or $admin_id < 1) {
        return false;
    }
    
    $group_id = SK_secureEncode($group_id);
    $admin_id = SK_secureEncode($admin_id);
    $group = SK_getUser($group_id);
    
    if (!isset($group['id']) or $group['type'] != "group") {
        return false;
    }
    
    if ($admin_id == $user['id']) {
        $admin = $user;
    } else {
        $admin = SK_getUser($admin_id);
    }
    
    if (!isset($admin['id']) or $admin['type'] != "user") {
        return false;
    }
    
    if (!SK_isGroupAdmin($group['id'])) {
        return false;
    }
    
    if (SK_isGroupAdmin($group['id'], $admin['id'])) {
        return false;
    }
    
    $query_one = "INSERT INTO " . DB_GROUP_ADMINS . " (active,admin_id,group_id) VALUES (1," . $admin['id'] . "," . $group['id'] . ")";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if ($sql_query_one) {
        $notification_data_array = array(
            'recipient_id' => $admin['id'],
            'text' => str_replace('{group_name}', '[b weight=500]'. $group['name'] .'[/b]', $lang['made_group_admin']),
            'type' => 'made_group_admin',
            'url' => 'index.php?tab1=timeline&id=' . $group['username']
        );
        SK_registerNotification($notification_data_array);
        
        return true;
    }
}

function SK_registerPost($data=array()) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $config, $user, $lang;
    $post_ability = false;
    $other_media = false;
    
    if (empty($data['timeline_id']) or $data['timeline_id'] == 0) {
        $data['timeline_id'] = $user['id'];
    }
    
    if (!is_numeric($data['timeline_id']) or $data['timeline_id'] < 1) {
        return false;
    }
    
    if ($data['timeline_id'] == $user['id']) {
        $timeline = $user;
    } else {
        $data['timeline_id'] = SK_secureEncode($data['timeline_id']);
        $timeline = SK_getUser($data['timeline_id'], true);
    }
    
    if ($timeline['type'] == "user" && $timeline['id'] != $user['id']) {
        return false;
    } elseif ($timeline['type'] == "page" && !SK_isPageAdmin($timeline['id'])) {
        return false;
    } elseif ($timeline['type'] == "group") {
        return false;
    }
    
    if (!isset($data['type']) or !preg_match('/(story|comment|message)/', $data['type'])) {
        return false;
    }
    
    $text = '';
    $media_id = 0;
    $soundcloud_uri = '';
    $soundcloud_title = '';
    $youtube_video_id = '';
    $youtube_title = '';
    $google_map_name = '';
    $recipient_id = 0;
    $type1 = $data['type'];
    $type2 = 'none';
    
    if ($type1 == "comment") {
        $type1 = 'story';
        $type2 = 'comment';
    }
    
    if (!empty($data['text'])) {
        $text = $data['text'];
        
        if ($data['type'] == "story") {

            if ($config['story_character_limit'] > 0) {

                if (strlen($data['text']) > $config['story_character_limit']) {
                    return false;
                }
            }
        } elseif ($data['type'] == "comment") {

            if ($config['comment_character_limit'] > 0) {

                if (strlen($data['text']) > $config['comment_character_limit']) {
                    return false;
                }
            }
        } elseif ($data['type'] == "message") {

            if ($config['message_character_limit'] > 0) {

                if (strlen($data['text']) > $config['message_character_limit']) {
                    return false;
                }
            }
        }

        // Links
        $link_regex = '/(http\:\/\/|https\:\/\/|www\.)([^\ ]+)/i';
        $i = 0;
        preg_match_all($link_regex, $text, $matches);
        
        foreach ($matches[0] as $match) {
            $match_url = strip_tags($match);
            $syntax = '[a]' . urlencode($match_url) . '[/a]';
            $text = str_replace($match, $syntax, $text);
        }
        
        // #Hashtags
        $hashtag_regex = '/#([^`~!@$%^&*\#()\-+=\\|\/\.,<>?\'\":;{}\[\]* ]+)/i';
        preg_match_all($hashtag_regex, $text, $matches);
        
        foreach ($matches[1] as $match) {
            
            if (!is_numeric($match)) {
                $hashdata = SK_getHashtag($match);
                
                if (is_array($hashdata)) {
                    $match_search = '#' . $match;
                    $match_replace = '#[' . $hashdata['id'] . ']';
                    $text = str_replace($match_search, $match_replace, $text);
                    $hashtag_query = "UPDATE " . DB_HASHTAGS . " SET last_trend_time=" . time() . ",trend_use_num=" . ($hashdata['trend_use_num'] + 1) . " WHERE id=" . $hashdata['id'];
                    $hashtag_sql_query = mysqli_query($dbConnect, $hashtag_query);
                }
            }
        }
        
        // @Mentions
        $mention_regex = '/@([A-Za-z0-9_]+)/i';
        preg_match_all($mention_regex, $text, $matches);
        
        foreach ($matches[1] as $match) {
            $match = SK_secureEncode($match);
            $match_user = SK_getUser($match);
            
            $match_search = '@' . $match;
            $match_replace = '@[' . $match_user['id'] . ']';
            
            if (isset($match_user['id'])) {
                $text = str_replace($match_search, $match_replace, $text);
                $mentions[] = $match_user['id'];
            }
        }
        
        $text = SK_secureEncode($text);
        $post_ability = true;
    }
    
    if (!empty($data['recipient_id']) && is_numeric($data['recipient_id']) && $data['recipient_id'] > 0) {
        $recipient_id = SK_secureEncode($data['recipient_id']);
    }
    
    if ($recipient_id > 0) {
        $recipient = SK_getUser($recipient_id, true);
        
        if (empty($recipient['id'])) {
            return false;
        }
        
        if ($timeline['id'] == $recipient['id']) {
            return false;
        }
        
        if ($recipient['type'] == "user") {
            
            if ($type1 == "story") {
                
                if ($recipient['timeline_post_privacy'] == "following") {
                    
                    if (!SK_isFollowing($timeline['id'], $recipient['id'])) {
                        return false;
                    }
                } elseif ($recipient['timeline_post_privacy'] == "none") {
                    return false;
                }
            } elseif ($type1 == "message" && $recipient['message_privacy'] == "following") {
                
                if (!SK_isFollowing($timeline['id'], $recipient['id'])) {
                    return false;
                }
            }
        } elseif ($recipient['type'] == "page") {
            
            if ($type1 == "story" && $recipient['timeline_post_privacy'] != "everyone") {
                
                if (!SK_isPageAdmin($recipient['id'])) {
                    return false;
                }
            } elseif ($type1 == "message" && $recipient['message_privacy'] != "everyone") {
                return false;
            }
        } elseif ($recipient['type'] == "group") {
            
            if ($type1 == "story") {
                
                if ($recipient['timeline_post_privacy'] == "members") {
                    
                    if (!SK_isFollowing($recipient['id'])) {
                        return false;
                    }
                } elseif ($recipient['timeline_post_privacy'] == "admins") {
                    
                    if (!SK_isGroupAdmin($recipient['id'])) {
                        return false;
                    }
                }
            } elseif ($type1 == "message") {
                return false;
            }
        }
    }
    
    if (isset($data['photos']['name'])) {
        
        if (count($data['photos']['name']) == 1) {
            $photo_param = array(
                'tmp_name' => $data['photos']['tmp_name'][0],
                'name' => $data['photos']['name'][0],
                'size' => $data['photos']['size'][0]
            );
            $photo_data = SK_registerMedia($photo_param);
            
            if (isset($photo_data['id'])) {
                $media_id = $photo_data['id'];
                $other_media = true;
                $post_ability = true;
            }
        } else {
            $query_one = "INSERT INTO " . DB_MEDIA . " (timeline_id,active,name,type) VALUES (" . $timeline['id'] . ",1,'temp_" . SK_generateKey() . "','album')";
            $sql_query_one = mysqli_query($dbConnect, $query_one);
            
            if ($sql_query_one) {
                $media_id = mysqli_insert_id($dbConnect);
                
                for ($i = 0; $i < count($data['photos']['name']); $i++) {
                    $photo_param = array(
                        'tmp_name' => $data['photos']['tmp_name'][$i],
                        'name' => $data['photos']['name'][$i],
                        'size' => $data['photos']['size'][$i]
                    );
                    $photo_data = SK_registerMedia($photo_param, $media_id);
                    
                    if (!empty($photo_data['id'])) {
                        $query_one = "INSERT INTO " . DB_POSTS . " (active,google_map_name,hidden,media_id,time,timeline_id,recipient_id,type1,type2) VALUES (1,'$google_map_name',1," . $photo_data['id'] . "," . time() . "," . $timeline['id'] . ",$recipient_id,'$type1','$type2')";
                        $sql_query_one = mysqli_query($dbConnect, $query_one);
                        
                        if ($sql_query_one) {
                            $media_story_id = mysqli_insert_id($dbConnect);
                            
                            mysqli_query($dbConnect, "UPDATE " . DB_POSTS . " SET post_id=id WHERE id=$media_story_id");
                            mysqli_query($dbConnect, "UPDATE " . DB_MEDIA . " SET post_id=$media_story_id WHERE id=" . $photo_data['id']);
                            SK_registerPostFollow($media_story_id);
                        }
                    }
                }
                
                $other_media = true;
                $post_ability = true;
            }
        }
    } elseif (!empty($data['soundcloud_uri']) && !empty($data['soundcloud_title'])) {
        $soundcloud_uri = SK_secureEncode($data['soundcloud_uri']);
        $soundcloud_title = SK_secureEncode($data['soundcloud_title']);
        $post_ability = true;
        $other_media = true;
    } elseif (!empty($data['youtube_video_id'])) {
        $youtube_video_id = SK_secureEncode($data['youtube_video_id']);
        $post_ability = true;
        $other_media = true;
        
        $regex_one = '/^(http\:\/\/|https\:\/\/|)(www\.|)youtube\.com\/watch\?v\=([A-Za-z0-9_\-]+)/i';
        $regex_two = '/^(http\:\/\/|https\:\/\/|)(www\.|)youtu\.be\/([A-Za-z0-9_\-]+)/i';
        $regex_three = '/^(http\:\/\/|https\:\/\/|)(www\.|)youtube\.com\/embed\/([A-Za-z0-9_\-]+)/i';
        $regex_four = '/^(http\:\/\/|https\:\/\/|)(www\.|)youtube\.com\/v\/([A-Za-z0-9_\-]+)/i';
        
        if (preg_match($regex_one, $youtube_video_id, $matches)) {
            $youtube_video_id = $matches[3];
        } elseif (preg_match($regex_two, $youtube_video_id, $matches)) {
            $youtube_video_id = $matches[3];
        } elseif (preg_match($regex_three, $youtube_video_id, $matches)) {
            $youtube_video_id = $matches[3];
        } elseif (preg_match($regex_four, $youtube_video_id, $matches)) {
            $youtube_video_id = $matches[3];
        }
        
        if (!empty($data['youtube_title'])) {
            $youtube_title = SK_secureEncode($data['youtube_title']);
        }
    }
    
    if (!empty($data['google_map_name'])) {
        $google_map_name = SK_secureEncode($data['google_map_name']);
        
        if ($other_media == false) {
            $post_ability = true;
        }
    }
    
    if ($post_ability == true) {
        $query_one = "INSERT INTO " . DB_POSTS . " (active,google_map_name,media_id,soundcloud_title,soundcloud_uri,text,time,timeline_id,recipient_id,type1,type2,youtube_video_id,youtube_title) VALUES (1,'$google_map_name',$media_id,'$soundcloud_title','$soundcloud_uri','$text'," . time() . "," . $timeline['id'] . ",$recipient_id,'$type1','$type2','$youtube_video_id','$youtube_title')";
        $sql_query_one = mysqli_query($dbConnect, $query_one);
        
        if ($sql_query_one) {
            $post_id = mysqli_insert_id($dbConnect);
            $query_two = "UPDATE " . DB_POSTS . " SET post_id=$post_id WHERE id=$post_id";
            $sql_query_two = mysqli_query($dbConnect, $query_two);
            SK_registerPostFollow($post_id);
            
            if ($sql_query_two) {
                
                if (isset($recipient['id'])) {
                    $notification_data_array = array(
                        'recipient_id' => $recipient_id,
                        'post_id' => $post_id,
                        'text' => $lang['posted_on_timeline'],
                        'type' => 'timeline_wall_post',
                        'url' => 'index.php?tab1=story&id=' . $post_id
                    );
                    SK_registerNotification($notification_data_array);
                }
                
                if (isset($mentions) && is_array($mentions) && $type1 == "story") {
                    
                    foreach ($mentions as $mention) {
                        $notification_data_array = array(
                            'recipient_id' => $mention,
                            'post_id' => $post_id,
                            'text' => $lang['mentioned_in_post'],
                            'type' => 'post_mention',
                            'url' => 'index.php?tab1=story&id=' . $post_id
                        );
                        SK_registerNotification($notification_data_array);
                    }
                }
                
                return $post_id;
            }
        }
    }
}

function SK_registerPostLike($post_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (!isset($post_id) or empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $post_type = SK_getPostType($post_id);
    $post_timeline_id = SK_getPostTimelineId($post_id);
    
    if (!preg_match('/(story|comment)/', $post_type)) {
        return false;
    }
    
    if (empty($post_timeline_id) or SK_isBlocked($post_timeline_id)) {
        return false;
    }
    
    if (SK_isPostLiked($post_id)) {
        $query_two = "DELETE FROM " . DB_POSTS . " WHERE post_id=$post_id AND timeline_id=" . $user['id'] . " AND type1='$post_type' AND type2='like' AND active=1";
        $sql_query_two = mysqli_query($dbConnect, $query_two);
        
        if ($sql_query_two) {
            return true;
        }
    } else {
        $query_two = "INSERT INTO " . DB_POSTS . " (timeline_id,active,post_id,time,type1,type2) VALUES (" . $user['id'] . ",1,$post_id," . time() . ",'$post_type','like')";
        $sql_query_two = mysqli_query($dbConnect, $query_two);
        
        if ($sql_query_two) {
            return true;
        }
    }
}

function SK_registerPostShare($post_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (!isset($post_id) or empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $post_type = SK_getPostType($post_id);
    $post_timeline_id = SK_getPostTimelineId($post_id);
    
    if (($post_type = SK_getPostType($post_id)) != "story") {
        return false;
    }
    
    if (empty($post_timeline_id) or SK_isBlocked($post_timeline_id)) {
        return false;
    }
    
    if (SK_isPostShared($post_id)) {
        $query_two = "DELETE FROM " . DB_POSTS . " WHERE post_id=$post_id AND timeline_id=" . $user['id'] . " AND type1='$post_type' AND type2='share' AND active=1";
        $sql_query_two = mysqli_query($dbConnect, $query_two);
        
        if ($sql_query_two) {
            return true;
        }
    } else {
        $query_two = "INSERT INTO " . DB_POSTS . " (timeline_id,active,post_id,time,type1,type2) VALUES (" . $user['id'] . ",1,$post_id," . time() . ",'$post_type','share')";
        $sql_query_two = mysqli_query($dbConnect, $query_two);
        
        if ($sql_query_two) {
            return true;
        }
    }
}

function SK_registerPostFollow($post_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (!isset($post_id) or empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $post_type = SK_getPostType($post_id);
    $post_timeline_id = SK_getPostTimelineId($post_id);
    
    if (($post_type = SK_getPostType($post_id)) != "story") {
        return false;
    }
    
    if (SK_isBlocked($post_timeline_id)) {
        return false;
    }
    
    if (SK_isPostFollowed($post_id)) {
        $query_two = "DELETE FROM " . DB_POSTS . " WHERE post_id=$post_id AND timeline_id=" . $user['id'] . " AND type1='$post_type' AND type2='follow' AND active=1";
        $sql_query_two = mysqli_query($dbConnect, $query_two);
        
        if ($sql_query_two) {
            return true;
        }
    } else {
        $query_two = "INSERT INTO " . DB_POSTS . " (timeline_id,active,post_id,time,type1,type2) VALUES (" . $user['id'] . ",1,$post_id," . time() . ",'$post_type','follow')";
        $sql_query_two = mysqli_query($dbConnect, $query_two);
        
        if ($sql_query_two) {
            return true;
        }
    }
}

function SK_registerPostComment($post_id=0, $timeline_id=0, $text='') {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $config, $user;
    
    if (!isset($post_id) or empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    
    $post_id = SK_secureEncode($post_id);
    $post_type = SK_getPostType($post_id);
    $post_timeline_id = SK_getPostTimelineId($post_id);
    $post_timeline = SK_getUser($post_timeline_id);
    $continue = true;
    
    if (($post_type = SK_getPostType($post_id)) != "story") {
        return false;
    }
    
    if (empty($post_timeline['id'])) {
        return false;
    }
    
    if (SK_isBlocked($post_timeline['id'])) {
        return false;
    }
    
    if (empty($timeline_id) or $timeline_id == 0) {
        $timeline_id = $user['id'];
    }
    
    if (!is_numeric($timeline_id) or $timeline_id < 1) {
        return false;
    }
    
    if ($timeline_id == $user['id']) {
        $timeline = $user;
    } else {
        $timeline_id = SK_secureEncode($timeline_id);
        $timeline = SK_getUser($timeline_id);
    }
    
    if ($timeline['type'] == "user" && $timeline['id'] != $user['id']) {
        return false;
    } elseif ($timeline['type'] == "page" && !SK_isPageAdmin($timeline['id'])) {
        return false;
    } elseif ($timeline['type'] == "group" && !SK_isGroupAdmin($timeline['id'])) {
        return false;
    }
    
    if ($post_timeline['type'] == "user" && $post_timeline['id'] != $timeline['id']) {
        
        if ($post_timeline['comment_privacy'] == "following") {
            
            if (!SK_isFollowing($timeline['id'], $post_timeline['id'])) {
                $continue = false;
            }
        }
    } elseif ($post_timeline['type'] == "group") {
        
        if (!SK_isFollowing($timeline['id'], $post_timeline['id'])) {
            $continue = false;
        }
    }
    
    
    if (empty($text)) {
        return false;
    }

    if ($config['comment_character_limit'] > 0) {

        if (strlen($text) > $config['comment_character_limit']) {
            return false;
        }
    }
    
    // Links
    $link_regex = '/(http\:\/\/|https\:\/\/|www\.)([^\ ]+)/i';
    preg_match_all($link_regex, $text, $matches);
    
    foreach ($matches[0] as $match) {
        $match_url = strip_tags($match);
        $syntax = '[a]' . urlencode($match_url) . '[/a]';
        $text = str_replace($match, $syntax, $text);
    }
    
    // #Hashtags
    $hashtag_regex = '/#([^`~!@$%^&*\#()\-+=\\|\/\.,<>?\'\":;{}\[\]* ]+)/i';
    preg_match_all($hashtag_regex, $text, $matches);
    
    foreach ($matches[1] as $match) {
        $hashdata = SK_getHashtag($match);
        
        if (is_array($hashdata)) {
            $match_search = '#' . $match;
            $match_replace = '#[' . $hashdata['id'] . ']';
            $text = str_replace($match_search, $match_replace, $text);
        }
    }
    
    // @Mentions
    $mention_regex = '/@([A-Za-z0-9_]+)/i';
    preg_match_all($mention_regex, $text, $matches);
    
    foreach ($matches[1] as $match) {
        $match = SK_secureEncode($match);
        $match_user = SK_getUser($match);
        
        $match_search = '@' . $match;
        $match_replace = '@[' . $match_user['id'] . ']';
        
        if (isset($match_user['id'])) {
            $text = str_replace($match_search, $match_replace, $text);
            $mentions[] = $match_user['id'];
        }
    }
    
    $text = SK_secureEncode($text);
    
    if ($continue == false) {
        return false;
    }
    
    $query_two = "INSERT INTO " . DB_POSTS . " (timeline_id,active,post_id,text,time,type1,type2) VALUES ($timeline_id,1,$post_id,'$text'," . time() . ",'story','comment')";
    $sql_query_two = mysqli_query($dbConnect, $query_two);
    
    if ($sql_query_two) {
        $comment_id = mysqli_insert_id($dbConnect);
        
        if (!SK_isPostFollowed($post_id)) {
            SK_registerPostFollow($post_id);
        }
        
        foreach (SK_getPostFollowers($post_id) as $follower) {
            
            if ($follower['id'] != $post_timeline_id) {
                $notification_data_array = array(
                    'recipient_id' => $follower['id'],
                    'post_id' => $post_id,
                    'type' => 'comment',
                    'url' => 'index.php?tab1=story&id=' . $post_id
                );
                SK_registerNotification($notification_data_array);
            }
        }
        
        if (isset($mentions) && is_array($mentions)) {
            
            foreach ($mentions as $mention) {
                $notification_data_array = array(
                    'recipient_id' => $mention,
                    'post_id' => $comment_id,
                    'text' => $lang['mentioned_in_comment'],
                    'type' => 'post_mention',
                    'url' => 'index.php?tab1=story&id=' . $post_id . '#comment_' . $comment_id
                );
                SK_registerNotification($notification_data_array);
            }
        }
        
        return $comment_id;
    }
}

function SK_updateTimeline($data=array()) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (empty($data['timeline_id']) or $data['timeline_id'] == 0) {
        $data['timeline_id'] = $user['id'];
    }
    
    if (!is_numeric($data['timeline_id']) or $data['timeline_id'] < 1) {
        return false;
    }
    
    if ($data['timeline_id'] == $user['id']) {
        $timeline = $user;
    } else {
        $data['timeline_id'] = SK_secureEncode($data['timeline_id']);
        $timeline = SK_getUser($data['timeline_id']);
        
        if (!isset($timeline['id'])) {
            return false;
        }
    }
    
    if ($timeline['type'] == "user" && $timeline['id'] != $user['id']) {
        return false;
    } elseif ($timeline['type'] == "page" && !SK_isPageAdmin($timeline['id'])) {
        return false;
    } elseif ($timeline['type'] == "group" && !SK_isGroupAdmin($timeline['id'])) {
        return false;
    }
    
    $update_query_one = "UPDATE " . DB_ACCOUNTS . " SET ";
    
    if (!empty($data['name'])) {
        $data['name'] = SK_secureEncode($data['name']);
        $update_query_one .= "name='" . $data['name'] . "',";
    }
    
    if (!empty($data['username'])) {
        
        if (SK_validateUsername($data['username']) && SK_getUsernameStatus($data['username'], $timeline['id']) == 200) {
            $data['username'] = SK_secureEncode($data['username']);
            $update_query_one .= "username='" . $data['username'] . "',";
        } elseif (SK_getUsernameStatus($data['username'], $timeline['id']) == 201) {
            // do nothing!
        } else {
            return false;
        }
    }
    
    if (!empty($data['about'])) {
        $data['about'] = SK_secureEncode($data['about']);
        $update_query_one .= "about='" . $data['about'] . "',";
    }
    
    if ($timeline['type'] == "user") {
        
        if (!empty($data['email'])) {
            $data['email'] = SK_secureEncode($data['email']);
            $update_query_one .= "email='" . $data['email'] . "',";
        }
        
        if (isset($data['timezone'])) {
            $data['timezone'] = SK_secureEncode($data['timezone']);
            $timezones = SK_getTimezones();
            
            if (!empty($timezones[$data['timezone']])) {
                $update_query_one .= "timezone='" . $data['timezone'] . "',";
            }
        }
        
        if (!empty($data['new_password']) && !empty($data['current_password'])) {
            $update_pass = false;
            $password_query = "SELECT password FROM " . DB_ACCOUNTS . " WHERE id=" . $timeline['id'];
            $password_sql_query = mysqli_query($dbConnect, $password_query);
            
            if (mysqli_num_rows($password_sql_query) == 1) {
                $password_sql_fetch = mysqli_fetch_array($password_sql_query);
                
                if (md5(trim($data['current_password'])) == $password_sql_fetch['password']) {
                    $update_pass = true;
                }
            }
            
            if ($update_pass == true) {
                $update_query_one .= "password='" . md5(trim($data['new_password'])) . "',";
            } else {
                return false;
            }
        }
    }
    
    if (isset($data['avatar']['name'])) {
        $avatar_data = SK_registerMedia($data['avatar']);
        
        if (isset($avatar_data['id'])) {
            $update_query_one .= "avatar_id=" . $avatar_data['id'] . ",";
        }
    }
    
    if (isset($data['cover']['name'])) {
        $cover_data = SK_registerMedia($data['cover']);
        
        if (isset($cover_data['id'])) {
            $update_query_one .= "cover_id=" . $cover_data['id'] . ",";
        }
    }
    
    $update_query_one .= "active=1 WHERE id=" . $timeline['id'];
    $sql_query_one = mysqli_query($dbConnect, $update_query_one);
    
    if (!$sql_query_one) {
        return false;
    }
    
    if ($timeline['type'] == "user") {
        $update_query_two = "UPDATE " . DB_USERS . " SET ";
        
        if (isset($data['birthday']) && is_array($data['birthday'])) {
            $birthday = SK_secureEncode(implode('-', $data['birthday']));
            $update_query_two .= "birthday='$birthday',";
        }
        
        if (!empty($data['gender'])) {
            $data['gender'] = SK_secureEncode($data['gender']);
            
            if (preg_match('/(male|female)/', $data['gender'])) {
                $update_query_two .= "gender='" . $data['gender'] . "',";
            } else {
                return false;
            }
        }
        
        if (isset($data['current_city'])) {
            $data['current_city'] = SK_secureEncode($data['current_city']);
            $update_query_two .= "current_city='" . $data['current_city'] . "',";
        }
        
        if (isset($data['hometown'])) {
            $data['hometown'] = SK_secureEncode($data['hometown']);
            $update_query_two .= "hometown='" . $data['hometown'] . "',";
        }
        
        if (isset($data['confirm_followers']) && preg_match('/(0|1)/', $data['confirm_followers'])) {
            $data['confirm_followers'] = SK_secureEncode($data['confirm_followers']);
            $update_query_two .= "confirm_followers=" . $data['confirm_followers'] . ",";
        }
        
        if (isset($data['follow_privacy']) && preg_match('/(everyone|following)/', $data['follow_privacy'])) {
            $data['follow_privacy'] = SK_secureEncode($data['follow_privacy']);
            $update_query_two .= "follow_privacy='" . $data['follow_privacy'] . "',";
        }
        
        if (isset($data['comment_privacy']) && preg_match('/(everyone|following)/', $data['comment_privacy'])) {
            $data['comment_privacy'] = SK_secureEncode($data['comment_privacy']);
            $update_query_two .= "comment_privacy='" . $data['comment_privacy'] . "',";
        }
        
        if (isset($data['message_privacy']) && preg_match('/(everyone|following)/', $data['message_privacy'])) {
            $data['message_privacy'] = SK_secureEncode($data['message_privacy']);
            $update_query_two .= "message_privacy='" . $data['message_privacy'] . "',";
        }
        
        if (isset($data['timeline_post_privacy']) && preg_match('/(everyone|following|none)/', $data['timeline_post_privacy'])) {
            $data['timeline_post_privacy'] = SK_secureEncode($data['timeline_post_privacy']);
            $update_query_two .= "timeline_post_privacy='" . $data['timeline_post_privacy'] . "',";
        }

        if (isset($data['post_privacy']) && preg_match('/(everyone|following)/', $data['post_privacy'])) {
            $data['post_privacy'] = SK_secureEncode($data['post_privacy']);
            $update_query_two .= "post_privacy='" . $data['post_privacy'] . "',";
        }
        
        $update_query_two .= "id=id WHERE id=" . $timeline['id'];
        $sql_query_two = mysqli_query($dbConnect, $update_query_two);
        
        if ($sql_query_two) {
            return true;
        }
    } elseif ($timeline['type'] == "page") {
        $update_query_two = "UPDATE " . DB_PAGES . " SET ";
        
        if (!empty($data['timeline_post_privacy'])) {
            $data['timeline_post_privacy'] = SK_secureEncode($data['timeline_post_privacy']);
            $update_query_two .= "timeline_post_privacy='" . $data['timeline_post_privacy'] . "',";
        }
        
        if (!empty($data['message_privacy'])) {
            $data['message_privacy'] = SK_secureEncode($data['message_privacy']);
            $update_query_two .= "message_privacy='" . $data['message_privacy'] . "',";
        }
        
        if (isset($data['address'])) {
            $data['address'] = SK_secureEncode($data['address']);
            $update_query_two .= "address='" . $data['address'] . "',";
        }
        
        if (isset($data['awards'])) {
            $data['awards'] = SK_secureEncode($data['awards']);
            $update_query_two .= "awards='" . $data['awards'] . "',";
        }
        
        if (isset($data['phone'])) {
            $data['phone'] = SK_secureEncode($data['phone']);
            $update_query_two .= "phone='" . $data['phone'] . "',";
        }
        
        if (isset($data['products'])) {
            $data['products'] = SK_secureEncode($data['products']);
            $update_query_two .= "products='" . $data['products'] . "',";
        }
        
        if (isset($data['website'])) {
            $data['website'] = SK_secureEncode($data['website']);
            $update_query_two .= "website='" . $data['website'] . "',";
        }
        
        $update_query_two .= "id=id WHERE id=" . $timeline['id'];
        $sql_query_two = mysqli_query($dbConnect, $update_query_two);
        
        if ($sql_query_two) {
            return true;
        }
    } elseif ($timeline['type'] == "group") {
        $update_query_two = "UPDATE ". DB_GROUPS ." SET ";
        
        if (!empty($data['group_privacy']) && preg_match('/(open|closed|secret)/', $data['group_privacy'])) {
            $data['group_privacy'] = SK_secureEncode($data['group_privacy']);
            $update_query_two .= "group_privacy='" . $data['group_privacy'] . "',";
        }
        
        if (!empty($data['add_privacy']) && preg_match('/(members|admins)/', $data['add_privacy'])) {
            $data['add_privacy'] = SK_secureEncode($data['add_privacy']);
            $update_query_two .= "add_privacy='" . $data['add_privacy'] . "',";
        }
        
        if (!empty($data['timeline_post_privacy'])) {
            $data['timeline_post_privacy'] = SK_secureEncode($data['timeline_post_privacy']);
            $update_query_two .= "timeline_post_privacy='" . $data['timeline_post_privacy'] . "',";
        }
        
        $update_query_two .= "id=id WHERE id=" . $timeline['id'];
        $sql_query_two = mysqli_query($dbConnect, $update_query_two);
        
        if ($sql_query_two) {
            return true;
        }
    }
}

function SK_registerMedia($upload, $album_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect;
    set_time_limit(0);
    
    if (!file_exists('photos/' . date('Y'))) {
        mkdir('photos/' . date('Y'), 0777, true);
    }
    
    if (!file_exists('photos/' . date('Y') . '/' . date('m'))) {
        mkdir('photos/' . date('Y') . '/' . date('m'), 0777, true);
    }
    
    $photo_dir = 'photos/' . date('Y') . '/' . date('m');
    
    if (is_uploaded_file($upload['tmp_name'])) {
        $upload['name'] = SK_secureEncode($upload['name']);
        $name = preg_replace('/([^A-Za-z0-9_\-\.]+)/i', '', $upload['name']);
        $ext = strtolower(substr($upload['name'], strrpos($upload['name'], '.') + 1, strlen($upload['name']) - strrpos($upload['name'], '.')));
        
        if ($upload['size'] > 1024) {
            
            if (preg_match('/(jpg|jpeg|png)/', $ext)) {
                
                list($width, $height) = getimagesize($upload['tmp_name']);
                
                $query_one = "INSERT INTO " . DB_MEDIA . " (extension,name,type) VALUES ('$ext','$name','photo')";
                $sql_query_one = mysqli_query($dbConnect, $query_one);
                
                if ($sql_query_one) {
                    $sql_id = mysqli_insert_id($dbConnect);
                    $original_file_name = $photo_dir . '/' . SK_generateKey() . '_' . $sql_id . '_' . md5($sql_id);
                    $original_file = $original_file_name . '.' . $ext;
                    
                    if (move_uploaded_file($upload['tmp_name'], $original_file)) {
                        $min_size = $width;
                        
                        if ($width > $height) {
                            $min_size = $height;
                        }
                        
                        $min_size = floor($min_size);
                        
                        if ($min_size > 920) {
                            $min_size = 920;
                        }
                        
                        $imageSizes = array(
                            'thumb' => array(
                                'type' => 'crop',
                                'width' => 64,
                                'height' => 64,
                                'name' => $original_file_name . '_thumb'
                            ),
                            '100x100' => array(
                                'type' => 'crop',
                                'width' => $min_size,
                                'height' => $min_size,
                                'name' => $original_file_name . '_100x100'
                            ),
                            '100x75' => array(
                                'type' => 'crop',
                                'width' => $min_size,
                                'height' => floor($min_size * 0.75),
                                'name' => $original_file_name . '_100x75'
                            )
                        );
                        
                        foreach ($imageSizes as $ratio => $data) {
                            $save_file = $data['name'] . '.' . $ext;
                            SK_processMedia($data['type'], $original_file, $save_file, $data['width'], $data['height']);
                        }
                        
                        SK_processMedia('resize', $original_file, $original_file, $min_size, 0);
                        mysqli_query($dbConnect, "UPDATE " . DB_MEDIA . " SET album_id=$album_id,url='$original_file_name',temp=0,active=1 WHERE id=$sql_id");
                        $get = array(
                            'id' => $sql_id,
                            'active' => 1,
                            'extension' => $ext,
                            'name' => $name,
                            'url' => $original_file_name
                        );
                        
                        return $get;
                    }
                }
            }
        }
    }
}

/* Register Cover Image */
function SK_registerCoverImage($upload, $pos=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect;
    set_time_limit(0);
    
    if (!file_exists('photos/' . date('Y'))) {
        mkdir('photos/' . date('Y'), 0777, true);
    }
    
    if (!file_exists('photos/' . date('Y') . '/' . date('m'))) {
        mkdir('photos/' . date('Y') . '/' . date('m'), 0777, true);
    }
    
    $photo_dir = 'photos/' . date('Y') . '/' . date('m');
    
    if (is_uploaded_file($upload['tmp_name'])) {
        $upload['name'] = SK_secureEncode($upload['name']);
        $name = preg_replace('/([^A-Za-z0-9_\-\.]+)/i', '', $upload['name']);
        $ext = strtolower(substr($upload['name'], strrpos($upload['name'], '.') + 1, strlen($upload['name']) - strrpos($upload['name'], '.')));
        
        if ($upload['size'] > 1024) {
            
            if (preg_match('/(jpg|jpeg|png)/', $ext)) {
                
                list($width, $height) = getimagesize($upload['tmp_name']);
                
                $query_one = "INSERT INTO " . DB_MEDIA . " (extension,name,type) VALUES ('$ext','$name','photo')";
                $sql_query_one = mysqli_query($dbConnect, $query_one);
                
                if ($sql_query_one) {
                    $sql_id = mysqli_insert_id($dbConnect);
                    $original_file_name = $photo_dir . '/' . SK_generateKey() . '_' . $sql_id . '_' . md5($sql_id);
                    $original_file = $original_file_name . '.' . $ext;
                    
                    if (move_uploaded_file($upload['tmp_name'], $original_file)) {
                        SK_processMedia('resize', $original_file, $original_file, $width, 0, 100);

                        $img = $original_file;
                        $cover_img_url = $original_file_name . '_cover.' . $ext;
                        $dst_x = 0;
                        $dst_y = 0;
                        $src_x = 0;
                        $src_y = 0;
                        $dst_w = $width;
                        $dst_h = $dst_w * (0.3);
                        $src_w = $width;
                        $src_h = $dst_h;
                        
                        if (!empty($pos) && is_numeric($pos) && $pos < $width) {
                            $pos = SK_secureEncode($pos);
                            $src_y = $width * $pos;
                        }
                        
                        $cover_img = imagecreatetruecolor($dst_w, $dst_h);
                        $image = imagecreatefromjpeg($img);
                        imagecopyresampled($cover_img, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
                        imagejpeg($cover_img, $cover_img_url, 100);
                        
                        mysqli_query($dbConnect, "UPDATE " . DB_MEDIA . " SET url='$original_file_name',temp=0,active=1 WHERE id=$sql_id");
                        $get = array(
                            'id' => $sql_id,
                            'active' => 1,
                            'extension' => $ext,
                            'name' => $name,
                            'url' => $original_file_name,
                            'cover_url' => $original_file_name . '_cover.' . $ext
                        );
                        
                        return $get;
                    }
                }
            }
        }
    }
}

function SK_createCover($cover_id=0, $pos=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect;
    
    if (!is_numeric($cover_id) or $cover_id < 1) {
        return false;
    }
    
    $cover_id = SK_secureEncode($cover_id);
    $query_one = "SELECT * FROM " . DB_MEDIA . " WHERE id=$cover_id";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        $cover = mysqli_fetch_assoc($sql_query_one);
        $img = $cover['url'] . '.' . $cover['extension'];
        $cover_img_url = $cover['url'] . '_cover.' . $cover['extension'];
        list($width, $height) = getimagesize($img);
        $dst_x = 0;
        $dst_y = 0;
        $src_x = 0;
        $src_y = 0;
        $dst_w = $width;
        $dst_h = $dst_w * (0.3);
        $src_w = $width;
        $src_h = $dst_h;
        
        if (!empty($pos) && is_numeric($pos) && $pos < $width) {
            $pos = SK_secureEncode($pos);
            $src_y = $width * $pos;
        }
        
        $cover_img = imagecreatetruecolor($dst_w, $dst_h);
        $image = imagecreatefromjpeg($img);
        imagecopyresampled($cover_img, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        imagejpeg($cover_img, $cover_img_url, 100);
        return $cover_img_url;
    }
}

/* Delete functions */
function SK_deleteFollow($following_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user, $config;
    
    if (!isset($following_id) or empty($following_id) or !is_numeric($following_id) or $following_id < 1) {
        return false;
    }
    
    $following_id = SK_secureEncode($following_id);
    $following = SK_getUser($following_id);
    $active = 1;
    
    if (!isset($following['id'])) {
        return false;
    }
    
    if (SK_isBlocked($following['id'])) {
        return false;
    }
    
    if ($following['type'] == "user" && $following['confirm_followers'] == 1) {
        $active = 0;
    }
    
    if ($following['type'] == "group" && $following['group_privacy'] == "closed") {
        $active = 0;
    }
    
    $query_one = "DELETE FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $user['id'] . " AND following_id=" . $following['id'];
    $sql_query_one = mysqli_query($dbConnect, $query_one);

    if ($config['friends'] == true) {
        $query_two = "DELETE FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $following['id'] . " AND following_id=" . $user['id'];
        $sql_query_two = mysqli_query($dbConnect, $query_two);
    }
    
    if ($following['type'] == "group" && SK_isGroupAdmin($following['id'])) {
        
        if (SK_countGroupAdmins($following['id']) > 1) {
            $query_two = "DELETE FROM " . DB_GROUP_ADMINS . " WHERE admin_id=" . $user['id'] . " AND group_id=" . $following['id'];
            mysqli_query($dbConnect, $query_two);
        }
    }
    
    return true;
}

function SK_deletePageAdmin($page_id=0, $admin_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (!isset($page_id) or empty($page_id) or !is_numeric($page_id) or $page_id < 1) {
        return false;
    }
    
    if (!isset($admin_id) or empty($admin_id) or !is_numeric($admin_id) or $admin_id < 1) {
        return false;
    }
    
    $page_id = SK_secureEncode($page_id);
    $admin_id = SK_secureEncode($admin_id);
    $page = SK_getUser($page_id);
    
    if (!isset($page['id']) or $page['type'] != "page") {
        return false;
    }
    
    if ($admin_id == $user['id']) {
        $admin = $user;
    } else {
        $admin = SK_getUser($admin_id);
    }
    
    if (!isset($admin['id']) or $admin['type'] != "user") {
        return false;
    }
    
    if (SK_isPageAdmin($page['id']) != "admin") {
        return false;
    }
    
    $query_one = "DELETE FROM " . DB_PAGE_ADMINS . " WHERE admin_id=" . $admin['id'] . " AND page_id=" . $page['id'];
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if ($sql_query_one) {
        return true;
    }
}

function SK_deleteGroupMember($group_id=0, $member_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (!isset($group_id) or empty($group_id) or !is_numeric($group_id) or $group_id < 1) {
        return false;
    }
    
    if (!isset($member_id) or empty($member_id) or !is_numeric($member_id) or $member_id < 1) {
        return false;
    }
    
    $group_id = SK_secureEncode($group_id);
    $member_id = SK_secureEncode($member_id);
    $group = SK_getUser($group_id);
    
    if (!isset($group['id']) or $group['type'] != "group") {
        return false;
    }
    
    if ($member_id == $user['id']) {
        $member = $user;
    } else {
        $member = SK_getUser($member_id);
    }
    
    if (!isset($member['id']) or $member['type'] != "user") {
        return false;
    }
    
    if ($member['id'] == $user['id'] or SK_isGroupAdmin($group['id'])) {
        $query_one = "DELETE FROM " . DB_FOLLOWERS . " WHERE follower_id=" . $member['id'] . " AND following_id=" . $group['id'];
        $sql_query_one = mysqli_query($dbConnect, $query_one);
        
        if ($sql_query_one) {
            $query_two = "DELETE FROM " . DB_GROUP_ADMINS . " WHERE admin_id=" . $member['id'] . " AND group_id=" . $group['id'];
            $sql_query_two = mysqli_query($dbConnect, $query_two);
            
            return true;
        }
    }
}

function SK_deleteGroupAdmin($group_id=0, $admin_id=0) {
    if ($GLOBALS['logged'] !== true) {
        return false;
    }
    
    global $dbConnect, $user;
    
    if (!isset($group_id) or empty($group_id) or !is_numeric($group_id) or $group_id < 1) {
        return false;
    }
    
    if (!isset($admin_id) or empty($admin_id) or !is_numeric($admin_id) or $admin_id < 1) {
        return false;
    }
    
    $group_id = SK_secureEncode($group_id);
    $admin_id = SK_secureEncode($admin_id);
    $group = SK_getUser($group_id);
    
    if (!isset($group['id']) or $group['type'] != "group") {
        return false;
    }
    
    if ($admin_id == $user['id']) {
        $admin = $user;
    } else {
        $admin = SK_getUser($admin_id);
    }
    
    if (!isset($admin['id']) or $admin['type'] != "user") {
        return false;
    }
    
    if (!SK_isGroupAdmin($group['id'])) {
        return false;
    }
    
    $query_one = "DELETE FROM " . DB_GROUP_ADMINS . " WHERE admin_id=" . $admin['id'] . " AND group_id=" . $group['id'];
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if ($sql_query_one) {
        return true;
    }
}

/* Other functions */
function SK_smoothLink($query='') {
    global $config;
    
    if ($config['smooth_links'] == 1) {
        $query = preg_replace(
            array(
                '/^index\.php\?tab1=timeline&tab2=([^\/]+)&tab3=([^\/]+)&recipient_id=([^\/]+)&id=([^\/]+)$/i',
                '/^index\.php\?tab1=timeline&tab2=([^\/]+)&tab3=([^\/]+)&id=([^\/]+)$/i',
                '/^index\.php\?tab1=timeline&tab2=([^\/]+)&id=([^\/]+)$/i',
                '/^index\.php\?tab1=timeline&id=([^\/]+)$/i',
                
                '/^index\.php\?tab1=messages&recipient_id=([A-Za-z0-9_]+)$/i',
                '/^index\.php\?tab1=story&id=([0-9]+)$/i',
                
                '/^index\.php\?tab1=welcome&tab2=forgot_password$/i',
                '/^index\.php\?tab1=welcome&tab2=password_reset&id=([A-Za-z0-9_]+)$/i',
                
                '/^index\.php\?tab1=([^\/]+)&query=([^\/]+)$/i',
                
                '/^index\.php\?tab1=([^\/]+)&tab2=([^\/]+)&tab3=([^\/]+)$/i',
                '/^index\.php\?tab1=([^\/]+)&tab2=([^\/]+)$/i',
                '/^index\.php\?tab1=([^\/]+)$/i'
            ),
            array(
                $config['site_url'] . '/@$4/$1/$2/$3',
                $config['site_url'] . '/@$3/$1/$2',
                $config['site_url'] . '/@$2/$1',
                $config['site_url'] . '/@$1',
                
                $config['site_url'] . '/messages/$1',
                $config['site_url'] . '/story/$1',
                
                $config['site_url'] . '/forgot-password',
                $config['site_url'] . '/password-reset/$1',
                
                $config['site_url'] . '/$1/$2',
                
                $config['site_url'] . '/$1/$2/$3',
                $config['site_url'] . '/$1/$2',
                $config['site_url'] . '/$1'
            ),
            $query
        );
    } else {
        $query = $config['site_url'] . '/' . $query;
    }
    
    return $query;
}

function SK_emoticonize($string='') {
    global $config, $emo;
    $dir = str_replace(array('http://', 'https://'), '//', $config['site_url']);
    $dir .= '/themes/' . $config['theme'] . '/emoticons';
    
    foreach ($emo as $code => $img) {
        $code = SK_secureEncode($code);
        $img = '<img src="' . $dir . '/' . $img . '" class="emoticon">';
        $string = str_replace($code, $img, $string);
    }
    
    
    return $string;
}

function SK_processMedia($run, $photo_src, $save_src, $width=0, $height=0, $quality=80) {
    
    if (!is_numeric($quality) or $quality < 0 or $quality > 100) {
        $quality = 80;
    }

    if (file_exists($photo_src)) {
        
        if (strrpos($photo_src, '.')) {
            $ext = substr($photo_src, strrpos($photo_src,'.') + 1, strlen($photo_src) - strrpos($photo_src, '.'));
            $fxt = (!in_array($ext, array('jpeg', 'png'))) ? "jpeg" : $ext;
        } else {
            $ext = $fxt = 0;
        }
        
        if (preg_match('/(jpg|jpeg|png)/', $ext)) {
            list($photo_width, $photo_height) = getimagesize($photo_src);
            $create_from = "imagecreatefrom" . $fxt;
            $photo_source = $create_from($photo_src);
            
            if ($run == "crop") {
                
                if ($width > 0 && $height > 0) {
                    $crop_width = $photo_width;
                    $crop_height = $photo_height;
                    $k_w = 1;
                    $k_h = 1;
                    $dst_x = 0;
                    $dst_y = 0;
                    $src_x = 0;
                    $src_y = 0;
                    
                    if ($width == 0 or $width > $photo_width) {
                        $width = $photo_width;
                    }
                    
                    if ($height == 0 or $height > $photo_height) {
                        $height = $photo_height;
                    }
                    
                    $crop_width = $width;
                    $crop_height = $height;
                    
                    if ($crop_width > $photo_width) {
                        $dst_x = ($crop_width - $photo_width) / 2;
                    }
                    
                    if ($crop_height > $photo_height) {
                        $dst_y = ($crop_height - $photo_height) / 2;
                    }
                    
                    if ($crop_width < $photo_width || $crop_height < $photo_height) {
                        $k_w = $crop_width / $photo_width;
                        $k_h = $crop_height / $photo_height;
                        
                        if ($crop_height > $photo_height) {
                            $src_x  = ($photo_width - $crop_width) / 2;
                        } elseif ($crop_width > $photo_width) {
                            $src_y  = ($photo_height - $crop_height) / 2;
                        } else {
                            
                            if ($k_h > $k_w) {
                                $src_x = round(($photo_width - ($crop_width / $k_h)) / 2);
                            } else {
                                $src_y = round(($photo_height - ($crop_height / $k_w)) / 2);
                            }
                        }
                    }
                    
                    $crop_image = @imagecreatetruecolor($crop_width, $crop_height);
                    
                    if ($ext == "png") {
                        @imagesavealpha($crop_image, true);
                        @imagefill($crop_image, 0, 0, @imagecolorallocatealpha($crop_image, 0, 0, 0, 127));
                    }
                    
                    @imagecopyresampled($crop_image, $photo_source ,$dst_x, $dst_y, $src_x, $src_y, $crop_width - 2 * $dst_x, $crop_height - 2 * $dst_y, $photo_width - 2 * $src_x, $photo_height - 2 * $src_y);
                    
                    /*$exif = exif_read_data($photo_src);

                    if (isset($exif['Orientation'])) {
                        $ort = $exif['Orientation'];

                        switch ($ort) {
                            case 2:
                                SK_orientImage($dimg);
                            break;

                            case 3:
                                $crop_image = imagerotate($crop_image, 180, -1);
                            break;

                            case 4:
                                SK_orientImage($dimg);
                            break;

                            case 5:
                                SK_orientImage($crop_image);
                                $crop_image = imagerotate($crop_image, -90, -1);
                            break;

                            case 6:
                                $crop_image = imagerotate($crop_image, -90, -1);
                            break;

                            case 7:
                                SK_orientImage($crop_image);
                                $crop_image = imagerotate($crop_image, -90, -1);
                            break;

                            case 8:
                                $crop_image = imagerotate($crop_image, 90, -1);
                            break;
                        }
                    }*/

                    @imageinterlace($crop_image, true);
                    @imagejpeg($crop_image, $save_src, $quality);
                    @imagedestroy($crop_image);
                }
            } elseif ($run == "resize") {
                
                if ($width == 0 && $height == 0) {
                    return false;
                }
                
                if ($width > 0 && $height == 0) {
                    $resize_width = $width;
                    $resize_ratio = $resize_width / $photo_width;
                    $resize_height = floor($photo_height * $resize_ratio);
                } elseif ($width == 0 && $height > 0) {
                    $resize_height = $height;
                    $resize_ratio = $resize_height / $photo_height;
                    $resize_width = floor($photo_width * $resize_ratio);
                } elseif ($width > 0 && $height > 0) {
                    $resize_width = $width;
                    $resize_height = $height;
                }
                
                if ($resize_width > 0 && $resize_height > 0) {
                    $resize_image = @imagecreatetruecolor($resize_width, $resize_height);
                    
                    if ($ext == "png") {
                        @imagesavealpha($resize_image, true);
                        @imagefill($resize_image, 0, 0, @imagecolorallocatealpha($resize_image, 0, 0, 0, 127));
                    }
                    
                    @imagecopyresampled($resize_image, $photo_source, 0, 0, 0, 0, $resize_width, $resize_height, $photo_width, $photo_height);

                    /*$exif = exif_read_data($photo_src);
                    
                    if (isset($exif['Orientation'])) {
                        $ort = $exif['Orientation'];

                        switch ($ort) {
                            case 2:
                                SK_orientImage($dimg);
                            break;
                            
                            case 3:
                                $resize_image = imagerotate($resize_image, 180, -1);
                            break;
                            
                            case 4:
                                SK_orientImage($dimg);
                            break;
                            
                            case 5:
                                SK_orientImage($resize_image);
                                $resize_image = imagerotate($resize_image, -90, -1);
                            break;
                            
                            case 6:
                                $resize_image = imagerotate($resize_image, -90, -1);
                            break;
                            
                            case 7:
                                SK_orientImage($resize_image);
                                $resize_image = imagerotate($resize_image, -90, -1);
                            break;
                            
                            case 8:
                                $resize_image = imagerotate($resize_image, 90, -1);
                            break;
                        }
                    }*/

                    @imageinterlace($resize_image, true);
                    @imagejpeg($resize_image, $save_src, $quality);
                    @imagedestroy($resize_image);
                }
            } elseif ($run == "scale") {
                
                if ($width == 0) {
                    $width = 100;
                }
                
                if ($height == 0) {
                    $height = 100;
                }
                
                $scale_width = $photo_width * ($width / 100);
                $scale_height = $photo_height * ($height / 100);
                $scale_image = @imagecreatetruecolor($scale_width, $scale_height);
                
                if ($ext == "png") {
                    @imagesavealpha($scale_image, true);
                    @imagefill($scale_image, 0, 0, imagecolorallocatealpha($scale_image, 0, 0, 0, 127));
                }
                
                @imagecopyresampled($scale_image, $photo_source, 0, 0, 0, 0, $scale_width, $scale_height, $photo_width, $photo_height);

                /*$exif = exif_read_data($photo_src);

                if (isset($exif['Orientation'])) {
                    $ort = $exif['Orientation'];
                    
                    switch ($ort) {
                        case 2:
                            SK_orientImage($dimg);
                        break;
                        
                        case 3:
                            $scale_image = imagerotate($scale_image, 180, -1);
                        break;
                        
                        case 4:
                            SK_orientImage($dimg);
                        break;
                        
                        case 5:
                            SK_orientImage($scale_image);
                            $scale_image = imagerotate($scale_image, -90, -1);
                        break;
                        
                        case 6:
                            $scale_image = imagerotate($scale_image, -90, -1);
                        break;
                        
                        case 7:
                            SK_orientImage($scale_image);
                            $scale_image = imagerotate($scale_image, -90, -1);
                        break;
                        
                        case 8:
                            $scale_image = imagerotate($scale_image, 90, -1);
                        break;
                    }
                }*/

                @imageinterlace($scale_image, true);
                @imagejpeg($scale_image, $save_src, $quality);
                @imagedestroy($scale_image);
            }
        }
    }
}

function SK_orientImage(&$image) {
    $x = 0;
    $y = 0;
    $height = null;
    $width = null;

    if ($width < 1) {
        $width  = imagesx($image);
    }

    if ($height < 1) {
        $height = imagesy($image);
    }

    if (function_exists('imageistruecolor') && imageistruecolor($image)) {
        $tmp = imagecreatetruecolor(1, $height);
    } else {
        $tmp = imagecreate(1, $height);
    }

    $x2 = $x + $width - 1;

    for ($i = (int)floor(($width - 1) / 2); $i >= 0; $i--) {
        imagecopy($tmp, $image, 0, 0, $x2 - $i, $y, 1, $height);
        imagecopy($image, $image, $x2 - $i, $y, $x + $i, $y, 1, $height);
        imagecopy($image, $tmp, $x + $i,  $y, 0, 0, 1, $height);
    }

    imagedestroy($tmp);
    return true;
}

function SK_generateKey($minlength=5, $maxlength=5, $uselower=true, $useupper=true, $usenumbers=true, $usespecial=false) {
    $charset = '';
    
    if ($uselower) {
        $charset .= "abcdefghijklmnopqrstuvwxyz";
    }
    
    if ($useupper) {
        $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    }
    
    if ($usenumbers) {
        $charset .= "123456789";
    }
    
    if ($usespecial) {
        $charset .= "~@#$%^*()_+-={}|][";
    }
    
    if ($minlength > $maxlength) {
        $length = mt_rand($maxlength, $minlength);
    } else {
        $length = mt_rand($minlength, $maxlength);
    }
    
    $key = '';
    
    for ($i = 0; $i < $length; $i++) {
        $key .= $charset[(mt_rand(0, strlen($charset) - 1))];
    }
    
    return $key;
}

function SK_createCaptcha() {
    $image = '';
    $image = @imagecreatetruecolor(80, 30);
    $background_color = @imagecolorallocate($image, 87, 143, 209);
    $text_color = @imagecolorallocate($image, 255, 255, 255);
    $pixel_color = @imagecolorallocate($image, 60, 75, 114);
    @imagefilledrectangle($image, 0, 0, 80, 30, $background_color);
    
    for ($i = 0; $i < 1000; $i++) {
        @imagesetpixel($image, rand() % 80, rand() % 30, $pixel_color);
    }
    
    $key = SK_generateKey(6, 6, false, false, true);
    $_SESSION['captcha_key'] = $key;
    @imagestring($image, 7, 13, 8, $key, $text_color);
    $images = glob('photos/captcha_*.png');
    
    if (is_array($images) && count($images) > 0) {
        
        foreach ($images as $image_to_delete) {
            @unlink($image_to_delete);
        }
    }
    
    $image_url = 'photos/captcha_' . time() . '_' . mt_rand(1, 9999) . '.png';
    @imagepng($image, $image_url);
    $get = array(
        'image' => $image_url
    );
    return $get;
}

function SK_getTime($unix, $details=false) {
    global $time;
    
    if ($details == true) {
        
        if (date('Y', $unix) == date('Y')) {
            
            if (date('dM', $unix) == date('dM')) {
                return date('h:i A', $unix);
            } else {
                return date('d M - h:i A', $unix);
            }
        } else {
            return date('d M Y - h:i A', $unix);
        }
    } else {
        $interval = 'Just now';
        
        if ($unix > $time) {
            $diff = $unix - $time;
            $prefix = 'after';
            $math = 'round';
        } else {
            $diff = $time - $unix;
            $prefix = 'before';
            $math = 'floor';
        }
        
        if ($diff >= 120) {
            $reminder = $math($diff / 60);
            $suffix = 'min';
            
            if ($diff >= (60 * 60)) {
                $reminder = $math($diff / (60 * 60));
                $suffix = 'hr';
                
                if ($diff >= (60 * 60 * 24)) {
                    $reminder = $math($diff / (60 * 60 * 24));
                    $suffix = 'day';
                    
                    if ($diff >= (60 * 60 * 24 * 7)) {
                        $reminder = $math($diff / (60 * 60 * 24 * 7));
                        $suffix = 'week';
                        
                        if ($diff > (60 * 60 * 24 * 31)) {
                            $reminder = $math($diff / (60 * 60 * 24 * 31));
                            $suffix = 'month';
                            
                            if ($diff > (60 * 60 * 24 * 30 * 12)) {
                                $reminder = $math($diff / (60 * 60 * 24 * 30 * 12));
                                $suffix = 'yr';
                            }
                        }
                    }
                }
            }
            
            $interval = $reminder . ' ' . $suffix;
            
            if ($reminder != 1) {
                $interval .= 's';
            }
            
            if ($prefix == "after") {
                $interval = 'after ' . $interval;
            }
        }
        
        return $interval;
    }
}