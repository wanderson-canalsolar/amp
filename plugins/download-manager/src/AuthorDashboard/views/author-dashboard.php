<?php

use WPDM\__\Template;

if (!defined("ABSPATH")) die();
if (!is_user_logged_in()) die();

?>
<div class="w3eden author-dashbboard">
    <?php

    /**
     * Menu URL template
     */
    $menu_url_template = add_query_arg(['adb_page' => '%s'], get_permalink(get_the_ID()));
    //if (isset($params['flaturl']) && $params['flaturl'] == 1)
    //    $menu_url_template = trailingslashit(get_permalink(get_the_ID())) . '%s/';

    $store = get_user_meta(get_current_user_id(), '__wpdm_public_profile', true);

    do_action("wpdm_author_dashboard_page_before");

    $logo = (isset($store['logo']) && $store['logo'] != '') ? $store['logo'] : get_avatar_url(get_current_user_id(), ['size' => 512]);

    ?>
    <div class="row">
        <div id="wpdm-dashboard-sidebar" class="col-md-3">
            <div id="logo-block">
                <img style="margin-bottom: 10px;border-radius: 4px" class="thumbnail shop-logo m-0 p-0" id="shop-logo" src="<?php echo $logo; ?>"/>
            </div>

            <div id="tabs" class="list-group m-0 p-0">
                <?php
                foreach ($menu_items as $menu_id => $menu_item) {
                    $menu_url = $menu_id !== '' ? sprintf($menu_url_template, $menu_id) : get_permalink(get_the_ID());
                    $menu_icon = isset($menu_item['icon']) ? $menu_item['icon'] : wpdm_valueof($default_icons, $menu_id, ['default' => 'fa fa-bars']);
                    ?>
                    <a class="adp-item <?= ($adb_page === $menu_id) ? 'active' : ''; ?>" href="<?php echo $menu_url; ?>">
                        <i class="<?php echo $menu_icon; ?> mr-3"></i><?php echo $menu_item['label']; ?>
                    </a>
                <?php } ?>

                <a class="adp-item" href="<?php echo wpdm_logout_url(); ?>"><i
                            class="fas fa-sign-out-alt color-danger mr-3"></i><?php _e("Logout", "download-manager"); ?>
                </a>
            </div>

        </div>
        <div id="wpdm-dashboard-content" class="col-md-9">

            <?php

            do_action("wpdm_author_dashboard_content_before");

            if ($adb_page == 'add-new' || $adb_page == 'edit-package')
                include WPDM()->template->locate("author-dashboard/new-package-form.php", __DIR__);
            else if ($adb_page != '' && isset($menu_items[$adb_page]['callback']) && $menu_items[$adb_page]['callback'] != '')
                call_user_func($menu_items[$adb_page]['callback']);
            else if ($adb_page != '' && isset($menu_items[$adb_page]['shortcode']) && $menu_items[$adb_page]['shortcode'] != '')
                echo do_shortcode($menu_items[$adb_page]['shortcode']);

            do_action("wpdm_author_dashboard_content_after");
            ?>

        </div>
    </div>

    <script>jQuery(function ($) {
            $("#tabs > li > a").click(function () {
                location.href = this.href;
            });
        });</script>

</div>

