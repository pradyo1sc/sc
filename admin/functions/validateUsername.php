<?php
function FA_validateUsername($query='') {
    if (strlen($query) > 3 && !is_numeric($query) && preg_match('/^[A-Za-z0-9_]+$/', $query)) {
        return true;
    }
}
