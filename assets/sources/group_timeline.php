<?php
if (SK_isBlocked($sk['timeline']['id'])) {
    header('Location: index.php?t=home');
}

if ($sk['timeline']['group_privacy'] === "secret") {
    
    if (!SK_isFollowing($sk['timeline']['id']) && !SK_isGroupAdmin($sk['timeline']['id'])) {
        header('Location: ' . SK_smoothLink('index.php?tab1=home'));
    }
}

$sk['content'] = SK_getPage('timeline/group');
