<?php
function group_settings() {
global $config, $dbConnect;

$query = "EXPLAIN ". DB_GROUPS;
$sql_query = mysqli_query($dbConnect, $query);

while ($sql_fetch = mysqli_fetch_assoc($sql_query)) {
    $default_settings[$sql_fetch['Field']] = $sql_fetch['Default'];
}

?>
<form class="content-container" method="post" action="?tab1=group_settings">
    <div class="content-header">Group Settings</div>
    <div class="content-wrapper">
        <label>Changes made will only be applied to groups created from now on.</label>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Add privacy</div>
        <div class="input float-left">
            <select name="add_privacy">
                <option value="members" <?php if ($default_settings['add_privacy'] == "members") echo 'selected'; ?>>Members</option>
                <option value="admins" <?php if ($default_settings['add_privacy'] == "admins") echo 'selected'; ?>>Admins</option>
            </select>
            <div class="info">Who can add members to group privacy by default</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Timeline post privacy</div>
        <div class="input float-left">
            <select name="timeline_post_privacy">
                <option value="members" <?php if ($default_settings['timeline_post_privacy'] == "members") echo 'selected'; ?>>Members</option>
                <option value="admins" <?php if ($default_settings['timeline_post_privacy'] == "admins") echo 'selected'; ?>>Only admins</option>
            </select>
            <div class="info">Who can post on group's timeline privacy by default</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="button-wrapper">
        <input type="submit" name="save_btn" value="Save Changes">
    </div>
    <input type="hidden" name="keep_blank" value="">
    <input type="hidden" name="update_group_settings" value="1">
</form>
<?php } ?>