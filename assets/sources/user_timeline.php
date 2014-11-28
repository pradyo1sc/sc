<?php
if (SK_isBlocked($sk['timeline']['id'])) {
    header('Location: index.php?tab1=home');
}

$sk['content'] = SK_getPage('timeline/user');
