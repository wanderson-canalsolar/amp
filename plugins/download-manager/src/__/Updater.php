<?php


namespace WPDM\__;


class Updater
{
    function __construct()
    {
        add_action('admin_footer', [$this, 'requestUpdateCheck']);
        add_action("wp_ajax_wpdm_check_update", [$this, 'checkUpdate']);
        add_action("after_plugin_row", [$this, 'updateNotice'], 10, 3);
        add_filter( 'site_transient_update_plugins', [$this, 'updateCore'] );
        add_filter( 'transient_update_plugins', [$this, 'updateCore'] );
        add_action('admin_notices', [$this, 'licenseNotice']);
    }

    function getLatestVersions()
    {
        $latest = get_option('wpdm_latest', false);
        $latest_check = get_option('wpdm_latest_check');
        $time = time() - intval($latest_check);
        if(!$latest || $time > 86400) {
            $latest_v_url = 'https://wpdmcdn.s3-accelerate.amazonaws.com/versions.json';
            $latest = wpdm_remote_get($latest_v_url);
            update_option('wpdm_latest', $latest, false);
            update_option('wpdm_latest_check', time(), false);
        }
        $latest = json_decode($latest);
        $latest = (array)$latest;
        return $latest;
    }

    function checkUpdate()
    {

        if (!current_user_can(WPDM_ADMIN_CAP) || get_option('wpdm_update_notice') === 'disabled') die();

        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        $latest = $this->getLatestVersions();
        $plugins = get_plugins();

        $page = isset($_REQUEST['page']) ? esc_attr($_REQUEST['page']) : '';
        $plugin_info_url = isset($_REQUEST['plugin_url']) ? $_REQUEST['plugin_url'] : 'https://www.wpdownloadmanager.com/purchases/';
        if (is_array($latest)) {
            foreach ($latest as $plugin_dir => $latestv) {
                if ($plugin_dir !== 'download-manager') {
                    if (!($page == 'plugins' || get_post_type() == 'wpdmpro')) die('');
                    $plugin_data = wpdm_plugin_data($plugin_dir);
                    if(!is_array($plugin_data) || !isset($plugin_data['Name'])) continue;
                    $plugin_name = $plugin_data['Name'];
                    $plugin_info_url = isset($plugin_data['PluginURI']) ? $plugin_data['PluginURI'] : '';
                    $active = is_plugin_active($plugin_data['plugin_index_file']) ? 'active' : '';
                    $current_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '0';
                    if (version_compare($current_version, $latestv, '<') == true) {
                        $trid = sanitize_title($plugin_name);
                        $plugin_update_url = admin_url('/edit.php?post_type=wpdmpro&page=settings&tab=plugin-update&plugin=' . $plugin_dir); //'https://www.wpdownloadmanager.com/purchases/?'; //
                        if ($trid != '') {
                            wpdm_plugin_update_email($plugin_name, $latestv, $plugin_update_url);
                            if ($page == 'plugins') {
                                echo <<<NOTICE
     <script type="text/javascript">
      jQuery(function(){
        jQuery('tr:data[data-slug={$trid}]').addClass('update').after('<tr class="plugin-update-tr {$active} update"><td colspan=3 class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>There is a new version of <strong>{$plugin_name}</strong> available. <a href="{$plugin_update_url}&v={$latestv}" style="margin-left:10px" target=_blank>Update now ( v{$latestv} )</a></p></div></td></tr>');
      });
      </script>
NOTICE;
                            } else {
                                echo <<<NOTICE
     <script type="text/javascript">
      jQuery(function(){
        jQuery('.wrap > h2').after('<div class="updated error" style="margin:10px 0px;padding:10px;border-left:2px solid #dd3d36;background: #ffffff"><div style="float:left;"><b style="color:#dd3d36;">Important!</b><br/>There is a new version of <u>{$plugin_name}</u> available.</div> <a style="border-radius:0; float:right;;color:#ffffff; background: #D54E21;padding:10px 15px;text-decoration: none;font-weight: bold;font-size: 9pt;letter-spacing:1px" href="{$plugin_update_url}&v={$latestv}"  target=_blank><i class="fa fa-sync"></i> update v{$latestv}</a><div style="clear:both"></div></div>');
         });
         </script>
NOTICE;
                            }
                        }
                    }
                }

            }
        }
        if (__::is_ajax())
            die('');
    }

    function updateNotice($plugin_file, $plugin_data, $status){
        if($plugin_file === 'download-manager/download-manager.php') {
            $vlic = admin_url('edit.php?post_type=wpdmpro&page=settings&tab=license');
            $wpdmli = admin_url('edit.php?post_type=wpdmpro&page=settings&tab=plugin-update');
            $message = "Please activate <strong>Download Manager Pro</strong> license for automatic update. <a href=\"{$vlic}\" target=_blank>Validate license key</a>";
            $license = wpdm_ldetails(get_option('_wpdm_license_key'));
            if(!$license || $license->status !== 'VALID') {
                ?>
                <tr class="plugin-update-tr <?php echo is_plugin_active($plugin_file) ? 'active' : 'inactive'; ?> update">
                    <td class="plugin-update colspanchange" colspan="4">
                        <div class="update-message notice inline notice-error notice-alt">
                            <p><?php echo $message; ?></p>
                        </div>
                    </td>
                </tr>
                <?php
            } else if($license->expire < time()){
                $renewlink = "https://www.wpdownloadmanager.com/user-dashboard/?udb_page=purchases/order/{$license->order_id}/";
                $message = "<strong>Download Manager Pro</strong> support and update period was expired on <strong>".date(get_option('date_format'), $license->expire)."</strong>. <a href=\"{$renewlink}\" target=_blank><strong>Renew Order</strong></a>";
                ?>
                <tr class="plugin-update-tr <?php echo is_plugin_active($plugin_file) ? 'active' : 'inactive'; ?> update">
                    <td class="plugin-update colspanchange" colspan="4">
                        <div class="update-message notice inline notice-error notice-alt">
                            <p><?php echo $message; ?></p>
                        </div>
                    </td>
                </tr>
                <?php
            }
        }
    }

    function updateCore($update_plugins){

        if ( ! is_object( $update_plugins ) )
            return $update_plugins;
        if ( ! isset( $update_plugins->response ) || ! is_array( $update_plugins->response ) )
            $update_plugins->response = array();

        $latest = $this->getLatestVersions();

        $wpdm_current = WPDM_VERSION;
        $wpdm_latest = is_array($latest) && isset($latest['download-manager'])?$latest['download-manager']:WPDM_VERSION;

        //No update is available yet
        if(version_compare($wpdm_current, $wpdm_latest, '>=')) return $update_plugins;

        $upcheck = get_option('__wpdm_core_update_check', false);

        //Check for update once a day
        if(($upcheck && (time() - $upcheck) < 86400) && isset($update_plugins->response['download-manager/download-manager.php'])) return $update_plugins;

        $item = wpdm_ldetails();
        $domain = strtolower(str_replace("www.", "", $_SERVER['HTTP_HOST']));
        $download_url = is_object($item) && isset($item->download_url) && $item->download_url != '' ? $item->download_url."&domain={$domain}" : '';
        $update_plugins->response['download-manager/download-manager.php'] = (object)array(
            'slug'         => 'download-manager-pro',
            'plugin'         => 'download-manager/download-manager.php',
            'new_version'  => $wpdm_latest,
            'url'          => 'https://www.wpdownloadmanager.com/',
            'package'      => $download_url,
        );
        update_option('__wpdm_core_update_check', time(), false);
        return $update_plugins;

    }

    function licenseNotice()
    {
        if (basename($_SERVER['REQUEST_URI']) != 'plugins.php' && basename($_SERVER['REQUEST_URI']) != 'index.php' && get_post_type() != 'wpdmpro') return '';
        if ((int)get_option('__wpdm_nlc') > time()) return '';
        if ($_SERVER['HTTP_HOST'] == 'localhost') return '';
        if (str_replace("www.", "", $_SERVER['HTTP_HOST']) == 'wpdownloadmanager.com') return '';
        //if (!isAjax()) {
        if (!is_valid_license_key()) {
            $time = (int)get_option('settings_ok');
            if ($time > time())
                echo "
        <div id=\"error\" class=\"error\" style='border-left: 0 !important;border-top: 3px solid #dd3d36 !important;'><p>
        Please enter a valid <a href='edit.php?post_type=wpdmpro&page=settings&tab=license'>license key</a> for <b>Download Manager</b></p>
        </div>
        ";
            else
                echo("
        <div id=\"error\" class=\"error\" style='border-left: 0 !important;border-top: 3px solid #dd3d36 !important;'><p>
        Trial period for <b>Download Manager</b> is expired.<br/>
        Please enter a valid <a style='font-weight: 900;text-decoration: underline' href='edit.php?post_type=wpdmpro&page=settings&tab=license'>license key</a> for Download Manager to reactivate it.<br/>
        <a href='https://www.wpdownloadmanager.com/'>Buy your copy now only at 59.00 usd</a></p>
        </div>
        ");
        }
        //}
    }


    /**
     * Add js code in admin footer to sent update check request
     */
    function requestUpdateCheck()
    {

        global $pagenow;
        if (!current_user_can(WPDM_ADMIN_CAP)) return;

        if(!in_array($pagenow, array('plugins.php'))) return;
        $tmpvar = explode("?", basename($_SERVER['REQUEST_URI']));
        $page = array_shift($tmpvar);
        $page = explode(".", $page);
        $page = array_shift($page);


        $page = $page == 'plugins' ? $page : get_post_type();

        ?>
        <script type="text/javascript">
            jQuery(function () {
                console.log('Checking WPDM Version!');
                jQuery.post(ajaxurl, {
                    action: 'wpdm_check_update',
                    page: '<?php echo $page; ?>'
                }, function (res) {
                    jQuery('#wpfooter').after(res);
                });


            });
        </script>

        <?php
    }
}
