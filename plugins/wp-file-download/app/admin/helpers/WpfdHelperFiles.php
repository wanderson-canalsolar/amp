<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Model;
use Joomunited\WPFramework\v1_0_5\Application;

defined('ABSPATH') || die();


/**
 * Class WpfdHelperFiles
 */
class WpfdHelperFiles
{
    /**
     * Convert bytes to size
     *
     * @param integer $bytes     Byte
     * @param integer $precision Decimal fraction
     *
     * @return string
     */
    public static function bytesToSize($bytes, $precision = 2)
    {
        $sz     = self::getSupportFileMeasure();
        $factor = floor((strlen($bytes) - 1) / 3);
        if ((int) $factor === -1) {
            return esc_html__('N/A', 'wpfd');
        }
        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- This is not problem
        return sprintf('%.' . $precision . 'f', $bytes / pow(1024, $factor)) . ' ' . esc_html__($sz[$factor], 'wpfd');
    }

    /**
     * Method send mail
     *
     * @param string  $action      Action
     * @param WP_USER $receiver    Receiver user
     * @param string  $cat_title   Category title
     * @param string  $website_url Website url
     * @param string  $file_title  File title
     *
     * @return void
     */
    public static function sendMail($action, $receiver, $cat_title, $website_url, $file_title)
    {
        $app          = Application::getInstance('Wpfd');
        $modalNotify  = Model::getInstance('notification');
        $configNotify = $modalNotify->getNotificationsConfig();
        $mail_option  = $modalNotify->getMailOptionConfig();
        $from_name    = $configNotify['notify_sender_name'];
        $from_mail    = $configNotify['notify_sender_email'];
        $iconlink     = '';
        $subject      = '';
        if ($action === 'added' && (int) $configNotify['notify_add_event'] === 1) {
            $iconlink = $app->getBaseUrl() . '/app/admin/assets/images/icon-download.png';
            $subject  = $configNotify['notify_add_event_subject'];
            $message  = $configNotify['notify_add_event_editor'];
        }
        if ($action === 'edited' && (int) $configNotify['notify_edit_event'] === 1) {
            $iconlink = $app->getBaseUrl() . '/app/admin/assets/images/icon-download.png';
            $subject  = $configNotify['notify_edit_event_subject'];
            $message  = $configNotify['notify_edit_event_editor'];
        }
        if ($action === 'delete' && (int) $configNotify['notify_delete_event'] === 1) {
            $iconlink = $app->getBaseUrl() . '/app/admin/assets/images/icon-download.png';
            $subject  = $configNotify['notify_delete_event_subject'];
            $message  = $configNotify['notify_delete_event_editor'];
        }
        if ($action === 'download' && (int) $configNotify['notify_download_event'] === 1) {
            $iconlink = $app->getBaseUrl() . '/app/admin/assets/images/icon-download.png';
            $subject  = $configNotify['notify_download_event_subject'];
            $message  = $configNotify['notify_download_event_editor'];
        }
        if (!isset($message) || empty($message) || empty($from_mail)) {
            return;
        }
        $to[] = $receiver->user_email;
        $user = wp_get_current_user();

        if ($user->ID) {
            $username = trim($user->first_name . ' ' . $user->last_name);
            if (!$username) {
                $username = $user->user_nicename;
            }
        } else {
            $username = 'guest';
        }
        $message   = self::emailBodyReplace(
            $message,
            $cat_title,
            $website_url,
            $file_title,
            $receiver->display_name,
            $iconlink,
            $username
        );
        $headers   = array();
        $headers[] = 'From: ' . $from_name . ' <' . $from_mail . '>';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        if ((int) $mail_option['mail_option_enable_smtp'] === 0) {
            wp_mail($to, $subject, $message, $headers);
        } else {
            self::wpmsSmtpSendMail($to, $subject, $message, $mail_option, $configNotify);
        }
    }

    /**
     * Email body replace
     *
     * @param string $email_body  Email body
     * @param string $cat_title   Category title
     * @param string $website_url Website url
     * @param string $file_title  File title
     * @param string $receiver    Receiver name
     * @param string $iconLink    Icon link
     * @param string $username    Current user name
     *
     * @return mixed
     */
    public static function emailBodyReplace(
        $email_body,
        $cat_title,
        $website_url,
        $file_title,
        $receiver,
        $iconLink,
        $username
    ) {
        $app        = Application::getInstance('Wpfd');
        $email_body = str_replace(
            $app->getBaseUrl() . '/app/admin/assets/images/icon-download.png',
            $iconLink,
            $email_body
        );
        $email_body = str_replace('{category}', $cat_title, $email_body);
        $email_body = str_replace('{website_url}', $website_url, $email_body);
        $email_body = str_replace('{file_name}', $file_title, $email_body);
        $email_body = str_replace('{username}', $username, $email_body);
        $email_body = str_replace('{receiver}', $receiver, $email_body);

        return $email_body;
    }


    /**
     * Do send email
     *
     * @param array  $to_email     To email array
     * @param string $subject      Subject
     * @param string $message      Message
     * @param array  $mail_option  Email options
     * @param array  $configNotify Notification settings
     *
     * @return void
     */
    private static function wpmsSmtpSendMail($to_email, $subject, $message, $mail_option, $configNotify)
    {
        $phpMailerClassPath = ABSPATH . WPINC . '/PHPMailer';
        if (file_exists($phpMailerClassPath)) {
            // phpcs:disable PHPCompatibility.Constants.NewMagicClassConstant.Found -- We use from php 5.6
            if (!class_exists('PHPMailer')) {
                require_once $phpMailerClassPath . '/PHPMailer.php';
                class_alias(PHPMailer\PHPMailer\PHPMailer::class, 'PHPMailer');
            }
            if (!class_exists('SMTP')) {
                require_once $phpMailerClassPath . '/SMTP.php';
                class_alias(PHPMailer\PHPMailer\SMTP::class, 'SMTP');
            }
            if (!class_exists('phpmailerException')) {
                require_once $phpMailerClassPath . '/Exception.php';
                class_alias(PHPMailer\PHPMailer\Exception::class, 'phpmailerException');
            }
            // phpcs:enable
        } else {
            require_once(ABSPATH . WPINC . '/class-phpmailer.php');
        }

        $mail          = new PHPMailer();
        $charset       = get_bloginfo('charset');
        $mail->CharSet = $charset;
        $from_name     = $configNotify['notify_sender_name'];
        $from_email    = $configNotify['notify_sender_email'];
        $mail->IsSMTP();
        /* If using smtp auth, set the username & password */
        if (1 === (int) $mail_option['mail_option_authentication']) {
            $mail->SMTPAuth = true;
            $mail->Username = $mail_option['mail_option_username'];
            $mail->Password = $mail_option['mail_option_password'];
        }
        /* Set the SMTPSecure value, if set to none, leave this blank */
        if ($mail_option['mail_option_encription'] !== 'none') {
            $mail->SMTPSecure = $mail_option['mail_option_encription'];
        }
        /* PHPMailer 5.2.10 introduced this option. However,
         this might cause issues if the server is advertising TLS with an invalid certificate. */
        $mail->SMTPAutoTLS = false;

        if (isset($mail_option['mail_option_smtp_insecure_ssl'])
            && $mail_option['mail_option_smtp_insecure_ssl'] !== false) {
            // Insecure SSL option enabled
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                )
            );
        }

        /* Set the other options */
        $mail->Host = $mail_option['mail_option_smtp_host'];
        $mail->Port = (int) $mail_option['mail_option_smtp_port'];
        // todo: Try catch here with logging email system
        try {
            $mail->SetFrom($from_email, $from_name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->MsgHTML($message);
            foreach ($to_email as $emailto) {
                $mail->AddAddress($emailto);
            }
            $mail->SMTPDebug = 0;
            /* Send mail and return result */
            $mail->Send();
            $mail->ClearAddresses();
            $mail->ClearAllRecipients();
        } catch (phpmailerException $e) {
            printf(esc_html__('Error: %s', 'wpfd'), esc_html($e->getMessage()));
        }
    }

    /**
     * Get list super admin
     *
     * @return array
     */
    public static function getListIDSuperAdmin()
    {
        $list             = array();
        $args             = array();
        $args['role__in'] = array('super admin', 'administrator');
        $super_users      = get_users($args);
        foreach ($super_users as $key => $value) {
            array_push($list, $value->data->ID);
        }

        return $list;
    }

    /**
     * Get support file measure list
     *
     * @return array
     */
    public static function getSupportFileMeasure()
    {
        return array(
            esc_html__('B', 'wpfd'),
            esc_html__('KB', 'wpfd'),
            esc_html__('MB', 'wpfd'),
            esc_html__('GB', 'wpfd'),
            esc_html__('TB', 'wpfd'),
            esc_html__('PB', 'wpfd')
        );
    }
}
