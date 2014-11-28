<?php
if (FA_isBlocked($sk['timeline']['id'])) {
    header('Location: index.php?tab1=home');
}

$sk['content'] = FA_getPage('timeline/user');
