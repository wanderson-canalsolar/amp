<?php

/**
 * Plugin Name: PE Recent Posts
 * Description: Simple Slider for Posts
 * Plugin URI: https://www.pixelemu.com/wordpress-plugins/i/3-pe-recent-posts
 * Author: pixelemu.com
 * Author URI: https://www.pixelemu.com
 * Version: 1.2
 * Text Domain: pe-recent-posts
 * Domain Path: /languages/
 * License: GPLv2 or later
 */

/* Popular posts tracking - tracks the number of views for a post in a custom field */
// Set/check session
add_action('init', 'pe_recent_posts_session', 1);
function pe_recent_posts_session()
{
	if (!session_id() && !is_admin()) {
		session_start();
	}
}
// count hits of posts
function pe_base_track_popular_posts()
{
	// Only run the process for single posts, pages and post types
	if (is_singular()) {
		global $post;
		$custom_field = '_pe_base_popular_posts_count';
		// Only track a one view per post for a single visitor session to avoid duplications
		if (!isset($_SESSION["pe-popular-posts-count-{$post->ID}"])) {
			// Update view count 
			$view_count = get_post_meta($post->ID, $custom_field, true);
			$stored_count = (isset($view_count) && !empty($view_count)) ? (intval($view_count) + 1) : 1;
			$update_meta = update_post_meta($post->ID, $custom_field, $stored_count);
			// Check for errors
			if (is_wp_error($update_meta))
				error_log($update_meta->get_error_message(), 0);
			// Store session in "viewed" state
			$_SESSION["pe-popular-posts-count-{$post->ID}"] = 1;
		}
		// uncomment these 3 lines to show views of post (right after <body> tag)
		/*echo '<p style="color:red; text-align:center; margin:1em 0;">';
		echo get_post_meta( $post->ID, $custom_field, true );
		echo ' views of this post</p>';*/
	}
}
add_action('wp_head', 'pe_base_track_popular_posts');
// excerpt limit - BEGIN
if (!function_exists('get_excerpt_plugin')) {
	function get_excerpt_plugin($count)
	{
		$excerpt = get_the_excerpt();
		$excerpt = strip_tags($excerpt);
		$excerpt = mb_substr($excerpt, 0, $count);
		$excerpt_final = '<div class="excerpt-text">' . $excerpt;
		if ($count > 0) {
			$excerpt_final .= '...';
		}
		$excerpt_final .= '</div>';

		return $excerpt_final;
	}
}
// excerpt limit - END
if (!class_exists('PE_Recent_Posts_Plugin')) {
	class PE_Recent_Posts_Plugin extends WP_Widget
	{
		public function __construct()
		{
			$widget_ops = array(
				'classname' => 'PE_Recent_Posts',
				'description' => __('Show recent posts.', 'pe-recent-posts'),
			);
			parent::__construct('PE_Recent_Posts', 'PE Recent Posts', $widget_ops);
		}
		public function widget($args,  $setup)
		{
			extract($args);
			$post_type = 'post';
			if (isset($setup['post_type'])) {
				$post_type = $setup['post_type'];
			}
			$post_type_category = 'category';
			if (isset($setup['post_type_category'])) {
				$post_type_category = $setup['post_type_category'];
			}
			$count_posts = wp_count_posts($post_type);
			$title_show = 1;
			if (isset($setup['title_show'])) {
				$title_show = $setup['title_show'];
			}
			$title_linkable = 1;
			if (isset($setup['title_linkable'])) {
				$title_linkable = $setup['title_linkable'];
			}
			$readmore = 0;
			if (isset($setup['readmore'])) {
				$readmore = $setup['readmore'];
			}
			$header_tag = 5;
			if (isset($setup['header_tag'])) {
				$header_tag = $setup['header_tag'];
			}
			$create_date = 0;
			if (isset($setup['create_date'])) {
				$create_date = $setup['create_date'];
			}
			$author = 0;
			if (isset($setup['author'])) {
				$author = $setup['author'];
			}
			$taxonomy_list = 0;
			if (isset($setup['taxonomy_list'])) {
				$taxonomy_list = $setup['taxonomy_list'];
			}
			$taxonomy_link = 0;
			if (isset($setup['taxonomy_link'])) {
				$taxonomy_link = $setup['taxonomy_link'];
			}
			$sticky_posts = 0;
			if (isset($setup['sticky_posts'])) {
				$sticky_posts = $setup['sticky_posts'];
			}
			$number_of_all_items = 9;
			if (isset($setup['number_of_all_items'])) {
				$number_of_all_items = $setup['number_of_all_items'];
			}
			if (!isset($setup['number_of_columns'])) {
				$number_of_columns = 1;
			} else {
				$number_of_columns = $setup['number_of_columns'];
			}
			if (!isset($setup['one_row_mobile'])) {
				$one_row_mobile = 0;
			} else {
				$one_row_mobile = $setup['one_row_mobile'];
			}
			if ($one_row_mobile == 0) {
				$one_row_mobile_class = 'one-row-mobile-off';
			} else {
				$one_row_mobile_class = 'one-row-mobile-on';
			}
			if (!isset($setup['number_of_rows'])) {
				$number_of_rows = 1;
			} else {
				$number_of_rows = $setup['number_of_rows'];
			}
			$interval = '0';
			if (!empty($setup['interval'])) {
				$interval = $setup['interval'];
			}
			$slider_pause = 'null';
			if (!empty($setup['slider_pause'])) {
				$slider_pause = $setup['slider_pause'];
			}
			$grid_spacing = 10;
			if (isset($setup['grid_spacing'])) {
				$grid_spacing = $setup['grid_spacing'];
			}
			$slide_width = 100 / $number_of_columns;
			$unique_id = $args['widget_id'];
			if (post_type_exists($post_type)) {
				if ($number_of_all_items > $count_posts->publish) {
					$number_of_all_items = $count_posts->publish;
				}
			}
			$order_posts = 'Date';
			if (isset($setup['order_posts'])) {
				$order_posts = $setup['order_posts'];
			}
			$meta_key = '';
			if ($order_posts == 'meta_value_num') {
				$meta_key = '_pe_base_popular_posts_count';
			}
			$order_direction = 'DESC';
			if (isset($setup['order_direction'])) {
				$order_direction = $setup['order_direction'];
			}
			$navigation_way = 1;
			if (isset($setup['navigation_way'])) {
				$navigation_way = $setup['navigation_way'];
			}
			$title_widget = apply_filters('widget_title', $setup['title']);
			if (empty($title_widget)) {
				$title_widget = false;
				$before_title = false;
				$after_title = false;
			}
			echo $before_widget;
			echo $before_title;
			echo $title_widget;
			echo $after_title;
			$desc_limit = 55;
			if (isset($setup['desc_limit'])) {
				$desc_limit = $setup['desc_limit'];
			}
			$show_thumbnail = 1;
			if (isset($setup['show_thumbnail'])) {
				$show_thumbnail = $setup['show_thumbnail'];
			}
			$thumbnail_linkable = 1;
			if (isset($setup['thumbnail_linkable'])) {
				$thumbnail_linkable = $setup['thumbnail_linkable'];
			}
			$image_alignment = 'left';
			if (isset($setup['image_alignment'])) {
				$image_alignment = $setup['image_alignment'];
			}
			$image_size = 'thumbnail';
			if (isset($setup['image_size'])) {
				$image_size = $setup['image_size'];
			}
			$category_id = '';
			if (isset($setup['category_id'])) {
				$category_id = $setup['category_id'];
			}
			$even_odd = '';
			if ($number_of_columns % 2) {
				$even_odd = 'odd-items-in-row';
			} else {
				$even_odd = 'even-items-in-row';
			}
			// get category for CPT
			$category_id_loop = '';
			$category_id_taxonomy = '';
			$tag_loop = '';
			$tax_query = '';
			$current_taxonomy = '';
			$field_value = '';
			// get taxonomies that belongs to $post_type
			$taxonomy_objects = get_object_taxonomies($post_type, 'names');
			// check for post type and post type taxonomy
			if (($post_type == 'post') && ($post_type_category == 'category')) {
				if (!empty($category_id)) {
					$category_id_loop = $category_id;
				} else {
					$category_id_loop = '';
				}
				$tag_loop = '';
				$tax_query = '';
			} else if (($post_type == 'post') && ($post_type_category == 'post_tag')) {
				if (!empty($category_id)) {
					$tag_loop = $category_id;
				} else {
					$tag_loop = '';
				}
				$category_id_loop = '';
				$tax_query = '';
			} else if (($post_type == 'post') && ($post_type_category == 'post_format ')) {
				if (!empty($category_id)) {
					$category_id_loop = $category_id;
				} else {
					$category_id_loop = '';
				}
				$tag_loop = '';
				$tax_query = '';
			} else if (($post_type != 'post')) {
				if (!empty($category_id) && in_array($post_type_category, $taxonomy_objects)) {
					$tax_query =
						array(
							array(
								'taxonomy' => '' . $post_type_category . '',
								'field'    => 'term_id',
								'terms'    => $category_id,
							),
						);
				} else {
					$tax_query = '';
				}
				$category_id_loop = '';
				$tag_loop = '';
			}
			// loop
			$loop = new WP_Query(array(
				'post_type' => '' . $post_type . '',
				'posts_per_page' => '' . $number_of_all_items . '',
				'ignore_sticky_posts' => '' . $sticky_posts . '',
				'meta_key' => '' . $meta_key . '',
				'orderby' => '' . $order_posts . '',
				'order' => '' . $order_direction . '',
				'cat' => $category_id_loop,
				'tax_query' => $tax_query,
				'tag_id' => $tag_loop
			));
			$number_of_all_items = $loop->post_count;
			$counter = 0;
			$counter_elements_in_row = 0;
			$counter_bullets = 0;
			while ($loop->have_posts()) : $loop->the_post();
				$counter_bullets++;
			endwhile;
			$bullets_on_board = '';
			if (($navigation_way == 1) && ($counter_bullets > ($number_of_columns * $number_of_rows))) {
				$bullets_on_board = 'bullets-on-board';
			}
			wp_reset_query();
			// check if CPT and category taxonomy exists, if they have relation
			if (post_type_exists($post_type) && taxonomy_exists($post_type_category) && !in_array($post_type_category, $taxonomy_objects) && !empty($post_type_category)) {
				echo __('Entered <strong>Post Type Taxonomy</strong> does not belong to <strong>Post Type</strong>.', 'pe-recent-posts');
			} else if (!post_type_exists($post_type) && (!taxonomy_exists($post_type_category) && !empty($post_type_category))) {
				echo __('Entered <strong>Post Type</strong> and <strong>Post Type Taxonomy</strong> does not exist.', 'pe-recent-posts');
			} else if (!post_type_exists($post_type)) {
				echo __('Entered <strong>Post Type</strong> does not exist.', 'pe-recent-posts');
			} else if (!taxonomy_exists($post_type_category) && !empty($post_type_category)) {
				echo __('Entered <strong>Post Type Taxonomy</strong> does not exist.', 'pe-recent-posts');
			} else { ?>
				<div id="myCarousel-<?php echo $unique_id; ?>" data-keyboard="false" class="pe-recent-posts-outer carousel slide <?php echo $one_row_mobile_class . ' ';
																											if ($navigation_way == 3) {
																												echo 'vertical';
																											} ?> <?php echo $bullets_on_board; ?> columns-<?php echo $number_of_columns . ' ' . $even_odd; ?>" style="margin-left: -<?php echo $grid_spacing; ?>px;">
					<?php if (($navigation_way == 1) && ($number_of_all_items > ($number_of_columns * $number_of_rows))) { ?>
						<?php $counter2 = 0; ?>
						<ol class="carousel-indicators" style="padding-left: <?php echo $grid_spacing; ?>px;" role="tablist" aria-label="<?php echo __('Indicators', 'pe-recent-posts'); ?>">
							<?php while ($loop->have_posts()) : $loop->the_post(); ?>
								<?php $counter2++; ?>
								<?php if (($counter2 % ($number_of_columns * $number_of_rows) == 1) || ($number_of_columns * $number_of_rows) == 1) {
									if ($counter2 == 1) { ?>
										<li id="title-<?php echo $unique_id; ?>-<?php echo $counter2; ?>" data-target="#myCarousel-<?php echo $unique_id; ?>" data-slide-to="0" class="active" tabindex="0" role="tab" aria-selected="false" aria-controls="<?php echo $unique_id; ?>-tab-<?php echo $counter2; ?>"><?php echo __('Item', 'pe-recent-posts'); ?> 1</li>
									<?php } else { ?>
										<li id="title-<?php echo $unique_id; ?>-<?php echo $counter2; ?>" data-target="#myCarousel-<?php echo $unique_id; ?>" data-slide-to="<?php echo ($counter2 - 1) / ($number_of_columns * $number_of_rows); ?>" tabindex="0" role="tab" aria-selected="false" aria-controls="<?php echo $unique_id; ?>-tab-<?php echo $counter2; ?>"><?php echo __('Item', 'pe-recent-posts'); ?> <?php echo (($counter2 - 1) / ($number_of_columns * $number_of_rows)) + 1; ?></li>
									<?php } ?>
								<?php } ?>
							<?php endwhile; ?>
							<?php wp_reset_query(); ?>
						</ol>
					<?php } else if (($navigation_way == 2) && ($number_of_all_items > ($number_of_columns * $number_of_rows))) { ?>
						<div class="pe-carousel-navigation-container left-right">
							<a class="carousel-control left" role="button" aria-controls="<?php echo $unique_id; ?>-container" aria-label="<?php echo __('Previous Slide', 'pe-recent-posts'); ?>" role="button" href="#myCarousel-<?php echo $unique_id; ?>" data-slide="prev"><i class="fa fa-chevron-left fa-2" aria-hidden="true"><span class="sr-only"><?php _e('Previous', 'pe-recent-posts'); ?></span></i></a>
							<a class="carousel-control right" role="button" aria-controls="<?php echo $unique_id; ?>-container" aria-label="<?php echo __('Next Slide', 'pe-recent-posts'); ?>" href="#myCarousel-<?php echo $unique_id; ?>" data-slide="next"><i class="fa fa-chevron-right fa-2" aria-hidden="true"><span class="sr-only"><?php _e('Next', 'pe-recent-posts'); ?></span></i></a>
						</div>
					<?php } else if (($navigation_way == 3) && ($number_of_all_items > ($number_of_columns * $number_of_rows))) { ?>
						<div class="pe-carousel-navigation-container up-down">
							<a class="carousel-control up" role="button" aria-controls="<?php echo $unique_id; ?>-container" aria-label="<?php echo __('Previous Slide', 'pe-recent-posts'); ?>" href="#myCarousel-<?php echo $unique_id; ?>" data-slide="prev"><i class="fa fa-chevron-up fa-2" aria-hidden="true"><span class="sr-only"><?php _e('Previous', 'pe-recent-posts'); ?></span></i></a>
							<a class="carousel-control down" role="button" aria-controls="<?php echo $unique_id; ?>-container" aria-label="<?php echo __('Next Slide', 'pe-recent-posts'); ?>" href="#myCarousel-<?php echo $unique_id; ?>" data-slide="next"><i class="fa fa-chevron-down fa-2" aria-hidden="true"><span class="sr-only"><?php _e('Next', 'pe-recent-posts'); ?></span></i></a>
						</div>
					<?php } ?>
					<div class="carousel-inner image-<?php echo $image_alignment; ?>" style="margin-bottom: -<?php echo $grid_spacing; ?>px;" id="<?php echo $unique_id; ?>-container" aria-live="off">
						<?php while ($loop->have_posts()) : $loop->the_post(); ?>
							<?php
							$counter++;
							$post_title = get_the_title();
							if ($counter_elements_in_row == $number_of_columns) {
								$counter_elements_in_row = 0;
							}
							$counter_elements_in_row++;
							global $post;
							$permalink = get_permalink($post->ID);

							$aria_labeledby = '';
							$aria_roledescription = '';
							$aria_label_slide = '';
							$current_panel_number = (($counter - 1) / ($number_of_columns * $number_of_rows)) + 1;
							$number_of_panels = intval(($number_of_all_items / ($number_of_columns * $number_of_rows)) + 1);
							if ($navigation_way == 1) {
								$aria_labeledby = 'aria-labelledby="title-' . $unique_id . '-' . $counter . '"';
							} else if ($navigation_way == 2 || $navigation_way == 3) {
								$aria_roledescription = 'aria-roledescription="' . __('slide', 'pe-recent-posts') . '"';
								$aria_label_slide = 'aria-label="' . $current_panel_number . ' of ' . $number_of_panels . '"';
							}

							if ($number_of_columns * $number_of_rows == 1) {
								if ($counter == 1) { ?>
									<div class="item active clearfix el-in-row-<?php echo $number_of_columns; ?>" id="<?php echo $unique_id; ?>-tab-<?php echo $counter; ?>" <?php echo $aria_labeledby . ' ' . $aria_roledescription . ' ' . $aria_label_slide; ?> tabindex="0">
									<?php } else { ?>
										<div class="item clearfix el-in-row-<?php echo $number_of_columns; ?>" id="<?php echo $unique_id; ?>-tab-<?php echo $counter; ?>" <?php echo $aria_labeledby . ' ' . $aria_roledescription . ' ' . $aria_label_slide; ?> tabindex="0">
										<?php } ?>
										<?php } else {
										if (($counter % ($number_of_columns * $number_of_rows) == 1)) {
											if ($counter == 1) { ?>
												<div class="item active clearfix el-in-row-<?php echo $number_of_columns; ?>" id="<?php echo $unique_id; ?>-tab-<?php echo $counter; ?>" <?php echo $aria_labeledby . ' ' . $aria_roledescription . ' ' . $aria_label_slide; ?> tabindex="0">
												<?php } else { ?>
													<div class="item clearfix el-in-row-<?php echo $number_of_columns; ?>" id="<?php echo $unique_id; ?>-tab-<?php echo $counter; ?>" <?php echo $aria_labeledby . ' ' . $aria_roledescription . ' ' . $aria_label_slide; ?> tabindex="0">
													<?php } ?>
											<?php }
									} ?>
											<ul class="thumbnails el-<?php echo $counter; ?> el-in-row-<?php echo $counter_elements_in_row; ?>" style="width: <?php echo $slide_width; ?>%;">
												<li>
													<div class="thumbnail-box" style="padding-left: <?php echo $grid_spacing; ?>px; padding-bottom: <?php echo $grid_spacing; ?>px;">
														<div class="thumbnail-box-in clearfix">
															<?php if ($image_alignment == 'bottom') { ?>
																<div class="caption fadeInUp animated <?php if (has_post_thumbnail()) {
																											echo 'image-on';
																										} ?>">
																	<?php if ($create_date == 1) { ?>
																		<span class="pe-creation-date"><?php echo get_the_date(); ?></span>
																	<?php } ?>
																	<?php if ($title_show == 1) { ?>
																		<h<?php echo $header_tag; ?> class="pe-recent-posts-title-tag">
																			<?php if ($title_linkable == 1) { ?>
																				<a href="<?php the_permalink(); ?>">
																				<?php } ?>
																				<?php the_title(); ?>
																				<?php if ($title_linkable == 1) { ?>
																				</a>
																			<?php } ?>
																		</h<?php echo $header_tag; ?>>
																	<?php } ?>
																	<?php if ($create_date == 2) { ?>
																		<span class="pe-creation-date"><?php echo get_the_date(); ?></span>
																	<?php } ?>
																	<?php if ($author == 1) { ?>
																		<span class="pe-author"><?php echo __('Created by: ', 'pe-recent-posts') . get_the_author(); ?></span>
																	<?php } ?>
																	<?php if ($taxonomy_list == 1) { ?>
																		<?php
																		$terms = get_the_terms($post->ID, $post_type_category);
																		echo '<ul class="pe-categories-list">';
																		foreach ($terms as $term) {
																			$term_link = get_term_link($term, $post_type_category);
																			if (is_wp_error($term_link))
																				continue;
																			echo '<li><a href="' . $term_link . '">' . $term->name . '</a><span class="pe-category-list-sep">, </span></li>';
																		}
																		echo '</ul>';
																		?>
																	<?php } ?>
																	<?php echo get_excerpt_plugin($desc_limit); ?>
																	<?php if ($readmore == 1) {
																		echo '<a class="readmore" href="' . $permalink . '">' . __('Read more', 'pe-recent-posts') . '</a>';
																	} ?>
																</div>
															<?php } ?>
															<?php if (has_post_thumbnail() && $show_thumbnail == '1') {
																$image_id = get_post_thumbnail_id();
																$image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
																if (!empty($image_alt)) {
																	$image_alternative_text = $image_alt;
																} else {
																	$image_alternative_text = $post_title;
																} ?>
																<?php if ($thumbnail_linkable == 1) { ?>
																	<a href="<?php the_permalink(); ?>">
																	<?php } ?>
																	<?php echo the_post_thumbnail($image_size, array(
																		'alt'   => $image_alternative_text
																	)); ?>
																	<?php if ($thumbnail_linkable == 1) { ?>
																	</a>
																<?php } ?>
															<?php } ?>
															<?php if ($image_alignment != 'bottom') { ?>
																<div class="caption fadeInUp animated <?php if (has_post_thumbnail()) {
																											echo 'image-on';
																										} ?>">
																	<?php if ($create_date == 1) { ?>
																		<span class="pe-creation-date"><?php echo get_the_date(); ?></span>
																	<?php } ?>
																	<?php if ($title_show == 1) { ?>
																		<h<?php echo $header_tag; ?> class="pe-recent-posts-title-tag">
																			<?php if ($title_linkable == 1) { ?>
																				<a href="<?php the_permalink(); ?>">
																				<?php } ?>
																				<?php the_title(); ?>
																				<?php if ($title_linkable == 1) { ?>
																				</a>
																			<?php } ?>
																		</h<?php echo $header_tag; ?>>
																	<?php } ?>
																	<?php if ($create_date == 2) { ?>
																		<span class="pe-creation-date"><?php echo get_the_date(); ?></span>
																	<?php } ?>
																	<?php if ($author == 1) { ?>
																		<span class="pe-author"><?php echo __('Created by: ', 'pe-recent-posts') . get_the_author(); ?></span>
																	<?php } ?>
																	<?php if ($taxonomy_list == 1) { ?>
																		<?php
																		echo '<span class="pe-categories-list-label">' . __('Category: ', 'pe-recent-posts') . '</span>';
																		$terms = get_the_terms($post->ID, $post_type_category);
																		echo '<ul class="pe-categories-list">';
																		foreach ($terms as $term) {
																			$term_link = get_term_link($term, $post_type_category);
																			if (is_wp_error($term_link))
																				continue;
																			echo '<li><a href="' . $term_link . '">' . $term->name . '</a><span class="pe-category-list-sep">, </span></li>';
																		}
																		echo '</ul>';
																		?>
																	<?php } ?>
																	<?php echo get_excerpt_plugin($desc_limit); ?>
																	<?php if ($readmore == 1) {
																		echo '<a class="readmore" href="' . $permalink . '">' . __('Read more', 'pe-recent-posts') . '</a>';
																	} ?>
																</div>
															<?php } ?>
														</div>
													</div>
												</li>
											</ul>
											<?php if (($counter % ($number_of_columns * $number_of_rows)) == 0) { ?>
													</div>
												<?php } ?>
											<?php endwhile; ?>
											<?php if ((($counter % ($number_of_columns * $number_of_rows)) != 0) && ($counter >= ($number_of_columns * $number_of_rows))) { ?>
												</div>
											<?php } ?>
											<?php wp_reset_query(); ?>
										</div>
										<?php
										if ($counter < ($number_of_columns * $number_of_rows)) { ?>
									</div>
								<?php } ?>
					</div>
					<?php if (($taxonomy_link == 1) && (!empty($category_id))) {
						$values = array(
							'orderby' => 'name',
							'order' => 'ASC',
							'taxonomy' => '' . $post_type_category . ''
						);
						$categories = get_categories($values); ?>
						<div class="pe-more-from-category">
							<?php foreach ($categories as $category) { ?>
								<?php if (in_array($category->cat_ID, $category_id)) { ?>
									<a href="<?php echo get_term_link($category); ?>"><?php echo __('More from', 'pe-recent-posts'); ?> <?php echo $category->cat_name; ?></a><br />
								<?php } ?>
							<?php } ?>
						</div>
					<?php } ?>
				<?php } ?>
				<?php echo $after_widget; ?>
				<script>
					jQuery(document).ready(
						function($) {
							const peRecentPostscarouselContainer = $('#<?php echo $unique_id; ?> .pe-recent-posts-outer');
							const peRecentPostsArrows = $('#<?php echo $unique_id; ?> .pe-recent-posts-outer .pe-carousel-navigation-container');
							const peRecentPostsArrow = $('#<?php echo $unique_id; ?> .pe-recent-posts-outer .pe-carousel-navigation-container .carousel-control');
							const peRecentPostsIndicatorsContainer = $('#<?php echo $unique_id; ?> .carousel-indicators');
							const peRecentPostsIndicators = $('#<?php echo $unique_id; ?> .carousel-indicators li');
							const peRecentPostsPanel = $('#<?php echo $unique_id; ?> .carousel-inner > .item');
							peRecentPostscarouselContainer.carousel({
								interval: <?php echo $interval; ?>,
								pause: "<?php echo $slider_pause; ?>"
							})

							$('#<?php echo $unique_id; ?> ol.carousel-indicators li:first-child').attr('aria-selected', 'true');

							peRecentPostsArrow.on('keydown', function(event) {

								if (event.which == 32) { // Space key
									event.preventDefault();
									$(this).click();
								}

							});

							peRecentPostsIndicators.on('keydown', function(event) {

								$(this).parent().children().attr('aria-selected', 'false');
								$('#<?php echo $unique_id; ?> ol.carousel-indicators li:first-child').attr('aria-selected', 'true');

								if (event.which == 9) { // Tab key
									if (event.shiftKey) { // + Shift Tab key

										$(this).siblings('li:not(.active)').attr('tabindex', '-1');

									} else {

										event.preventDefault();
										let tabID = $(this).attr('aria-controls');
										$(this).click();
										$(this).parent().siblings('.carousel-inner').find('#' + tabID).focus();

									}

								}

								if (event.which == 13) { // Enter key
									$(this).click();
								}

								if (event.which == 39) { // Right arrow

									event.preventDefault();
									if ($(this).is(':last-child')) {
										$(this).siblings().first().focus().click().attr('aria-selected', 'true');
									} else {
										$(this).next().focus().click().attr('aria-selected', 'true');
									}

								}

								if (event.which == 37) { // Left arrow

									event.preventDefault();
									if ($(this).is(':first-child')) {
										$(this).siblings().last().focus().click().attr('aria-selected', 'true');
									} else {
										$(this).prev().focus().click().attr('aria-selected', 'true');
									}

								}

								if (event.which == 36) { // Home key

									event.preventDefault();
									$(this).parent().children('li:first-child').focus().click().attr('aria-selected', 'true');

								}

								if (event.which == 35) { // End key

									event.preventDefault();
									$(this).parent().children('li:last-child').focus().click().attr('aria-selected', 'true');

								}

							});

							peRecentPostsPanel.on('keydown', function(event) {

								let tabPanel = event.target.getAttribute('id');

								if (event.which == 9) { // Tab key

									$(this).parent('.carousel-inner').siblings('.carousel-indicators').children('li').attr('tabindex', '0');

									if (event.shiftKey) { // + Shift Tab key
										if ($(this).parent().parent().hasClass('bullets-on-board')) {
											if (!$(this).find('a').is(":focus")) {
												event.preventDefault();
												$(this).parent('.carousel-inner').siblings('.carousel-indicators').children('li[aria-controls="' + tabPanel + '"]').focus();
											}
										}

									}

								}

							});

							if (peRecentPostscarouselContainer.hasClass('one-row-mobile-on') && $(window).width() < 768) { // one row for mobiles
								$("#<?php echo $unique_id; ?> ul.thumbnails").wrap("<div class='pe-item-mobile item el-in-row-<?php echo $number_of_columns; ?>'></div>"); //wrap items
								$("#<?php echo $unique_id; ?> .pe-item-mobile").unwrap(); // remove original '.item' divs
								$("#<?php echo $unique_id; ?> .pe-item-mobile:first-of-type").addClass('active'); // add class active for the first item
								if (peRecentPostscarouselContainer.children('.carousel-indicators').length > 0) { //only if indicators (bullets) are enabled
									const peRecentPostsItemsNumber = $('.pe-item-mobile').length; // get numer of items
									$(peRecentPostsIndicators.parent('ol')).empty(); // remove indicators (bullets) items
									for (i = 0; i < peRecentPostsItemsNumber; i++) { // add bullets for mobile
										peRecentPostsIndicatorsContainer.append('<li data-target="#myCarousel-<?php echo $unique_id; ?>" data-slide-to="' + i + '" tabindex="0">' + i + '</li>');
									}
									$(peRecentPostsIndicatorsContainer).children('li:first-child').addClass('active'); // add class active for the first bullet
								}
							}
						}
					);
				</script>
			<?php }

		//Admin Form
		public function form($setup)
		{
			$setup = wp_parse_args((array) $setup, array(
				'title' => __('MISC Posts', 'pe-recent-posts'),
				'title_show' => '1',
				'title_linkable' => '1',
				'readmore' => '0',
				'header_tag' => '5',
				'create_date' => '0',
				'author' => '0',
				'taxonomy_list' => '0',
				'post_type' => 'post',
				'post_type_category' => 'category',
				'taxonomy_link' => '0',
				'sticky_posts' => '0',
				'number_of_all_items' => '9',
				'number_of_columns' => '1',
				'one_row_mobile' => '0',
				'number_of_rows' => '3',
				'order_posts' => 'Date',
				'order_direction' => 'DESC',
				'navigation_way' => '1',
				'title' => __('PE Recent Posts', 'pe-recent-posts'),
				'desc_limit' => '55',
				'image_alignment' => 'left',
				'show_thumbnail' => '1',
				'thumbnail_linkable' => '1',
				'image_size' => 'thumbnail',
				'grid_spacing' => '10',
				'interval' => '5000',
				'slider_pause' => 'null',
				'category_id' => ''
			));
			$title_widget = esc_attr($setup['title']);
			$post_type = $setup['post_type'];
			$post_type_category = $setup['post_type_category'];
			$taxonomy_link = $setup['taxonomy_link'];
			$sticky_posts = $setup['sticky_posts'];
			$title_show = $setup['title_show'];
			$title_linkable = $setup['title_linkable'];
			$readmore = $setup['readmore'];
			$header_tag = $setup['header_tag'];
			$create_date = $setup['create_date'];
			$author = $setup['author'];
			$taxonomy_list = $setup['taxonomy_list'];
			$number_of_all_items = $setup['number_of_all_items'];
			$order_posts = $setup['order_posts'];
			$desc_limit = $setup['desc_limit'];
			$show_thumbnail = $setup['show_thumbnail'];
			$thumbnail_linkable = $setup['thumbnail_linkable'];
			$image_alignment = $setup['image_alignment'];
			$image_size = $setup['image_size'];
			$category_id = $setup['category_id'];
			$number_of_rows = $setup['number_of_rows'];
			$number_of_columns = $setup['number_of_columns'];
			$one_row_mobile = $setup['one_row_mobile'];
			$grid_spacing = $setup['grid_spacing'];
			$interval = $setup['interval'];
			$slider_pause = $setup['slider_pause'];
			?>
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'pe-recent-posts'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title_widget; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post Type', 'pe-recent-posts'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>" type="text" value="<?php echo $post_type; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('post_type_category'); ?>"><?php _e('Post Type Taxonomy', 'pe-recent-posts'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('post_type_category'); ?>" name="<?php echo $this->get_field_name('post_type_category'); ?>" type="text" value="<?php echo $post_type_category; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('category_id'); ?>"><?php _e('Taxonomy items (empty items are not displayed)', 'pe-recent-posts'); ?></label>
					<?php
					$values = array(
						'orderby' => 'name',
						'order' => 'ASC',
						'taxonomy' => '' . $post_type_category . ''
					);
					$categories = get_categories($values);
					if (!empty($categories)) :
					?>
						<?php
						echo '<div style="max-height:150px; overflow:auto; border:1px solid #dfdfdf; padding:5px; margin-bottom:5px;">';
						echo '<ul class="pe-recent-posts-id-list categories-id-list">';
						foreach ($categories as $category) {
							if ($category_id) {
								$checked = in_array($category->cat_ID, $category_id) ? ' checked="checked"' : '';
							} else {
								$checked = '';
							}
							$option = '<li><input type="checkbox" name="' . $this->get_field_name('category_id') . '[]" id="page-' . $category->cat_ID . '" value="' . $category->cat_ID . '" ' . $checked . '>';
							$option .= '<span>(ID: ' . $category->cat_ID . ') ' . $category->cat_name . '</span></li>';
							echo $option;
						}
						echo '</ul>';
						echo '</div>';
						?>
					<?php endif; ?>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('taxonomy_link'); ?>"><?php _e('Archive link at the bottom (only if taxonomy was selected)', 'pe-recent-posts'); ?></label>
					<select class="pe-recent-posts-source-select" name="<?php echo $this->get_field_name('taxonomy_link'); ?>" id="<?php echo $this->get_field_id('taxonomy_link'); ?>">
						<option value="0" <?php selected($setup['taxonomy_link'], '0'); ?>><?php _e('Hide', 'pe-recent-posts'); ?></option>
						<option value="1" <?php selected($setup['taxonomy_link'], '1'); ?>><?php _e('Show', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('sticky_posts'); ?>"><?php _e('Force display sticky posts (only for posts)', 'pe-recent-posts'); ?></label>
					<select class="pe-recent-posts-source-select" name="<?php echo $this->get_field_name('sticky_posts'); ?>" id="<?php echo $this->get_field_id('sticky_posts'); ?>">
						<option value="0" <?php selected($setup['sticky_posts'], '0'); ?>><?php _e('Yes', 'pe-recent-posts'); ?></option>
						<option value="1" <?php selected($setup['sticky_posts'], '1'); ?>><?php _e('No', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('number_of_columns'); ?>"><?php _e('Number of items in row', 'pe-recent-posts'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('number_of_columns'); ?>" name="<?php echo $this->get_field_name('number_of_columns'); ?>" type="text" value="<?php echo $number_of_columns; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('number_of_rows'); ?>"><?php _e('Number of rows', 'pe-recent-posts'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('number_of_rows'); ?>" name="<?php echo $this->get_field_name('number_of_rows'); ?>" type="text" value="<?php echo $number_of_rows; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('one_row_mobile'); ?>"><?php _e('Make one row for mobile ( <768px )', 'pe-recent-posts'); ?></label>
					<select name="<?php echo $this->get_field_name('one_row_mobile'); ?>" id="<?php echo $this->get_field_id('one_row_mobile'); ?>">
						<option value="0" <?php selected($setup['one_row_mobile'], '0'); ?>><?php _e('No', 'pe-recent-posts'); ?></option>
						<option value="1" <?php selected($setup['one_row_mobile'], '1'); ?>><?php _e('Yes', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('number_of_all_items'); ?>"><?php _e('Number of all items', 'pe-recent-posts'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('number_of_all_items'); ?>" name="<?php echo $this->get_field_name('number_of_all_items'); ?>" type="text" value="<?php echo $number_of_all_items; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('title_show'); ?>"><?php _e('Show Post Title', 'pe-recent-posts'); ?></label>
					<select class="pe-recent-posts-source-select" name="<?php echo $this->get_field_name('title_show'); ?>" id="<?php echo $this->get_field_id('title_show'); ?>">
						<option value="0" <?php selected($setup['title_show'], '0'); ?>><?php _e('No', 'pe-recent-posts'); ?></option>
						<option value="1" <?php selected($setup['title_show'], '1'); ?>><?php _e('Yes', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('title_linkable'); ?>"><?php _e('Post Title Linkable', 'pe-recent-posts'); ?></label>
					<select class="pe-recent-posts-source-select" name="<?php echo $this->get_field_name('title_linkable'); ?>" id="<?php echo $this->get_field_id('title_linkable'); ?>">
						<option value="0" <?php selected($setup['title_linkable'], '0'); ?>><?php _e('No', 'pe-recent-posts'); ?></option>
						<option value="1" <?php selected($setup['title_linkable'], '1'); ?>><?php _e('Yes', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('header_tag'); ?>"><?php _e('Header Tag For Title', 'pe-recent-posts'); ?></label>
					<select class="pe-recent-posts-source-select" name="<?php echo $this->get_field_name('header_tag'); ?>" id="<?php echo $this->get_field_id('header_tag'); ?>">
						<option value="1" <?php selected($setup['header_tag'], '1'); ?>><?php _e('H1', 'pe-recent-posts'); ?></option>
						<option value="2" <?php selected($setup['header_tag'], '2'); ?>><?php _e('H2', 'pe-recent-posts'); ?></option>
						<option value="3" <?php selected($setup['header_tag'], '3'); ?>><?php _e('H3', 'pe-recent-posts'); ?></option>
						<option value="4" <?php selected($setup['header_tag'], '4'); ?>><?php _e('H4', 'pe-recent-posts'); ?></option>
						<option value="5" <?php selected($setup['header_tag'], '5'); ?>><?php _e('H5', 'pe-recent-posts'); ?></option>
						<option value="6" <?php selected($setup['header_tag'], '6'); ?>><?php _e('H6', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('create_date'); ?>"><?php _e('Creation Date', 'pe-recent-posts'); ?></label>
					<select class="pe-recent-posts-source-select" name="<?php echo $this->get_field_name('create_date'); ?>" id="<?php echo $this->get_field_id('create_date'); ?>">
						<option value="0" <?php selected($setup['create_date'], '0'); ?>><?php _e('Hide', 'pe-recent-posts'); ?></option>
						<option value="1" <?php selected($setup['create_date'], '1'); ?>><?php _e('Show above title', 'pe-recent-posts'); ?></option>
						<option value="2" <?php selected($setup['create_date'], '2'); ?>><?php _e('Show below title', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('author'); ?>"><?php _e('Author', 'pe-recent-posts'); ?></label>
					<select class="pe-recent-posts-source-select" name="<?php echo $this->get_field_name('author'); ?>" id="<?php echo $this->get_field_id('author'); ?>">
						<option value="0" <?php selected($setup['author'], '0'); ?>><?php _e('Hide', 'pe-recent-posts'); ?></option>
						<option value="1" <?php selected($setup['author'], '1'); ?>><?php _e('Show', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('taxonomy_list'); ?>"><?php _e('Post type taxonomies', 'pe-recent-posts'); ?></label>
					<select class="pe-recent-posts-source-select" name="<?php echo $this->get_field_name('taxonomy_list'); ?>" id="<?php echo $this->get_field_id('taxonomy_list'); ?>">
						<option value="0" <?php selected($setup['taxonomy_list'], '0'); ?>><?php _e('Hide', 'pe-recent-posts'); ?></option>
						<option value="1" <?php selected($setup['taxonomy_list'], '1'); ?>><?php _e('Show', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('readmore'); ?>"><?php _e('Readmore', 'pe-recent-posts'); ?></label>
					<select class="pe-recent-posts-source-select" name="<?php echo $this->get_field_name('readmore'); ?>" id="<?php echo $this->get_field_id('readmore'); ?>">
						<option value="0" <?php selected($setup['readmore'], '0'); ?>><?php _e('Hide', 'pe-recent-posts'); ?></option>
						<option value="1" <?php selected($setup['readmore'], '1'); ?>><?php _e('Show', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('order_direction'); ?>"><?php _e('Order Direction', 'pe-recent-posts'); ?></label>
					<select name="<?php echo $this->get_field_name('order_direction'); ?>" id="<?php echo $this->get_field_id('order_direction'); ?>">
						<option value="ASC" <?php selected($setup['order_direction'], 'ASC'); ?>><?php _e('ASC', 'pe-recent-posts'); ?></option>
						<option value="DESC" <?php selected($setup['order_direction'], 'DESC'); ?>><?php _e('DESC', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('order_posts'); ?>"><?php _e('Ordering', 'pe-recent-posts'); ?></label>
					<select name="<?php echo $this->get_field_name('order_posts'); ?>" id="<?php echo $this->get_field_id('order_posts'); ?>">
						<option value="date" <?php selected($setup['order_posts'], 'date'); ?>><?php _e('Date', 'pe-recent-posts'); ?></option>
						<option value="title" <?php selected($setup['order_posts'], 'title'); ?>><?php _e('Title', 'pe-recent-posts'); ?></option>
						<option value="comment_count" <?php selected($setup['order_posts'], 'comment_count'); ?>><?php _e('Most commented', 'pe-recent-posts'); ?></option>
						<option value="meta_value_num" <?php selected($setup['order_posts'], 'meta_value_num'); ?>><?php _e('Most read', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('navigation_way'); ?>"><?php _e('Navigation', 'pe-recent-posts'); ?></label>
					<select name="<?php echo $this->get_field_name('navigation_way'); ?>" id="<?php echo $this->get_field_id('navigation_way'); ?>">
						<option value="0" <?php selected($setup['navigation_way'], '0'); ?>><?php _e('None', 'pe-recent-posts'); ?></option>
						<option value="1" <?php selected($setup['navigation_way'], '1'); ?>><?php _e('Bullets', 'pe-recent-posts'); ?></option>
						<option value="2" <?php selected($setup['navigation_way'], '2'); ?>><?php _e('Arrows (prev/next)', 'pe-recent-posts'); ?></option>
						<option value="3" <?php selected($setup['navigation_way'], '3'); ?>><?php _e('Arrow (up/down)', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('desc_limit'); ?>"><?php _e('Description Limit (chars)', 'pe-recent-posts'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('desc_limit'); ?>" name="<?php echo $this->get_field_name('desc_limit'); ?>" type="text" value="<?php echo $desc_limit; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('image_alignment'); ?>"><?php _e('Image Alignment', 'pe-recent-posts'); ?></label>
					<select name="<?php echo $this->get_field_name('image_alignment'); ?>" id="<?php echo $this->get_field_id('image_alignment'); ?>">
						<option value="left" <?php selected($setup['image_alignment'], 'left'); ?>><?php _e('left', 'pe-recent-posts'); ?></option>
						<option value="right" <?php selected($setup['image_alignment'], 'right'); ?>><?php _e('right', 'pe-recent-posts'); ?></option>
						<option value="top" <?php selected($setup['image_alignment'], 'top'); ?>><?php _e('top', 'pe-recent-posts'); ?></option>
						<option value="bottom" <?php selected($setup['image_alignment'], 'bottom'); ?>><?php _e('bottom', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('show_thumbnail'); ?>"><?php _e('Show Thumbnail', 'pe-recent-posts'); ?></label>
					<select name="<?php echo $this->get_field_name('show_thumbnail'); ?>" id="<?php echo $this->get_field_id('show_thumbnail'); ?>">
						<option value="0" <?php selected($setup['show_thumbnail'], '0'); ?>><?php _e('No', 'pe-recent-posts'); ?></option>
						<option value="1" <?php selected($setup['show_thumbnail'], '1'); ?>><?php _e('Yes', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('thumbnail_linkable'); ?>"><?php _e('Thumbnail Linkable', 'pe-recent-posts'); ?></label>
					<select name="<?php echo $this->get_field_name('thumbnail_linkable'); ?>" id="<?php echo $this->get_field_id('thumbnail_linkable'); ?>">
						<option value="0" <?php selected($setup['thumbnail_linkable'], '0'); ?>><?php _e('No', 'pe-recent-posts'); ?></option>
						<option value="1" <?php selected($setup['thumbnail_linkable'], '1'); ?>><?php _e('Yes', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('interval'); ?>"><?php _e('Interval in ms ( 0 - autoplay is disabled )', 'pe-recent-posts'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('interval'); ?>" name="<?php echo $this->get_field_name('interval'); ?>" type="text" value="<?php echo $interval; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('slider_pause'); ?>"><?php _e('Pause on hover', 'pe-recent-posts'); ?></label>
					<select name="<?php echo $this->get_field_name('slider_pause'); ?>" id="<?php echo $this->get_field_id('slider_pause'); ?>">
						<option value="hover" <?php selected($setup['slider_pause'], 'hover'); ?>><?php _e('Yes', 'pe-recent-posts'); ?></option>
						<option value="null" <?php selected($setup['slider_pause'], 'null'); ?>><?php _e('No', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('image_size'); ?>"><?php _e('Image Size', 'pe-recent-posts'); ?></label>
					<select name="<?php echo $this->get_field_name('image_size'); ?>" id="<?php echo $this->get_field_id('image_size'); ?>">
						<option value="thumbnail" <?php selected($setup['image_size'], 'thumbnail'); ?>><?php _e('thumbnail', 'pe-recent-posts'); ?></option>
						<option value="medium" <?php selected($setup['image_size'], 'medium'); ?>><?php _e('medium', 'pe-recent-posts'); ?></option>
						<option value="large" <?php selected($setup['image_size'], 'large'); ?>><?php _e('large', 'pe-recent-posts'); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('grid_spacing'); ?>"><?php _e('Grid Spacing (px)', 'pe-recent-posts'); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('grid_spacing'); ?>" name="<?php echo $this->get_field_name('grid_spacing'); ?>" type="text" value="<?php echo $grid_spacing; ?>" />
				</p>
	<?php
		}
		//Update widget
		public function update($new_setup, $old_setup)
		{
			$setup = $old_setup;
			$setup['title'] = strip_tags($new_setup['title']);
			$setup['post_type'] = $new_setup['post_type'];
			$setup['post_type_category'] = $new_setup['post_type_category'];
			$setup['taxonomy_link'] = $new_setup['taxonomy_link'];
			$setup['sticky_posts'] = $new_setup['sticky_posts'];
			$setup['title_show'] = $new_setup['title_show'];
			$setup['title_linkable'] = $new_setup['title_linkable'];
			$setup['readmore'] = $new_setup['readmore'];
			$setup['header_tag'] = $new_setup['header_tag'];
			$setup['create_date'] = $new_setup['create_date'];
			$setup['author'] = $new_setup['author'];
			$setup['taxonomy_list'] = $new_setup['taxonomy_list'];
			$setup['number_of_all_items']  = $new_setup['number_of_all_items'];
			$setup['number_of_columns']  = $new_setup['number_of_columns'];
			$setup['one_row_mobile']  = $new_setup['one_row_mobile'];
			$setup['number_of_rows']  = $new_setup['number_of_rows'];
			$setup['order_posts']  = $new_setup['order_posts'];
			$setup['order_direction']  = $new_setup['order_direction'];
			$setup['navigation_way']  = $new_setup['navigation_way'];
			$setup['desc_limit']  = strip_tags($new_setup['desc_limit']);
			$setup['image_alignment']  = $new_setup['image_alignment'];
			$setup['show_thumbnail']  = $new_setup['show_thumbnail'];
			$setup['thumbnail_linkable']  = $new_setup['thumbnail_linkable'];
			$setup['image_size']  = $new_setup['image_size'];
			$setup['category_id'] = $new_setup['category_id'];
			$setup['grid_spacing']  = strip_tags($new_setup['grid_spacing']);
			$setup['interval']  = strip_tags($new_setup['interval']);
			$setup['slider_pause']  = strip_tags($new_setup['slider_pause']);
			return $setup;
		}
	}
}
//add CSS
function pe_recent_posts_css()
{
	if (!(wp_style_is('animate.css', 'enqueued'))) {
		wp_enqueue_style('animate', plugins_url() . '/pe-recent-posts/css/animate.css');
	}
	if (!(wp_style_is('font-awesome.min.css', 'enqueued'))) {
		wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
	}
	wp_enqueue_style('pe-recent-posts', plugins_url() . '/pe-recent-posts/css/pe-recent-posts.css');
}
add_action('wp_enqueue_scripts', 'pe_recent_posts_css', 20);

//add JS
function pe_recent_posts_js()
{
	wp_enqueue_script('jquery');
	if (!(wp_script_is('bootstrap.js', 'enqueued') || wp_script_is('bootstrap.min.js', 'enqueued'))) {
		wp_register_script('bootstrap.min', plugins_url() . '/pe-recent-posts/js/bootstrap.min.js', array('jquery'), '3.3.0', false);
		wp_enqueue_script('bootstrap.min');
	}
}
add_action('wp_enqueue_scripts', 'pe_recent_posts_js', 1);

//load widget
function pe_recent_posts_register_widget()
{
	return register_widget('PE_Recent_Posts_Plugin');
}
add_action('widgets_init', 'pe_recent_posts_register_widget');

//enable translations
add_action('plugins_loaded', 'pe_recent_posts_textdomain');
function pe_recent_posts_textdomain()
{
	load_plugin_textdomain('pe-recent-posts', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
	?>