<?php

/**
 * Class WpfdDivi
 */
class WpfdDivi extends DiviExtension
{

    /**
     * The gettext domain for the extension's translations.
     *
     * @var string $gettext_domain Text domain
     */
    public $gettext_domain = 'wpfd';

    /**
     * The extension's WP Plugin name.
     *
     *@var string $name Name
     */
    public $name = 'divi';

    /**
     * The extension's version
     *
     * @var string $version Version
     */
    public $version = '1.0.0';

    /**
     * DIVI constructor.
     *
     * @param string $name Name
     * @param array  $args Attributes
     */
    public function __construct($name = 'wpfd_divi', $args = array())
    {
        $this->plugin_dir     = plugin_dir_path(__FILE__);
        $this->plugin_dir_url = plugin_dir_url($this->plugin_dir);

        parent::__construct($name, $args);
    }
}

new WpfdDivi;
