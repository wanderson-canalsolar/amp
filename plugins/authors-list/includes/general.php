<?php

if ( ! function_exists( 'authors_list_textdomain' ) ) {

    /**
     * Translation
     *
     * @since 1.0.0
     */
    function authors_list_textdomain() {
        
        load_plugin_textdomain( 'authors-list', false, AUTHORS_LIST_DIR_NAME . '/languages' ); 

    } add_action( 'init', 'authors_list_textdomain' );

}

if ( ! function_exists( 'authors_list_query_modification' ) ) {

    function authors_list_query_modification( $class ) {    

        if ( 'rand' == $class->query_vars['orderby'] ) {
            $class->query_orderby = str_replace( 'user_login', 'RAND()', $class->query_orderby );
        }

        return $class;

    } add_action( 'pre_user_query', 'authors_list_query_modification' );

}    