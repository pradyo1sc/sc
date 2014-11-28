<?php
if (FA_isBlocked($sk['timeline']['id'])) {
    header('Location: index.php?t=home');
}

if ($sk['timeline']['group_privacy'] === "secret") {
    
    if (!FA_isFollowing($sk['timeline']['id']) && !FA_isGroupAdmin($sk['timeline']['id'])) {
        header('Location: ' . FA_smoothLink('index.php?tab1=home'));
    }
}

$sk['content'] = FA_getPage('timeline/group');
