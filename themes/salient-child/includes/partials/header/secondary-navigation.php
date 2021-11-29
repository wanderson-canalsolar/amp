<?php
/**
* Secondary navigation bar
*
* @package    Salient WordPress Theme
* @subpackage Partials
* @version 10.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$nectar_options = get_nectar_theme_options();

$using_page_header         = nectar_using_page_header( $post->ID );
$header_format             = ( ! empty( $nectar_options['header_format'] ) ) ? $nectar_options['header_format'] : 'default';
$header_link_hover_effect  = ( ! empty( $nectar_options['header-hover-effect'] ) ) ? $nectar_options['header-hover-effect'] : 'default';
$mobile_fixed              = ( ! empty( $nectar_options['header-mobile-fixed'] ) ) ? $nectar_options['header-mobile-fixed'] : 'false';
$bg_header                 = ( ! empty( $post->ID ) && $post->ID != 0 ) ? $using_page_header : 0;
$bg_header                 = ( $bg_header == 1 ) ? 'true' : 'false';
$trans_header              = ( ! empty( $nectar_options['transparent-header'] ) && $nectar_options['transparent-header'] === '1' ) ? $nectar_options['transparent-header'] : 'false';
$perm_trans              	 = ( ! empty( $nectar_options['header-permanent-transparent'] ) && $trans_header != 'false' && $bg_header === 'true' && $header_format != 'centered-menu-bottom-bar' ) ? $nectar_options['header-permanent-transparent'] : 'false';
$header_remove_stickiness  = ( ! empty( $nectar_options['header-remove-fixed'] ) ) ? $nectar_options['header-remove-fixed'] : '0';
$header_mobile_func        = ( ! empty( $nectar_options['secondary-header-mobile-display'] ) && $perm_trans !== '1' ) ? $nectar_options['secondary-header-mobile-display'] : 'default';

if ( $header_format === 'left-header' ) {
	$header_remove_stickiness = '0'; 
}

$using_secondary      	 	= ( ! empty( $nectar_options['header_layout'] ) && $header_format !== 'left-header' ) ? $nectar_options['header_layout'] : ' ';
$secondary_header_text 		= ( ! empty( $nectar_options['secondary-header-text'] ) ) ? 'true' : 'false';

if ( $using_secondary === 'header_with_secondary' ) { ?>
	
	<div id="header-secondary-outer" class="<?php echo esc_attr( $header_format ); ?>" data-mobile="<?php echo esc_attr($header_mobile_func); ?>" data-remove-fixed="<?php echo esc_attr( $header_remove_stickiness ); ?>" data-lhe="<?php echo esc_attr( $header_link_hover_effect ); ?>" data-secondary-text="<?php echo esc_attr( $secondary_header_text ); ?>" data-full-width="<?php echo ( ! empty( $nectar_options['header-fullwidth'] ) && $nectar_options['header-fullwidth'] === '1' ) ? 'true' : 'false'; ?>" data-mobile-fixed="<?php echo esc_attr( $mobile_fixed ); ?>" data-permanent-transparent="<?php echo esc_attr( $perm_trans ); ?>" >
		<div class="container">
            <div class="row" style="background-color: #000000;border-bottom-left-radius: 15px; border-bottom-right-radius: 15px; padding: 0 5px">
            <div class="col span_5">
                <nav>
                    <?php

                    if ( has_nav_menu( 'secondary_nav' ) ) { ?>
                        <ul class="sf-menu">
                            <?php
                            wp_nav_menu(
                                array(
                                    'walker'         => new Nectar_Arrow_Walker_Nav_Menu(),
                                    'theme_location' => 'secondary_nav',
                                    'container'      => '',
                                    'items_wrap'     => '%3$s',
                                )
                            );
                            nectar_hook_secondary_header_menu_items();

                            ?>
                        </ul>
                        <?php
                    }

                    ?>

                </nav>
            </div>
            <div class="col span_4" style="text-align: center">
                <span style="color:#ffffff"> <?php  echo wp_date($nectar_options['secondary-header-text']);?></span>

            </div>
            <div class="col span_3">
                <?php  if ( ! empty( $nectar_options['enable_social_in_header'] ) &&
                    $nectar_options['enable_social_in_header'] === '1' &&
                    $header_format !== 'centered-menu-bottom-bar' ) {
                    nectar_header_social_icons( 'secondary-nav' );
                }
                ?>
            </div>
            </div>
		</div>
	</div>

    <div id="top-mobile" class="mobile" style="background-color:#000000;padding: 8px 0">
        <div class="container">
            <div class="row" style="background-color: #000000;align-items: center;justify-content: center;display: flex;">
            <div class="col" style="width: 70%">
                <span style="color:#ffffff;font-weight: bold"> <?php  echo wp_date($nectar_options['secondary-header-text']);?></span>
            </div>
            <div class="col" style="width: 30%">
                <?php echo   nectar_header_social_icons( 'secondary-nav' ); ?>
            </div>
            </div>
        </div>
    </div>


	
<?php }
