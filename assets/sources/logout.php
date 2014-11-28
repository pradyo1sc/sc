<?php
if (isset($_SESSION['user_id'])) {
    unset($_SESSION['user_id']);
}

if (isset($_SESSION['user_pass'])) {
    unset($_SESSION['user_pass']);
}

setcookie('sk_u_i', 0, time()-60);
setcookie('sk_u_p', 0, time()-60);

header('Location: ' . SK_smoothLink('index.php?tab1=welcome'));
