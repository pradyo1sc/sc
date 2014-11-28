<?php
function edit_group() {
global $config;

if (empty($_GET['id'])) {
    return null;
}

$group = SK_getAccount($_GET['id']);

?>
<form class="content-container" method="post" action="?tab1=edit_group&id=<?php echo $_GET['id'] ?>">
    <div class="content-header">Edit Group <small>(<?php echo $group['name']; ?>)</small></div>
    <div class="content-wrapper">
        <div class="label float-left">Name</div>
        <div class="input float-left">
            <input type="text" name="name" value="<?php echo $group['name']; ?>">
            <div class="info">Group's name</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Username</div>
        <div class="input float-left">
            <input type="text" name="username" value="<?php echo $group['username']; ?>">
            <div class="info">Group's unique username</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">E-mail</div>
        <div class="input float-left">
            <input type="text" name="email" value="<?php echo $group['email']; ?>">
            <div class="info">Group's email</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">About</div>
        <div class="input float-left">
            <textarea name="about"><?php echo $group['about']; ?></textarea>
            <div class="info">Group's about</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Group privacy</div>
        <div class="input float-left">
            <select name="group_privacy">
                <option value="open"<?php if ($group['group_privacy'] == "open") echo ' selected'; ?>>Open Group</option>
                <option value="closed"<?php if ($group['group_privacy'] == "closed") echo ' selected'; ?>>Closed Group</option>
                <option value="secret"<?php if ($group['group_privacy'] == "secret") echo ' selected'; ?>>Secret Group</option>
            </select>
            <div class="info">Group's privacy</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Add privacy</div>
        <div class="input float-left">
            <select name="add_privacy">
                <option value="members"<?php if ($group['add_privacy'] == "members") echo ' selected'; ?>>Members</option>
                <option value="admins"<?php if ($group['add_privacy'] == "admins") echo ' selected'; ?>>Only admins</option>
            </select>
            <div class="info">Who can add members to group</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Timeline post privacy</div>
        <div class="input float-left">
            <select name="timeline_post_privacy">
                <option value="everyone"<?php if ($group['timeline_post_privacy'] == "everyone") echo ' selected'; ?>>Everyone</option>
                <option value="admins"<?php if ($group['timeline_post_privacy'] == "admins") echo ' selected'; ?>>Only admins</option>
            </select>
            <div class="info">Who can post on group's timeline</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="button-wrapper">
        <input type="submit" name="save_btn" value="Save Changes">
    </div>
    <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
    <input type="hidden" name="update_group_information" value="1">
    <input type="hidden" name="keep_blank" value="">
</form>
<?php } ?>