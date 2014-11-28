<?php
$sk['searches'] = array();

if (!empty($_GET['query'])) {
    $search_query = FA_secureEncode($_GET['query']);
    
    foreach (FA_getSearch($search_query, 0, 30) as $search) {
        $sk['searches'][] = $search;
    }
}

$sk['content'] = FA_getPage('search/content');
