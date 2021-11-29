<?php

/**
 * Class WpfdHelperResponse
 */
class WpfdHelperResponse
{

    /**
     * Xressponse array
     *
     * @var array
     */
    protected $xresponse = array();

    /**
     * Store console message
     *
     * @param string $msg Message
     *
     * @return void
     */
    public function console($msg)
    {
        $this->xresponse[] = array('cn', $msg);
    }

    /**
     * Store alert message
     *
     * @param string $msg Message
     *
     * @return void
     */
    public function alert($msg)
    {
        $this->xresponse[] = array('al', $msg);
    }

    /**
     * Assign data
     *
     * @param string $id   Id
     * @param array  $data Data
     *
     * @return void
     */
    public function assign($id, $data)
    {
        $this->xresponse[] = array('as', $id, $data);
    }

    /**
     * Redirect response
     *
     * @param string  $url   Url
     * @param integer $delay Delay
     *
     * @return void
     */
    public function redirect($url = '', $delay = 0)
    {
        $this->xresponse[] = array('rd', $url, $delay);
    }

    /**
     * Reload response
     *
     * @return void
     */
    public function reload()
    {
        $this->xresponse[] = array('rl');
    }

    /**
     * Store script to execution
     *
     * @param string $script Script
     *
     * @return void
     */
    public function script($script = '')
    {
        $this->xresponse[] = array('js', $script);
    }

    /**
     * Store variable
     *
     * @param string $var   Variable
     * @param mixed  $value Value
     *
     * @return void
     */
    public function variable($var, $value)
    {
        $this->xresponse[] = array('vr', $var, $value);
    }

    /**
     * Set response
     *
     * @param mixed $a Response
     *
     * @return void
     */
    public function setResponse($a)
    {
        $this->xresponse = $a;
    }

    /**
     * Get Json response
     *
     * @return string
     */
    public function getJSON()
    {
        return json_encode($this->xresponse);
    }

    /**
     * Get data from post
     *
     * @return array|boolean
     */
    public function getData()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Check it where function was call
        if ((isset($_POST['__xr'])) && ((int) $_POST['__xr'] === 1)) {
            $post = isset($_POST['z']) ? json_decode(stripslashes($_POST['z']), true) : array();

            return $post;
        } else {
            return false;
        }
        // phpcs:enable
    }
}
