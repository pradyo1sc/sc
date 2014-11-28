<?php
function announcements() {
global $dbConnect, $config;
?>
<div class="announcement-container">
    <div class="announcement-header">New Announcement</div>
    <form class="announcement-writer-wrapper" method="post" action="?tab1=announcements">
        <textarea name="announcement_text" placeholder="Write a new announcement... HTML allowed!"></textarea>
        <button>Add</button>
        <input type="hidden" name="add_new_announcement" value="1">
        <input type="hidden" name="keep_blank" value="">
    </form>
</div>

<div class="announcement-container">
    <div class="announcement-header">Announcements</div>
    <?php
    $query = "SELECT id,text,time FROM " . DB_ANNOUNCEMENTS . " ORDER BY id DESC";
    $sql_query = mysqli_query($dbConnect, $query);

    while ($sql_fetch = mysqli_fetch_assoc($sql_query)) {
        $query_two = "SELECT COUNT(id) AS count FROM " . DB_ANNOUNCEMENT_VIEWS . " WHERE announcement_id=" . $sql_fetch['id'];
        $sql_query_two = mysqli_query($dbConnect, $query_two);
        $sql_fetch_two = mysqli_fetch_assoc($sql_query_two);
    ?>
    <div class="announcement">
        <div class="text">
            <?php echo $sql_fetch['text']; ?> - <a href="?tab1=announcements&delete_announcement=<?php echo $sql_fetch['id']; ?>">Delete</a>
        </div>
        <div class="time">
            <?php echo date('M  j Y, h:i A', $sql_fetch['time']); ?>
        </div>
        <div class="time">
            <?php echo $sql_fetch_two['count']; ?> people read this
        </div>
    </div>
    <?php
    }
    ?>
</div>
<?php } ?>