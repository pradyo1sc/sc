<?php
$id = 0;

if ($logged == true) {
    $id = $sk['user']['id'];
}

if (!empty($_GET['id'])) {
    $id = FA_secureEncode($_GET['id']);
    
    if (is_numeric($id)) {
        $sql_query_text = "id=" . $id;
    } elseif (preg_match('/[A-Za-z0-9_]/', $id)) {
        $sql_query_text = "username='". $id ."'";
    }
    
    if (!empty($sql_query_text)) {
        $timeline = FA_getUser($id, true);
        
        if (is_array($timeline) && isset($timeline['id'])) {
            $sk['timeline'] = $timeline;
            $sk['config']['site_title'] = $sk['timeline']['name'];
            
            if ($sk['timeline']['type'] == "user") {
                include('user_timeline.php');
            } elseif ($sk['timeline']['type'] == "page") {
                include('page_timeline.php');
            } elseif ($sk['timeline']['type'] == "group") {
                include('group_timeline.php');
            }
        }
    }
}
