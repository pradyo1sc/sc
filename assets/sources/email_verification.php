<?php
if (!isset($dbConnect)) {
    exit();
}

$redirect = true;

if (!empty($_GET['email']) && !empty($_GET['key'])) {
    $email = FA_secureEncode($_GET['email']);
    $key = FA_secureEncode($_GET['key']);
    
    $query_one = "SELECT id,password FROM " . DB_ACCOUNTS . " WHERE email='$email' AND email_verification_key='$key'";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (($sql_numrows_one = mysqli_num_rows($sql_query_one)) == 1) {
        $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
        
        $query_two = "UPDATE " . DB_ACCOUNTS . " SET email_verified=1 WHERE id=" . $sql_fetch_one['id'];
        $sql_query_two = mysqli_query($dbConnect, $query_two);
        
        if ($sql_query_two) {
            $_SESSION['user_id'] = $sql_fetch_one['id'];
            $_SESSION['user_pass'] = $sql_fetch_one['password'];
            
            setcookie('sk_u_i', $_SESSION['user_id'], time() + (60 * 60 * 24 * 7));
            setcookie('sk_u_p', $_SESSION['user_pass'], time() + (60 * 60 * 24 * 7));
        }
    }
}

if ($redirect === true) {
    header('Location: ' . FA_smoothLink('index.php?tab1=welcome'));
}
