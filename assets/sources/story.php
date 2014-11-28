<?php
if (!empty($_GET['id'])) {
    $sk['post']['id'] = SK_secureEncode($_GET['id']);
    
    if (is_numeric($sk['post']['id']) && $sk['post']['id'] > 0) {
        $sk['content'] = SK_getPage('story-page/content');
    }
}
