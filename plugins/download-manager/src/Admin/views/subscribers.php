<?php
global $wpdb, $current_user;
$limit = 20;

$_GET['paged'] = isset($_GET['paged'])?(int)$_GET['paged']:1;
$start = isset($_GET['paged'])?((int)($_GET['paged']-1)*$limit):0;
$field = isset($_GET['sfield'])?wpdm_query_var('sfield'):'id';
$ord = isset($_GET['sorder'])?wpdm_query_var('sorder'):'desc';
$pid = isset($_GET['pid'])?(int)$_GET['pid']:0;
if($pid > 0) $cond = " and e.pid=$pid";
if(isset($_GET['uniq'])) $group = " group by e.email";


?>

<div class="wrap w3eden">

    <?php

    $actions = [
        ['link' => "edit.php?post_type=wpdmpro&page=wpdm-subscribers&task=export" .  (wpdm_query_var('lockOption') != ''?'&lockOption='.wpdm_query_var('lockOption'):''), "class" => "info", "name" => '<i class="sinc far fa-arrow-alt-circle-down"></i> ' . __("Export All", "download-manager")],
        ['link' => "edit.php?post_type=wpdmpro&page=wpdm-subscribers&task=export&uniq=1" .  (wpdm_query_var('lockOption') != ''?'&lockOption='.wpdm_query_var('lockOption'):''), "class" => "primary", "name" => '<i class="sinc far fa-arrow-alt-circle-down"></i> ' . __("Export Unique Emails", "download-manager")]
    ];

    $lockOption = wpdm_query_var('lockOption', 'txt', 'email');
    $menus = [
        ['link' => "edit.php?post_type=wpdmpro&page=wpdm-subscribers&lockOption=email", "name" => __("Email Lock", "download-manager"), "active" => ($lockOption === 'email' || $lockOption === '')],
        ['link' => "edit.php?post_type=wpdmpro&page=wpdm-subscribers&lockOption=google", "name" => __("Google", "download-manager"), "active" => ($lockOption === 'google')],
        ['link' => "edit.php?post_type=wpdmpro&page=wpdm-subscribers&lockOption=linkedin", "name" => __("LinkedIn", "download-manager"), "active" => ($lockOption === 'linkedin')],
    ];

    WPDM()->admin->pageHeader(esc_attr__( 'Subscribers', WPDM_TEXT_DOMAIN ), 'users color-purple', $menus, $actions);

    ?>

    <div class="wpdm-admin-page-content">

    <?php
    $lockoption = wpdm_query_var('lockOption');
    $lockoption = $lockoption?$lockoption:'email';
    $lockoption = str_replace(array("/","\\"), "", $lockoption);
    include wpdm_admin_tpl_path("subscribers/{$lockoption}.php"); ?>

</div>
</div>
<style>
    .dropdown-menu .list-group{
        margin: 0 5px;
        font-size: 8pt;
        border-radius: 0;
    }
    .dropdown-menu .list-group .list-group-item{
        padding: 5px;
        border-radius: 0;
    }
</style>
<script>
    jQuery(function ($) {
        $('#subsform').on('submit', function(e){
            if($('#subsform input[type=checkbox]:checked').length < 1) {
                WPDM.bootAlert("<?= esc_attr__( 'No item selected', WPDM_TEXT_DOMAIN )?>", "<?= esc_attr__( 'Select one or more item to delete.', WPDM_TEXT_DOMAIN )?>");
                return false;
            }
            if(!confirm('<?=esc_attr__( 'Are you sure?', WPDM_TEXT_DOMAIN );?>')) return false;
        });
    });
</script>
