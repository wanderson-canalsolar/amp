<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Model;

defined('ABSPATH') || die();

/**
 * Class WpfdModelNotification
 */
class WpfdModelNotification extends Model
{

    /**
     * Save notifications params
     *
     * @param array $datas Datas
     *
     * @return boolean
     */
    public function saveNotifications($datas)
    {
        update_option('_wpfd_notifications', $datas);

        return true;
    }


    /**
     * Save mail option params
     *
     * @param array $datas Datas
     *
     * @return boolean
     */
    public function saveMailOption($datas)
    {
        update_option('_wpfd_mail_option', $datas);

        return true;
    }

    /**
     * Get Notification config
     *
     * @return array
     */
    public function getNotificationsConfig()
    {
        $defaultConfig = array(
            'notify_sender_name'            => 'WP File Download',
            'notify_sender_email'           => '',
            'notify_category_owner'         => '',
            'notify_file_owner'             => '',
            'notify_super_admin'            => '',
            'notify_add_event'              => 1,
            'notify_add_event_subject'      => 'A new file has been added',
            'notify_add_event_email'        => '',
            'notify_add_event_editor'       => '',
            'notify_edit_event'             => 1,
            'notify_edit_event_subject'     => 'A new file has been edited',
            'notify_edit_event_email'       => '',
            'notify_edit_event_editor'      => '',
            'notify_delete_event'           => 1,
            'notify_delete_event_subject'   => 'A new file has been delete',
            'notify_delete_event_email'     => '',
            'notify_delete_event_editor'    => '',
            'notify_download_event'         => 0,
            'notify_download_event_subject' => 'A new file has been downloaded',
            'notify_download_event_email'   => '',
            'notify_download_event_editor'  => ''
        );

        $config = get_option('_wpfd_notifications', $defaultConfig);
        if ($config['notify_add_event_editor']) {
            $config['notify_add_event_editor'] = str_replace('\\', '', $config['notify_add_event_editor']);
        }
        if ($config['notify_edit_event_editor']) {
            $config['notify_edit_event_editor'] = str_replace('\\', '', $config['notify_edit_event_editor']);
        }
        if ($config['notify_delete_event_editor']) {
            $config['notify_delete_event_editor'] = str_replace('\\', '', $config['notify_delete_event_editor']);
        }
        if ($config['notify_download_event_editor']) {
            $config['notify_download_event_editor'] = str_replace('\\', '', $config['notify_download_event_editor']);
        }

        return (array) $config;
    }

    /**
     * Get mail option config
     *
     * @return array
     */
    public function getMailOptionConfig()
    {
        $defaultConfig = array(
            'mail_option_mailer_smtp'    => 'smtp',
            'mail_option_enable_smtp'    => 0,
            'mail_option_smtp_host'      => 'smtp.gmail.com',
            'mail_option_smtp_port'      => 25,
            'mail_option_encription'     => 'ssl',
            'mail_option_authentication' => 1,
            'mail_option_username'       => '',
            'mail_option_password'       => ''
        );

        $config = get_option('_wpfd_mail_option', $defaultConfig);

        return (array) $config;
    }
}
