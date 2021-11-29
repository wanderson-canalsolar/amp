<?php
/**
 * Header menu items and logo
 *
 * @package Salient WordPress Theme
 * @subpackage Partials
 * @version 11.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $woocommerce;

$nectar_header_options = nectar_get_header_variables();
$nectar_options        = get_nectar_theme_options();

?>

<header id="top">
    <div class="container">
        <div id="top1" class="row  align-items-center" style="align-items: center;">
            <div class="col span_1 logo_coluna">
                <a id="logo" href="<?php echo esc_url( home_url() ); ?>" data-supplied-ml-starting-dark="<?php echo esc_attr( $nectar_header_options['using_mobile_logo_starting_dark'] ); ?>" data-supplied-ml-starting="<?php echo esc_attr( $nectar_header_options['using_mobile_logo_starting'] ); ?>" data-supplied-ml="<?php echo esc_attr( $nectar_header_options['using_mobile_logo'] ); ?>" <?php echo wp_kses_post( $nectar_header_options['logo_class'] ); ?>>
                    <?php nectar_logo_output( $nectar_header_options['activate_transparency'], $nectar_header_options['side_widget_class'], $nectar_header_options['using_mobile_logo'] ); ?>
                </a>
            </div>
            <div class="col span_4 newsletter_coluna">
                <div class="content">
                    <div role="main" id="newsletter-96c8970d771d59520dff"></div>
                </div>

            </div>
            <div class="col span_4 search_coluna" >
                <form method="get" action="/">
                    <div class="content" style="display:flex">
                        <input type="text" class="newsletter"  name="s" placeholder="Buscar no Canal Solar" />
                        <button type="submit" class="button btn-xs" style="background-color: #E64F38;
                            margin-left: -2px;
                            color: #ffffff;
                            border-radius: 4px !important;
                            font-size: 19px;">
                            <i class="fa fa-search"></i></button>
                    </div>
                </form>
            </div>
            <div class="col span_2 pot_coluna" style="background-color: #eeeeee; border-radius: 10px; padding: ">
                <?php

                if ( is_active_sidebar( 'header-2' ) ) : ?>
                    <div id="header1" class="header1" style="padding: 0 7%;">
                        <?php dynamic_sidebar( 'header-2' ); ?>
                    </div>

                <?php endif; ?>

            </div>

            <div class="col span_1 login_coluna">
                <div class="pull-right">
                    <nav>
                        <ul class="buttons sf-menu">

                            <?php
                            // Pull right.
                            nectar_header_button_items();


                            ?>

                        </ul>

                    </nav>
                </div><!--/right-aligned-menu-items-->

            </div>
        </div>
        <div class="row">


            <div class="col span_12" style="margin-top: 7px;margin-bottom: 7px;">
                <?php

                if ( $nectar_header_options['side_widget_area'] === '1' || $nectar_header_options['side_widget_class'] === 'simple' ) {
                    ?>

                        <div style="float: left">
                            <a class="mobile-only" href="<?php echo esc_url( home_url() ); ?>"
                               data-supplied-ml-starting-dark="<?php echo esc_attr( $nectar_header_options['using_mobile_logo_starting_dark'] ); ?>"
                               data-supplied-ml-starting="<?php echo esc_attr( $nectar_header_options['using_mobile_logo_starting'] ); ?>"
                               data-supplied-ml="<?php echo esc_attr( $nectar_header_options['using_mobile_logo'] ); ?>"
                                <?php echo wp_kses_post( $nectar_header_options['logo_class'] ); ?>>
                                <?php echo '<img class="mobile-only-logo" alt="' . get_bloginfo( 'name' ) . '" src="' . nectar_options_img( $nectar_options['mobile-logo'] ) . '" />';?>
                            </a>
                        </div>

                    <div style="margin-top: 25px;" class="slide-out-widget-area-toggle mobile-icon right <?php echo esc_attr( $nectar_header_options['side_widget_class'] ); ?>" data-custom-color="<?php echo esc_attr($nectar_header_options['ocm_menu_btn_color']); ?>" data-icon-animation="simple-transform">
                        <div> <a href="#sidewidgetarea" aria-label="<?php echo esc_attr__('Navigation Menu', 'salient'); ?>" aria-expanded="false" class="<?php echo 'closed' . esc_attr($menu_label_class); ?>">
                                <?php if( true === $menu_label ) { echo '<i class="label">' . esc_html__('Menu','salient') .'</i>'; } ?><span aria-hidden="true"> <i class="lines-button x2"> <i class="lines"></i> </i> </span>
                            </a>
                        </div>
                    </div>



                <?php } ?>



                <?php if ( is_active_sidebar( 'header-2' ) ) : ?>
                    <div class="mobile" style="padding: 0 7%;">
                        <?php dynamic_sidebar( 'header-2' ); ?>
                    </div>

                <?php endif; ?>


                <?php
                if ( $nectar_header_options['header_format'] === 'left-header' ) {
                    echo '<div class="nav-outer">';
                }
                ?>

                <nav>

                    <ul id="principal" class="sf-menu" style="width: 100%;">
                        <?php
                        if ( $nectar_header_options['has_main_menu'] === 'true' ) {
                            wp_nav_menu(
                                array(
                                    'walker'         => new Nectar_Arrow_Walker_Nav_Menu(),
                                    'theme_location' => 'top_nav',
                                    'container'      => '',
                                    'items_wrap'     => '%3$s',
                                )
                            );
                        } else {
                            echo '<li class="no-menu-assigned"><a href="#">No menu assigned</a></li>';
                        }

                        if ( ! empty( $nectar_options['enable_social_in_header'] ) &&
                            $nectar_options['enable_social_in_header'] === '1' &&
                            $nectar_header_options['using_secondary'] !== 'header_with_secondary' &&
                            $nectar_header_options['header_format'] !== 'menu-left-aligned' &&
                            $nectar_header_options['header_format'] !== 'centered-menu' &&
                            $nectar_header_options['header_format'] !== 'left-header' &&
                            $nectar_header_options['header_format'] !== 'centered-menu-bottom-bar' ) {

                            echo '<li id="social-in-menu" class="button_social_group">';
                            nectar_header_social_icons( 'main-nav' );
                            echo '</li>';
                        }

                        ?>
                    </ul>


                    <?php
                    if ( $nectar_header_options['header_format'] !== 'menu-left-aligned' &&
                        $nectar_header_options['header_format'] !== 'centered-menu-bottom-bar' ) { ?>
                        <ul class="buttons sf-menu" data-user-set-ocm="<?php echo esc_attr( $nectar_header_options['user_set_side_widget_area'] ); ?>">

                            <?php

                            if ( ! empty( $nectar_options['enable_social_in_header'] ) &&
                                $nectar_options['enable_social_in_header'] === '1' &&
                                $nectar_header_options['using_secondary'] !== 'header_with_secondary' &&
                                $nectar_header_options['header_format'] === 'centered-menu' ) {

                                echo '<li id="social-in-menu" class="button_social_group">';
                                nectar_header_social_icons( 'main-nav' );
                                echo '</li>';
                            }

                            // Pull right.
                            if ( $nectar_header_options['header_format'] === 'centered-menu' &&
                                $nectar_header_options['using_pr_menu'] === 'true' ||
                                $nectar_header_options['header_format'] === 'centered-logo-between-menu' &&
                                $nectar_header_options['using_pr_menu'] === 'true' ) {
                                wp_nav_menu(
                                    array(
                                        'walker'         => new Nectar_Arrow_Walker_Nav_Menu(),
                                        'theme_location' => 'top_nav_pull_right',
                                        'container'      => '',
                                        'items_wrap'     => '%3$s',
                                    )
                                );
                                nectar_hook_pull_right_menu_items();
                            }

                            nectar_header_button_items();
                            ?>

                        </ul>
                    <?php } ?>

                </nav>

                <?php
                if ( $nectar_header_options['header_format'] === 'left-header' ) {
                    echo '</div>';
                }

                if ( $nectar_header_options['header_format'] === 'centered-menu' ||
                    $nectar_header_options['header_format'] === 'centered-logo-between-menu' ) {
                    nectar_logo_spacing();
                }
                ?>

            </div><!--/span_9-->

            <?php if ( $nectar_header_options['header_format'] === 'menu-left-aligned' ) { ?>


                <?php
            } elseif ( $nectar_header_options['header_format'] === 'left-header' ) {

                if ( ! empty( $nectar_options['enable_social_in_header'] ) &&
                    $nectar_options['enable_social_in_header'] === '1' &&
                    $nectar_header_options['using_secondary'] !== 'header_with_secondary' ) {
                    echo '<div class="button_social_group"><ul><li id="social-in-menu">';
                    nectar_header_social_icons( 'main-nav' );
                    echo '</li></ul></div>';
                }
            }
            ?>

        </div>


        <?php
        if( $nectar_header_options['side_widget_class'] === 'simple' ) {
            get_template_part( 'includes/partials/header/classic-mobile-nav' );
        }
        ?>
    </div>
</header>
