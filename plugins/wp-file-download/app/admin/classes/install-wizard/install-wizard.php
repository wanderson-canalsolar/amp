<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once(WPFD_PLUGIN_DIR_PATH . 'app/admin/classes/install-wizard/handler-wizard.php');
/**
 * Class WpfdInstallWizard
 */
class WpfdInstallWizard
{
    /**
     * Init step params
     *
     * @var array
     */
    protected $steps = array(
            'environment' => array(
                    'name' => 'Environment Check',
                    'view' => 'viewEvironment',
                    'action' => 'saveEvironment'
            ),
            'theme_config' => array(
                    'name' => 'Theme Selection',
                    'view' => 'viewThemeConfig',
                    'action' => 'saveThemeConfig'
            ),
            'theme_settings' => array(
                    'name' => 'Theme Settings',
                    'view' => 'viewThemeSettings',
                    'action' => 'saveThemeSettings',
            )
    );
    /**
     * Init current step params
     *
     * @var array
     */
    protected $current_step = array();
    /**
     * WpfdInstallWizard constructor.
     */
    public function __construct()
    {
        /**
         * Filter check capability of current user to run first install plugin
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpfd_capability = apply_filters('wpfd_user_can', current_user_can('manage_options'), 'first_install_plugin');
        if ($wpfd_capability) {
            add_action('admin_menu', array($this, 'adminMenus'));
            add_action('admin_init', array($this, 'runWizard'));
        }
    }
    /**
     * Add admin menus/screens.
     *
     * @return void
     */
    public function adminMenus()
    {
        add_dashboard_page('', '', 'manage_options', 'wpfd-setup', '');
    }

    /**
     * Execute wizard
     *
     * @return void
     */
    public function runWizard()
    {
        // Update option _wpfd_installed
        update_option('_wpfd_installed', 'true');

        if (!class_exists('WpfdTool')) {
            $path_wpfdtool = WPFD_PLUGIN_DIR_PATH . 'app' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
            $path_wpfdtool .= DIRECTORY_SEPARATOR . 'WpfdTool.php';
            require_once $path_wpfdtool;
        }
        WpfdTool::createCategoryIfNoneExist();
        // phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- View request, no action
        wp_enqueue_style(
            'wpfd_wizard',
            WPFD_PLUGIN_URL  . 'app/admin/assets/ui/css/install-wizard.css',
            array(),
            WPFD_VERSION
        );
        wp_enqueue_script(
            'jquery-minicolors',
            WPFD_PLUGIN_URL . 'app/admin/assets/js/jquery.minicolors.min.js',
            array(),
            WPFD_VERSION
        );
        wp_enqueue_script(
            'wpfd_wizard_script',
            WPFD_PLUGIN_URL . 'app/admin/assets/ui/js/wizard.js',
            array('jquery'),
            WPFD_VERSION,
            true
        );
        // Load fonts
        wp_enqueue_style('wpfd-admin-ui-font-nutiosans', 'https://fonts.googleapis.com/css?family=Nunito+Sans:100,100i,300,300i,400,400i,500,500i,700,700i,900,900&amp;subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese');
        // Get step
        $this->steps = apply_filters('wpfd_setup_wizard_steps', $this->steps);
        $this->current_step  = isset($_GET['step']) ? sanitize_key($_GET['step']) : current(array_keys($this->steps));

        // Save action
        if (!empty($_POST['wpfd_save_step']) && isset($this->steps[$this->current_step]['action'])) {
            call_user_func(array('WpfdHandlerWizard', $this->steps[$this->current_step]['action']), $this->current_step);
        }

        // Render
        $this->setHeader();
        if (!isset($_GET['step'])) {
            require_once(WPFD_PLUGIN_DIR_PATH . 'app/admin/classes/install-wizard/content/viewWizard.php');
        } elseif (isset($_GET['step']) && $_GET['step'] === 'wizard_done') {
            require_once(WPFD_PLUGIN_DIR_PATH . 'app/admin/classes/install-wizard/content/viewDone.php');
        } else {
            $this->setMenu();
            $this->setContent();
        }
        $this->setFooter();
        // phpcs:enable
        exit();
    }

    /**
     * Get next link step
     *
     * @param string $step Current step
     *
     * @return string
     */
    public function getNextLink($step = '')
    {
        if (!$step) {
            $step = $this->current_step;
        }

        $keys = array_keys($this->steps);

        if (end($keys) === $step) {
            return add_query_arg('step', 'wizard_done', remove_query_arg(array('activate_error', 'theme')));
        }

        $step_index = array_search($step, $keys, true);
        if (false === $step_index) {
            return '';
        }

        return add_query_arg('step', $keys[$step_index + 1], remove_query_arg(array('activate_error', 'theme')));
    }

    /**
     * Get step link
     *
     * @param string $step  Current step
     * @param string $theme Selected theme
     *
     * @return string
     */
    public function getThemeConfigLink($step = '', $theme = 'default')
    {
        if (!$step) {
            $step = $this->current_step;
        }

        $keys = array_keys($this->steps);

        if (end($keys) === $step) {
            return add_query_arg('step', 'wizard_done', remove_query_arg(array('activate_error', 'theme')));
        }

        $step_index = array_search($step, $keys, true);
        if (false === $step_index) {
            return '';
        }

        return add_query_arg(array('step' => $keys[$step_index + 1], 'theme' => $theme), remove_query_arg('activate_error'));
    }

    /**
     * Output the menu for the current step.
     *
     * @return void
     */
    public function setMenu()
    {
        $output_steps = $this->steps;
        ?>
        <div class="wpfd-wizard-steps">
            <ul class="wizard-steps">
                <?php
                $i = 0;
                foreach ($output_steps as $key => $step) {
                    $position_current_step = array_search($this->current_step, array_keys($this->steps), true);
                    $position_step = array_search($key, array_keys($this->steps), true);
                    $is_visited = $position_current_step > $position_step;
                    $i ++;
                    if ($key === $this->current_step) {
                        ?>
                        <li class="actived"><div class="layer"><?php echo esc_html($i) ?></div></li>
                        <?php
                    } elseif ($is_visited) {
                        ?>
                        <li class="visited">
                            <a href="<?php echo esc_url(add_query_arg('step', $key, remove_query_arg(array('activate_error', 'theme')))); ?>">
                                <div class="layer"><?php echo esc_html($i) ?></div></a>
                        </li>
                        <?php
                    } else {
                        ?>
                        <li><div class="layer"><?php echo esc_html($i) ?></div></li>
                        <?php
                    }
                }
                ?>
            </ul>
        </div>
        <?php
    }


    /**
     * Output the content for the current step.
     *
     * @return void
     */
    public function setContent()
    {
        echo '<div class="">';
        if (!empty($this->steps[$this->current_step]['view'])) {
            require_once(WPFD_PLUGIN_DIR_PATH . 'app/admin/classes/install-wizard/content/' . $this->steps[$this->current_step]['view'] . '.php');
        }
        echo '</div>';
    }

    /**
     * Setup Wizard Header.
     *
     * @return void
     */
    public function setHeader()
    {
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php esc_html_e('WP File Download &rsaquo; Setup Wizard', 'wpfd'); ?></title>
            <?php do_action('admin_print_styles'); ?>
            <?php do_action('admin_head'); ?>
        </head>
        <body class="wpfd-wizard-setup wp-core-ui">
        <div class="wpfd-wizard-content p-d-20">
        <?php
    }

    /**
     * Setup Wizard Footer.
     *
     * @return void
     */
    public function setFooter()
    {
        ?>
        </div>
        </body>
        <?php wp_print_footer_scripts(); ?>
        </html>
        <?php
    }
}

new WpfdInstallWizard();
