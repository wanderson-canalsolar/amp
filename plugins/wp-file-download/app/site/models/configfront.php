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
 * Class WpfdModelConfigfront
 */
class WpfdModelConfigfront extends Model
{
    /**
     * Get global configuration
     *
     * @return array
     */
    public function getGlobalConfig()
    {
        $allowedext_str                           = '7z,ace,bz2,dmg,gz,rar,tgz,zip,csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,'
                                                    . 'ppt,pptx,rtf,tex,txt,xls,xlsx,xml,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,aiff,alac,amr,au,'
                                                    . 'cdda,flac,m3u,m4a,m4p,mid,mp3,mp4,mpa,ogg,pac,ra,wav,wma,3gp,asf,avi,flv,m4v,mkv,mov,mpeg,mpg,rm,swf,'
                                                    . 'vob,wmv,css,img';
        $extension_viewer                         = 'png,jpg,pdf,ppt,pptx,doc,docx,xls,xlsx,dxf,ps,eps,xps,psd,tif,tiff,bmp,svg,pages,ai,dxf,ttf,txt,mp3,mp4';
        $defaultConfig                            = array('allowedext' => $allowedext_str);
        $defaultConfig['maxinputfile']            = 10;
        $defaultConfig['deletefiles']             = 0;
        $defaultConfig['catparameters']           = 1;
        $defaultConfig['defaultthemepercategory'] = 'default';
        $defaultConfig['date_format']             = 'd-m-Y';
        $defaultConfig['use_google_viewer']       = 'lightbox';
        $defaultConfig['extension_viewer']        = $extension_viewer;
        $defaultConfig['uri']                     = 'download';
        $defaultConfig['rmdownloadext']           = 0;
        $defaultConfig['ga_download_tracking']    = 0;
        $defaultConfig['plain_text_search']       = 0;
        $defaultConfig['useeditor']               = 0;
        $defaultConfig['restrictfile']            = 0;
        $defaultConfig['enablewpfd']              = 0;
        $defaultConfig['shortcodecat']            = 1;
        $defaultConfig['paginationnunber']        = 100;
        $defaultConfig['open_pdf_in']             = 0;
        $defaultConfig['custom_icon']             = 1;
        $defaultConfig['download_category']       = 0;
        $defaultConfig['download_selected']       = 0;
        $defaultConfig['track_user_download']     = 0;
        $defaultConfig['show_empty_folder']       = 0;
        $defaultConfig['icon_set']                = 'default';
        $defaultConfig['not_authorized_page']     = '0';
        $defaultConfig['auto_generate_preview']   = 0;
        $defaultConfig['secure_preview_file']     = 0;
        $defaultConfig['guest_download_files']    = 1;
        $defaultConfig['guest_preview_files']     = 1;

        $config                                   = get_option('_wpfd_global_config', $defaultConfig);
        $config                                   = array_merge($defaultConfig, $config);
        return (array)$config;
    }

    /**
     * Get config of a theme
     *
     * @param string $theme_name Theme name
     *
     * @return mixed
     */
    public function getConfig($theme_name = '')
    {

        if ($theme_name !== '') {
            $theme = $theme_name;
        } else {
            $theme = get_option('_wpfd_theme', 'default');
        }
        $default_config = '{"marginleft":"10","marginright":"10", "margintop":"10", "marginbottom":"10",';
        $default_config .= '"showsize":"1","showtitle":"1","croptitle":"0","showdescription":"1","showversion":"1",';
        $default_config .= '"showhits":"1","showdownload":"1","bgdownloadlink":"#76bc58",';
        $default_config .= '"colordownloadlink":"#ffffff","showdateadd":"1","showdatemodified":"0",';
        $default_config .= '"showsubcategories":"1","showcategorytitle":"1","showbreadcrumb":"1","showfoldertree":"0"}';

        $ggd_config = '{"ggd_marginleft":"10", "ggd_marginright":"10", "ggd_margintop":"10", "ggd_marginbottom":"10",';
        $ggd_config .= '"ggd_croptitle":"0", "ggd_showsize":"1", "ggd_showtitle":"1", "ggd_showdescription":"1",';
        $ggd_config .= '"ggd_showversion":"1", "ggd_showhits":"1", "ggd_showdownload":"1",';
        $ggd_config .= '"ggd_bgdownloadlink":"#76bc58", "ggd_colordownloadlink":"#ffffff", "ggd_showdateadd":"1",';
        $ggd_config .= '"ggd_showdatemodified":"0", "ggd_showsubcategories":"1", "ggd_showcategorytitle":"1",';
        $ggd_config .= '"ggd_showbreadcrumb":"1", "ggd_showfoldertree":"0", "ggd_download_popup":"1"}';

        $table_config = '{"table_stylingmenu":"1","table_showsize":"1","table_showtitle":"1",';
        $table_config .= '"table_showdescription":"1","table_showversion":"1","table_showhits":"1",';
        $table_config .= '"table_showdownload":"1","table_croptitle":"0","table_bgdownloadlink":"#76bc58",';
        $table_config .= '"table_colordownloadlink":"#ffffff","table_showdateadd":"1","table_showdatemodified":"0",';
        $table_config .= '"table_showsubcategories":"1","table_showcategorytitle":"1","table_showbreadcrumb":"1",';
        $table_config .= '"table_showfoldertree":"0"}';

        $tree_config = '{"tree_showsize":"1", "tree_croptitle":"0",';
        $tree_config .= '"tree_showtitle":"1", "tree_showdescription":"1", "tree_showversion":"1",';
        $tree_config .= ' "tree_showhits":"1","tree_showdownload":"1", "tree_bgdownloadlink":"#76bc58",';
        $tree_config .= '"tree_colordownloadlink":"#ffffff","tree_showdateadd":"1", "tree_showdatemodified":"0",';
        $tree_config .= '"tree_showsubcategories":"1", "tree_showcategorytitle":"1", "tree_download_popup":"1"}';

        $custom_config = '{"marginleft":"10","marginright":"10", "margintop":"10", "marginbottom":"10",';
        $custom_config .= '"showsize":"1","showtitle":"1","croptitle":"0","showdescription":"1","showversion":"1",';
        $custom_config .= '"showhits":"1","showdownload":"1","bgdownloadlink":"#76bc58",';
        $custom_config .= '"colordownloadlink":"#ffffff","showdateadd":"1","showdatemodified":"0",';
        $custom_config .= '"showsubcategories":"1","showcategorytitle":"1","showbreadcrumb":"1","showfoldertree":"0",';
        $custom_config .= '"' . $theme . '_showbreadcrumb":"1","' . $theme . '_showfoldertree":"0",';
        $custom_config .= '"' . $theme . '_show' . $theme . 'border":"1","' . $theme . '_showsize":"1","' . $theme . '_croptitle":"0",';
        $custom_config .= '"' . $theme . '_showtitle":"1","' . $theme . '_showdescription":"1","' . $theme . '_showversion":"1","' . $theme . '_showhits":"1",';
        $custom_config .= '"' . $theme . '_showdownload":"1","' . $theme . '_bgdownloadlink":"#76bc58","' . $theme . '_colordownloadlink":"#ffffff",';
        $custom_config .= '"' . $theme . '_showdateadd":"1","' . $theme . '_showdatemodified":"0","' . $theme . '_showsubcategories":"1",';
        $custom_config .= '"' . $theme . '_showcategorytitle":"1","' . $theme . '_download_popup":"1", "' . $theme . '_styling":"1", "' . $theme . '_stylingmenu":"1",';
        $custom_config .= '"' . $theme . '_marginleft":"10","' . $theme . '_marginright":"10", "' . $theme . '_margintop":"10", "' . $theme . '_marginbottom":"10"}';

        $defaults = array(
            'default' => $default_config,
            'ggd'     => $ggd_config,
            'table'   => $table_config,
            'tree'    => $tree_config
        );
        $default_params = isset($default[$theme]) ? $defaults[$theme] : $custom_config;
        $theme_params = get_option('_wpfd_' . $theme . '_config', $default_params);
        if (is_string($theme_params)) {
            $theme_params = json_decode($theme_params, true);
        }
        return $theme_params;
    }

    /**
     * Get config for single file
     *
     * @return array
     */
    public function getFileConfig()
    {
        $defaultConfig = array(
            'singlebg'        => '#444444',
            'singlehover'     => '#888888',
            'singlefontcolor' => '#ffffff',
        );
        $config = get_option('_wpfd_global_file_config', $defaultConfig);
        return (array)$config;
    }

    /**
     * Get search config
     *
     * @return array
     */
    public function getSearchConfig()
    {
        $defaultConfig = array(
            'search_page'           => (int) get_option('_wpfd_search_page_id'),
            'plain_text_search'     => 0,
            'cat_filter'            => 1,
            'tag_filter'            => 0,
            'display_tag'           => 'searchbox',
            'create_filter'         => 1,
            'update_filter'         => 1,
            'file_per_page'         => 15,
            'include_global_search' => 1,
            'shortcode'             => '[wpfd_search]'
        );
        $config = get_option('_wpfd_global_search_config', $defaultConfig);
        return (array)$config;
    }
    /**
     * List all themes inside themes folder
     *
     * @return array
     */
    public function getThemes()
    {
        $app       = Application::getInstance('Wpfd');
        $results   = array();
        $path_wpfd = $app->getPath() . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'themes';
        $path_wpfd .= DIRECTORY_SEPARATOR . 'wpfd-*';
        foreach (glob($path_wpfd, GLOB_ONLYDIR) as $rep) {
            $dir       = explode(DIRECTORY_SEPARATOR, $rep);
            $results[] = substr($dir[count($dir) - 1], 5);
        }
        $dirs         = wp_upload_dir();
        $clonedThemes = $dirs['basedir'] . '/wpfd-themes/';

        if (file_exists($clonedThemes)) {
            foreach (glob($clonedThemes . 'wpfd-*', GLOB_ONLYDIR) as $rep) {
                $results[] = str_replace('wpfd-', '', basename($rep));
            }
        }
        unset($clonedThemes);
        // Additional themes path on wp-content
        $clonedThemes = WP_CONTENT_DIR . DIRECTORY_SEPARATOR .'wp-file-download' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR;
        if (file_exists($clonedThemes)) {
            foreach (glob($clonedThemes . 'wpfd-*', GLOB_ONLYDIR) as $rep) {
                $results[] = str_replace('wpfd-', '', basename($rep));
            }
        }

        return $results;
    }

    /**
     * Get allowed ext for uploading file
     *
     * @return array
     */
    public function getAllowedExt()
    {
        $params = $this->getGlobalConfig();
        $allowedExtensions = explode(',', $params['allowedext']);
        return array_map('trim', $allowedExtensions);
    }
}
