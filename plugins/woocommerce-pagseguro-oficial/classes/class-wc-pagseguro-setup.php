<?php
/**
 ************************************************************************
Copyright [2016] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
 ************************************************************************
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists('WC_PagSeguro_Setup')):

    class WC_PagSeguro_Setup
    {
        /**
         * When plugin is activated
         */
        public static function plugin_activated()
        {
            $pages = new WC_PagSeguro_Pages();
            $page_id = $pages->create_pagseguro_page();
            $pages->create_pagseguro_checkout_page($page_id);
            $pages->create_pagseguro_direct_payment_checkout_page($page_id);
            $pages->create_pagseguro_order_confirmation_checkout_page($page_id);
            $pages->create_pagseguro_checkout_error($page_id);
        }
    }

endif;