<?php
function page_settings() {
global $config, $dbConnect;

$query = "EXPLAIN ". DB_PAGES;
$sql_query = mysqli_query($dbConnect, $query);

while ($sql_fetch = mysqli_fetch_assoc($sql_query)) {
    $default_settings[$sql_fetch['Field']] = $sql_fetch['Default'];
}

?>
<form class="content-container" method="post" action="?tab1=page_settings">
    <div class="content-header">Page Settings</div>
    <div class="content-wrapper">
        <label>Changes made will only be applied to pages created from now on.</label>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Message privacy</div>
        <div class="input float-left">
            <select name="message_privacy">
                <option value="everyone" <?php if ($default_settings['message_privacy'] == "everyone") echo 'selected'; ?>>Everyone</option>
                <option value="none" <?php if ($default_settings['message_privacy'] == "none") echo 'selected'; ?>>No one</option>
            </select>
            <div class="info">Message privacy by default</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Timeline post privacy</div>
        <div class="input float-left">
            <select name="timeline_post_privacy">
                <option value="everyone" <?php if ($default_settings['timeline_post_privacy'] == "everyone") echo 'selected'; ?>>Everyone</option>
                <option value="admins" <?php if ($default_settings['timeline_post_privacy'] == "admins") echo 'selected'; ?>>Only admins</option>
            </select>
            <div class="info">Who can post on page's timeline privacy by default</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="button-wrapper">
        <input type="submit" name="save_btn" value="Save Changes">
    </div>
    <input type="hidden" name="keep_blank" value="">
    <input type="hidden" name="update_page_settings" value="1">
</form>
<?php } ?>