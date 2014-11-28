<?php
function SK_getPost($post_id=0) {
    global $config, $dbConnect;
    
    $query_one = "SELECT id,post_id,timeline_id,recipient_id,type1,type2 FROM " . DB_POSTS . " WHERE id=$post_id AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);
    
    if (mysqli_num_rows($sql_query_one) == 1) {
        $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
        $sql_fetch_one['type'] = $sql_fetch_one['type1'];
        
        if ($sql_fetch_one['type2'] == "comment") {
            $sql_fetch_one['type'] = 'comment';
        }
        
        return $sql_fetch_one;
    }
}
