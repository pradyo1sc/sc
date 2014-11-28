<?php
function edit_user() {
global $config;

if (empty($_GET['id'])) {
    return null;
}

$user = FA_getAccount($_GET['id']);

?>
<form class="content-container" method="post" action="?tab1=edit_user&id=<?php echo $_GET['id'] ?>">
    <div class="content-header">Edit User <small>(<?php echo $user['name']; ?>)</small></div>
    <div class="content-wrapper">
        <div class="label float-left">Verified</div>
        <div class="input float-left">
            <select name="verified">
                <option value="0"<?php if ($user['verified'] == 0) echo ' selected'; ?>>No</option>
                <option value="1"<?php if ($user['verified'] == 1) echo ' selected'; ?>>Yes</option>
            </select>
            <div class="info">Verified user or not</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Name</div>
        <div class="input float-left">
            <input type="text" name="name" value="<?php echo $user['name']; ?>">
            <div class="info">User's name</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Username</div>
        <div class="input float-left">
            <input type="text" name="username" value="<?php echo $user['username']; ?>">
            <div class="info">User's unique username</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">E-mail</div>
        <div class="input float-left">
            <input type="text" name="email" value="<?php echo $user['email']; ?>">
            <div class="info">User's email</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">About</div>
        <div class="input float-left">
            <textarea name="about"><?php echo $user['about']; ?></textarea>
            <div class="info">User's about</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Current city</div>
        <div class="input float-left">
            <input type="text" name="current_city" value="<?php echo $user['current_city']; ?>">
            <div class="info">User's current city</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Hometown</div>
        <div class="input float-left">
            <input type="text" name="hometown" value="<?php echo $user['hometown']; ?>">
            <div class="info">User's hometown</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Gender</div>
        <div class="input float-left">
            <select name="gender">
                <option value="male"<?php if ($user['gender'] == "male") echo ' selected'; ?>>Male</option>
                <option value="female"<?php if ($user['gender'] == "female") echo ' selected'; ?>>Female</option>
            </select>
            <div class="info">User's gender</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Birthday</div>
        <div class="input float-left">
            <select name="birthday[0]">
                <?php
                $months = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December');
                for ($i = 1; $i <= 31; $i++) { ?>
                <option value="<?php echo $i; ?>"<?php if ($i == $user['birth']['date']) echo ' selected'; ?>><?php echo $i; ?></option>
                <?php } ?>
            </select>
            <select name="birthday[1]">
                <?php
                for ($i = 1; $i <= 12; $i++) {
                if (!empty($months[$i])) {
                ?>
                <option value="<?php echo $i; ?>"<?php if ($i == $user['birth']['month']) echo ' selected'; ?>><?php echo $months[$i]; ?></option>
                <?php }
                } ?>
            </select>
            <select name="birthday[2]">
                <?php for ($i = date('Y')-100; $i <= date('Y')-13; $i++) { ?>
                <option value="<?php echo $i; ?>"<?php if ($i == $user['birth']['year']) echo ' selected'; ?>><?php echo $i; ?></option>
                <?php } ?>
            </select>
            <div class="info">User's birthday</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Confirm followers</div>
        <div class="input float-left">
            <select name="confirm_followers">
                <option value="0"<?php if ($user['confirm_followers'] == 0) echo ' selected'; ?>>No</option>
                <option value="1"<?php if ($user['message_privacy'] == 1) echo ' selected'; ?>>Yes</option>
            </select>
            <div class="info">Should user confirm when someone wants to follow</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Follow privacy</div>
        <div class="input float-left">
            <select name="follow_privacy">
                <option value="everyone"<?php if ($user['follow_privacy'] == "everyone") echo ' selected'; ?>>Everyone</option>
                <option value="following"<?php if ($user['follow_privacy'] == "following") echo ' selected'; ?>>Only people user is following</option>
            </select>
            <div class="info">Who can follow user</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Message privacy</div>
        <div class="input float-left">
            <select name="message_privacy">
                <option value="everyone"<?php if ($user['message_privacy'] == "everyone") echo ' selected'; ?>>Everyone</option>
                <option value="following"<?php if ($user['message_privacy'] == "following") echo ' selected'; ?>>Only people user is following</option>
            </select>
            <div class="info">Who can message user</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Timeline post privacy</div>
        <div class="input float-left">
            <select name="timeline_post_privacy">
                <option value="everyone"<?php if ($user['timeline_post_privacy'] == "everyone") echo ' selected'; ?>>Everyone</option>
                <option value="following"<?php if ($user['timeline_post_privacy'] == "following") echo ' selected'; ?>>Only people user is following</option>
            </select>
            <div class="info">Who can post on user&#39;s timeline</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Comment privacy</div>
        <div class="input float-left">
            <select name="comment_privacy">
                <option value="everyone"<?php if ($user['comment_privacy'] == "everyone") echo ' selected'; ?>>Everyone</option>
                <option value="following"<?php if ($user['comment_privacy'] == "following") echo ' selected'; ?>>Only people user is following</option>
            </select>
            <div class="info">Who can comment on user&#39;s posts</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="button-wrapper">
        <input type="submit" name="save_btn" value="Save Changes">
    </div>
    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
    <input type="hidden" name="update_user_information" value="1">
    <input type="hidden" name="keep_blank" value="">
</form>

<form class="content-container" method="post" action="?tab1=edit_user&id=<?php echo $_GET['id'] ?>">
    <div class="content-header">Update Password <small>(<?php echo $user['name']; ?>)</small></div>
    <div class="content-wrapper">
        <div class="label float-left">New password</div>
        <div class="input float-left">
            <input type="text" name="password">
            <div class="info">New password for user</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="button-wrapper">
        <input type="submit" name="save_btn" value="Save Changes">
    </div>
    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
    <input type="hidden" name="update_user_password" value="1">
    <input type="hidden" name="keep_blank" value="">
</form>
<?php } ?>