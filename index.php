<?php
/* * * * * * * * * * * * * *
Gang Group
Copyright (c) 2014

Author Fa
Date: 27/Nov/2014
* * * * * * * * * * * * * */

error_reporting(0);
require_once('assets/includes/core.php');

if (!isset($_GET['tab1'])) {
    $_GET['tab1'] = 'welcome';
}

switch ($_GET['tab1']) {
    
    // Welcome page source
    case 'welcome':
        include('assets/sources/welcome.php');
    break;
    
    // Email verification source
    case 'email-verification':
        include('assets/sources/email_verification.php');
    break;
    
    // Home page source
    case 'home':
        include('assets/sources/home.php');
    break;
    
    // Messages page source
    case 'messages':
        include('assets/sources/messages.php');
    break;
    
    // Timeline page source
    case 'timeline':
        include('assets/sources/timeline.php');
    break;
    
    // Story page source
    case 'story':
        include('assets/sources/story.php');
    break;

    // Album page source
    case 'album':
        include('assets/sources/album.php');
    break;
    
    // Create page source
    case 'create_page':
        include('assets/sources/create_page.php');
    break;
    
    // Create group page source
    case 'create_group':
        include('assets/sources/create_group.php');
    break;
    
    // Hashtag page source
    case 'hashtag':
        include('assets/sources/hashtag.php');
    break;
    
    // Search page source
    case 'search':
        include('assets/sources/search.php');
    break;
    
    // User settings page source
    case 'settings':
        include('assets/sources/user_settings.php');
    break;
    
    // More features page source
    case 'more':
        include('assets/sources/more.php');
    break;
    
    // Terms page source
    case 'terms':
        include('assets/sources/terms.php');
    break;
    
    // Logout source
    case 'logout':
        include('assets/sources/logout.php');
    break;
    
}

// If no sources found
if (empty($sk['content'])) {
    $sk['content'] = FA_getPage('welcome/error');
}

echo FA_getPage('container');
mysqli_close($dbConnect);