<?php

/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

defined('ABSPATH') || die();

$content = '';

if (!empty($this->versions)) {
    $content .= '<table>';
    foreach ($this->versions as $meta_id => $file) {
        $version = '1';
        $content .= '<tr>';
        $content .= '<td><a href="admin-ajax.php?action=wpfd&task=file.download&version=' . (int) $version . '&id=';
        $content .= (string) $this->file_id . '&vid=' . (string) $file['meta_id'] . '&catid=' . (int) $file['catid'] . '" >';
        $content .= date('Y M d', strtotime($file['created_time'])) . ' ';
        $content .= '</a></td>';
        $content .= '<td>' . WpfdHelperFiles::bytesToSize((int) $file['size']) . '</td>';
        $content .= '<td><a data-id="' . (string) $this->file_id . '" data-vid="' . (string) $file['meta_id'] . '" data-catid="';
        $content .= (int) $file['catid'] . '" href="#" class="restore"><i class="icon-restore"></i></a>';
        if (apply_filters('wpfdAddonCategoryFrom', $file['catid']) === 'dropbox') {
            $content .= '';
        } else {
            $content .= '<a data-id="' . (string) $this->file_id . '" data-vid="' . (string) $file['meta_id'] . '" data-catid="';
            $content .= (int) $file['catid'] . '" href="#" class="trash"><i class="icon-trash"></i></a></td>';
        }
        $content .= '</tr>';
    }
    $content .= '</table>';
}
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- cast above
echo $content;
