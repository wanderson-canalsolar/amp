<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

namespace Joomunited\WP_File_Download\Admin\Fields;

use Joomunited\WPFramework\v1_0_5\Field;
use Joomunited\WPFramework\v1_0_5\Application;

defined('ABSPATH') || die();

/**
 * Class Editor
 */
class Editor extends Field
{
    /**
     * Add field Editor
     *
     * @param array $field Fields
     * @param array $data  Data
     *
     * @return string
     */
    public function getfield($field, $data)
    {
        $attributes       = $field['@attributes'];
        $html             = '';
        $html             .= '<div class="ju-settings-option full-width">';
        $html             .= '';
        $html             .= '<div class="controls-editor">';
        $advanced_buttons = 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,'
                            . 'justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator';
        $settings         = array(
            'textarea_name' => $attributes['name'],
            //'quicktags'     => array( 'buttons' => 'em,strong,link' ),
            'media_buttons' => false,
            'editor_height' => 425,
            'tinymce'       => array(
                'theme_advanced_buttons1' => $advanced_buttons,
                'theme_advanced_buttons2' => '',
            ),
            'editor_css'    => '<style>#wp-description-editor-tools {display:none}</style>'
        );

        ob_start();
        $value_editor = $attributes['value'];
        if (!$value_editor) {
            $value_editor = $this->renderValue($attributes['event']);
        }
        wp_editor(htmlspecialchars_decode($value_editor), $attributes['name'], $settings);
        $html .= ob_get_clean();
        if (!empty($attributes['help']) && $attributes['help'] !== '') {
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Possibility to translate by our deployment script
            $html .= '<p class="help - block">' . __($attributes['help'], 'wpfd') . '</p>';
        }

        $msg_title = 'Tag {receiver} = The user that receive the notification Email . Tag {username} =';
        $msg_title .= 'The username who downloaded file(if user logged in then it’s replaced by user display';
        $msg_title .= 'name(first name + last name) , if user is not logged in then it’s replaced by';
        $msg_title .= '& quot;guest & quot;';
        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Keep this
        $html      .= '<p title="' . esc_html__($msg_title, 'wpfd') . '">
                    Support tag: {category}, {receiver}, {username}, {website_url}, {file_name}
                 </p>';
        $html      .= '</div></div>';

        return $html;
    }

    /**
     * Render html msg
     *
     * @param boolean $valueEvent IsValueEvent
     *
     * @return string
     */
    public function renderValue($valueEvent)
    {
        $app   = Application::getInstance('Wpfd');
        $value = '';
        if (!$valueEvent) {
            return $value;
        }

        $value = '<table border="0" width="100 % " cellspacing="0" cellpadding="0" bgcolor="#fafafa">
        <tbody>
            <tr>
              <td>
                <table border = "0" width = "600" cellspacing = "0" cellpadding = "0" align = "center">
                  <tbody>
                    <tr>
                      <td>
                        <table class="content" style = "max-width: 700px; width: 100%;" border = "0" cellspacing = "0" 
                        cellpadding = "0" align = "center" bgcolor = "#ffffff">
                          <tbody>
                            <tr>
                              <td class="header" style = "padding: 40px 30px 20px 30px;" bgcolor = "#64B7EB" >
                                <table border = "0" width = "70" cellspacing = "0" cellpadding = "0" align = "left" 
                                style = "width:70px !important;" >
                                  <tbody>
                                    <tr>
                                      <td style = "padding: 0 20px 20px 0;" height = "70">
                                      <img class="fix" style = "height: auto;" 
                                      src = "' . $app->getBaseUrl() . '/app/admin/assets/images/icon-download.png"
                                       alt = "" width = "70" height = "70" border = "0" />
                                       </td>
                                    </tr>
                                  </tbody>
                                </table>
                                <table border = "0" width = "425" cellspacing = "0" cellpadding = "0" align = "left" 
                                style = "width:425px !important;">
                                  <tbody>
                                    <tr>
                                      <td>
                                        <table class="col425" style = "max-width: 525px; width: 100%;" border = "0" 
                                        cellspacing = "0" cellpadding = "0" align = "left" >
                                          <tbody>
                                            <tr>
                                              <td height = "70" >
                                                <table border = "0" width = "100%" cellspacing = "0" cellpadding = "0" >
                                                  <tbody>
                                                    <tr>
                                                      <td class="subhead" 
                                                      style = "color: #ffffff; font-family: sans-serif; font-size: 15px; letter-spacing: 5px; padding: 0 0 0 3px;" >
                                                       File manager 
                                                       </td>
                                                    </tr>
                                                    <tr>
                                                      <td class="h1" 
                                                      style = "color: #fff; font-family: sans-serif; font-size: 33px; font-weight: bold; line-height: 38px; padding: 5px 0 0 0;" >
                                                       A file has been ' . $valueEvent . ' 
                                                       </td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                              </td>
                                            </tr>
                                          </tbody>
                                        </table>
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>
                              </td>
                            </tr>
                            <tr>
                              <td class="innerpadding borderbottom"
                               style = "border-bottom: 1px solid #f2eeed; padding: 30px 30px 30px 30px;" >
                                <table border = "0" width = "100%" cellspacing = "0" cellpadding = "0" >
                                  <tbody>
                                    <tr>
                                      <td class="h2" 
                                      style = "color: #153643; font-family: sans-serif; font-size: 24px; font-weight: bold; line-height: 28px; padding: 0 0 15px 0;" >
                                       Hello {receiver},
                                       </td>
                                    </tr>
                                    <tr>
                                      <td class="bodycopy" 
                                      style = "color: #153643; font-family: sans-serif; font-size: 16px; line-height: 22px;" > 
                                      You receive this notification because a file has been ' . $valueEvent . ' on {website_url}
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>
                              </td>
                            </tr>
                            <tr>
                              <td class="innerpadding borderbottom"
                               style = "border-bottom: 1px solid #f2eeed; padding: 30px 30px 30px 30px;" >
                                <table class="col380" style = "width: 100%;" border = "0" cellspacing = "0"
                                 cellpadding = "0" align = "left" >
                                  <tbody>
                                    <tr>
                                      <td >
                                        <table border = "0" width = "100%" cellspacing = "0" cellpadding = "0" >
                                          <tbody>
                                            <tr>
                                              <td class="bodycopy" 
                                              style = "color: #153643; font-family: sans-serif; font-size: 16px; line-height: 22px;" >
                                               A file has been ' . $valueEvent . ' by a user who owns it or an administrator, you can visit the website to get more information about it .
                                               </td>
                                            </tr>
                                            <tr>
                                              <td style = "padding: 20px 0 0 0;" >
                                                <table class="buttonwrapper" border = "0" cellspacing = "0"
                                                 cellpadding = "0" bgcolor = "#64B7EB" >
                                                  <tbody>
                                                    <tr>
                                                      <td class="button" style = "font-family: sans-serif; font-size: 18px; font-weight: bold; padding: 0 30px 0 30px; text-align: center;" height = "45" >
                                                      <a style = "color: #ffffff; text-decoration: none;"
                                                       href = "{website_url}"> 
                                                       Visit the website 
                                                       </a>
                                                       </td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                              </td>
                                            </tr>
                                          </tbody>
                                        </table>
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td class="footer" style = "padding: 20px 30px 15px 30px;" bgcolor = "#44525f" >
                        <table border = "0" width = "100%" cellspacing = "0" cellpadding = "0" >
                          <tbody>
                            <tr>
                              <td class="footercopy" style = "color: #ffffff; font-family: sans-serif; font-size: 12px;" align = "center" >
                              You receive this Email because an administrator has registered it in the file manager notification system . Please get in touch with an administrator to manage your email preference .
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
          </tbody>
        </table>';

        return $value;
    }
}
