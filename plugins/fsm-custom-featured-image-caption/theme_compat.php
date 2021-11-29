<?php

$fsm_custom_featured_image_caption_options = get_option( 'fsm_custom_featured_image_caption_options' );
if (!is_admin() && isset($fsm_custom_featured_image_caption_options['theme_compat_divi']) && $fsm_custom_featured_image_caption_options['theme_compat_divi']==1)
{
//REPLACE DIVI print_thumbnail function to allow showing captions
/* this function prints thumbnail from Post Thumbnail or Custom field or First post image */
	if ( ! function_exists( 'print_thumbnail' )) {

		function print_thumbnail($thumbnail = '', $use_timthumb = true, $alttext = '', $width = 100, $height = 100, $class = '', $echoout = true, $forstyle = false, $resize = true, $post='', $et_post_id = '' ) {
			if ( is_array( $thumbnail ) ) {
				extract( $thumbnail );
			}

			if ( empty( $post ) ) global $post, $et_theme_image_sizes;

			$output         = '';
			$raw            = false;
			$thumbnail_orig = $thumbnail;

			$et_post_id = ! empty( $et_post_id ) ? (int) $et_post_id : $post->ID;

			if ( has_post_thumbnail( $et_post_id ) ) {
				$thumb_array['use_timthumb'] = false;

				$image_size_name = $width . 'x' . $height;
				$et_size = isset( $et_theme_image_sizes ) && array_key_exists( $image_size_name, $et_theme_image_sizes ) ? $et_theme_image_sizes[$image_size_name] : array( $width, $height );

				$et_attachment_image_attributes = wp_get_attachment_image_src( get_post_thumbnail_id( $et_post_id ), $et_size );
				$thumbnail = $et_attachment_image_attributes[0];
			} else {
				$thumbnail = et_multisite_thumbnail( $thumbnail );

				$cropPosition = '';

				$allow_new_thumb_method = false;

				$new_method = true;
				$new_method_thumb = '';
				$external_source = false;

				$allow_new_thumb_method = !$external_source && $new_method && empty( $cropPosition );

				if ( $allow_new_thumb_method && !empty( $thumbnail ) ) {
					if ( 'data:image' === substr( $thumbnail, 0, 10 ) ) {
						$new_method_thumb = $thumbnail;
						$raw              = true;
					} else {
						$et_crop          = get_post_meta( $post->ID, 'et_nocrop', true );
						$et_crop          = empty( $et_crop ) ? true : false;
						$new_method_thumb = et_resize_image( et_path_reltoabs( $thumbnail ), $width, $height, $et_crop );
						if ( is_wp_error( $new_method_thumb ) ) {
							$new_method_thumb = '';
						}
					}
				}

				$thumbnail = $new_method_thumb;
			}

			if ( false === $forstyle && $resize ) {
				if ( $width < 480 && et_is_responsive_images_enabled() && ! $raw ) {
					$output = sprintf(
						'<img src="%1$s" alt="%2$s" class="%3$s" srcset="%4$s " sizes="%5$s " %6$s />',
						esc_url( $thumbnail ),
						esc_attr( wp_strip_all_tags( $alttext ) ),
						empty( $class ) ? '' : esc_attr( $class ),
						$thumbnail_orig . ' 479w, ' . $thumbnail . ' 480w',
						'(max-width:479px) 479px, 100vw',
						apply_filters( 'et_print_thumbnail_dimensions', ' width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '"' )
					);
				} else {
					$output = sprintf(
						'<img src="%1$s" alt="%2$s" class="%3$s"%4$s />',
						$raw ? $thumbnail : esc_url( $thumbnail ),
						esc_attr( wp_strip_all_tags( $alttext ) ),
						empty( $class ) ? '' : esc_attr( $class ),
						apply_filters( 'et_print_thumbnail_dimensions', ' width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '"' )
					);

					if ( ! $raw ) {
						$output = et_image_add_srcset_and_sizes( $output );
					}
				}
			} else {
				$output = $thumbnail;
			}
			
			$output = apply_filters('divi_thumbnail_html',$output,$et_post_id);

			if ($echoout) echo et_core_intentionally_unescaped( $output, 'html' );
			else return $output;
		}

	}


}
