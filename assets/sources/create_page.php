<?php
if ($logged != true) {
    header('Location: ' . SK_smoothLink('index.php?tab1=welcome'));
}

$sk['content'] = SK_getPage('page/create');
