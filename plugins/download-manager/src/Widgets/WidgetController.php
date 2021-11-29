<?php


namespace WPDM\Widgets;


class WidgetController
{
    private static $widget_controller_instance = null;

    public static function instance(){
        if ( is_null( self::$widget_controller_instance ) ) {
            self::$widget_controller_instance = new self();
        }
        return self::$widget_controller_instance;
    }

    private function __construct()
    {
        add_action('widgets_init', function(){
            include __DIR__.'/Affiliate.php';
            include __DIR__.'/Categories.php';
            include __DIR__.'/CatPackages.php';
            include __DIR__.'/ListPackages.php';
            include __DIR__.'/NewDownloads.php';
            include __DIR__.'/PackageInfo.php';
            include __DIR__.'/Tags.php';
            include __DIR__.'/Search.php';
            include __DIR__.'/TopDownloads.php';
        });

    }
}


