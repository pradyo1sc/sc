<?php
function statistics() {
global $config, $dbConnect;

$today_query = date('Y') . '-' . date('m') . '-' . date('d');
$month_query = date('Y') . '-' . date('m');
$year_query = date('Y');

// User registration
$users_registered_main_query = "SELECT id FROM " . DB_ACCOUNTS . " WHERE type='user' AND id IN (SELECT id FROM " . DB_USERS . ") AND active=1";

$users_registered_query['today'] = $users_registered_main_query . " AND timestamp LIKE '$today_query%'";
$users_registered_query['month'] = $users_registered_main_query . " AND timestamp LIKE '$month_query%'";
$users_registered_query['year'] = $users_registered_main_query . " AND timestamp LIKE '$year_query%'";
$users_registered_query['total'] = $users_registered_main_query;

foreach ($users_registered_query as $time => $query) {
    $sql_query = mysqli_query($dbConnect, $query);
    $users_registered[$time] = mysqli_num_rows($sql_query);
}

// Page created
$pages_created_main_query = "SELECT id FROM " . DB_ACCOUNTS . " WHERE type='page' AND id IN (SELECT id FROM " . DB_PAGES . ") AND active=1";

$pages_created_query['today'] = $pages_created_main_query . " AND timestamp LIKE '$today_query%'";
$pages_created_query['month'] = $pages_created_main_query . " AND timestamp LIKE '$month_query%'";
$pages_created_query['year'] = $pages_created_main_query . " AND timestamp LIKE '$year_query%'";
$pages_created_query['total'] = $pages_created_main_query;

foreach ($pages_created_query as $time => $query) {
    $sql_query = mysqli_query($dbConnect, $query);
    $pages_created[$time] = mysqli_num_rows($sql_query);
}

// Groups created
$groups_created_main_query = "SELECT id FROM " . DB_ACCOUNTS . " WHERE type='group' AND id IN (SELECT id FROM " . DB_GROUPS . ") AND active=1";

$groups_created_query['today'] = $groups_created_main_query . " AND timestamp LIKE '$today_query%'";
$groups_created_query['month'] = $groups_created_main_query . " AND timestamp LIKE '$month_query%'";
$groups_created_query['year'] = $groups_created_main_query . " AND timestamp LIKE '$year_query%'";
$groups_created_query['total'] = $groups_created_main_query;

foreach ($groups_created_query as $time => $query) {
    $sql_query = mysqli_query($dbConnect, $query);
    $groups_created[$time] = mysqli_num_rows($sql_query);
}

// Stories posted
$stories_posted_main_query = "SELECT id FROM " . DB_POSTS . " WHERE type1='story' AND type2='none' AND active=1";

$stories_posted_query['today'] = $stories_posted_main_query . " AND timestamp LIKE '$today_query%'";
$stories_posted_query['month'] = $stories_posted_main_query . " AND timestamp LIKE '$month_query%'";
$stories_posted_query['year'] = $stories_posted_main_query . " AND timestamp LIKE '$year_query%'";
$stories_posted_query['total'] = $stories_posted_main_query;

foreach ($stories_posted_query as $time => $query) {
    $sql_query = mysqli_query($dbConnect, $query);
    echo mysqli_error($dbConnect);
    $stories_posted[$time] = mysqli_num_rows($sql_query);
}

// Comments posted
$comments_posted_main_query = "SELECT id FROM " . DB_POSTS . " WHERE type1='story' AND type2='comment' AND active=1";

$comments_posted_query['today'] = $comments_posted_main_query . " AND timestamp LIKE '$today_query%'";
$comments_posted_query['month'] = $comments_posted_main_query . " AND timestamp LIKE '$month_query%'";
$comments_posted_query['year'] = $comments_posted_main_query . " AND timestamp LIKE '$year_query%'";
$comments_posted_query['total'] = $comments_posted_main_query;

foreach ($comments_posted_query as $time => $query) {
    $sql_query = mysqli_query($dbConnect, $query);
    echo mysqli_error($dbConnect);
    $comments_posted[$time] = mysqli_num_rows($sql_query);
}

// Posts Liked
$posts_liked_main_query = "SELECT id FROM " . DB_POSTS . " WHERE type1 IN ('story','comment') AND type2='like' AND active=1";

$posts_liked_query['today'] = $posts_liked_main_query . " AND timestamp LIKE '$today_query%'";
$posts_liked_query['month'] = $posts_liked_main_query . " AND timestamp LIKE '$month_query%'";
$posts_liked_query['year'] = $posts_liked_main_query . " AND timestamp LIKE '$year_query%'";
$posts_liked_query['total'] = $posts_liked_main_query;

foreach ($posts_liked_query as $time => $query) {
    $sql_query = mysqli_query($dbConnect, $query);
    echo mysqli_error($dbConnect);
    $posts_liked[$time] = mysqli_num_rows($sql_query);
}

// Posts Shared
$posts_shared_main_query = "SELECT id FROM " . DB_POSTS . " WHERE type1 IN ('story','comment') AND type2='share' AND active=1";

$posts_shared_query['today'] = $posts_shared_main_query . " AND timestamp LIKE '$today_query%'";
$posts_shared_query['month'] = $posts_shared_main_query . " AND timestamp LIKE '$month_query%'";
$posts_shared_query['year'] = $posts_shared_main_query . " AND timestamp LIKE '$year_query%'";
$posts_shared_query['total'] = $posts_shared_main_query;

foreach ($posts_shared_query as $time => $query) {
    $sql_query = mysqli_query($dbConnect, $query);
    echo mysqli_error($dbConnect);
    $posts_shared[$time] = mysqli_num_rows($sql_query);
}

// Posts Reported
$posts_reported_main_query = "SELECT id FROM " . DB_POSTS . " WHERE type1 IN ('story','comment') AND type2='share' AND active=1";

$posts_reported_query['today'] = $posts_reported_main_query . " AND timestamp LIKE '$today_query%'";
$posts_reported_query['month'] = $posts_reported_main_query . " AND timestamp LIKE '$month_query%'";
$posts_reported_query['year'] = $posts_reported_main_query . " AND timestamp LIKE '$year_query%'";
$posts_reported_query['total'] = $posts_reported_main_query;

foreach ($posts_reported_query as $time => $query) {
    $sql_query = mysqli_query($dbConnect, $query);
    echo mysqli_error($dbConnect);
    $posts_reported[$time] = mysqli_num_rows($sql_query);
}

?>
<div class="statistics-container">
    <div class="statistics-header">Statistics</small></div>
    
    <div class="statistics-wrapper">
        <label>Website statistics</label>
    </div>
    
    <div class="statistics-wrapper">
        <div class="title">Users Registered</div>
        <br>
        
        <div class="float-left stats span25">
        Today
        <br>
        <strong><?php echo $users_registered['today']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Month
        <br>
        <strong><?php echo $users_registered['month']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Year
        <br>
        <strong><?php echo $users_registered['year']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        Total
        <br>
        <strong><?php echo $users_registered['total']; ?></strong>
        </div>
        
        <div class="float-clear"></div>
    </div>
    
    <div class="statistics-wrapper">
        <div class="title">Pages Created</div>
        <br>
        
        <div class="float-left stats span25">
        Today
        <br>
        <strong><?php echo $pages_created['today']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Month
        <br>
        <strong><?php echo $pages_created['month']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Year
        <br>
        <strong><?php echo $pages_created['year']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        Total
        <br>
        <strong><?php echo $pages_created['total']; ?></strong>
        </div>
        
        <div class="float-clear"></div>
    </div>
    
    <div class="statistics-wrapper">
        <div class="title">Groups Created</div>
        <br>
        
        <div class="float-left stats span25">
        Today
        <br>
        <strong><?php echo $groups_created['today']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Month
        <br>
        <strong><?php echo $groups_created['month']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Year
        <br>
        <strong><?php echo $groups_created['year']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        Total
        <br>
        <strong><?php echo $groups_created['total']; ?></strong>
        </div>
        
        <div class="float-clear"></div>
    </div>
    
    <div class="statistics-wrapper">
        <div class="title">Stories Posted</div>
        <br>
        
        <div class="float-left stats span25">
        Today
        <br>
        <strong><?php echo $stories_posted['today']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Month
        <br>
        <strong><?php echo $stories_posted['month']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Year
        <br>
        <strong><?php echo $stories_posted['year']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        Total
        <br>
        <strong><?php echo $stories_posted['total']; ?></strong>
        </div>
        
        <div class="float-clear"></div>
    </div>
    
    <div class="statistics-wrapper">
        <div class="title">Comments Posted</div>
        <br>
        
        <div class="float-left stats span25">
        Today
        <br>
        <strong><?php echo $comments_posted['today']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Month
        <br>
        <strong><?php echo $comments_posted['month']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Year
        <br>
        <strong><?php echo $comments_posted['year']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        Total
        <br>
        <strong><?php echo $comments_posted['total']; ?></strong>
        </div>
        
        <div class="float-clear"></div>
    </div>
    
    <div class="statistics-wrapper">
        <div class="title">Stories Shared</div>
        <br>
        
        <div class="float-left stats span25">
        Today
        <br>
        <strong><?php echo $posts_shared['today']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Month
        <br>
        <strong><?php echo $posts_shared['month']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Year
        <br>
        <strong><?php echo $posts_shared['year']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        Total
        <br>
        <strong><?php echo $posts_shared['total']; ?></strong>
        </div>
        
        <div class="float-clear"></div>
    </div>
    
    <div class="statistics-wrapper">
        <div class="title">Posts Liked</div>
        <br>
        
        <div class="float-left stats span25">
        Today
        <br>
        <strong><?php echo $posts_liked['today']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Month
        <br>
        <strong><?php echo $posts_liked['month']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Year
        <br>
        <strong><?php echo $posts_liked['year']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        Total
        <br>
        <strong><?php echo $posts_liked['total']; ?></strong>
        </div>
        
        <div class="float-clear"></div>
    </div>
    
    <div class="statistics-wrapper">
        <div class="title">Posts Reported</div>
        <br>
        
        <div class="float-left stats span25">
        Today
        <br>
        <strong><?php echo $posts_reported['today']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Month
        <br>
        <strong><?php echo $posts_reported['month']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        This Year
        <br>
        <strong><?php echo $posts_reported['year']; ?></strong>
        </div>
        
        <div class="float-left stats span25">
        Total
        <br>
        <strong><?php echo $posts_reported['total']; ?></strong>
        </div>
        
        <div class="float-clear"></div>
    </div>
</div>
<?php } ?>