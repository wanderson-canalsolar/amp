<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\View;
use Joomunited\WPFramework\v1_0_5\Utilities;
use Joomunited\WPFramework\v1_0_5\Form;

defined('ABSPATH') || die();

/**
 * Class WpfdViewCategory
 */
class WpfdViewCategory extends View
{
    /**
     * Current theme name
     *
     * @var string
     */
    public $prefix;
    /**
     * Render category
     *
     * @param null $tpl Template name
     *
     * @return void
     */
    public function render($tpl = null)
    {
        $id_category = Utilities::getInt('id');
        if (empty($id_category)) {
            echo '';
            wp_die();
        }
        Application::getInstance('Wpfd');
        $modelCat         = $this->getModel('category');
        $this->category   = $modelCat->getCategory($id_category);
        if (!$this->category) {
            echo '';
            wp_die();
        }
        $this->params     = (array) $this->category->params;
        $modelConfig      = $this->getModel('config');
        $this->mainConfig = $modelConfig->getConfig();
        $this->themes     = $modelConfig->getThemes();

        if (Utilities::getInput('onlyTheme', 'GET', 'int')) {
            $newTheme           = Utilities::getInput('theme', 'GET', 'string');
            $this->prefix = $newTheme . '_';
            if ($newTheme === 'default') {
                $this->prefix = '';
            }
            $defaultThemeConfig = $modelConfig->getThemeParams($newTheme);
            $this->params       = wp_parse_args($this->params, $defaultThemeConfig);

            if (WpfdBase::checkExistTheme($newTheme)) {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Print output html
                echo $this->loadTemplate('theme-' . $newTheme);
            } else {
                $dir = trailingslashit(dirname(wpfd_locate_theme($newTheme, 'theme.php')));
                $this->setPath($dir);
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Print output html
                echo $this->loadTemplate('theme-' . $newTheme);
            }
            die();
        }
        $defaultThemeConfig = $modelConfig->getThemeParams($this->params['theme']);
        $this->params       = wp_parse_args($this->params, $defaultThemeConfig);
        $form               = new Form();
        $this->prefix = $this->params['theme'] . '_';
        if ($this->params['theme'] === 'default') {
            $this->prefix = '';
        }
        if ($form->load('category', (array) $this->category->params)) {
            $this->form = $form->render();
        }
        parent::render($tpl);
    }

    /**
     * Helper functions to render a switcher field
     *
     * @param string  $field_name Option name
     * @param string  $label      Field label
     * @param boolean $echo       Print or return
     *
     * @return string
     */
    public function renderSwitcher($field_name, $label, $echo = true)
    {
        $name    = esc_attr('params[' . $this->prefix . $field_name . ']');
        $value   = (isset($this->params[$this->prefix . $field_name]) && ((int) $this->params[$this->prefix . $field_name]) === 1) ? '1' : '0';
        $checked = ($value === '1') ? ' checked' : '';

        $html = '<div class="control-group">';
        $html .= '<label class="control-label" for="ref_' . $name . '">' . esc_html($label) . '</label>
        <div class="ju-switch-button">
            <label class="switch">
                <input type="checkbox" name="ref_' . $name . '" id="ref_' . $name . '" ' . esc_attr($checked) . ' />
                <span class="slider"></span>
            </label>
            <input type="hidden" name="' . $name . '" value="' . esc_attr($value) . '" />
        </div>';
        $html .= '</div>';
        if (true === $echo) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Print output html
            echo $html;
            return '';
        } else {
            return $html;
        }
    }

    /**
     * Helper functions to render a minicolors field
     *
     * @param string  $field_name Option name
     * @param string  $label      Field label
     * @param boolean $echo       Print or return
     *
     * @return string
     */
    public function renderColor($field_name, $label, $echo = true)
    {
        $name  = esc_attr('params[' . $this->prefix . $field_name . ']');
        $value = esc_html($this->params[$this->prefix . $field_name]);

        $html = '<div class="control-group">';
        $html .= '
        <label class="control-label" for="' . $name . '">' . esc_html($label) . '</label>
        <div class="controls">
            <input title="" name="' . $name . '"
                   value="' . $value . '"
                   class="ju-input minicolors wp-color-field" type="text" aria-autocomplete="false">
        </div>';
        $html .= '</div>';

        if (true === $echo) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Print output html
            echo $html;
            return '';
        } else {
            return $html;
        }
    }

    /**
     * Generate input text
     *
     * @param string  $field_name Field name
     * @param string  $label      Field label
     * @param boolean $echo       Print or return
     *
     * @return string
     */
    public function renderText($field_name, $label, $echo = true)
    {
        $name  = esc_attr('params[' . $this->prefix . $field_name . ']');
        $value = esc_html($this->params[$this->prefix . $field_name]);

        $html = '<div class="control-group">';
        $html .= '<label class="control-label" for="' . $name . '">' . esc_html($label) . '</label>
        <div class="controls">
            <input title="" name="' . $name . '" value="' . $value . '"
                   class="ju-input" type="text">
        </div>';
        $html .= '</div>';

        if (true === $echo) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Print output html
            echo $html;
            return '';
        } else {
            return $html;
        }
    }

    /**
     * Generate theme node
     *
     * @param string $name    Theme name
     * @param string $checked Checked state
     *
     * @return string
     */
    public function themeNode($name, $checked = '')
    {
        $html = '<div ref="' . esc_attr($name) . '" class="wpfd-theme ' . esc_attr($checked) . '">';
        $html .= '<p>' . esc_html(ucfirst($name)) . '</p>';
        $html .= '<div class="wpfd-theme-select-icon">';
        $html .= '<img src="' . esc_url($this->themeIcon($name)) . '" title="' . esc_attr($name) . '" />';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate theme icon url
     *
     * @param string $name Theme name
     *
     * @return string
     */
    public function themeIcon($name)
    {
        $iconPath = wpfd_locate_theme($name, 'theme-icon.png');
        if (!file_exists($iconPath)) {
            return WPFD_PLUGIN_URL . 'app/admin/assets/ui/images/custom-theme-icon.png';
        }

        return wpfd_abs_path_to_url($iconPath);
    }
}
