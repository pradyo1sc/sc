<?php
require_once('assets/includes/core.php');
$redirect = true;

function FA_importMedia($url='') {
    global $dbConnect;
    
    if (empty($url)) {
        return false;
    }
    
    if (($source = @file_get_contents($url)) == false) {
        return false;
    }
    
    if (!file_exists('photos/' . date('Y'))) {
        mkdir('photos/' . date('Y'), 0777, true);
    }
    
    if (!file_exists('photos/' . date('Y') . '/' . date('m'))) {
        mkdir('photos/' . date('Y') . '/' . date('m'), 0777, true);
    }
    
    $photo_dir = 'photos/' . date('Y') . '/' . date('m');
    $name = preg_replace('/([^A-Za-z0-9_\-\.]+)/i', '', $url);
    $url_ext = $url;
    
    if (($qs_ext_pos = strrpos($url, '?')) !== false) {
        $url_ext = substr($url, 0, $qs_ext_pos);
    }
    
    $dot_ext_pos = strrpos($url_ext, '.');
    $url_ext = strtolower(substr($url_ext, $dot_ext_pos + 1, strlen($url_ext) - $dot_ext_pos));
    
    if (!preg_match('/^(jpg|jpeg|png)$/', $url_ext)) {
        return false;
    }
    
    $query_one = "INSERT INTO " . DB_MEDIA . " (extension,name,type) VALUES ('$url_ext','$name','photo')";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (!$sql_query_one) {
        return false;
    }
    
    $sql_id = mysqli_insert_id($dbConnect);
    $original_file_name = $photo_dir . '/' . FA_generateKey() . '_' . $sql_id . '_' . md5($sql_id);
    $original_file = $original_file_name . '.' . $url_ext;
    $register_cover = @file_put_contents($original_file, $source);
    
    if ($register_cover) {
        list($width, $height) = getimagesize($original_file);
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
            ),
            'cover' => array(
                'type' => 'crop',
                'width' => $min_size,
                'height' => floor($min_size * 0.3),
                'name' => $original_file_name . '_cover'
            )
        );
        
        foreach ($imageSizes as $ratio => $data) {
            $save_file = $data['name'] . '.' . $url_ext;
            FA_processMedia($data['type'], $original_file, $save_file, $data['width'], $data['height']);
        }
        
        mysqli_query($dbConnect, "UPDATE " . DB_MEDIA . " SET url='$original_file_name',temp=0,active=1 WHERE id=$sql_id");
        $get = array(
            'id' => $sql_id,
            'active' => 1,
            'extension' => $url_ext,
            'name' => $name,
            'url' => $original_file_name
        );
        
        return $get;
    }
}

// Get type
if (!empty($_GET['type'])) {
    $type = $_GET['type'];
    
    // if type is facebook
    if ($type == "facebook") {
        
        if (!empty($_GET['code'])) {
            
            $code = $_GET['code'];
            $client_id = $fb_app_id;
            $client_secret = $fb_app_secret;
            $redirect_uri = $config['site_url'] . '/import.php?type=facebook';
            
            $getAccessTokenUrl = "https://graph.facebook.com/oauth/access_token?client_id=$client_id&redirect_uri=$redirect_uri&client_secret=$client_secret&code=$code";
            $getAccessToken = @file_get_contents($getAccessTokenUrl);
            
            $explodeAccessTokenStageOne = explode('&', $getAccessToken);
            $explodeAccessTokenStageTwo = explode('=', $explodeAccessTokenStageOne[0]);
            $access_token = $explodeAccessTokenStageTwo[1];
            
            if (!empty($access_token)) {
                $getApiUrl = "https://graph.facebook.com/me?access_token=$access_token&fields=email,gender,name,cover,picture.width(720).height(720)";
                $getApi = @file_get_contents($getApiUrl);
                $getJson = @json_decode($getApi, true);
                
                if (!empty($getJson['name']) && !empty($getJson['id'])) {
                    $getJson['name'] = FA_secureEncode($getJson['name']);
                    $getJson['id'] = FA_secureEncode($getJson['id']);
                    $getJson['username'] = 'fb_' . $getJson['id'];
                    $possibleUsername = preg_replace('/([^a-z_])/i', '', strtolower($getJson['name']));

                    if (strlen($possibleUsername) > 3) {
                        $query_one = "SELECT id FROM " . DB_ACCOUNTS . " WHERE username='$possibleUsername' AND type='user' AND active=1";
                        $sql_query_one = mysqli_query($dbConnect, $query_one);
                        
                        if (mysqli_num_rows($sql_query_one) === 0) {
                            $getJson['username'] = $possibleUsername;
                        }
                    }
                    
                    if (!empty($getJson['email'])) {
                        $getJson['email'] = FA_secureEncode($getJson['email']);
                    } else {
                        $getJson['email'] = $getJson['username'] . '@facebook.com';
                    }
                    
                    if (!empty($getJson['gender'])) {
                        $getJson['gender'] = FA_secureEncode($getJson['gender']);
                    } else {
                        $getJson['gender'] = 'male';
                    }
                    
                    $getJson['password'] = md5($getJson['email']);
                    
                    $query_one = "SELECT id,password FROM " . DB_ACCOUNTS . " WHERE email='" . $getJson['email'] . "' AND type='user' AND active=1";
                    $sql_query_one = mysqli_query($dbConnect, $query_one);
                    
                    if (($sql_numrows_one = mysqli_num_rows($sql_query_one)) == 1) {
                        $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
                        
                        $_SESSION['user_id'] = $sql_fetch_one['id'];
                        $_SESSION['user_pass'] = $sql_fetch_one['password'];
                        
                        setcookie('sk_u_i', $_SESSION['user_id'], time() + (60 * 60 * 24 * 7));
                        setcookie('sk_u_p', $_SESSION['user_pass'], time() + (60 * 60 * 24 * 7));
                    } else {
                        
                        if (($register = FA_registerUser($getJson)) != false) {
                            $register['password'] = $getJson['password'];
                            $sk['mail'] = $register;
                            
                            $_SESSION['user_id'] = $register['id'];
                            $_SESSION['user_pass'] = md5($getJson['password']);
                            
                            setcookie('sk_u_i', $_SESSION['user_id'], time() + (60 * 60 * 24 * 7));
                            setcookie('sk_u_p', $_SESSION['user_pass'], time() + (60 * 60 * 24 * 7));
                            
                            $to = $register['email'];
                            $subject = $config['site_name'] . ' - Account Password';
                            
                            $headers = "From: " . $config['email'] . "\r\n";
                            $headers .= "MIME-Version: 1.0\r\n";
                            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
                            
                            $message = FA_getPage('emails/facebook-registration');
                            mail($to, $subject, $message, $headers);
                            
                            $redirect = true;
                            
                            if (!empty($getJson['cover']) && is_array($getJson['cover'])) {
                                $cover = FA_importMedia($getJson['cover']['source']);
                                
                                if (is_array($cover)) {
                                    $query_one = "UPDATE " . DB_ACCOUNTS . " SET cover_id=" . $cover['id'] . " WHERE id=" . $register['id'];
                                    $sql_query_one = mysqli_query($dbConnect, $query_one);
                                }
                            }
                            
                            if (is_array($getJson['picture']) && !empty($getJson['picture']['data']['url'])) {
                                $avatar = FA_importMedia($getJson['picture']['data']['url']);
                                
                                if (is_array($avatar)) {
                                    $query_two = "UPDATE " . DB_ACCOUNTS . " SET avatar_id=" . $avatar['id'] . " WHERE id=" . $register['id'];
                                    $sql_query_two = mysqli_query($dbConnect, $query_two);
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $redirect = true;
        }
    } else {
        $redirect = true;
    }
} else {
    $redirect = true;
}

if ($redirect == true) {
    header('Location: ' . FA_smoothLink('index.php?tab1=welcome'));
}

?>