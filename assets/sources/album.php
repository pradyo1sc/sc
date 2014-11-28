<?php
if ($_GET['tab2'] == "create") {
    
    if ($logged != true) {
	    header('Location: ' . FA_smoothLink('index.php?tab1=welcome'));
	}
	
    $sk['content'] = FA_getPage('album/create');
} elseif (is_numeric($_GET['tab2'])) {
    $album_id = FA_secureEncode($_GET['tab2']);
    $query_one = "SELECT id,name,descr,timeline_id FROM " . DB_MEDIA . " WHERE id=" . $album_id . " AND type='album' AND temp=0 AND active=1";
    $sql_query_one = mysqli_query($dbConnect, $query_one);

    if (mysqli_num_rows($sql_query_one) == 1) {
        $sk['album'] = mysqli_fetch_assoc($sql_query_one);

        if ($sk['album']['timeline_id'] == $user['id']) {
            $sk['album']['timeline'] = $user;
        } else {
            $sk['album']['timeline'] = FA_getUser($sk['album']['timeline_id']);
        }

        $continue = true;

        if ($sk['album']['timeline']['post_privacy'] == "following") {

            if (!FA_isFollowing($user['id'], $sk['album']['timeline']['id'])) {
                $continue = false;

            }
        }
        
        if ($continue == true) {
            $sk['photos'] = FA_getAlbumPhotos($album_id);
            $sk['content'] = FA_getPage('album/content');
        }
    }
}
?>