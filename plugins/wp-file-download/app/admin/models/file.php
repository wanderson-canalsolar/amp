<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Model;

defined('ABSPATH') || die();

/**
 * Class WpfdModelFile
 */
class WpfdModelFile extends Model
{
    /**
     * Get file info by ID
     *
     * @param integer $id_file File id
     *
     * @return array|boolean
     */
    public function getFile($id_file)
    {
        $app           = Application::getInstance('Wpfd', __FILE__);
        $modelConfig   = $this->getInstance('config');
        $modelCategory = $this->getInstance('category');
        $modelTokens   = $this->getInstance('tokens');
        $token = '';
        $params       = $modelConfig->getConfig();
        $row = get_post($id_file, ARRAY_A);
        if ($row === false || !$row) {
            return false;
        }
        $row['title']       = $row['post_title'];
        $row['description'] = $row['post_excerpt'];
        $row['created_time'] = get_date_from_gmt($row['post_date_gmt']);
        $row['modified_time'] = get_date_from_gmt($row['post_modified_gmt']);
        $row['created']     = mysql2date(
            WpfdBase::loadValue($params, 'date_format', get_option('date_format')),
            $row['created_time']
        );
        $row['modified']    = mysql2date(
            WpfdBase::loadValue($params, 'date_format', get_option('date_format')),
            $row['modified_time']
        );
        $metadata           = get_post_meta($id_file, '_wpfd_file_metadata', true);
        $expirationDate     = get_post_meta($id_file, '_wpfd_file_meta_expiration_date', true);
        if (!is_wp_error($metadata) && !empty($metadata)) {
            foreach ($metadata as $key => $value) {
                $row[$key] = $value;
            }
        }

        $row['state']       = ($row['post_status'] === 'publish') ? 1 : 0;
        $row['publish']     = get_date_from_gmt($row['post_date_gmt']);
        $row['expiration']  = $expirationDate;

        $term_list      = wp_get_post_terms($id_file, 'wpfd-category', array('fields' => 'ids'));
        if (!is_wp_error($term_list) && count($term_list) > 0) {
            $row['catid'] = $term_list[0];
        } else {
            $row['catid'] = 0;
        }
        $remote_url  = isset($metadata['remote_url']) ? $metadata['remote_url'] : false;
        $viewer_type = WpfdBase::loadValue($params, 'use_google_viewer', 'lightbox');

        $extension_viewer_list = 'png,jpg,pdf,ppt,pptx,doc,docx,xls,xlsx,dxf,ps,eps,xps,psd,tif,tiff,bmp,svg,pages,ai,dxf,ttf,txt,mp3,mp4';
        $extension_viewer      = explode(',', WpfdBase::loadValue($params, 'extension_viewer', $extension_viewer_list));
        $extension_viewer      = array_map('trim', $extension_viewer);

        if ($viewer_type !== 'no' &&
            in_array($row['ext'], $extension_viewer)
            && ($remote_url === false)
        ) {
            $row['viewer_type'] = $viewer_type;
            $row['viewerlink'] = WpfdHelperFile::getMediaViewerUrl($row['ID'], $row['catid'], $row['ext']);
        }

        return stripslashes_deep($row);
    }

    /**
     * Save file data
     *
     * @param array $datas File data
     *
     * @return boolean
     */
    public function save($datas)
    {
        /**
         * Filter allow modify file data before save to database
         *
         * @param array File data array
         *
         * @return array
         */
        $datas = apply_filters('wpfd_before_save_file_params', $datas);
        // Fix zero time
        if (strpos($datas['publish'], '00:00:00')) {
            $postTime = get_the_date('h:i:s', $datas['id']);
            $postDate = date('Y-m-d', strtotime($datas['publish']));
            $datas['publish'] = $postDate . ' ' . $postTime;
        }
        if (strpos($datas['expiration'], '00:00:00')) {
            $exPostTime = get_the_date('h:i:s', $datas['id']);
            $exPostDate = date('Y-m-d', strtotime($datas['expiration']));
            $datas['expiration'] = $exPostDate . ' ' . $exPostTime;
        }

        $my_post              = array(
            'ID'                => $datas['id'],
            'post_title'        => $datas['title'],
            'post_modified'     => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', 1),
            'post_date'         => $datas['publish'],
            'post_date_gmt'     => get_gmt_from_date($datas['publish']),
            'post_status'       => (int) $datas['state'] === 1 ? 'publish' : 'private',
            'post_excerpt'      => $datas['description']
        );
        $my_post['post_name'] = sanitize_title($datas['title'], $datas['id']);
        if (isset($datas['remoteurl']) && !empty($datas['remoteurl'])) {
            $my_post['guid'] = $datas['remoteurl'];
        }
        // Update the post into the database
        wp_update_post($my_post);

        $metadata                            = get_post_meta($datas['id'], '_wpfd_file_metadata', true);
        $metaExpirationDate                  = get_post_meta($datas['id'], '_wpfd_file_meta_expiration_date', true);
        $metadata['hits']                    = $datas['hits'];
        $metadata['state']                   = $datas['state'];
        $metadata['version']                 = $datas['version'];
        $metadata['file_tags']               = $datas['file_tags'];
        $metadata['canview']                 = $datas['canview'];
        $metadata['file_custom_icon']        = $datas['file_custom_icon'];
        $metadata['social']                  = isset($datas['social']) ? $datas['social'] : 0;
        $metadata['file_multi_category']     = $datas['file_multi_category'];
        $metadata['file_multi_category_old'] = $datas['file_multi_category_old'];
        if (isset($datas['ext']) && !empty($datas['ext'])) {
            $metadata['ext'] = $datas['ext'];
        }
        if (isset($datas['remoteurl']) && !empty($datas['remoteurl'])) {
            $metadata['remote_url'] = true;
            $metadata['file']       = $datas['remoteurl'];
            $metadata['size'] = wpfd_remote_file_size($datas['remoteurl']);
        }
        $metaExpirationDate                  = $datas['expiration'];
        /**
         * Filter allow modify file meta data before save to database
         *
         * @param array File meta data array
         *
         * @return array
         */
        $metadata = apply_filters('wpfd_before_save_file_metadata', $metadata, $datas);
        update_post_meta($datas['id'], '_wpfd_file_metadata', $metadata);
        update_post_meta($datas['id'], '_wpfd_file_meta_expiration_date', $metaExpirationDate);
        wp_set_post_terms($datas['id'], $datas['file_tags'], 'wpfd-tag');

        /**
         * After file data was saved to database
         *
         * @param array File data array
         */
        do_action('wpfd_save_file_params', $datas);
        return true;
    }

    /**
     * Update a file
     *
     * @param integer $id    Post id
     * @param array   $datas Post data
     *
     * @return boolean
     */
    public function updateFile($id, $datas)
    {
        $my_post = array(
            'ID'            => $id,
            'post_title'    => $datas['title'],
            'post_modified' => date('Y-m-d H:i:s')
        );

        // Update the post into the database
        wp_update_post($my_post);
        $metadata = get_post_meta($id, '_wpfd_file_metadata', true);
        foreach ($datas as $key => $value) {
            if (isset($metadata[$key])) {
                $metadata[$key] = $value;
            }
        }
        update_post_meta($id, '_wpfd_file_metadata', $metadata);

        return true;
    }

    /**
     * Delete file
     *
     * @param integer $id Post id
     *
     * @return boolean
     */
    public function delete($id)
    {
        if (!wp_delete_post($id, true)) {
            return false;
        }

        return true;
    }

    /**
     * Delete file version
     *
     * @param integer $vid Meta id
     *
     * @return boolean True on successful delete, false on failure.
     */
    public function deleteVersion($vid)
    {
        $result = delete_metadata_by_mid('post', $vid);

        return $result;
    }

    /**
     * Delete old versions
     *
     * @param integer $fileId File id
     * @param integer $catId  Category id
     * @param integer $keep   Max keep versions
     *
     * @return void
     */
    public function deleteOldVersions($fileId, $catId, $keep = 10)
    {
        global $wpdb;

        if ((int) $keep > 100) {
            $keep = 100;
        }
        // Get all versions of file
        $results  = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM ' . $wpdb->postmeta . ' WHERE post_id = %d AND meta_key = %s ORDER BY meta_id DESC',
                (int) $fileId,
                '_wpfd_file_versions'
            ),
            ARRAY_A
        );
        $versions = array();
        if (!empty($results)) {
            foreach ($results as $result) {
                $version            = unserialize($result['meta_value']);
                $version['meta_id'] = $result['meta_id'];
                $versions[]         = $version;
            }
        }

        $totalVersions = count($versions);

        if (!empty($versions) && $totalVersions > $keep) {
            // Sort version by date
            usort($versions, array('WpfdModelFile', 'cmpCreatedTime'));

            $keepVersions = array_slice($versions, 0, $keep);

            foreach ($versions as $version) {
                if (in_array($version, $keepVersions)) {
                    continue;
                }
                // Delete in database
                $result   = delete_metadata_by_mid('post', $version['meta_id']);

                // Delete file on disk
                $file_dir = WpfdBase::getFilesPath($catId) . '/' . $version['file'];
                if ($result) {
                    if (file_exists($file_dir)) {
                        unlink($file_dir);
                    }
                }
            }
        }
    }

    /**
     * Compare by create_time
     *
     * @param array $a Array A
     * @param array $b Array B
     *
     * @return integer
     */
    private function cmpCreatedTime($a, $b)
    {
        return (date_create_from_format('Y-m-d H:i:s', $a['created_time']) > date_create_from_format('Y-m-d H:i:s', $b['created_time'])) ? -1 : 1;
    }

    /**
     * Add version for file
     *
     * @param array $file File array
     *
     * @return void
     */
    public function addVersion($file)
    {
        $metadata                 = array();
        $metadata['ext']          = $file['ext'];
        $metadata['size']         = $file['size'];
        $metadata['version']      = $file['version'];
        $metadata['file']         = $file['file'];
        $metadata['remote_url']   = isset($file['remote_url']) ? $file['remote_url'] : '';
        $metadata['created_time'] = date('Y-m-d H:i:s');

        add_post_meta($file['ID'], '_wpfd_file_versions', $metadata);
    }

    /**
     * Get version file
     *
     * @param integer $vid Meta id
     *
     * @return boolean
     */
    public function getVersion($vid)
    {

        $metaData = get_metadata_by_mid('post', $vid);
        $version  = false;
        if ($metaData !== null) {
            $version = $metaData->meta_value;
        }

        return $version;
    }

    /**
     * Get all versions of file
     *
     * @param integer $file_id    Post id
     * @param integer $idCategory Category id
     *
     * @return array
     */
    public function getVersions($file_id, $idCategory)
    {
        global $wpdb;

        $results  = $wpdb->get_results($wpdb->prepare(
            'SELECT * FROM ' . $wpdb->postmeta . ' WHERE post_id = %d AND meta_key = %s ORDER BY meta_id DESC',
            (int) $file_id,
            '_wpfd_file_versions'
        ), ARRAY_A);
        $versions = array();
        if (!empty($results)) {
            foreach ($results as $result) {
                $version            = unserialize($result['meta_value']);
                $version['meta_id'] = $result['meta_id'];
                $version['catid']   = $idCategory;
                $versions[]         = $version;
            }
        }

        return $versions;
    }
}
