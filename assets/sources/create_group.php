<?php
if ($logged != true) {
    header('Location: ' . FA_smoothLink('index.php?tab1=welcome'));
}

$sk['content'] = FA_getPage('group/create');
