<?php
global $post, $current_user;

use WPDM\__\Template;

if (!defined('ABSPATH')) die();

if (isset($pid))
    $post = get_post($pid);
else {
    $post = new stdClass();
    $post->ID = 0;
    $post->post_title = '';
    $post->post_content = '';
}

if (isset($hide)) $hide = explode(',', $hide);
else $hide = array();
$can_edit = $post->ID === 0 || (array_intersect($current_user->roles, get_option('__wpdm_front_end_admin', array()))) || $post->post_author == get_current_user_id() ? 1 : 0;
if ($can_edit) {
    ?>
    <div class="w3eden">
        <link rel="stylesheet" type="text/css"
              href="<?php echo plugins_url('/download-manager/assets/css/chosen.css'); ?>"/>
        <style>
            .cat-card ul,
            .cat-card label,
            .cat-card li {
                padding: 0;
                margin: 0;
                font-size: 9pt;
            }

            .cat-card ul {
                margin-left: 20px;
            }

            .cat-card > ul {
                padding-top: 10px;
            }

            #wpdm-pf .card {
                margin-bottom: 15px;
            }
        </style>
        <div class="wpdm-front">
            <?php if ($post->ID > 0) { ?>
                <form id="wpdm-pf" action="" method="post">
                    <?php wp_nonce_field(NONCE_KEY, '__wpdmepnonce'); ?>
                    <?php do_action("wpdm_before_new_package_form", $post->ID); ?>
                    <div class="row">

                        <div class="col-md-8">


                            <input type="hidden" id="act" name="act"
                                   value="<?php echo $adb_page == 'edit-package' ? '_ep_wpdm' : '_ap_wpdm'; ?>"/>

                            <input type="hidden" name="id" id="id" value="<?php echo isset($pid) ? $pid : 0; ?>"/>
                            <div class="form-group">
                                <input id="title" class="form-control form-control-lg" placeholder="Enter title here"
                                       type="text"
                                       value="<?php echo isset($post->post_title) ? $post->post_title : ''; ?>"
                                       name="pack[post_title]"/>
                            </div>
                            <div class="form-group">
                                <div class="card " id="package-description">
                                    <div class="card-header">
                                        <strong><?= esc_attr__("Description", "download-manager"); ?></strong></div>
                                    <div class="card-body-desc">
                                        <?php $cont = isset($post) ? $post->post_content : '';
                                        wp_editor(stripslashes($cont), 'post_content', array('textarea_name' => 'pack[post_content]', 'teeny' => 1, 'media_buttons' => 0)); ?>
                                    </div>
                                </div>
                            </div>


                            <?php require Template::locate('author-dashboard/new-package-form/attached-files.php', dirname(__DIR__)); ?>

                            <?php require Template::locate('author-dashboard/new-package-form/package-settings.php', dirname(__DIR__)); ?>


                            <?php

                            do_action("wpdm-package-form-left");
                            do_action("wpdm_frontend_add_package_left");
                            ?>


                        </div>
                        <div class="col-md-4">

                            <?php include WPDM\__\Template::locate('author-dashboard/new-package-form/attach-file.php', dirname(__DIR__)); ?>

                            <?php include WPDM\__\Template::locate('author-dashboard/new-package-form/categories.php', dirname(__DIR__)); ?>

                            <?php include WPDM\__\Template::locate('author-dashboard/new-package-form/tags.php', dirname(__DIR__)); ?>

                            <?php do_action("wpdm_frontend_add_package_sidebar", $params, $post); ?>

                            <?php include WPDM\__\Template::locate('author-dashboard/new-package-form/featured-image.php', dirname(__DIR__)); ?>

                            <?php include WPDM\__\Template::locate('author-dashboard/new-package-form/additional-previews.php', dirname(__DIR__)); ?>

                            <?php include WPDM\__\Template::locate('author-dashboard/new-package-form/author.php', dirname(__DIR__)); ?>

                            <div class="card card-primary " id="form-action">
                                <div class="card-header">
                                    <b>Actions</b>
                                </div>
                                <div class="card-body">

                                    <label><input class="wpdm-checkbox"
                                                  type="checkbox" <?php if (isset($post->post_status)) checked($post->post_status, 'draft'); ?>
                                                  value="draft"
                                                  name="status"> <?= esc_attr__("Save as Draft", "download-manager"); ?>
                                    </label><br/><br/>


                                    <button type="submit" accesskey="p" tabindex="5" id="publish"
                                            class="btn btn-success btn-block btn-lg" name="publish"><i
                                                class="far fa-hdd" id="psp"></i>
                                        &nbsp;<?php echo $post->post_status == 'auto-draft' ? __("Create Package", "download-manager") : __("Update Package", "download-manager"); ?>
                                    </button>

                                </div>
                            </div>

                        </div>
                    </div>
                    <?php do_action("wpdm_after_new_package_form", $post->ID); ?>
                </form>
            <?php } else { ?>

                <form id="wpdm-pf" action="" method="post">
                    <?php do_action("wpdm_before_new_package_form", 0); ?>
                    <input type="hidden" id="act" name="act" value="_ap_wpdm"/>
                    <input type="hidden" name="id" id="id" value="0"/>
                    <?php wp_nonce_field(NONCE_KEY, '__wpdmepnonce'); ?>

                    <div class="card">
                        <div class="card-header"><?= esc_attr__("Create New Package", "download-manager"); ?></div>
                        <div class="card-body">
                            <div class="form-group">
                                <input id="title" class="form-control form-control-lg" required="required"
                                       placeholder="Enter title here" type="text"
                                       value="<?php echo isset($post->post_title) ? $post->post_title : ''; ?>"
                                       name="pack[post_title]"/>
                            </div>
                            <input type="hidden" value="auto-draft" name="status">
                            <button type="submit" accesskey="p" tabindex="5" id="publish" class="btn btn-primary"
                                    name="publish"><i class="fas fa-hdd" id="psp"></i>
                                &nbsp;<?php echo __("Continue...", "download-manager"); ?></button>

                        </div>
                    </div>
                    <?php do_action("wpdm_after_new_package_form", 0); ?>
                </form>

            <?php } ?>
        </div>


        <script type="text/javascript"
                src="<?php echo plugins_url('/download-manager/assets/js/chosen.jquery.min.js'); ?>"></script>
        <script type="text/javascript">

            var ps = "", pss = "", allps = "";

            jQuery(function ($) {

                $('.w3eden select').chosen();
                $('span.infoicon').css({
                    color: 'transparent',
                    width: '16px',
                    height: '16px',
                    cursor: 'pointer'
                }).tooltip({placement: 'right', html: true});
                $('span.infoicon').tooltip({placement: 'right'});
                $('.nopro').click(function () {
                    if (this.checked) $('.wpdmlock').removeAttr('checked');
                });

                $('.wpdmlock').click(function () {

                    if (this.checked) {
                        $('#' + $(this).attr('rel')).slideDown();
                        $('.nopro').removeAttr('checked');
                    } else {
                        $('#' + $(this).attr('rel')).slideUp();
                    }
                });

                // jQuery( "#pdate" ).datepicker({dateFormat:'yy-mm-dd'});
                // jQuery( "#udate" ).datepicker({dateFormat:'yy-mm-dd'});

                jQuery('#wpdm-pf').submit(function () {
                    try {
                        var editor = tinymce.get('post_content');
                        editor.save();
                    } catch (e) {
                    }
                    if (jQuery('#title').val() === '') return false;
                    jQuery('#__psp').removeClass('fa-hdd').addClass('fa-sun fa-spin');
                    jQuery('#publish').attr('disabled', 'disabled');
                    jQuery('#wpdm-pf').ajaxSubmit({
                        //dataType: 'json',
                        beforeSubmit: function () {
                            jQuery('#sving').fadeIn();
                        },
                        success: function (res) {
                            jQuery('#sving').fadeOut();
                            jQuery('#nxt').slideDown();
                            jQuery(".card-file.card-danger").remove();
                            if (res.result == '_ap_wpdm') {
                                <?php
                                $edit_url = $burl . $sap . 'adb_page=edit-package/%d/';
                                if (isset($params['flaturl']) && $params['flaturl'] == 1)
                                    $edit_url = $burl . '/edit-package/%d/'; ?>
                                var edit_url = '<?php echo $edit_url; ?>';
                                edit_url = edit_url.replace('%d', res.id);
                                location.href = edit_url;
                                WPDM.notify("<?= esc_attr__("Package Created Successfully!", "download-manager"); ?>", "success", "top-right", 4000);
                                WPDM.notify("<?= esc_attr__("Opening Edit Widnow...", "download-manager"); ?>", "info", 7000);
                                jQuery('#wpdm-pf').prepend('<div class="alert alert-success" style="width:300px;" data-title="" data-dismiss="alert"><?= esc_attr__("Opening Edit Window ...", "download-manager"); ?></div>');
                            } else {
                                jQuery('#__psp').removeClass('fa-sun fa-spin').addClass('fa-hdd');
                                jQuery('#publish').removeAttr('disabled');
                                WPDM.notify("<span class='lead'><?= esc_attr__("Package Updated Successfully!", "download-manager"); ?></span>", "success", "bottom-full", 7000);
                            }
                        }


                    });
                    return false;
                });


                allps = jQuery('#pps_z').val();
                if (allps == undefined) allps = '';
                jQuery('#__ps').val(allps.replace(/\]\[/g, "\n").replace(/[\]|\[]+/g, ''));

                jQuery('#gps').click(__shuffle);

                jQuery('body').on('click', '#gpsc', function () {
                    var allps = "";
                    __shuffle();
                    for (k = 0; k < jQuery('#pcnt').val(); k++) {
                        allps += "[" + randomPassword(pss, jQuery('#ncp').val()) + "]";

                    }
                    vallps = allps.replace(/\]\[/g, "\n").replace(/[\]|\[]+/g, '');
                    jQuery('#__ps').val(vallps);

                });

                jQuery('body').on('click', '#pins', function () {
                    var aps;
                    aps = jQuery('#__ps').val();
                    aps = aps.replace(/\n/g, "][");
                    allps = "[" + aps + "]";
                    jQuery(jQuery(this).data('target')).val(allps);
                    tb_remove();
                });

                allps = $('#pps_z').val();
                if (allps == undefined) allps = '';
                $('#__ps').val(allps.replace(/\]\[/g, "\n").replace(/[\]|\[]+/g, ''));

                $('#gps').click(function () {
                    __shuffle();
                });

                $('body').on('click', '#gpsc', function () {
                    var allps = "";
                    __shuffle();
                    for (k = 0; k < $('#pcnt').val(); k++) {
                        allps += "[" + randomPassword(pss, $('#ncp').val()) + "]";

                    }
                    vallps = allps.replace(/\]\[/g, "\n").replace(/[\]|\[]+/g, '');
                    $('#__ps').val(vallps);

                });

                $('body').on('click', '#pins', function () {
                    var aps;
                    aps = $('#__ps').val();
                    aps = aps.replace(/\n/g, "][");
                    allps = "[" + aps + "]";
                    $(wpdm_pass_target).val(allps);
                    $('#generatepass').modal('hide');
                });


            });

            jQuery('#upload-main-preview').click(function () {
                tb_show('', '<?php echo admin_url('media-upload.php?type=image&TB_iframe=1&width=640&height=551'); ?>');
                window.send_to_editor = function (html) {
                    var imgurl = jQuery('img', html).attr('src');
                    var img = document.createElement("IMG");
                    img.setAttribute("src", imgurl);
                    var hdn = document.createElement("input");
                    hdn.setAttribute("type", "hidden");
                    hdn.setAttribute("name", "file[preview]");
                    hdn.setAttribute("value", imgurl);
                    document.getElementById('img').appendChild(hdn).appendChild(img);
                    tb_remove();
                };
                return false;
            });


            function randomPassword(chars, size) {

                //var size = 10;
                if (parseInt(size) == Number.NaN || size == "") size = 8;
                var i = 1;
                var ret = "";
                while (i <= size) {
                    $max = chars.length - 1;
                    $num = Math.floor(Math.random() * $max);
                    $temp = chars.substr($num, 1);
                    ret += $temp;
                    i++;
                }
                return ret;
            }

            function __shuffle() {
                var $ = jQuery;
                var sl = 'abcdefghijklmnopqrstuvwxyz';
                var cl = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                var nm = '0123456789';
                var sc = '~!@#$%^&*()_';
                ps = "";
                pss = "";
                ps = sl;
                if ($('#passtrn').val() > 1) ps += cl;
                if ($('#passtrn').val() > 2) ps += nm;
                if ($('#passtrn').val() > 3) ps += sc;
                var i = 0;
                while (i <= ps.length) {
                    $max = ps.length - 1;
                    $num = Math.floor(Math.random() * $max);
                    $temp = ps.substr($num, 1);
                    pss += $temp;
                    i++;
                }
                //jQuery('#__ps').val(pss);
            }



        </script>

    </div>
<?php } else { ?>
    <div class="w3eden">
        <div class="alert alert-danger">
            <?php echo __("You are not authorized to edit this package!", "download-manager") ?>
        </div>
    </div>
<?php } ?>
