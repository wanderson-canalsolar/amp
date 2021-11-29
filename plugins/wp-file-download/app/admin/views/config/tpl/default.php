<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

// No direct access.
defined('ABSPATH') || die();

use Joomunited\WPFramework\v1_0_5\Application;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- $msg is not print out
$msg = isset($_GET['msg']) ? (string) esc_html($_GET['msg']) : '';
?>
<?php if ($msg !== '') { ?>
    <div id="message" class="updated notice notice-success">
        <p><?php esc_html_e('Settings saved', 'wpfd'); ?></p>
    </div>
<?php } ?>
<div class="wrap wpfd-config">
    <div id="icon-options-general" class="icon32"></div>
    <h2><?php esc_html_e('WP File Download Configuration', 'wpfd'); ?></h2>
    <div id="dashboard-widgets-wrap">
        <div id="dashboard-widgets" class="metabox-holder ">
            <div id="wpfd-container-config" class="">
                <div class="tab-header">
                    <ul class="nav-tab-wrapper" id="wpfd-tabs-config">
                        <?php
                        $this->tabs = array(
                            'main' => esc_html__('Main settings', 'wpfd'),
                            'search' => esc_html__('File Search & Upload', 'wpfd'),
                        );
                        $cont_first = 0;
                        foreach ($this->tabs as $configTab => $val) {
                            $active_class = ($cont_first === 0) ? ' active' : '';
                            $cont_first++;
                            ?>
                            <a id="<?php echo esc_attr('tab-' . $configTab); ?>" class="nav-tab<?php echo esc_attr($active_class); ?>"
                               data-tab-id="<?php echo esc_attr($configTab); ?>"
                               href="#<?php echo esc_attr($configTab); ?>"><?php echo esc_html(ucfirst($val)); ?>
                            </a>
                        <?php } ?>
                        <?php
                        /**
                         * Action fire after configuration tabs
                         *
                         * @internal
                         */
                        do_action('wpfdAddonAfterTap');
                        ?>
                        <?php
                        foreach ($this->themeforms as $theme => $val) {
                            $active_class = '';
                            ?>
                            <a id="<?php echo esc_attr('tab-' . $theme); ?>" class="nav-tab<?php echo esc_attr($active_class); ?>"
                               data-tab-id="<?php echo esc_attr($theme); ?>"
                               href="#<?php echo esc_attr($theme); ?>">
                                <?php echo (esc_attr($theme) === 'ggd') ? strtoupper(esc_attr($theme)) : ucfirst(esc_attr($theme)); ?>
                                <?php esc_html_e('theme', 'wpfd'); ?>
                            </a>
                        <?php } ?>
                        <a id="tab-file" class="nav-tab" data-tab-id="file" href="#file">
                            <?php esc_html_e('Single file', 'wpfd'); ?>
                        </a>
                        <a id="tab-clone" class="nav-tab" data-tab-id="clone" href="#clone">
                            <?php esc_html_e('Clone Theme', 'wpfd'); ?>
                        </a>
                        <?php
                        /**
                         * Action fire on cloud configuration tabs
                         *
                         * @internal
                         */
                        do_action('wpfd_addon_configuration_tab');
                        ?>
                        <a id="tab-jutranslation" class="nav-tab" data-tab-id="jutranslation" href="#jutranslation">
                            <?php echo esc_html__('Translation', 'wpfd'); ?>
                        </a>
                    </ul>
                </div>
                <div class="tab-content" id="wpfd-tabs-content-config">
                    <div id="wpfd-main-config" class="tab-pane active">
                        <?php
                        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- print output from render() of framework
                        echo $this->configform;
                        ?>
                    </div>
                    <div id="wpfd-search-config" class="tab-pane ">
                        <?php
                        echo $this->searchform;
                        echo $this->upload_form;
                        ?>
                    </div>
                    <?php
                    foreach ($this->themeforms as $theme => $val) {
                        ?>
                        <div id="wpfd-<?php echo esc_attr($theme); ?>-config" class="tab-pane">
                            <?php echo $this->themeforms[$theme]; ?>
                        </div>
                    <?php } ?>
                    <div id="wpfd-file-config" class="tab-pane ">
                        <?php
                        echo $this->file_configform;
                        echo $this->file_catform;
                        ?>
                    </div>
                    <div id="wpfd-clone-config" class="tab-pane ">
                        <?php
                        echo $this->clone_form;
                        // phpcs:enable
                        ?>
                    </div>
                    <div id="wpfd-theme-cloudconnection" class="tab-pane ">
                        <?php
                        /**
                         * Action fire for Google Drive configuration
                         *
                         * @internal
                         */
                        do_action('wpfd_addon_configuration_content');
                        ?>
                    </div>
                    <div id="wpfd-jutranslation-config" class="tab-pane ">
                        <?php
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escape output in getInput()
                        echo \Joomunited\WPFileDownload\Jutranslation\Jutranslation::getInput(); ?>
                    </div>
                    <?php
                    /**
                     * Action fire after configuration content
                     *
                     * @internal
                     */
                    do_action('wpfdAddonAfterContent');
                    ?>
                </div>
            </div>

        </div>
    </div>
    <div id="mybootstrap" style="margin:0"></div>
</div>
<style>
    #wpfd-tabs, #wpfd-tabs-config {
        margin-bottom: 1px;
    }

    #wpfd-tabs .nav-tab.active,
    #wpfd-tabs-config .nav-tab.active {
        background-color: #FFF;
        color: #464646;
    }

    #wpfd-tabs-content, #wpfd-tabs-content-config {
        background: #fff;
        border-left: 1px solid #CCC;
        padding: 10px 10px 30px 10px
    }

    #wpfd-tabs-content .tab-pane {
        display: none
    }

    #wpfd-tabs-content-config .tab-pane {
        display: none
    }

    #wpfd-tabs-content .tab-pane.active {
        display: block
    }

    #wpfd-tabs-content-config .tab-pane.active {
        display: block
    }

    #wpfd-tabs-content-config textarea {
        width: 100%;
    }
</style>
<script type="text/javascript">
    wpfdajaxurl = "<?php echo wpfd_sanitize_ajax_url(Application::getInstance('Wpfd')->getAjaxUrl());  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- keep this, if not it error ?>";
    jQuery(document).ready(function ($) {

        $("#wpfd-tabs-config .nav-tab").click(function (e) {
            e.preventDefault();
            $("#wpfd-tabs-config .nav-tab").removeClass('active');
            var id_tab = $(this).data('tab-id');
            $("#tab-" + id_tab).addClass('active');
            $("#wpfd-tabs-content-config .tab-pane").removeClass('active');
            $("#wpfd-" + id_tab + '-config').addClass('active');
            $("#wpfd-theme-" + id_tab).addClass('active');
            document.cookie = 'active_tab=' + id_tab;
        });

        function setTabFromCookie() {
            var active_tab = getCookie('active_tab');
            if (active_tab !== "") {
                $("#tab-" + active_tab).click();
            }
        }

        function getCookie(cname) {
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1);
                if (c.indexOf(name) === 0) return c.substring(name.length, c.length);
            }
            return "";
        }

        setTabFromCookie();
        if ($("input[name=dropboxKey]").val() !== '' && $("input[name=dropboxSecret]").val() !== '') {
            $('#dropboxAuthor + .help-block').html('');
        }
        else {
            $("#dropboxAuthor").attr('type', 'hidden');
        }
    });
</script>
