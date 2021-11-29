<?php
/**
 * WP Framework
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

namespace Joomunited\WPFramework\v1_0_5\Fields;

use Joomunited\WPFramework\v1_0_5\Field;

defined('ABSPATH') || die();

/**
 * Class Input
 */
class Input extends Field
{

    /**
     * Sanitize input value
     *
     * @param mixed $value Value to sanitize
     *
     * @return mixed
     */
    public function sanitize($value)
    {
        return filter_var($value, FILTER_SANITIZE_STRING);
    }
}
