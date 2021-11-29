<?php

use WPDM\__\FileSystem;
use WPDM\__\Crypt;
use WPDM\__\Messages;
use WPDM\__\TempStorage;

if(!defined("ABSPATH")) die('!');

error_reporting(0);

global $current_user, $dfiles;

//Check for blocked IPs
if(wpdm_ip_blocked()) {
    $_ipblockedmsg =  __('Your IP address is blocked!', 'download-manager');
    $ipblockedmsg = get_option('__wpdm_blocked_ips_msg', '');
    $ipblockedmsg = $ipblockedmsg == ''?$_ipblockedmsg:$ipblockedmsg;
    Messages::error($ipblockedmsg, 1);
}

//Check for blocked users by email
if(is_user_logged_in() && !wpdm_verify_email($current_user->user_email)) {
    $emsg =  get_option('__wpdm_blocked_domain_msg');
    if(trim($emsg) === '') $emsg = __('Your email address is blocked!', 'download-manager');
    Messages::fullPage('Error!', $emsg, 'error');
}

do_action("wpdm_onstart_download", $package);

$speed = (int)get_option('__wpdm_download_speed', 10240); //in KB - default 10 MB
$speed = $speed > 0 ? $speed : 10240;
$speed = apply_filters('wpdm_download_speed', $speed);
$user = get_user_by('id', $package['post_author']);
$user_upload_dir = UPLOAD_DIR . $user->user_login . '/';

$_content_dir = str_replace('\\','/',WP_CONTENT_DIR);
$_old_up_dir = $_content_dir.'/uploads/download-manager-files/';
//wpdmdd($package);
//Only published packages are downloadable
$downloadable_post_status = apply_filters("wpdm_downloadable_post_status", array('publish','private'), $package);
if(!in_array($package['post_status'], $downloadable_post_status)) Messages::fullPage("404", "<div class='card p-4 bg-danger text-white'>".__( "Package you are trying to download is not available!" , "download-manager" )."</div>");

$limit_msg = Messages::download_limit_exceeded($package['ID']);

if (wpdm_is_download_limit_exceed($package['ID'])) Messages::fullPage("Error!", $limit_msg, 'error');
//$files = WPDM()->package->getFiles($package['ID']);
$files = $package['files'];

$fileCount = count($files) + (isset($package['package_dir']) && $package['package_dir'] !== '' ? 1 : 0);

if($fileCount === 0){
    Messages::fullPage(__( "No Files", "download-manager" ),  __( "No file is attached with this package!", "download-manager" ));
}

//$idvdl = Individual file download status
$idvdl = ( WPDM()->package->isSingleFileDownloadAllowed( $package['ID'] ) || wpdm_query_var('oid', false) ) && isset($_GET['ind']);

$parallel_download = (int)get_option('__wpdm_parallel_download', 1);

if($parallel_download === 0 && (int)TempStorage::get("download.".wpdm_get_client_ip()) === 1)
    Messages::error(get_option('__wpdm_parallel_download_msg', "Another download is in progress from your IP, please wait until finished."), 1);

if ($fileCount > 1 && !$idvdl) {
    $zipped = get_post_meta($package['ID'], "__wpdm_zipped_file", true);
    $cache_zip = get_option('__wpdm_cache_zip', 0);
    $cache_zip_po = get_post_meta($package['ID'], "__wpdm_cache_zip", true);

    $cache_zip = $cache_zip_po == -1 || !$cache_zip_po ? $cache_zip : $cache_zip_po;
    if ($zipped == '' || !file_exists($zipped) || $cache_zip == 0) {
        $zipname = sanitize_file_name($package['title']) . '-' . $package['ID'] . '.zip';
        $zipped = FileSystem::zipFiles($files, $zipname);
        update_post_meta($package['ID'], "__wpdm_zipped_file", $zipped);
    }
    WPDM()->downloadHistory->add($package['ID'], '', wpdm_query_var('oid'));
    FileSystem::downloadFile($zipped, sanitize_file_name($package['title']) . '.zip', $speed, 1, $package);
}
else {

    /*if(!wpdm_query_var('ind')) {
        $package_dir = get_post_meta($package['ID'], '__wpdm_package_dir', true);
        $package_dir = $package_dir !== '' && file_exists($package_dir) ? $package_dir : Crypt::decrypt($package_dir);
        if ($package_dir !== '' && !file_exists($package_dir)) {
            Messages::fullPage('Error!', "<div class='card bg-danger text-white p-4'>" . __("Invalid dir path.", "download-manager") . "</div>", 'error');
        }

        if ($package_dir !== '' && file_exists($package_dir)) {
            $zipped = \WPDM\__\FileSystem::zipDir($package_dir);
            WPDM()->downloadHistory->add($package['ID'], $package_dir, wpdm_query_var('oid'));
            \WPDM\__\FileSystem::downloadFile($zipped, basename($zipped));
            die();
        }
    }*/

    //Individual file or single file download section

    $indfile = '';

    if (isset($_GET['ind'])) {
        $indfile = isset($files[esc_attr($_GET['ind'])])?$files[esc_attr($_GET['ind'])]:Crypt::decrypt(esc_attr($_GET['ind']));
        if(!$indfile) {
            $tmpfiles = $files;
            $indfile = array_shift($tmpfiles);
        }
    } else if ($fileCount == 1) {
        $tmpfiles = $files;
        $indfile = array_shift($tmpfiles);
        unset($tmpfiles);
    }

    $firstfile = array_shift($files);
    $firstfile = file_exists($firstfile) ? $firstfile : UPLOAD_DIR.$firstfile;

    WPDM()->downloadHistory->add($package['ID'], $indfile ? $indfile : $firstfile, wpdm_query_var('oid'));

    //URL Download
    if ($indfile != '' && strpos($indfile, '://')) {

        if (!isset($package['url_protect']) || $package['url_protect'] == 0) {
            $indfile = wpdm_escs(htmlspecialchars_decode($indfile));
            header('location: ' . $indfile);

        } else {
            $r_filename = wpdm_basename($indfile);
            $r_filename = explode("?", $r_filename);
            $r_filename = $r_filename[0];
            wpdm_download_file($indfile, $r_filename, $speed, 1, $package);

        }

        die();
    }


    /*$tmp = explode("wp-content", $indfile);
    $tmp = end($tmp);
    if ($indfile != '' && file_exists(UPLOAD_DIR . $indfile))
        $filepath = UPLOAD_DIR . $indfile;
    else if ($indfile != '' && file_exists($user_upload_dir.$indfile))
        $filepath = $user_upload_dir.$indfile;
    else if ($indfile != '' && file_exists($indfile))
        $filepath = $indfile;
    else if ($indfile != '' && file_exists(WP_CONTENT_DIR . $tmp)) //path fix on site move
        $filepath = WP_CONTENT_DIR . $tmp;
    else if ($indfile != '' && file_exists($_old_up_dir . $indfile)) //path fix on site move
        $filepath = $_old_up_dir . $indfile;
    else {
        $filepath = $firstfile;
    }*/

    $filepath = WPDM()->fileSystem->absPath($indfile, $package['ID']);
    if(!$filepath)
        Messages::fullPage('Error!', "<div class='card bg-danger text-white p-4'>" . __("Sorry! File not found!", "download-manager") . "</div>", 'error');
    //$plock = get_wpdm_meta($file['id'],'password_lock',true);
    //$fileinfo = get_wpdm_meta($package['id'],'fileinfo');

    $filename = wpdm_basename($filepath);
    $filename = preg_replace("/([0-9]+)[wpdm]+_/", "", $filename);

    wpdm_download_file($filepath, $filename, $speed, 1, $package);
    //@unlink($filepath);

}

TempStorage::kill("download.".wpdm_get_client_ip());

do_action("after_download", $package);

die();

