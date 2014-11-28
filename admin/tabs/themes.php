<?php
function themes() {
global $config;
?>
<div class="theme-container">
    <div class="theme-header">Themes</div>
    <?php
    $themes = glob('../themes/*', GLOB_ONLYDIR);
    
    foreach ($themes as $theme_url) {
    include($theme_url . '/info.php');
    $theme = str_replace('../themes/', '', $theme_url);
    ?>
    <div class="theme-wrapper">
        <div class="float-left icon">
            <img src="<?php echo $theme_url; ?>/icon.png" width="87%" alt="<?php echo $theme; ?>" valign="middle">
        </div>
        <div class="float-left info">
            <div class="name">
                <strong><?php echo $name; ?></strong>
            </div>
            <div class="version">
                <label>Version:</label> <?php echo $version; ?>
            </div>
            <div class="author">
                <label>Author:</label> <a href="<?php echo $author_website; ?>"><?php echo $author; ?></a>
            </div>
        </div>
        <div class="float-right btn">
            <?php if ($theme == $config['theme']) { ?>
            <button class="activate-btn active-btn">Activated</button>
            <?php }
            else { ?>
            <form method="post" action="?tab1=themes">
                <button class="activate-btn">Activate</button>
                <input type="hidden" name="activate_theme" value="1">
                <input type="hidden" name="theme" value="<?php echo $theme; ?>">
                <input type="hidden" name="keep_blank" value="">
            </form>
            <?php } ?>
        </div>
        <div class="float-clear"></div>
    </div>
    <?php } ?>
    <div class="theme-wrapper">
        <div class="info">To install a new theme, upload it on the <strong>themes</strong> folder.</div>
        <div class="info"><strong>Note:</strong> Admin panel was designed independently. Theme changes will not reflect any change in admin panel.</div>
    </div>
</div>
<?php } ?>