<?php

if ( ! function_exists( 'authors_list_sc' ) ) :

	/**
	 * Shortcode
	 *
	 * @since 1.0.0
	 */
	function authors_list_sc( $atts = false, $content = false ) {

		// if no atts supplied make it an empty array
		if ( ! $atts ) $atts = array();
		// default values
		$defaults = array(
			'style' => '1',
            'columns' => '4',
            'columns_direction' => 'horizontal',
			'avatar_size' => 500,
            'amount' => false,
			'show_avatar' => 'yes',
			'show_title' => 'yes',
			'show_count' => 'yes',
			'show_bio' => 'yes',
            'show_link' => 'yes',
            'orderby' => 'post_count',
            'order' => 'DESC',
            'skip_empty' => 'yes',
            'minimum_posts_count' => 0,
            'bio_word_trim' => false,
            'only_authors' => 'yes',
            'exclude' => '',
            'include' => '',
            'roles' => '',
            'latest_post_after' => '',
            'post_types' => '',
            'name_starts_with' => '',
            'link_to' => 'archive',
            'link_to_meta_key' => '',
            'pagination' => 'no',
            
            'categories' => '',
            'taxonomy' => '',
            'terms' => '',

            'before_avatar' => '',
            'before_title' => '',
            'before_count' => '',
            'before_bio' => '',
            'before_link' => '',

            'after_avatar' => '',
            'after_title' => '',
            'after_count' => '',
            'after_bio' => '',
            'after_link' => '',

            'bp_member_types' => '',
		);

		// merge settings
        $settings = array_merge( $defaults, $atts );            

        // atts
        $atts = array(
	        'fields'  => 'ID',
	        'orderby' => $settings['orderby'],
	        'order'   => $settings['order'],
        );

        // pagination
        if ( $settings['pagination'] == 'yes' && $settings['amount'] ) {
            $total_users = count_users();
            $total_users = $total_users['avail_roles']['author'];
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            $total_pages = ceil( $total_users / $settings['amount'] );
            $atts['number'] = $settings['amount'];
            $atts['offset'] = $paged ? ($paged - 1) * $settings['amount'] : 0;
        }

        // get last post date
        $get_last_post_date = true;
        if ( $settings['orderby'] == 'post_date' || ! empty( $settings['latest_post_after'] ) ) {
            $get_last_post_date = true;
        }
        // order by last name
        if ( $settings['orderby'] == 'last_name' ) {
            $atts['meta_key'] = 'last_name';
            $atts['orderby'] = 'meta_value';
        }

        // order by first name
        if ( $settings['orderby'] == 'first_name' ) {
            $atts['meta_key'] = 'first_name';
            $atts['orderby'] = 'meta_value';
        }
        
        // only authors
        if ( $settings['only_authors'] == 'yes' ) {
            $atts['who'] = 'colunistas';
        }

        // exclude
        if ( ! empty( $settings['exclude'] ) ) {
            $atts['exclude'] = explode( ',', $settings['exclude'] );
        }

        // include
        if ( ! empty( $settings['include'] ) ) {
            $atts['include'] = explode( ',', $settings['include'] );
        }

        // switch "categories" to "taxonomy" and "terms"
        if ( ! empty( $settings['categories'] ) ) {
            $settings['taxonomy'] = 'category';
            $settings['terms'] = $settings['categories'];
        }

        // default taxonomy
        if ( empty( $settings['taxonomy'] ) ) {
            $settings['taxonomy'] = 'category';
        }

        // include based on taxonomy/term
        if ( ! empty( $settings['terms'] ) ) {
            
            // array of supplied categories
            $terms = explode( ',', $settings['terms'] );

            // query arguments
            $args = array(
                'posts_per_page'         => -1,
                'orderby'                => 'author',
                'order'                  => 'ASC',
                'cache_results'          => false,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'tax_query'              => array(
                    array(
                        'taxonomy'         => $settings['taxonomy'],
                        'terms'            => $terms,
                        'include_children' => true
                    )
                )
            );

            // get posts
            $posts_array = get_posts( $args );
            
            // get author IDs
            $post_author_ids = false;
            if ( $posts_array ) {
                $post_author_ids = wp_list_pluck( $posts_array, 'post_author' );
                $post_author_ids = array_unique( $post_author_ids );
            }

            if ( is_array( $post_author_ids ) ) {
                if ( empty( $atts['include'] ) ) {
                    $atts['include'] = $post_author_ids;
                } else {
                    $atts['include'] = array_merge( $atts['include'], $post_author_ids );
                }
            }            

        }

        // roles
        if ( ! empty( $settings['roles'] ) ) {
            $atts['role__in'] = explode( ',', $settings['roles'] );
            unset( $atts['who'] );
        }

        // post types
        if ( ! empty( $settings['post_types'] ) ) {
            $atts['has_published_posts'] = explode( ',', $settings['post_types'] );
            unset( $atts['who'] );
            $settings['skip_empty'] = 'no';
        }

        // limit by first letter
        if ( ! empty( $settings['name_starts_with'] ) ) {
            $atts['search'] = sanitize_text_field( $settings['name_starts_with'] ) . '*';
            $atts['search_columns'] = array(
                'display_name',
            );
        }

		// get authors order by post count
		$authors_ids = get_users( $atts );

		// start output buffer
		ob_start();

		$item_class = '';
		switch ( $settings['columns'] ) {
			case '4':
				$item_class .= 'authors-list-col-3';
				break;
			case '3':
				$item_class .= 'authors-list-col-4';
				break;
			case '2':
				$item_class .= 'authors-list-col-6';
				break;
		}

        $output_items = array();
        $count = 0;
        $i = 1;
        ?><style>
            .authors-list-cols-dir-horizontal .authors-list-col {
                display: block;
                float: left;
                margin-right: 1.42%
            }

            .authors-list-cols-dir-horizontal .authors-list-col-1 {
                width: 5.198%
            }

            .authors-list-cols-dir-horizontal .authors-list-col-2 {
                width: 13.81%
            }

            .authors-list-cols-dir-horizontal .authors-list-col-3 {
                width: 22.43%
            }

            .authors-list-cols-dir-horizontal .authors-list-col-4 {
                width: 31.05%
            }

            .authors-list-cols-dir-horizontal .authors-list-col-5 {
                width: 39.67%
            }

            .authors-list-cols-dir-horizontal .authors-list-col-6 {
                width: 48.29%
            }

            .authors-list-cols-dir-horizontal .authors-list-col-7 {
                width: 56.9%
            }

            .authors-list-cols-dir-horizontal .authors-list-col-8 {
                width: 65.52%
            }

            .authors-list-cols-dir-horizontal .authors-list-col-9 {
                width: 74.14%
            }

            .authors-list-cols-dir-horizontal .authors-list-col-10 {
                width: 82.76%
            }

            .authors-list-cols-dir-horizontal .authors-list-col-11 {
                width: 91.38%
            }

            .authors-list-cols-dir-horizontal .authors-list-col-12 {
                width: 100%
            }

            .authors-list-cols-dir-horizontal .authors-list-col-last {
                margin-right: 0
            }

            .authors-list-cols-dir-horizontal .authors-list-col-first {
                clear: both
            }

            .authors-list-cols-dir-horizontal.authors-list-cols-2 .authors-list-col:nth-child(2n) {
                margin-right: 0
            }

            .authors-list-cols-dir-horizontal.authors-list-cols-2 .authors-list-col:nth-child(2n+1) {
                clear: both
            }

            .authors-list-cols-dir-horizontal.authors-list-cols-3 .authors-list-col:nth-child(3n) {
                margin-right: 0
            }

            .authors-list-cols-dir-horizontal.authors-list-cols-3 .authors-list-col:nth-child(3n+1) {
                clear: both
            }

            .authors-list-cols-dir-horizontal.authors-list-cols-4 .authors-list-col:nth-child(4n) {
                margin-right: 0
            }

            .authors-list-cols-dir-horizontal.authors-list-cols-4 .authors-list-col:nth-child(4n+1) {
                clear: both
            }

            .authors-list-cols-dir-vertical {
                column-gap: 3.42%
            }

            .authors-list-cols-dir-vertical.authors-list-cols-2 {
                column-count: 2
            }

            .authors-list-cols-dir-vertical.authors-list-cols-3 {
                column-count: 3
            }

            .authors-list-cols-dir-vertical.authors-list-cols-3 {
                column-count: 3
            }

            .authors-list-cols-dir-vertical.authors-list-cols-4 {
                column-count: 4
            }

            .authors-list-clearfix:after, .authors-list-clearfix:before {
                content: " ";
                display: table
            }

            .authors-list-clearfix:after {
                clear: both
            }

            .authors-list-item {
                margin-bottom: 8px;
                position: relative
            }

            .authors-list-cols-dir-vertical .authors-list-item {
                break-inside: avoid-column;
                page-break-inside: avoid
            }

            .authors-list-item-thumbnail {
                margin-bottom: 20px;
                position: relative
            }

            .authors-list-item-thumbnail a, .authors-list-item-thumbnail img {
                display: block;
                position: relative;
                border: 0
            }

            .authors-list-item-thumbnail img {
                max-width: 100%;
                height: auto
            }

            .authors-list-item-title {
                font-size: 22px;
                font-weight: 700;
                margin-bottom: 5px;
                line-height: 1.2;
            }

            .authors-list-item-title a {
                color: inherit
            }

            .authors-list-item-subtitle {
                margin-bottom: 5px;
                font-size: 80%
            }

            .authors-list-item-social {
                margin-bottom: 10px
            }

            .authors-list-item-social a {
                font-size: 15px;
                margin-right: 5px;
                text-decoration: none
            }

            .authors-list-item-social svg {
                width: 15px
            }

            .authors-list-item-social-facebook {
                fill: #3b5998
            }

            .authors-list-item-social-instagram {
                fill: #405de6
            }

            .authors-list-item-social-linkedin {
                fill: #0077b5
            }

            .authors-list-item-social-pinterest {
                fill: #bd081c
            }

            .authors-list-item-social-tumblr {
                fill: #35465c
            }

            .authors-list-item-social-twitter {
                fill: #1da1f2
            }

            .authors-list-item-social-youtube {
                fill: red
            }

            .authors-list-item-excerpt {
                margin-bottom: 10px
            }

            .authors-list-items-s2 .authors-list-item-main {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                padding: 30px;
                color: #fff;
                background: rgba(0, 0, 0, .3)
            }

            .authors-list-items-s2 .authors-list-item-thumbnail {
                margin-bottom: 0
            }

            .authors-list-items-s2 .authors-list-item-title {
                color: inherit
            }

            .authors-list-items-s2 .authors-list-item-link {
                color: inherit
                font-size: 14px;
            }

            .authors-list-items-s3 .authors-list-item-thumbnail {
                margin-bottom: 0
            }

            .authors-list-items-s3 .authors-list-item-main {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                top: 0;
                padding: 30px;
                opacity: 0;
                transform: scale(0);
                transition: all .3s;
                background: #fff;
                border: 2px solid #eee
            }

            .authors-list-items-s3 .authors-list-item:hover .authors-list-item-main {
                opacity: 1;
                transform: scale(1)
            }

            .authors-list-items-s4 .authors-list-item-thumbnail {
                float: left;
                margin-right: 20px;
                width: 25%
            }

            .authors-list-item-s4 .authors-list-item-main {
                overflow: hidden
            }

            .author-list-item-after-avatar, .author-list-item-after-bio, .author-list-item-after-count, .author-list-item-after-link, .author-list-item-after-title, .author-list-item-before-avatar, .author-list-item-before-bio, .author-list-item-before-count, .author-list-item-before-link, .author-list-item-before-title {
                margin-bottom: 5px
            }

            @media only screen and (max-width: 767px) {
                .authors-list-cols-dir-horizontal .authors-list-col {
                    width: 100%;
                    margin-right: 0 !important
                }

                .authors-list-cols-dir-vertical {
                    column-count: 1 !important
                }
            }

            .authors-list-pagination {
                text-align: center
            }

            .authors-list-pagination li {
                display: inline-block;
                margin: 0 2px
            }
            a.authors-list-item-link {
                letter-spacing: -0.5px;
            }

            .authors-list-pagination li a, .authors-list-pagination li > span {
                display: inline-block;
                border: 1px solid rgba(0, 0, 0, .2);
                padding: 10px;
                line-height: 1
            }</style>
        <?php ?><div id="owl-carousel-authors" class="owl-carousel-authors owl-carousel authors-list-items authors-list-items-s<?php echo $settings['style']; ?> authors-list-clearfix authors-list-cols-<?php echo esc_attr( $settings['columns'] ); ?> authors-list-cols-dir-<?php echo esc_attr( $settings['columns_direction'] ); ?>"><?php

			// loop through each author
			foreach ( $authors_ids as $author_id ) : $count++;

                // get post count
                $post_types = 'post';
                if ( ! empty( $settings['post_types'] ) ) {
                    $post_types = explode( ',', $settings['post_types'] );
                }
                $post_count = count_user_posts( $author_id, $post_types, true );

				// no posts, end
				if ( ! $post_count && $settings['skip_empty'] == 'yes' ) {
					continue;
                }

                // less than minimum posts, end
                if ( $post_count < $settings['minimum_posts_count'] ) {
                    continue;
                }

                // buddypress member type
                if ( ! empty( $settings['bp_member_types'] ) && function_exists( 'bp_get_member_type' ) ) {
                    $bp_member_types = explode( ',', $settings['bp_member_types'] );
                    if ( ! in_array( bp_get_member_type( $author_id ), $bp_member_types ) ) {
                        continue;
                    }
                }

                // get last post date if needed
                if ( $get_last_post_date ) {

                    // skip if no posts
                    if ( ! $post_count ) {
                        $latest_post_date_unix = 1;
                    }

                    // get latest post
                    $latest_post = get_posts(array(
                        'author'      => $author_id,
                        'orderby'     => 'date',
                        'order'       => 'desc',
                        'numberposts' => 1
                    ));



                    $latest_post_date_unix = strtotime( $latest_post[0]->post_date );
                    $latest_post_date_ymd = date( 'yyyymmdd', strtotime( $latest_post[0]->post_date ) );

                } else {

                    $latest_post_date_unix = 1;
                    $latest_post_date_ymd = 1;

                }
                
                // skip if latest post older than date limit
                if ( ! empty( $settings['latest_post_after'] ) ) {

                    // skip if no posts
                    if ( ! $post_count ) {
                        continue;
                    }

                    if ( $settings['latest_post_after'] == 'daily' ) {
                        $latest_post_date = $latest_post_date_ymd;
                        $limit_post_date = current_time( 'yyyymmdd' );
                    } else {
                        $latest_post_date = $latest_post_date_unix;
                        $limit_post_date = strtotime( $settings['latest_post_after'] . ' days ago' );
                    }

                    if ( $latest_post_date < $limit_post_date ) {
                        continue;
                    }

                }

                // for ordering by comment count
                $comment_count = 0;
                if ( $settings['orderby'] == 'comment_count' ) {
                    $comment_count = get_comments( array(
                        'post_author' => $author_id,
                        'fields' => 'ids',
                        'count' => true,
                        'update_comment_meta_cache' => false,
                        'update_comment_post_cache' => false,
                    ));
                }

				// vars
				$name = get_the_author_meta( 'display_name', $author_id );
				$bio = get_the_author_meta( 'description', $author_id );
                $posts_url = get_author_posts_url( $author_id );

                if ( $settings['link_to'] == 'bbpress_profile' && function_exists( 'bbp_get_user_profile_link' ) ) {
                    $posts_url = bbp_get_user_profile_url( $author_id );
                }

                if ( $settings['link_to'] == 'buddypress_profile' && function_exists( 'bp_core_get_user_domain' ) ) {
                    $posts_url = bp_core_get_user_domain( $author_id );
                }

                if ( $settings['link_to'] == 'meta' && ! empty( $settings['link_to_meta_key'] ) ) {
                    $posts_url = authors_list_get_meta( $author_id, sanitize_text_field( $settings['link_to_meta_key'] ) );
                }

                // start output buffer
                ob_start();
				?>

                <?php

                if($count == $i ) : ?><div class="item">

                <?php endif; ?>

                <div class="authors-list-item authors-list-item-clearfix authors-list-col <?php echo esc_attr( $item_class ); ?>">

                    <?php if ( $settings['show_avatar'] == 'yes' ) : ?>
						<div class="authors-list-item-thumbnail col span_4">
                            <?php if ( $settings['before_avatar'] ) : ?>
                                <div class="author-list-item-before-avatar"><?php echo authors_list_parse_vars( $author_id, $settings['before_avatar'] ); ?></div>
                            <?php endif; ?>
							<a href="<?php echo $posts_url; ?>">
                                <?php echo get_avatar( $author_id, $settings['avatar_size'] ); ?>
                            </a>
                            <?php if ( $settings['after_avatar'] ) : ?>
                                <div class="author-list-item-after-avatar"><?php echo authors_list_parse_vars( $author_id, $settings['after_avatar'] ); ?></div>
                            <?php endif; ?>
						</div>

                        <!-- .team-item-thumbnail -->

					<?php endif; ?>

					<div class="authors-list-item-main col span_8">
                        <?php if ( $settings['before_title'] ) : ?>
                            <div class="author-list-item-before-title"><?php echo authors_list_parse_vars( $author_id, $settings['before_title'] ); ?></div>
                        <?php endif; ?>

						<?php if ( $settings['show_title'] == 'yes' ) : ?>
							<div class="authors-list-item-title vermelho"><a href="<?php echo $posts_url; ?>"><?php echo esc_html( $name ); ?></a></div>
						<?php endif; ?>
                        
                        <?php if ( $settings['after_title'] ) : ?>
                            <div class="author-list-item-after-title"><?php echo authors_list_parse_vars( $author_id, $settings['after_title'] ); ?></div>
                        <?php endif; ?>

                        <?php if ( $settings['before_count'] ) : ?>
                            <div class="author-list-item-before-count"><?php echo authors_list_parse_vars( $author_id, $settings['before_count'] ); ?></div>
                        <?php endif; ?>

						<?php if ( $settings['show_count'] == 'yes' ) : ?>
							<div class="authors-list-item-subtitle"><?php echo esc_html( $post_count ); ?> <?php esc_html_e( 'posts', 'authors-list' ); ?></div>
						<?php endif; ?>

                        <?php if ( $settings['after_count'] ) : ?>
                            <div class="author-list-item-after-count"><?php echo authors_list_parse_vars( $author_id, $settings['after_count'] ); ?></div>
                        <?php endif; ?>

                        <?php if ( $settings['before_bio'] ) : ?>
                            <div class="author-list-item-before-bio"><?php echo authors_list_parse_vars( $author_id, $settings['before_bio'] ); ?></div>
                        <?php endif; ?>

						<?php if ( $settings['show_bio'] == 'yes' ) : ?>
                            <div class="authors-list-item-excerpt"><?php 
                                if ( $settings['bio_word_trim'] ) {
                                    echo wp_trim_words( $bio, intval( $settings['bio_word_trim'] ) ); 
                                } else {
                                    echo $bio;
                                }
                            ?></div>
						<?php endif; ?>

                        <?php if ( $settings['after_bio'] ) : ?>
                            <div class="author-list-item-after-bio"><?php echo authors_list_parse_vars( $author_id, $settings['after_bio'] ); ?></div>
                        <?php endif; ?>

                        <?php if ( $settings['before_link'] ) : ?>
                            <div class="author-list-item-before-link"><?php echo authors_list_parse_vars( $author_id, $settings['before_link'] ); ?></div>
                        <?php endif; ?>

						<?php if ( $settings['show_link'] == 'yes' ) : ?>
							<a href="<?php echo  get_permalink($latest_post[0]->ID );; ?>" class="authors-list-item-link">
                                <?php echo $latest_post[0]->post_title; ?></a>
						<?php endif; ?>

                        <?php if ( $settings['after_link'] ) : ?>
                            <div class="author-list-item-after-link"><?php echo authors_list_parse_vars( $author_id, $settings['after_link'] ); ?></div>
                        <?php endif; ?>

					</div><!-- .team-item-main -->

                </div><!-- .authors-list-item -->

                <?php if($count % 4 == 0) :

                    $i = $count +1;
                    ?></div>
                 <?php endif; ?>

				<?php

                $output_item = ob_get_contents();
                ob_end_clean();

                // end output buffer
                $output_items[] = array(
                    'date_unix' => $latest_post_date_unix,
                    'comment_count' => $comment_count,
                    'output'    => $output_item,
                );

				// limit reached, end
				if ( $settings['amount'] && $count >= $settings['amount'] ) {
					break;
				}

            endforeach;

            // order by latest post date
            if ( $settings['orderby'] == 'post_date' ) {
                $array_column = array_column( $output_items, 'date_unix' );
                if ( $settings['order'] == 'DESC' ) {
                    array_multisort( $array_column, SORT_DESC, SORT_NUMERIC, $output_items );
                } else {
                    array_multisort( $array_column, SORT_ASC, SORT_NUMERIC, $output_items );
                }
            }

            // order by comment count
            if ( $settings['orderby'] == 'comment_count' ) {
                $array_column = array_column( $output_items, 'comment_count' );
                if ( $settings['order'] == 'DESC' ) {
                    array_multisort( $array_column, SORT_DESC, SORT_NUMERIC, $output_items );
                } else {
                    array_multisort( $array_column, SORT_ASC, SORT_NUMERIC, $output_items );
                }
            }

            // display output
            foreach ( $output_items as $output_item ) {
                echo $output_item['output'];
            }

		?></div><!-- authors-list-items --><?php

        if ( $settings['pagination'] == 'yes' && $settings['amount'] ) :

            ?><div class="authors-list-pagination"><?php
                $current_page = max(1, get_query_var('paged'));
                echo paginate_links(array(
                    'base' => get_pagenum_link(1) . '%_%',
                    'format' => 'page/%#%/',
                    'current' => $current_page,
                    'total' => $total_pages,
                    'prev_next'    => false,
                    'type'         => 'list',
                ));
            ?></div><!-- .authors-list-pagination --><?php

        endif;
		
		$output = ob_get_contents();
		ob_end_clean();

		return $output;

	} add_shortcode( 'authors_list', 'authors_list_sc' );

endif; // end if function exists

if ( ! function_exists( 'authors_list_parse_vars' ) ) {

    /**
     * Replace {var} with user meta
     * 
     * @since 1.0.2
     */
    function authors_list_parse_vars( $user_id, $text ) {

        $text = preg_replace_callback( '/{al:([^\s]+)}/i', function( $matches ) use ( $user_id ){
            return authors_list_get_meta( $user_id, $matches[1] );
        }, $text );

        $text = preg_replace_callback( '/\{alf:([^}]+)\}/i', function( $matches ) use ( $user_id ){
            
            // no match
            if ( empty( $matches[1]) ) return;

            // get all data in an array
            $data = explode( ' ', $matches[1] );
            
            // no match for func name
            if ( empty( $data[0] ) ) return;
            
            // get function name
            $function_name = 'authors_list_display_' . $data[0];

            // no function by that name, return
            if ( ! function_exists( $function_name ) ) return;

            // any args?
            $function_args = array(
                'user_id' => $user_id,
            );
            unset( $data[0] );
            if ( count( $data ) > 0 ) {
                foreach ( $data as $data_item ) {
                    $data_item_args = explode( '=', $data_item );
                    if ( ! empty( $data_item_args[0] ) && ! empty( $data_item_args[1] ) ) {
                        $function_args[$data_item_args[0]] = trim( $data_item_args[1],"'" );
                    }

                }
            }

            return call_user_func( $function_name, $function_args );

        }, $text );

        return $text;

    }

}

if ( ! function_exists( 'authors_list_get_meta' ) ) {

    /**
     * Get meta field value
     * 
     * @since 1.0.2
     */
    function authors_list_get_meta( $user_id = false, $name = false ) {

        // no user ID and meta field supplied
        if ( ! $user_id || ! $name ) {
            return;
        }

        // get user meta
        $value = get_user_meta( $user_id, $name, true );        

        // no user meta, try userdata
        if ( ! $value ) {
            $user_data = get_userdata( $user_id );
            if ( ! empty( $user_data->data->$name ) ) {
                $value = $user_data->data->$name;
            }
        }

        // return the value
        return $value;

    }

}

if ( ! function_exists( 'authors_list_display_posts') ) {

    /**
     * Display author posts
     * 
     * @since 1.0.4
     */
    function authors_list_display_posts( $args = array() ) {

        if ( empty( $args['amount'] ) ) {
            $args['amount'] = 1;
        }

        if ( empty( $args['type'] ) ) {
            $args['type'] = 'list';
        }

        // get posts
        $posts = get_posts(array(
            'author' => $args['user_id'],
            'numberposts' => $args['amount'],
        ));

        // no posts found, return
        if ( ! $posts ) {
            return;
        }

        $el_wrap = 'ul';
        $el_item = 'li';

        if ( $args['type'] == 'plain' ) {
            $el_wrap = 'div';
            $el_item = 'div';
        }

        // output buffer
        ob_start();
        ?>
            <<?php echo $el_wrap; ?> class="authors-list-posts">
                <?php foreach ( $posts as $post ) : ?>
                    <<?php echo $el_item; ?> class="authors-list-posts-item"><a href="<?php echo get_permalink( $post->ID ); ?>"><?php echo get_the_title( $post->ID ); ?></a></<?php echo $el_item; ?>>
                <?php endforeach; ?>
            </<?php echo $el_wrap; ?>>
        <?php
        $output = ob_get_contents();
        ob_end_clean();

        // return output
        return $output;

    }

}

if ( ! function_exists( 'authors_list_display_social') ) {

    /**
     * Display author social
     * 
     * @since 1.0.8
     */
    function authors_list_display_social( $args = array() ) {

        $user_id = $args['user_id'];

        $urls = array();

        $urls['facebook'] = get_user_meta( $user_id, 'facebook', true );
        $urls['instagram'] = get_user_meta( $user_id, 'instagram', true );
        $urls['linkedin'] = get_user_meta( $user_id, 'linkedin', true );
        $urls['pinterest'] = get_user_meta( $user_id, 'pinterest', true );
        $urls['tumblr'] = get_user_meta( $user_id, 'tumblr', true );
        $urls['twitter'] = get_user_meta( $user_id, 'twitter', true );
        $urls['youtube'] = get_user_meta( $user_id, 'youtube', true );

        if ( ! empty( $urls['twitter'] ) ) {
            $urls['twitter'] = 'http://twitter.com/' . $urls['twitter'];
        }

        $user_data = get_userdata( $user_id );
        if ( ! empty( $user_data->user_url ) ) {
            $urls['website'] = $user_data->user_url;
        }

        $icons = array();
        
        $icons['facebook'] = '<path d="M23.9981 11.9991C23.9981 5.37216 18.626 0 11.9991 0C5.37216 0 0 5.37216 0 11.9991C0 17.9882 4.38789 22.9522 10.1242 23.8524V15.4676H7.07758V11.9991H10.1242V9.35553C10.1242 6.34826 11.9156 4.68714 14.6564 4.68714C15.9692 4.68714 17.3424 4.92149 17.3424 4.92149V7.87439H15.8294C14.3388 7.87439 13.8739 8.79933 13.8739 9.74824V11.9991H17.2018L16.6698 15.4676H13.8739V23.8524C19.6103 22.9522 23.9981 17.9882 23.9981 11.9991Z"/>';
        $icons['instagram'] = '<path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>';
        $icons['linkedin'] = '<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>';
        $icons['pinterest'] = '<path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/>';
        $icons['tumblr'] = '<path d="M14.563 24c-5.093 0-7.031-3.756-7.031-6.411V9.747H5.116V6.648c3.63-1.313 4.512-4.596 4.71-6.469C9.84.051 9.941 0 9.999 0h3.517v6.114h4.801v3.633h-4.82v7.47c.016 1.001.375 2.371 2.207 2.371h.09c.631-.02 1.486-.205 1.936-.419l1.156 3.425c-.436.636-2.4 1.374-4.156 1.404h-.178l.011.002z"/>';
        $icons['twitter'] = '<path d="M23.954 4.569c-.885.389-1.83.654-2.825.775 1.014-.611 1.794-1.574 2.163-2.723-.951.555-2.005.959-3.127 1.184-.896-.959-2.173-1.559-3.591-1.559-2.717 0-4.92 2.203-4.92 4.917 0 .39.045.765.127 1.124C7.691 8.094 4.066 6.13 1.64 3.161c-.427.722-.666 1.561-.666 2.475 0 1.71.87 3.213 2.188 4.096-.807-.026-1.566-.248-2.228-.616v.061c0 2.385 1.693 4.374 3.946 4.827-.413.111-.849.171-1.296.171-.314 0-.615-.03-.916-.086.631 1.953 2.445 3.377 4.604 3.417-1.68 1.319-3.809 2.105-6.102 2.105-.39 0-.779-.023-1.17-.067 2.189 1.394 4.768 2.209 7.557 2.209 9.054 0 13.999-7.496 13.999-13.986 0-.209 0-.42-.015-.63.961-.689 1.8-1.56 2.46-2.548l-.047-.02z"/>';
        $icons['youtube'] = '<path d="M23.495 6.205a3.007 3.007 0 0 0-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 0 0 .527 6.205a31.247 31.247 0 0 0-.522 5.805 31.247 31.247 0 0 0 .522 5.783 3.007 3.007 0 0 0 2.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 0 0 2.088-2.088 31.247 31.247 0 0 0 .5-5.783 31.247 31.247 0 0 0-.5-5.805zM9.609 15.601V8.408l6.264 3.602z"/>';
        $icons['website'] = '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="external-link-alt" class="svg-inline--fa fa-external-link-alt fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M432,320H400a16,16,0,0,0-16,16V448H64V128H208a16,16,0,0,0,16-16V80a16,16,0,0,0-16-16H48A48,48,0,0,0,0,112V464a48,48,0,0,0,48,48H400a48,48,0,0,0,48-48V336A16,16,0,0,0,432,320ZM488,0h-128c-21.37,0-32.05,25.91-17,41l35.73,35.73L135,320.37a24,24,0,0,0,0,34L157.67,377a24,24,0,0,0,34,0L435.28,133.32,471,169c15,15,41,4.5,41-17V24A24,24,0,0,0,488,0Z"></path></svg>';

        // output buffer
        ob_start();
        ?>
        <div class="authors-list-item-social">
            <?php foreach ( $urls as $site => $url ) : ?>
                <?php if ( ! empty( $url ) ) : ?>
                    <a target="_blank" rel="nofollow external noopener noreferrer" href="<?php echo esc_url( $url ); ?>" class="authors-list-item-social-<?php echo esc_attr( $site ); ?>">
                        <svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <?php echo $icons[$site]; ?>
                        </svg>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
        $output = ob_get_contents();
        ob_end_clean();

        // return output
        return $output;

    }

}

if ( ! function_exists( 'authors_list_buddypress_follow_link') ) {

    /**
     * Display follow link buddypress
     * 
     * @since 1.1.5
     */
    function authors_list_display_buddypress_follow_link( $args = array() ) {

        $user_id = $args['user_id'];

        if ( function_exists( 'bp_follow_add_follow_button' ) && bp_loggedin_user_id() && bp_loggedin_user_id() != $user_id ) {
            bp_follow_add_follow_button( array(
                'leader_id'   => $user_id,
                'follower_id' => bp_loggedin_user_id()
            ) );
        }
    }

}
