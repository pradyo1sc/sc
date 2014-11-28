<?php
if (!empty($_GET['id'])) {
    $sk['post']['id'] = FA_secureEncode($_GET['id']);
    
    if (is_numeric($sk['post']['id']) && $sk['post']['id'] > 0) {
        $sk['content'] = FA_getPage('story-page/content');
    }
}
