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
use Joomunited\WPFramework\v1_0_5\Model;

defined('ABSPATH') || die();

/**
 * Class Desc
 */
class Desc extends Field
{

    /**
     * Display field description
     *
     * @param array $field Fields
     * @param array $data  Data
     *
     * @return string
     */
    public function getfield($field, $data)
    {
        $attributes  = $field['@attributes'];
        $modelConfig = Model::getInstance('config');
        $config      = $modelConfig->getConfig();
        $html        = '';
        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Possibility to translate by our deployment script
        $tooltip     = isset($attributes['tooltip']) ? __($attributes['tooltip'], 'wpfd') : '';
        $html        .= '<div class="control-group">';
        if (!empty($attributes['label']) && $attributes['label'] !== '' &&
            !empty($attributes['name']) && $attributes['name'] !== '') {
            $html .= '<label title="' . $tooltip . '" class="control-label" for="' . $attributes['name'] . '">';
            // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Dynamic translate
            $html .= esc_html__($attributes['label'], 'wpfd') . '</label>';
        }
        $html .= '<div class="controls">';
        if ((int) $config['useeditor'] === 0) {
            $html .= '<textarea name="' . $attributes['name'] . '" id="' . $attributes['name'] . '"
                        class="' . $attributes['class'] . '">';
            $html .= htmlspecialchars($attributes['value'], ENT_COMPAT, 'UTF-8') . '</textarea>';
        } else {
            $advanced_buttons1 = 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,'
                                 . 'justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator';
            $settings          = array(
                'textarea_name' => $attributes['name'],
                //'quicktags'     => array( 'buttons' => 'em,strong,link' ),
                'media_buttons' => false,
                'tinymce'       => array(
                    'plugins'                 => 'wordpress,wplink',
                    'theme_advanced_buttons1' => $advanced_buttons1,
                    'theme_advanced_buttons2' => '',
                ),
                'editor_css'    => '<style>#wp-description-editor-tools {display:none}</style>'
            );
            ob_start();
            wp_editor(htmlspecialchars_decode($attributes['value']), $attributes['name'], $settings);
            $html .= ob_get_clean();
            if (!empty($attributes['help']) && $attributes['help'] !== '') {
                // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Possibility to translate by our deployment script
                $html .= '<p class="help-block">' . __($attributes['help'], 'wpfd') . '</p>';
            }
        }


        $html .= '</div></div>';


        if ((int) $config['useeditor'] === 1) {
            ob_start();
            global $wp_version, $tinymce_version;

            $version    = 'ver=' . $tinymce_version;
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped,WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Don't need escape
            $mce_suffix = false !== strpos($wp_version, '-src') ? '' : '.min';
            $baseurl    = includes_url('js/tinymce');
            $suffix     = SCRIPT_DEBUG ? '' : '.min';
            echo "<script type='text/javascript' src='" . $baseurl . '/tinymce' . $mce_suffix . '.js?' . $version . "'></script>\n";
            echo "<script type='text/javascript' 
                    src='" . $baseurl . '/plugins/compat3x/plugin' . $suffix . '.js?' . $version . "'></script>\n";
            ?>
            <?php
            $toolbar1 = 'insertfile undo redo | styleselect | bold italic underline strikethrough |'
                        . ' alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image';
            ?>
            <script type="text/javascript">
                (function () {
                    tinymce.init({
                        selector: "#<?php echo $attributes['name'];?>",
                        theme: 'modern',
                        skin: 'lightgray',
                        height: 150,
                        toolbar1: '<?php echo $toolbar1;?>',
                        menubar: false,
                        plugins: "wordpress,wplink",
                        formats: {
                            alignleft: [
                                {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign: 'left'}},
                                {selector: 'img,table,dl.wp-caption', classes: 'alignleft'}
                            ],
                            aligncenter: [
                                {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign: 'center'}},
                                {selector: 'img,table,dl.wp-caption', classes: 'aligncenter'}
                            ],
                            alignright: [
                                {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign: 'right'}},
                                {selector: 'img,table,dl.wp-caption', classes: 'alignright'}
                            ],
                            strikethrough: {inline: 'del'}
                        },
                        setup: function (editor) {
                            editor.on('change', function () {
                                tinymce.triggerSave();
                            });
                        }
                    });

                }());
            </script>
            <?php
            // phpcs:enable
            $html .= ob_get_clean();
        }

        return $html;
    }
}
