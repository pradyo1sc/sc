<?php
$sk['posts'] = array();

if (!empty($_GET['query'])) {
    $search_query = str_replace('#', '', SK_secureEncode($_GET['query']));
    
    $hashdata = SK_getHashtag($search_query);
    
    if (is_array($hashdata) && count($hashdata) > 0) {
        $search_string = "#[" . $hashdata['id'] . "]";
        $query_one = "SELECT id FROM " . DB_POSTS . " WHERE text LIKE '%$search_string%' AND type1='story' AND type2='none' AND hidden=0 AND active=1";
        $sql_query_one = mysqli_query($dbConnect, $query_one);
        
        while ($sql_fetch_one = mysqli_fetch_assoc($sql_query_one)) {
            $story = SK_getStory($sql_fetch_one['id']);

            if (is_array($story)) {
                $sk['posts'][] = SK_getStory($sql_fetch_one['id']);
            }
        }
    }
}

$sk['content'] = SK_getPage('hashtag/content');
