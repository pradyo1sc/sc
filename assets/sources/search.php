<?php
$sk['searches'] = array();

if (!empty($_GET['query'])) {
    $search_query = SK_secureEncode($_GET['query']);
    
    foreach (SK_getSearch($search_query, 0, 30) as $search) {
        $sk['searches'][] = $search;
    }
}

$sk['content'] = SK_getPage('search/content');
