<?php

namespace WPDM\SocialConnect;



class SocialConnect {

    private static $instance;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

	function __construct(){

	    new FacebookConnect();
		new GoogleConnect();
		new LinkedinConnect();
		new TwitterConnect();

	}

	public static function TwitterAuthUrl($pid, $action = 'tweet'){
	    return home_url("/?connect=twitter&package=".$pid.'&do='.$action);
    }
    public static function LinkedinAuthUrl($pid){
        return LinkedInConnect::LoginURL($pid);
    }
    public static function GooglePlusUrl($pid){
        return home_url("/?connect=google&package=".$pid);
    }
    public static function GoogleAuthUrl($pid){
        return home_url("/?connect=google&package=".$pid);
    }
    public static function FacebookLikeUrl($pid){
        return home_url("/?connect=facebook&like=1&package=".$pid);
    }
    public static function FacebookAuthUrl($pid){
        return home_url("/?connect=facebook&package=".$pid);
    }
}


