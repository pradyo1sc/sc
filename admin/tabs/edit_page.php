<?php
function edit_page() {
global $config, $dbConnect;

if (empty($_GET['id'])) {
    return null;
}

$page = SK_getAccount($_GET['id']);

?>
<form class="content-container" method="post" action="?tab1=edit_page&id=<?php echo $_GET['id'] ?>">
    <div class="content-header">Edit Page <small>(<?php echo $page['name']; ?>)</small></div>
    <div class="content-wrapper">
        <div class="label float-left">Verified</div>
        <div class="input float-left">
            <select name="verified">
                <option value="0"<?php if ($page['verified'] == 0) echo ' selected'; ?>>No</option>
                <option value="1"<?php if ($page['verified'] == 1) echo ' selected'; ?>>Yes</option>
            </select>
            <div class="info">Verified page or not</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Name</div>
        <div class="input float-left">
            <input type="text" name="name" value="<?php echo $page['name']; ?>">
            <div class="info">Page's name</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Username</div>
        <div class="input float-left">
            <input type="text" name="username" value="<?php echo $page['username']; ?>">
            <div class="info">Page's unique username</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">E-mail</div>
        <div class="input float-left">
            <input type="text" name="email" value="<?php echo $page['email']; ?>">
            <div class="info">Page's email</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">About</div>
        <div class="input float-left">
            <textarea name="about"><?php echo $page['about']; ?></textarea>
            <div class="info">Page's about</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Category</div>
        <div class="input float-left">
            <select name="category_id">
                <?php
                $query_one = "SELECT id,name FROM ". DB_PAGE_CATEGORIES ." WHERE category_id=0 AND active=1";
                $sql_query_one = mysqli_query($dbConnect, $query_one);
                
                while ($sql_fetch_one = mysqli_fetch_array($sql_query_one, MYSQLI_ASSOC)) { ?>
                <optgroup label="<?php echo $sql_fetch_one['name']; ?>">
                    <?php
                    $query_two = "SELECT id,name FROM ". DB_PAGE_CATEGORIES ." WHERE category_id=". $sql_fetch_one['id'] ." AND active=1";
                    $sql_query_two = mysqli_query($dbConnect, $query_two);
                    
                    while ($sql_fetch_two = mysqli_fetch_array($sql_query_two, MYSQLI_ASSOC)) { ?>
                    <option value="<?php echo $sql_fetch_two['id']; ?>"<?php if ($page['category_id'] == $sql_fetch_two['id']) echo ' selected'; ?>><?php echo $sql_fetch_two['name']; ?></option>
                    <?php } ?>
                </optgroup>
                <?php } ?>
            </select>
            <div class="info">Page's category</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Address</div>
        <div class="input float-left">
            <input type="text" name="address" value="<?php echo $page['address']; ?>">
            <div class="info">Page's address</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Awards</div>
        <div class="input float-left">
            <input type="text" name="awards" value="<?php echo $page['awards']; ?>">
            <div class="info">Page's awards</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Phone</div>
        <div class="input float-left">
            <input type="text" name="phone" value="<?php echo $page['phone']; ?>">
            <div class="info">Page's phone number</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Products</div>
        <div class="input float-left">
            <input type="text" name="products" value="<?php echo $page['products']; ?>">
            <div class="info">Page's products</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Website</div>
        <div class="input float-left">
            <input type="text" name="website" value="<?php echo $page['website']; ?>">
            <div class="info">Page's website</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Message privacy</div>
        <div class="input float-left">
            <select name="message_privacy">
                <option value="everyone"<?php if ($page['message_privacy'] == "everyone") echo ' selected'; ?>>Everyone</option>
                <option value="none"<?php if ($page['message_privacy'] == "none") echo ' selected'; ?>>No one</option>
            </select>
            <div class="info">Who can message page</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Timeline post privacy</div>
        <div class="input float-left">
            <select name="timeline_post_privacy">
                <option value="everyone"<?php if ($page['timeline_post_privacy'] == "everyone") echo ' selected'; ?>>Everyone</option>
                <option value="admins"<?php if ($page['timeline_post_privacy'] == "admins") echo ' selected'; ?>>Only admins</option>
            </select>
            <div class="info">Who can post on page&#39;s timeline</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="button-wrapper">
        <input type="submit" name="save_btn" value="Save Changes">
    </div>
    <input type="hidden" name="page_id" value="<?php echo $page['id']; ?>">
    <input type="hidden" name="update_page_information" value="1">
    <input type="hidden" name="keep_blank" value="">
</form>
<?php } ?>