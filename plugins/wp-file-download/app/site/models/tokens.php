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
 * Class WpfdModelTokens
 */
class WpfdModelTokens extends Model
{
    /**
     * Time to Expired the token
     *
     * @var integer
     */
    private $seconds = 900; // 15 min

    /**
     * Get or create new token if not exists
     *
     * @return string
     */
    public function getOrCreateNew()
    {
        if (!is_user_logged_in()) {
            return '';
        }
        $sessionToken = isset($_SESSION['wpfdToken']) ? $_SESSION['wpfdToken'] : null;
        if ($sessionToken === null) {
            $token                 = $this->createToken();
            $_SESSION['wpfdToken'] = $token;
        } else {
            $tokenId = $this->tokenExists($sessionToken);
            if ($tokenId) {
                $this->updateToken($tokenId);
                $token                 = $sessionToken;
                $_SESSION['wpfdToken'] = $token;
            } else {
                $token                 = $this->createToken();
                $_SESSION['wpfdToken'] = $token;
            }
        }

        return $token;
    }

    /**
     * Create a new token
     *
     * $param $fileId File of this token
     *
     * @return string
     */
    public function createToken()
    {
        global $wpdb;
        $table    = $wpdb->prefix . 'wpfd_tokens';
        $tokenKey = md5(uniqid(mt_rand(), true));
        $token    = array(
            'token'      => $tokenKey,
            'created_at' => time(),
            'file_id'    => 0
        );
        $wpdb->insert($table, $token);

        return $tokenKey;
    }

    /**
     * Methode to check if a token exists
     *
     * @param string $token Token string
     *
     * @return string|boolean file, false if an error occurs
     */
    public function tokenExists($token)
    {
        global $wpdb;

        $result = $wpdb->get_results($wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'wpfd_tokens WHERE token = %s LIMIT 1',
            $token
        ));
        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
        if (is_countable($result) && count($result) > 0) {
            // Check exp token
            if ($this->isExpired($result[0])) {
                return false;
            }

            return $token;
        }

        return false;
    }

    /**
     * Check token is Expired
     *
     * @param string $token Token string
     *
     * @return boolean
     */
    public function isExpired($token)
    {
        if ($token) {
            // Check expired time
            /**
             * Filter to change token live time
             *
             * @param int Token live time in seconds
             *
             * @return int
             */
            $expiredTime = $token->created_at + apply_filters('wpfd_token_live_time', $this->seconds);
            if ($expiredTime > time()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Method to update a token
     *
     * @param string $token Token string
     *
     * @return false|integer file, false if an error occurs
     */
    public function updateToken($token)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'wpfd_tokens';
        $data  = array(
            'token'      => $token,
            'created_at' => time(),
            'file_id'    => 0
        );
        $where = array('token' => $token);

        return $wpdb->update($table, $data, $where);
    }

    /**
     * Method to delete all tokens
     *
     * @return integer|boolean number of affected rows, false if an error occurs
     */
    public function removeTokens()
    {
        global $wpdb;
        /**
         * Filter to change token live time
         *
         * @param int Token live time in seconds
         *
         * @return int
         *
         * @ignore
         */
        $time = time() - apply_filters('wpfd_token_live_time', $this->seconds);

        return $wpdb->query(
            $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wpfd_tokens WHERE created_at < %d', $time)
        );
    }
}
