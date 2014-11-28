<?php
function general_settings() {
global $config;
?>
<form class="content-container" method="post" action="?tab1=general_settings">
    <div class="content-header">Site Information</div>

    <div class="content-wrapper">
        <label>Website Settings</label>
    </div>

    <div class="content-wrapper">
        <div class="label float-left">Name</div>
        <div class="input float-left">
            <input type="text" name="site_name" value="<?php echo $config['site_name']; ?>">
            <div class="info">Name of your site</div>
        </div>
        <div class="float-clear"></div>
    </div>

    <div class="content-wrapper">
        <div class="label float-left">Title</div>
        <div class="input float-left">
            <input type="text" name="site_title" value="<?php echo $config['site_title']; ?>">
            <div class="info">Site's title</div>
        </div>
        <div class="float-clear"></div>
    </div>

    <div class="content-wrapper">
        <div class="label float-left">E-mail</div>
        <div class="input float-left">
            <input type="text" name="site_email" value="<?php echo $config['email']; ?>">
            <div class="info">Site's email. All emails to your users will be send from this email.</div>
        </div>
        <div class="float-clear"></div>
    </div>

    <div class="content-wrapper">
        <div class="label float-left">Email verification</div>
        <div class="input float-left">
            <select name="email_verification">
                <option value="1" <?php if ($config['email_verification'] == 1) echo 'selected'; ?>>On</option>
                <option value="0" <?php if ($config['email_verification'] == 0) echo 'selected'; ?>>Off</option>
            </select>
            <div class="info">Enable email verification
            </div>
        </div>
        <div class="float-clear"></div>
    </div>

    <div class="content-wrapper">
        <div class="label float-left">Chat</div>
        <div class="input float-left">
            <select name="chat">
                <option value="1" <?php if ($config['chat'] == 1) echo 'selected'; ?>>On</option>
                <option value="0" <?php if ($config['chat'] == 0) echo 'selected'; ?>>Off</option>
            </select>
            <div class="info">Enable chat system
            </div>
        </div>
        <div class="float-clear"></div>
    </div>

    <div class="content-wrapper">
        <div class="label float-left">Captcha</div>
        <div class="input float-left">
            <select name="captcha">
                <option value="1" <?php if ($config['captcha'] == 1) echo 'selected'; ?>>On</option>
                <option value="0" <?php if ($config['captcha'] == 0) echo 'selected'; ?>>Off</option>
            </select>
            <div class="info">Enable captcha on registration</div>
        </div>
        <div class="float-clear"></div>
    </div>

    <div class="content-wrapper">
        <div class="label float-left">Language</div>
        <div class="input float-left">
            <select name="language">
                <?php
                $languages = glob('../assets/languages/*.php');
                    
                foreach($languages as $language) {
                    $language = str_replace('../assets/languages/', '', $language);
                    $language = preg_replace('/([A-Za-z]+)\.php/i', '$1', $language); ?>
                    <option value="<?php echo $language; ?>" <?php if ($language == $config['language']) echo 'selected'; ?>><?php echo ucfirst($language); ?></option>
                <?php } ?>
            </select>
            <div class="info">Default language</div>
        </div>
        <div class="float-clear"></div>
    </div>

    <div class="content-wrapper">
        <div class="label float-left">Smooth Links</div>
        <div class="input float-left">
            <select name="smooth_links">
                <option value="0" <?php if ($config['smooth_links'] == 0) echo 'selected'; ?>>Off</option>
                <option value="1" <?php if ($config['smooth_links'] == 1) echo 'selected'; ?>>On</option>
            </select>
            <div class="info">Enable smooth links, e.g. <?php echo $config['site_url']; ?>/home. <br><br> <strong>Note:</strong> Modifications required. Contact me if you need help.</div>
        </div>
        <div class="float-clear"></div>
    </div>

    <div class="content-wrapper">
        <div class="label float-left">Censored words</div>
        <div class="input float-left">
            <input type="text" name="censored_words" value="<?php echo $config['censored_words']; ?>">
            <div class="info">Words to be censored, separated by a comma (,)</div>
        </div>
        <div class="float-clear"></div>
    </div>

    <div class="content-wrapper">
        <label>Registration Fields</label>
    </div>

    <div class="content-wrapper">
        <div class="label float-left">Fields</div>
        <div class="input float-left">
            <input type="checkbox" name="reg_req_birthday" value="1"<?php if ($config['reg_req_birthday'] == true) { echo ' checked="yes"'; } ?> style="display: inline-block; vertical-align: middle;"> Birthday
            <br>
            <input type="checkbox" name="reg_req_currentcity" value="1"<?php if ($config['reg_req_currentcity'] == true) { echo ' checked="yes"'; } ?> style="display: inline-block; vertical-align: middle;"> Current City
            <br>
            <input type="checkbox" name="reg_req_hometown" value="1"<?php if ($config['reg_req_hometown'] == true) { echo ' checked="yes"'; } ?> style="display: inline-block; vertical-align: middle;"> Hometown
            <br>
            <input type="checkbox" name="reg_req_about" value="1"<?php if ($config['reg_req_about'] == true) { echo ' checked="yes"'; } ?> style="display: inline-block; vertical-align: middle;"> About
        </div>
        <div class="float-clear"></div>
    </div>

    <div class="content-wrapper">
        <label>Connectivity Settings</label>
    </div>

    <div class="content-wrapper">
        <div class="label float-left">Type</div>
        <div class="input float-left">
            <select name="friends">
                <option value="0" <?php if ($config['friends'] != true) echo 'selected'; ?>>Follow System</option>

                <option value="1" <?php if ($config['friends'] == true) echo 'selected'; ?>>Friends System</option>
            </select>

            <div class="info">
                <strong>Note:</strong> If you migrate from one system to another, all existing <strong>followings, followers, friends, likes, memberships will be deleted</strong> to avoid issues.
            </div>
        </div>
        <div class="float-clear"></div>
    </div>

    <div class="content-wrapper">
        <label>Character Limits</label>
    </div>

    <?php
    $limits = array(
        50, 100, 150, 200, 250, 300, 400, 500, 1000, 2000, 2500, 5000
        );
    ?>

    <div class="content-wrapper">
        <div class="label float-left">Story Character Limit</div>
        <div class="input float-left">
            <select name="story_character_limit">
                <option value="0"<?php if ($config['story_character_limit'] == 0) { echo ' selected="yes"'; } ?>>No limit</option>
                <?php
                foreach ($limits as $limit) {
                    if ($limit == $config['story_character_limit']) {
                        echo '<option value="' . $limit . '" selected="yes">' . $limit . '</option>';
                    } else {
                        echo '<option value="' . $limit . '">' . $limit . '</option>';
                    }
                }
                ?>
            </select>
            <div class="info">Maximum number of characters can be used by Users to post a Story.
            </div>
        </div>
        <div class="float-clear"></div>
    </div>

    <div class="content-wrapper">
        <div class="label float-left">Comment Character Limit</div>
        <div class="input float-left">
            <select name="comment_character_limit">
                <option value="0"<?php if ($config['comment_character_limit'] == 0) { echo ' selected="yes"'; } ?>>No limit</option>
                <?php
                foreach ($limits as $limit) {
                    if ($limit == $config['comment_character_limit']) {
                        echo '<option value="' . $limit . '" selected="yes">' . $limit . '</option>';
                    } else {
                        echo '<option value="' . $limit . '">' . $limit . '</option>';
                    }
                }
                ?>
            </select>
            <div class="info">Maximum number of characters can be used by Users to post a Comment.
            </div>
        </div>
        <div class="float-clear"></div>
    </div>

    <div class="content-wrapper">
        <div class="label float-left">Message Character Limit</div>
        <div class="input float-left">
            <select name="message_character_limit">
                <option value="0"<?php if ($config['message_character_limit'] == 0) { echo ' selected="yes"'; } ?>>No limit</option>
                <?php
                foreach ($limits as $limit) {
                    if ($limit == $config['message_character_limit']) {
                        echo '<option value="' . $limit . '" selected="yes">' . $limit . '</option>';
                    } else {
                        echo '<option value="' . $limit . '">' . $limit . '</option>';
                    }
                }
                ?>
            </select>
            <div class="info">Maximum number of characters can be used by Users to write a Message.
            </div>
        </div>
        <div class="float-clear"></div>
    </div>

    <div class="button-wrapper">
        <input type="submit" name="save_btn" value="Save Changes">
    </div>

    <input type="hidden" name="keep_blank" value="">
    <input type="hidden" name="update_site_settings" value="1">
</form>
<?php } ?>