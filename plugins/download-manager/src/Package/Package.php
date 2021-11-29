<?php


namespace WPDM\Package;


class Package extends PackageController
{
    public $ID;
    public $title;
    public $description;
    public $excerpt;
    public $files;
    public $fileList;
    public $post_status;
    public $version;
    public $publish_date;
    public $publish_date_timestamp;
    public $update_date;
    public $update_date_timestamp;
    public $avail_date;
    public $expire_date;
    public $link_label;
    public $download_count;
    public $view_count;
    public $access;
    public $author;
    public $quota;
    public $package_size;

    function __construct($ID = null)
    {
        parent::__construct($ID);

        $this->init($ID);

    }

    function init($ID)
    {
        if ((int)$ID > 0) {
            global $wpdb;
            $pack = get_post($ID);
            if ($pack && $pack->post_type === 'wpdmpro') {
                $this->ID = $pack->ID;
                $this->title = $pack->post_title;
                $this->description = wpautop($pack->post_content);
                $this->description = str_replace("[wpdm", "[__wpdm", $this->description);
                $this->description = do_shortcode($this->description);
                $this->excerpt = wpautop($pack->post_excerpt);
                $this->post_status = $pack->post_status;
                $this->publish_date_timestamp = strtotime($pack->post_date);
                $this->publish_date = wp_date(get_option('date_format'), $this->publish_date_timestamp);
                $this->update_date_timestamp = strtotime($pack->post_modified);
                $this->update_date = wp_date(get_option('date_format'), $this->update_date_timestamp);
                $this->author = $pack->post_author;
                $this->files = $this->getFiles($ID, true);

                $meta = $this->metaData($ID);
                $this->avail_date = wpdm_valueof($meta, '__wpdm_publish_date') ? wp_date(get_option('date_format')." ".get_option('time_format'), wpdm_valueof($meta, '__wpdm_publish_date')) : 0;
                $this->expire_date = wpdm_valueof($meta, '__wpdm_expire_date') ? wp_date(get_option('date_format')." ".get_option('time_format'), wpdm_valueof($meta, '__wpdm_expire_date')) : 0;
                $this->download_count = wpdm_valueof($meta, '__wpdm_download_count');
                $this->view_count = wpdm_valueof($meta, '__wpdm_view_count');
                $this->package_size = wpdm_valueof($meta, '__wpdm_package_size');
                $this->quota = wpdm_valueof($meta, '__wpdm_quota');
                $this->link_label = wpdm_valueof($meta, '__wpdm_link_label');
                $this->version = wpdm_valueof($meta, '__wpdm_version');
            }
        }
        $this->fileList = new FileList();
        return $this;
    }
}
