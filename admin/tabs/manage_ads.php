<?php
function manage_ads() {
global $config;
?>
<form class="content-container" method="post" action="?tab1=manage_ads">
    <div class="content-header">Manage Ads</small></div>
    <div class="content-wrapper">
        <label>Copy and paste your HTML Ad Codes here.</label>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Home Ad Placement</div>
        <div class="input float-left">
            <textarea name="ad_place_home"><?php echo $config['ad_place_home']; ?></textarea>
            <div class="info">Advertisement on Home page</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Message Ad Placement</div>
        <div class="input float-left">
            <textarea name="ad_place_messages"><?php echo $config['ad_place_messages']; ?></textarea>
            <div class="info">Advertisement on Messages page</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Timeline Ad Placement</div>
        <div class="input float-left">
            <textarea name="ad_place_timeline"><?php echo $config['ad_place_timeline']; ?></textarea>
            <div class="info">Advertisement on Timeline page</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Search Ad Placement</div>
        <div class="input float-left">
            <textarea name="ad_place_search"><?php echo $config['ad_place_search']; ?></textarea>
            <div class="info">Advertisement on Search page</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="content-wrapper">
        <div class="label float-left">Hashtag Ad Placement</div>
        <div class="input float-left">
            <textarea name="ad_place_hashtag"><?php echo $config['ad_place_hashtag']; ?></textarea>
            <div class="info">Advertisement on Hashtag page</div>
        </div>
        <div class="float-clear"></div>
    </div>
    <div class="button-wrapper">
        <input type="submit" name="save_btn" value="Save Changes">
    </div>
    <input type="hidden" name="update_ad_codes" value="1">
    <input type="hidden" name="keep_blank" value="">
</form>
<?php } ?>