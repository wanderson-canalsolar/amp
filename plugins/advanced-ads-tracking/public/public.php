<?php

class Advanced_Ads_Tracking {

    /**
     *  PHP Time Zone for the WP installation
     */
    public static $WP_DateTimeZone;

    /**
     * name of the impressions table
     */
    protected $impressions_table = '';

    /**
     * name of the clicks table
     */
    protected $clicks_table = '';

    /**
     *
     * @var Advanced_Ads_Tracking_Util
     */
    protected $util;

    /**
     * default click link base
     */
    const CLICKLINKBASE = 'linkout';

    /**
     *
     * @var Advanced_Ads_Tracking_Plugin
     * @since 1.2.0
     */
    protected $plugin;

    /**
     *
     * @var boolean
     */
    protected $is_ajax;

    /**
     *
     * @var boolean
     */
    protected $is_admin;

    /**
     * sum of ad impression and clicks for all ads
     *
     * @var arr
     * @since 1.2.6
     */
    protected $sums;

	/**
	 * correspondence between ad ID-s and target link if any, for Google Analytics usage
	 *
	 * @var arr
	 */
	private $ad_targets = array();

    /**
     * ad ids that should be tracked using JavaScript
     *
     * @var arr
     */
    protected $ad_ids = array();

	/**
	 * Ads for which page query string should be transmitted.
	 *
	 * @var array
	 */
	protected $transmit_pageqs = array();

    /**
     * Initialize the plugin
     * and styles.
     *
     * @since     1.0.0
     */
    public function __construct( $is_admin, $is_ajax ) {

        self::$WP_DateTimeZone = self::get_wp_timezone();

        global $wpdb;

        // load table names
        $this->impressions_table = $wpdb->prefix . "advads_impressions";
        $this->clicks_table = $wpdb->prefix . "advads_clicks";

        $this->plugin = Advanced_Ads_Tracking_Plugin::get_instance();
        $this->time_zone = new DateTimeZone('UTC');
        $this->util = Advanced_Ads_Tracking_Util::get_instance();
        $this->util->set_plugin( $this->plugin );
        $this->is_ajax = $is_ajax;
        $this->is_admin = $is_admin;

        // anyone (even admin previews)
        // wrap ad in tracking link
        add_filter( 'advanced-ads-output-inside-wrapper', array( $this, 'add_tracking_link' ), 10, 2 );

        // get sums
        $this->sums = $this->util->get_sums();

        add_filter( 'advanced-ads-can-display', array( $this, 'can_display' ), 10, 2 );

        // handle special ajax events
        if ( $this->is_ajax ) {
            // load functions based on tracking method settings
            $this->ajax_init_ad_select();
        // no ajax, no admin
        } elseif ( ! $this->is_admin ) {
            // register two redirect methods, because the first might fail if other plugins also use it
            add_action('plugins_loaded', array($this, 'url_redirect'), 1);
            add_action('wp_loaded', array($this, 'url_redirect'), 1);
			// load functions based on tracking method settings (after the 'parse_query' hook)
            add_action( 'wp', array( $this, 'load_tracking_method' ), 10 );
            add_action( 'wp_footer', array( $this, 'output_ad_ids' ), PHP_INT_MAX );
			add_filter( 'advanced-ads-pro-passive-cb-for-ad', array( $this, 'add_passive_cb_for_ad' ), 10, 2 );
        }

        $this->load_plugin_textdomain();

		if ( !defined( 'ADVANCED_ADS_TRACKING_NO_PUBLIC_STATS' ) ) {
			add_action( 'wp_loaded', array( $this, 'is_public_stat' ) );
		}

        // scheduled email hook
        add_action( 'advanced_ads_daily_email', array( $this, 'daily_email' ) );

        add_shortcode( AAT_IMP_SHORTCODE, array( $this, 'impression_shortcode' ) );

		add_action( 'advanced_ads_daily_report', array( $this, 'individual_email_report' ) );

    }

	/**
	 * Add a wrapper for the ad
	 */
	public function add_wrapper( $wrapper, $ad ) {
        // if cannot track, abort
        if( ! $this->plugin->check_ad_tracking_enabled( $ad ) ) {
            return $wrapper;
        }
		$options = $ad->options();

		if (
			( !isset( $options['placement_type'] ) || false === strpos( $options['placement_type'], 'sticky' ) || !isset( $options['sticky']['trigger'] ) || 'timeout' != $options['sticky']['trigger'] ) &&
			( !isset( $options['layer_placement'] ) || empty( $options['layer_placement']['trigger'] ) )
		) {

			// If not sticky, or sticky but no timeout, AND not layer ad or no trigger, abort
			return $wrapper;
		}
		// add the ad id to the wrapper
		$wrapper['data-advadstrackid'] = $ad->id;
		$wrapper['data-advadstrackbid'] = get_current_blog_id();
		return $wrapper;
	}

	/**
	 * Send email report for individual ads
	 */
	public function send_individual_email() {
		$this->individual_email_report();
		die;
	}

    /**
     *  Impression shortcode
     */
    public function impression_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts, AAT_IMP_SHORTCODE );
        $ID = absint( $atts['id'] );
        if ( !$ID ) return;
        $ad = get_post( $ID );
        if ( $ad->post_type != Advanced_Ads::POST_TYPE_SLUG ) return;
        $title = $ad->post_title;
        $sum = ( isset( $this->sums['impressions'][$ID] ) )? $this->sums['impressions'][$ID] : false;
        ob_start();
        if ( false !== $sum ) {
            echo $sum;
        } else {
            echo '0';
        }
        $output = ob_get_clean();
        return $output;
    }

    /**
     *  get DateTimeZone object for the WP installation
     */
    public static function get_wp_timezone() {
        $_time_zone = get_option( 'timezone_string' );
        if ( $_time_zone ) {
            $time_zone = new DateTimeZone( $_time_zone );
        } else {
            $offset_option = get_option( 'gmt_offset' );
            $pattern = '/(-|\+)?((\d+)(:\d\d)?)/';
            preg_match( $pattern, $offset_option, $result );
            if ( $result ) {
                $zero = ( 1 == strlen( $result[3] ) )? '0' : '';
                $sign = ( isset( $result[1] ) && !empty( $result[1] ) )? $result[1] : '+';
                $gmt = $sign . $zero . $result[2];
                if ( !isset( $result[4] ) || empty($result[4]) ) $gmt .= ':00';

                // $time_zone = DateTime::createFromFormat( 'O', $gmt )->getTimezone();
                $time_zone = date_create( '2015-11-01T12:00:00' . $gmt )->getTimezone();
            } else {
                // fallback timezone ( WP's default )
                $time_zone = new DateTimeZone( 'UTC' );
            }
        }
        return $time_zone;
    }

    /**
     *  Draw the public stat page
     *
     *  @since N/A
     */
    protected function display_public_stats( $ad_id ) {
        require_once AAT_BASE_PATH . 'public/views/ad-stats.php';
        die;
    }

    /**
     *  get ad ID from the public hash
     *
     *  @since N/A
     */
    protected function ad_hash_to_id( $hash ) {
        $all_ads = Advanced_Ads::get_ads( array( 'post_status' => array( 'publish', 'future', 'draft', 'pending' ) ) );
        foreach ( $all_ads as $_ad ) {
            $ad = new Advanced_Ads_Ad( $_ad->ID );
            $options = $ad->options();
            if ( ! isset( $options['tracking'] ) ) continue;
            if ( ! isset( $options['tracking']['public-id'] ) ) continue;
            if ( $hash == $options['tracking']['public-id'] ) return $_ad->ID;
        }
        return false;
    }

    /**
     *  Check if it's a public stat url
     *
     *  @since N/A
     */
    public function is_public_stat() {
        if ( is_admin() || !isset( $_SERVER['HTTP_HOST'] ) ) {
            return;
        }

        $options = $this->plugin->options();

        $protocol = 'http';
        if ( is_ssl() ) {
            $protocol .= 's';
        }
        $protocol .= '://';

        $full_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        // site url including eventual blog slug in sub-directory multisite
        $site_url = site_url();

        $sub1 = substr( $full_url, strlen( $site_url ) );
        $stats_slug = ( isset( $options['public-stats-slug'] ) )? $options['public-stats-slug'] : Advanced_Ads_Tracking_Admin::PUBLIC_STATS_DEFAULT;

        $permalink = get_option( 'permalink_structure' );

        $ad_hash = false;
        if ( empty( $permalink ) ) {
            if ( isset( $_GET[ $stats_slug ] ) ) {
                $ad_hash = $_GET[ $stats_slug ];
            }
        } else {
            if ( 0 === strpos( $sub1, '/' . $stats_slug . '/' ) ) {
                $expl = explode( '/', $sub1 );
                $ad_hash = $expl[2];
            }
        }
        if ( $ad_hash ) {
            $ad_id = $this->ad_hash_to_id( $ad_hash );
            if ( false !== $ad_id ) {
                $this->display_public_stats( $ad_id );
            }
        }
    }

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.2.6.2
     */
    public function load_plugin_textdomain() {
	    load_plugin_textdomain( 'advanced-ads-tracking', false, AAT_BASE_DIR . '/languages' );
    }

    /**
     *
     */
    public function ajax_init_ad_select() {
        $this->load_tracking_method( true );
    }

    /**
     * redirect the visitor if he uses click tracking
     *
     * @since 1.1.0
     */
    public function url_redirect(){
        // check if the current url matches the click base
        $request_uri = trim(urldecode($_SERVER['REQUEST_URI']), '/');

        // remove subdirectory if exists
        if( isset( $_SERVER['HTTP_HOST'] ) && $sub_pos = strpos(home_url(), $_SERVER['HTTP_HOST']) ){
            // get subdirectory
            $subdirectory = trim(substr(home_url(), $sub_pos + mb_strlen( $_SERVER['HTTP_HOST'] ) ), '/');
            // replace subdirectory
            if( $subdirectory ) $request_uri = str_replace($subdirectory . '/', '', $request_uri);
        }

        $options = $this->plugin->options();
        $linkbase = isset($options['linkbase']) ? $options['linkbase'] : self::CLICKLINKBASE;

        $permalink = get_option( 'permalink_structure' );

        // abort if this is obviously not a tracking link
        if ( $permalink ) {
            if ( strpos( $request_uri, $linkbase ) !== 0 ) return;
        } else {
            if ( !isset( $_GET[ $linkbase ] ) ) return;
        }

        $ad_id = false;

        // check if the current url has a number in it
        if ( $permalink ) {

            $matches = array();
            preg_match( '@/(\d+)\??@', $request_uri, $matches );

            if ( isset( $matches[1] ) ) {
                $ad_id = ( int ) trim( $matches[1], '/' );
            }

        } else {
            $ad_id = absint( $_GET[ $linkbase ] );
        }

        // redirect, if ad id was found
        if ( $ad_id ) {
            // load the ad
            $ad = new Advanced_Ads_Ad($ad_id);
            if(!isset($ad->id)) return;

            // check if a url is given
            $ad_options = $ad->options();

            // get url
            if( isset($ad_options['tracking']['link']) && $ad_options['tracking']['link'] != '' ){
                $url = trim( $ad_options['tracking']['link'] );
            } elseif ( isset($ad_options['url']) && $ad_options['url'] != '' ) {
                $url = trim( $ad_options['url'] );
            } else {
                $url = false;
            }

            if( $url ){
                // Need a referrer because the click base url does not contain any information on the post where the ad was displayed and clicked
                $referrer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : false;

                if ( $referrer && is_string( $referrer ) ) {

                    /**
                     *  If called within the 'plugins_loaded' action, prevent redirecting
                     *  url_to_postid need to be called after the 'init' hook. Also stop tracking
                     *
                     *  [https://codex.wordpress.org/Function_Reference/url_to_postid]
                     */
                    if ( 0 === did_action( 'init' ) ) {
                        return;
                    }
                    // $post_id = url_to_postid( $referrer );

                    // hotfix for WPML – remove url_to_postid filter to get an unchanged url
                    global $sitepress;
                    remove_filter('url_to_postid', array($sitepress, 'url_to_postid'));

                    $post_id = url_to_postid( $referrer );

                    // reassign WPML filter
                    add_filter('url_to_postid', array($sitepress, 'url_to_postid'));

                    $post = get_post( $post_id );

                    parse_str( $_SERVER['QUERY_STRING'], $tracking_query_args );

                    if ( $post ) {
                        /**
                         *  the post ID was found by its URL.
                         */
                        $cats = get_the_category( $post->ID );

                        $url = str_replace( '[POST_ID]', $post->ID, $url );
                        $url = str_replace( '[POST_SLUG]', $post->post_name, $url );

                        $cats_slugs = array();
                        foreach ( $cats as $cat ) {
                            $cats_slugs[] = $cat->slug;
                        }

                        $url = str_replace( '[CAT_SLUG]', implode( ',', $cats_slugs ), $url ) ;

                    } else {
                        /***
                         *  post ID not found by its url ( eg: landing page )
                         */
                        $expl_url = explode( '?', $url );
                        if ( 1 < count( $expl_url ) ) {
                            // if query string is present ( and placeholder must be used in url query string )
                            $baseurl = $expl_url[0];
                            parse_str( $expl_url[1], $parsed );

                            // remove placeholders that can’t be used on non-single posts
                            $p_holders = array( '[POST_ID]', '[POST_SLUG]', '[CAT_SLUG]' );
                            $query_arr = array();
                            foreach ( $parsed as $key => $value ) {
                                if ( !in_array( $value, $p_holders ) ) {
                                    // if not related to the placeholder systems, add it to the final url
                                    $query_arr[$key] = $value;
                                }
                            }
                            if ( !empty( $query_arr ) ) {
								$url = $baseurl;
								$use_ampersand = false;
								end( $query_arr );
								$last_key = key( $query_arr );
								reset( $query_arr );
								foreach ( $query_arr as $key => $value ) {
									if ( $use_ampersand ) {
										$url .= '&';
									} else {
										$url .= '?';
										$use_ampersand = true;
									}
									$url .= $key ;
									if ( $key == $last_key ) {
										if ( !empty( $value ) ) {
											$url .= '=' . $value;
										}
									} else {
										$url .= '=' . $value;
									}
								}
							} else {
								$url = $baseurl;
							}
                        }
                    }

                    /**
                     * Pass query arguments from tracking link to the target url.
                     */
                    if ( !empty( $tracking_query_args ) ) {
                        // Do not include the tracking link base.
                        if ( isset( $tracking_query_args[ $linkbase ] ) ) {
                            unset( $tracking_query_args[ $linkbase ] );
                        }
                        $url = add_query_arg( $tracking_query_args, $url );
                    }

                    /**
					 * Pass query string from referer (if any);
					 */
					$can_transmit_qs = apply_filters( 'advanced-ads-tracking-query-string', false, $ad_id );
					if ( $can_transmit_qs ) {
						$parsed_referer = parse_url( $referrer );
						if ( isset( $parsed_referer['query'] ) ) {
							parse_str( $parsed_referer['query'], $referer_query );
							if ( ! empty( $referer_query ) ) {
								$url = add_query_arg( $referer_query, $url );
							}
						}
					}



                } else {
                    // remove attributes from URL
                    $url = str_replace( array( '[POST_ID]', '[POST_SLUG]', '[CAT_SLUG]' ), array('', '', ''), $url );
                }

                // replace [AD_ID] with the ad’s ID, if given
                $url = str_replace( '[AD_ID]', $ad_id, $url ) ;

                // track the click
                $args = array(
                    'ad_id' => $ad->id,
                );

                $this->track_click($args);

                /**
                 * last chance for other scripts to change the redirect URL
                 * originally introduced to allow "fixing" issues when a wrong URL was created
                 */
                $url = apply_filters( 'advanced-ads-tracking-redirect-url', $url );

                if(isset($options['nofollow']) && $options['nofollow']){
                    header("X-Robots-Tag: noindex, nofollow", true);
                } else {
                    header("X-Robots-Tag: noindex", true);
				}

	            /**
				 * Redirect to the target URL
				 *
	             * no-cache: page should not be cached
				 * no-store: browsers should not store the redirect, which would prevent them from calling the /linkout page
	             */
                header("Cache-Control: no-cache, no-store, must-revalidate");
                header("HTTP/1.1 307  Temporary Redirect");
                header('Location: '. esc_url_raw( $url ));


                die();
            }
        }

        return;
    }

    /**
     * load the scripts and hooks according to the tracking method
     *
     * @since 1.0.0
     */
    public function load_tracking_method( $ajax_compat = false ) {
        $options = $this->plugin->options();
        $method = isset( $options['method'] ) ? $options['method'] : null;
        $method = apply_filters( 'advanced-ads-tracking-method', $method );

      // don’t track if user is logged in and constant to not track actions from logged-in users is set
	if( $this->plugin->ignore_logged_in_user() ){
		return;
	}

        // for ajax: can not yet distinguish methods
        if ( true !== $ajax_compat ) {
            $need_load_header_scripts = 'frontend' === $method;
            if ( apply_filters( 'advanced-ads-tracking-load-header-scripts', $need_load_header_scripts ) ) {
                // load header scripts
                add_action( 'wp_enqueue_scripts', array( $this, 'load_header_scripts') );
            }
        }
		
        switch ($method) {
            case 'frontend':
				add_filter( 'advanced-ads-output-wrapper-options', array( $this, 'add_wrapper' ), 20, 2 );
                if ( true !== $ajax_compat ) {
                    // collect ad id, so that JavaScript can access it
                    add_action( 'advanced-ads-output', array( $this, 'collect_ad_id' ), 10, 3 );
					break;
                }
            case 'shutdown':
                // 'shutdown' or 'frontend' + AJAX
                add_action( 'shutdown', array( $this, 'track_on_shutdown' ) );

				// Use AJAX for delayed ads.
				if ( isset( $options['delayed-ads'] ) ) {
					add_action( 'wp_enqueue_scripts', array( $this, 'load_header_scripts') );
					add_filter( 'advanced-ads-output-wrapper-options', array( $this, 'add_wrapper' ), 20, 2 );
                    add_action( 'advanced-ads-output', array( $this, 'collect_ad_id' ), 10, 3 );
				}
				
				// collect ads ID-s for google Analytics
				if ( defined( 'ADVANCED_ADS_TRACKING_FORCE_ANALYTICS' ) && ADVANCED_ADS_TRACKING_FORCE_ANALYTICS ) {
					if ( has_action( 'advanced-ads-output', array( $this, 'collect_ad_id' ) ) ) break;
					add_action( 'advanced-ads-output', array( $this, 'collect_ad_id' ), 10, 3 );
				}
				
                break;
			case 'ga':
				add_action( 'advanced-ads-output', array( $this, 'collect_ad_id' ), 10, 3 );
				add_action( 'wp_enqueue_scripts', array( $this, 'load_header_scripts') );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_ga_scripts'), PHP_INT_MAX );
				add_action( 'wp_head', array( $this, 'ga_wp_head' ) );
				add_action( 'wp_footer', array( $this, 'ga_wp_footer' ), PHP_INT_MAX );
				
				add_filter( 'advanced-ads-output-wrapper-options', array( $this, 'add_wrapper' ), 20, 2 );
				break;
            case 'onrequest':
            default:
                // track impression when output is loaded
                add_action( 'advanced-ads-output', array( $this, 'track_on_output' ), 10, 3 );

				// Use AJAX for delayed ads.
				if ( isset( $options['delayed-ads'] ) ) {
					add_action( 'wp_enqueue_scripts', array( $this, 'load_header_scripts') );
					add_filter( 'advanced-ads-output-wrapper-options', array( $this, 'add_wrapper' ), 20, 2 );
                    add_action( 'advanced-ads-output', array( $this, 'collect_ad_id' ), 10, 3 );
				}
				
				// also collect ads ID-s for google Analytic
				if ( defined( 'ADVANCED_ADS_TRACKING_FORCE_ANALYTICS' ) && ADVANCED_ADS_TRACKING_FORCE_ANALYTICS ) {
					if ( false !== has_action( 'advanced-ads-output', array( $this, 'collect_ad_id' ) ) ) {
						add_action( 'advanced-ads-output', array( $this, 'collect_ad_id' ), 10, 3 );
					}
				}
        }

		// Parallel analytics tracking && multi-site
		if ( ( 'ga' != $method && defined( 'ADVANCED_ADS_TRACKING_FORCE_ANALYTICS' ) && ADVANCED_ADS_TRACKING_FORCE_ANALYTICS ) || is_multisite() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_ga_scripts') );
			add_action( 'wp_head', array( $this, 'ga_wp_head' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'load_header_scripts') );
			add_action( 'wp_footer', array( $this, 'ga_wp_footer' ), PHP_INT_MAX );
			
			add_filter( 'advanced-ads-output-wrapper-options', array( $this, 'add_wrapper' ), 20, 2 );
		}
    }

	/**
	 *  print Google Analytics related javascript in <head />
	 */
	public function ga_wp_head() {
		?><script type="text/javascript">
			if ( 'undefined' == typeof advadsGATracking ) window.advadsGATracking = {};
		</script>
		<?php
	}

	/**
	 *  print Google Analytics related javascript within the 'wp_footer' action
	 */
	public function ga_wp_footer() {
		if ( !empty( $this->ad_targets ) ) {
            if ( is_singular() ) {
                $post = get_post();
                $context = array(
                    'postID' => $post->ID,
                    'postSlug' => $post->post_name,
                );
                $categories = get_the_category( $post->ID );
                $cats_slugs = array();
                foreach ( $categories as $cat ) {
                    $cats_slugs[] = $cat->slug;
                }
                $cats = implode( ',', $cats_slugs );
                $context['cats'] = $cats;
            }
			?><script type="text/javascript">
				if ( 'undefined' == typeof window.advadsGATracking ) window.advadsGATracking = {};
                <?php if ( is_singular() ) : ?>
				advadsGATracking.postContext = <?php echo json_encode( $context ); ?>;
                <?php endif; ?>
			</script><?php
		}
	}

	/**
	 *  load Google Analytics related scripts (in footer)
	 */
	public function enqueue_ga_scripts() {
		wp_register_script(
			'advadsTrackingGAFront',
			AAT_BASE_URL . 'public/assets/js/ga-tracking.js',
			array( 'jquery' ),
			AAT_VERSION,
			true
		);
		$translations = array(
			'Impressions' => __( 'Impressions', 'advanced-ads-tracking' ),
			'Clicks' => __( 'Clicks', 'advanced-ads-tracking' ),
		);
		wp_localize_script( 'advadsTrackingGAFront', 'advadsGALocale', $translations );
		wp_enqueue_script( 'advadsTrackingGAFront' );
	}

    /**
     * track impression on output
     *
     * @since N/A
     */
    public function track_on_shutdown() {
		$advads = Advanced_Ads::get_instance();
		
		$tracking_options = $this->plugin->options();
		$placements = Advanced_Ads::get_ad_placements_array();
		
		$ads_in_groups = array();
		
        foreach ( $advads->current_ads as $_ad ) {
            if ( ! isset( $_ad['type'] ) || 'ad' !== $_ad['type'] ) {
                continue;
            }
			$is_trigger_ad = false; // (cache busting)
			if ( 'frontend' == $tracking_options['method']  && isset( $_ad['placement_id'] ) ) {
				foreach ( $placements as $_id => $_placement ) {
					if ( $_id == $_ad['placement_id'] ) {
						if ( isset( $_placement['options']['cache-busting'] ) && 'on' == $_placement['options']['cache-busting'] ) {
							if ( isset( $_placement['options']['layer_placement'] ) && ! empty( $_placement['options']['layer_placement']['trigger'] ) ) {
								$is_trigger_ad = true;
								break;
							}
							if ( isset( $_placement['options']['sticky'] ) && ! empty( $_placement['options']['sticky']['trigger'] ) ) {
								$is_trigger_ad = true;
								break;
							}
						}
					}
				}
			}
			
			$is_delayed_ad = false; // ( static ad and groups )
			if ( isset( $tracking_options['delayed-ads'] ) ) {
				
				foreach ( $placements as $_id => $_placement ) {
					
					if ( $_ad['type'] . '_' . $_ad['id'] == $_placement['item'] ) {
						if ( isset( $_placement['options']['layer_placement'] ) && ! empty( $_placement['options']['layer_placement']['trigger'] ) ) {
							$is_delayed_ad = true;
							break;
						}
						if ( isset( $_placement['options']['sticky'] ) && ! empty( $_placement['options']['sticky']['trigger'] ) ) {
							$is_delayed_ad = true;
							break;
						}
					}
					
					if ( 0 === strpos( $_placement['item'], 'group' ) ) {
						if (
							( isset( $_placement['options']['layer_placement'] ) && ! empty( $_placement['options']['layer_placement']['trigger'] ) ) ||
							( isset( $_placement['options']['sticky'] ) && ! empty( $_placement['options']['sticky']['trigger'] ) )
						) {
							
							$group_id = absint( str_replace( 'group_', '', $_placement['item'] ) );
							if ( ! isset( $ads_in_groups[ $group_id ] ) ) {
								
								$_adsingroup = Advanced_Ads::get_ads(
									array(
										'tax_query' => array(
											array(
												'taxonomy' => Advanced_Ads::AD_GROUP_TAXONOMY,
												'field' => 'term_id',
												'terms' => $group_id,
											)
										)
									)
								);
								$ads_in_groups[ $group_id ] = array();
								
								foreach ( $_adsingroup as $__ad ) {
									$ads_in_groups[ $group_id ][] = $__ad->ID;
								}
								
							}
							if ( in_array( absint( $_ad['id'] ), $ads_in_groups[ $group_id ] ) ) {
								$is_delayed_ad = true;
								break;
							}
						}
						
					}
					
				}
				
			}
			
			if ( $is_trigger_ad || $is_delayed_ad ) {
				continue;
			}
			
            $ad = new Advanced_Ads_Ad( $_ad['id'] );
			
            // check if this ad should be tracked
            // do not track empty ad (if ad output is available)
            if( !$this->plugin->check_ad_tracking_enabled( $ad )
			|| ! array_key_exists( 'output', $_ad )
			|| '' === trim( $_ad['output'] ) ) {
                continue;
            }

            $args = array(
                'ad_id' => $ad->id,
            );

            $this->track_impression( $args );
        }
    }

    /**
     * load header scripts
     *
     * @since 1.0.0
     */
    public function load_header_scripts(){
        // ajax script for tracking
        $options = $this->plugin->options();
        $method = isset( $options['method'] ) ? $options['method'] : null;

		$blog_id = get_current_blog_id();
        $params = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'ajaxActionName' => Advanced_Ads_Tracking_Ajax::TRACK_IMPRESSION,
            'method' => $method,
			'blogId' => $blog_id,
        );

        $deps = array( 'jquery' );

        if ( class_exists( 'Advanced_Ads_Pro' ) ) {
            $pro_options = Advanced_Ads_Pro::get_instance()->get_options();
            if ( ! empty( $pro_options['cache-busting']['enabled'] ) ) {
                $deps[] = 'advanced-ads-pro/cache_busting';
            }
        }

        wp_enqueue_script( 'advadsTrackingHandle', AAT_BASE_URL . 'public/assets/js/script.js', $deps, AAT_VERSION, true );
        wp_localize_script( 'advadsTrackingHandle', 'advadsTracking', $params );
    }

    /**
     * collect ad id, so that JavaScript can access it
     *
     * @param obj Advanced_Ads_Ad $ad
     * @param string $output
     * @param array $output_options
     */
    public function collect_ad_id( Advanced_Ads_Ad $ad, $output, $output_options = array() ) {

        // do not track ad for passive cache-busting
        if ( ! isset( $output_options['global_output'] ) || ! $output_options['global_output'] ) {
            return;
        }

        // check if this ad should be tracked
        if( ! $this->plugin->check_ad_tracking_enabled( $ad ) ) {
            return;
        }

        // do not track empty ads
        if ( empty( $output ) ) {
            return;
        }
		
        $options = $this->plugin->options();
        $method = isset( $options['method'] ) ? $options['method'] : null;
        $method = apply_filters( 'advanced-ads-tracking-method', $method );
		
		// do not collect ads with display trigger.
		if ( 'frontend' == $method || isset( $options['delayed-ads'] ) ) {
			$ad_options = $ad->options();
			if ( 
				isset( $ad_options['layer_placement'] ) && !empty( $ad_options['layer_placement']['trigger'] ) ||
				isset( $ad_options['sticky'] ) && !empty( $ad_options['sticky']['trigger'] )
			) {
				$utils = Advanced_Ads_Tracking_Util::get_instance();
				$utils->collect_blog_data();
				return;
			}
		}
		
		$blog_id = get_current_blog_id();

		$tracking_options = $this->plugin->options();
		if ( 'ga' == $tracking_options['method'] ) {
			$can_transmit_pageqs = apply_filters( 'advanced-ads-tracking-query-string', false, $ad->id );
			if ( $can_transmit_pageqs ) {
				if ( ! isset( $this->transmit_pageqs[ $blog_id ] ) ) {
					$this->transmit_pageqs[ $blog_id ] = array();
				}
				$this->transmit_pageqs[ $blog_id ][ $ad->id ] = true;
			}
		}

		if ( !isset( $this->ad_ids[$blog_id] ) ) {
			$this->ad_ids[$blog_id] = array();
		}
		$utils = Advanced_Ads_Tracking_Util::get_instance();
		$utils->collect_blog_data();
        $this->ad_ids[$blog_id][] = $ad->id;

    }

    /**
     * output ad ids
     */
    public function output_ad_ids() {
		$utils = Advanced_Ads_Tracking_Util::get_instance();
		foreach ( $this->ad_ids as $bid => $ads ) {
			$utils->collect_blog_data();
		}

		$blog_data = $utils->get_blog_data();

        echo '<script type="text/javascript">';
		if ( empty( $this->ad_ids ) ) {
			echo 'var advads_tracking_ads = {};';
		} else {
			echo 'var advads_tracking_ads = ' . json_encode( $this->ad_ids ) . ';';
		}
		echo 'var advads_tracking_urls = ' . json_encode( $blog_data['ajaxurls'] ) . ';';
		echo 'var advads_gatracking_uids = ' . json_encode( $blog_data['gaUIDs'] ) . ';';
		echo 'var advads_tracking_methods = ' . json_encode( $blog_data['methods'] ) . ';';
		echo 'var advads_tracking_parallel = ' . json_encode( $blog_data['parallelTracking'] ) . ';';
		echo 'var advads_tracking_linkbases = ' . json_encode( $blog_data['linkbases'] ) . ';';
		echo 'var advads_gatracking_allads = ' . json_encode( $blog_data['allads'] ) . ';';

		echo 'var advads_gatracking_anonym = ';
		echo defined( 'ADVANCED_ADS_DISABLE_ANALYTICS_ANONYMIZE_IP' ) ? 'false;' : 'true;';

		if ( ! empty( $this->transmit_pageqs ) ) {
			echo 'var advads_gatracking_transmitpageqs = ' . json_encode( $this->transmit_pageqs ) . ';';
		}

		echo '</script>';
    }

    /**
     * track impression on output
     *
     * @since 1.0.0
     * @param obj $ad object
     * @param string $output
     */
    public function track_on_output($ad, $output, $output_options = array() ) {
        // do not track ad for passive cache-busting
        if ( !isset( $output_options['global_output'] ) || ! $output_options['global_output'] ) {
            return;
        }

        // check if this ad should be tracked
        if(!$this->plugin->check_ad_tracking_enabled($ad)) {
            return;
        }

        // do not track empty ads
        if ( empty( $output ) ) {
            return;
        }

		// Do not track delayed ads when AJAX option enabled.
		$ad_options = $ad->options();
		$plugin_options = $this->plugin->options();
		
		if ( 
			(
				isset( $ad_options['layer_placement'] ) && !empty( $ad_options['layer_placement']['trigger'] ) ||
				isset( $ad_options['sticky'] ) && !empty( $ad_options['sticky']['trigger'] ) 
			) && isset( $plugin_options['delayed-ads'] )
		) {
			return;
		}
		
        $args = array(
            'ad_id' => $ad->id,
        );

        $this->track_impression( $args );
    }

    /**
     * add impression to database
     *
     * @since 1.0.0
     * @deprecated 1.2.0 use util class instead
     */
    public function track_impression( $args = array() ) {
        $this->util->track_impression( $args );
    }

    /**
     * add click to database
     *
     * @since 1.1.0
     * @deprecated 1.2.0 use util class instead
     */
    public function track_click( $args = array() ) {
        $this->util->track_click( $args );
    }

    /**
     * add a link to the ad content either for the %link% placeholder or a wrapper
     *
     * @since 1.1.0
     * @param string $content ad content
     * @param obj $ad ad object
     */
    public function add_tracking_link( $content = '', $ad = 0 ) {
        $ad_options = $ad->options();
        $options = $this->plugin->options();
	$general_options = Advanced_Ads::get_instance()->options();

		// do not add link if click tracking is not supported by the ad type
		if ( !in_array( $ad_options['type'], Advanced_Ads_Tracking_Plugin::$types_using_click_tracking ) ) {
			return $content;
		}

	// get url
	if( isset($ad_options['tracking']['link']) && $ad_options['tracking']['link'] != '' ){
	    $url = $ad_options['tracking']['link'];
	} elseif( isset($ad_options['url']) && $ad_options['url'] != '' ){
	    $url = $ad_options['url'];
	} else {
	    $url = false;
	}

        if ( $url ) {
			$bid = get_current_blog_id();
            $link = self::build_click_tracking_url( $ad );
			$this->ad_targets[ $bid ][ $ad->id ] = $url;
            if ( is_string($link) && $link !== '' ) {
                // if ad contains a %link% placeholder

                $nofollow = '';
		    if (
			    ( isset( $ad_options['tracking']['nofollow'] ) && 1 === absint( $ad_options['tracking']['nofollow'] ) ) ||
			    ( isset( $options['nofollow'] ) && ( !isset( $ad_options['tracking']['nofollow'] ) || "0" !== $ad_options['tracking']['nofollow'] ) )
		    ) {
			    $nofollow = ' rel="nofollow"';
		    }
		    $target = Advanced_Ads_Tracking_Util::get_target( $ad );
                if ( strpos( $content, '%link%' ) !== false ) {
					if ( $this->plugin->check_ad_tracking_enabled( $ad, 'click' ) ) {
						// only use the tacking url if click tracking is enabled
						$content = str_replace( '%link%', $link, $content );
						$content = str_replace( 'href="', 'data-bid="' . $bid . '" href="', $content );
					} else {
						$content = str_replace( '%link%', esc_url( $url ), $content );
					}
                } elseif ( $this->plugin->check_ad_tracking_enabled( $ad, 'click' ) ) {
					// wrap ad into tracking link
                    $content = '<a data-bid="' . $bid . '" href="'.$link.'"'.$nofollow.$target.'>'.$content.'</a>';
                } else {
					// wrap ad into original link
                    $content = '<a href="'. esc_url( $url ) .'"'.$nofollow.$target.'>'.$content.'</a>';
                }
            }
        }

        return $content;
    }

    /**
     * build click tracking url
     *
     * @since 1.1.0
     * @param obj $ad ad object
     * @return string $url click tracking url
     */
    public static function build_click_tracking_url( $ad = null ){
        if ( $ad === null || ! isset( $ad->id ) || $ad->id == 0 ) {
            return;
        }

        $options = Advanced_Ads_Tracking_Plugin::get_instance()->options();
        $linkbase = isset($options['linkbase']) ? $options['linkbase'] : self::CLICKLINKBASE;
        $base = apply_filters('advanced-ads-tracking-click-url-base', $linkbase, $ad);

        $permalink = get_option( 'permalink_structure' );

        if ( ! $permalink ) {
            $home = home_url( '/' );
            if ( false !== strpos( $home, '?' ) ) {
                $target_url = $home . '&' . $base . '=' . $ad->id;
            } else {
                $target_url = $home . '?' . $base . '=' . $ad->id;
            }
        } else {
            $target_url = home_url( '/' . $base . '/' . $ad->id );
            /**
             * hotfix caused by WPML plugin that adds variables through home_url filter
             * but useful for similar scripts too
             */
            if( $pos = strpos($target_url, "?") ) {
                $target_url = substr($target_url, 0, $pos );
            }
        }


        /**
         * allow to manipulate the click tracking URL
         */
        $target_url = apply_filters( 'advanced-ads-tracking-click-tracking-url', $target_url );

        return $target_url;
    }

	/**
	 * check if ad can be displayed based on tracking options
	 *
	 * @since 1.2.6
	 * @param bool $can_dieplay
	 * @param obj $ad Advanced_Ads_Ad
	 * @return bool $can_display false if should not be displayed in frontend
	 */
	public function can_display( $can_display, $ad ) {
		if ( ! $can_display ) {
			return false;
		}

		$options = $ad->options();
		$sums = $this->sums;
		$ad_id = $ad->id;

		// check impression limits
		if( isset( $sums['impressions'][ $ad_id ] ) && isset( $options['tracking']['impression_limit'] ) && $options['tracking']['impression_limit'] ){
			$impression_limit = absint( $options['tracking']['impression_limit'] );
			if( $sums['impressions'][ $ad_id ] >= $impression_limit ){
				return false;
			}
		}

		if ( isset( $options['type'] ) && in_array( $options['type'], Advanced_Ads_Tracking_Plugin::$types_using_click_tracking ) ) {
			// check click limits
			if( isset( $sums['clicks'][ $ad_id ] ) && isset( $options['tracking']['click_limit'] ) && $options['tracking']['click_limit'] ){
				$click_limit = absint( $options['tracking']['click_limit'] );
				if( $sums['clicks'][ $ad_id ] >= $click_limit ){
					return false;
				}
			}
		}

		if ( !defined( 'ADVANCED_ADS_TRACKING_NO_HOURLY_LIMIT' ) || !ADVANCED_ADS_TRACKING_NO_HOURLY_LIMIT ) {
			$limiter = new Advanced_Ads_Tracking_Limiter( $ad_id );
			$can_display = $limiter->can_display( $can_display, $ad );
		}
		return $can_display;

	}

	/**
	 *  deactivation
	 */
	public static function deactivate() {
        wp_clear_scheduled_hook( 'advanced_ads_daily_email' );
		wp_clear_scheduled_hook( 'advanced_ads_auto_comp' );
		wp_clear_scheduled_hook( 'advanced_ads_daily_report' );
	}

    /**
     *  daily ( & weekly & monthly ) email function
     */
    public function daily_email() {
        $options = $this->plugin->options();

	    if ( 'ga' == $options['method'] ) {
		    $this->log_report_cron( 'full report: email reports are not working with Google Analytics' );
		    return;
		}

        $sched = isset( $options['email-sched'] )? $options['email-sched'] : 'daily';
        $now = date_create( 'now', self::$WP_DateTimeZone );

        $this->log_report_cron( 'full report: schedule: ' . $sched );
        $this->log_report_cron( 'full report: current time: ' . print_r( $now, true ) );

	/**
	 *  site admin reports
	 */
		$result = 'not sent';
        switch ( $sched ) {
            case 'monthly':
                if ( '01' == $now->format( 'd' ) ) {
                    // if start of month
                    $result = $this->util->send_email_report();
	                $this->log_report_cron( 'full report: schedule: ' . $sched );
                }
                break;

            case 'weekly':
                if ( '1' == $now->format( 'w' ) ) {
                    // if monday
	                $result = $this->util->send_email_report();
                }
                break;

            default: // daily
	            $result = $this->util->send_email_report();
        }

	    $this->log_report_cron( 'full report: send?: ' . print_r( $result, true ) );
    }

	/**
	 *  Individual ad email function
	 */
	public function individual_email_report() {

		$options = $this->plugin->options();

		if ( isset( $options['method'] ) && 'ga' == $options['method'] ) {
			$this->log_report_cron( 'email reports are not working with Google Analytics' );
			return;
		}

		$per_ad_reports = $this->util->get_ad_reports_params();

		$now = date_create( 'now', self::$WP_DateTimeZone );

		foreach ( $per_ad_reports as $item ) {

			if ( 'never' == $item['frequency'] ) continue;
			$frequency = $item['frequency'];
			$ad_id = $item['id'];
			$period = $item['period'];
			$recip = $item['recip'];
			$period_name = $item['period-literal'];

			$order_id = get_post_meta( $ad_id, 'advanced_ads_selling_order', true );
			if ( $order_id ) {
				// if ad was sold via WooCommerce
				$post = get_post( $ad_id );
				$order = wc_get_order( $order_id );
				global $woocommerce;
				if ( isset( $woocommerce->version ) && version_compare( $woocommerce->version, '3.0', ">=" ) ) {
					$recip = $order->get_billing_email();
				} else {
					$recip = $order->billing_email;
				}
			}

			// string used in debug log, if enabled
			$debug_string = 'report for ad ID ' . $ad_id;

			if ( empty( $recip ) ) {
				$this->log_report_cron( $debug_string . ': recipient missing' );
				continue;
			}

			$this->log_report_cron( $debug_string . ': frequency: ' . $frequency );
			$this->log_report_cron( $debug_string . ': current time: ' . print_r( $now, true ) );

			$subject = sprintf( __( 'Ad statistics for %s', 'advanced-ads-tracking' ), $period_name );

			$result = 'not sent';
			switch ( $frequency ) {
				case 'monthly':
					if ( '01' == $now->format( 'd' ) ) {
						// if start of month
						$result = $this->util->send_individual_ad_report( array(
							'subject' => $subject,
							'to' => $recip,
							'id' => $ad_id,
							'period' => $period,
						) );
					}
					break;

				case 'weekly':
					if ( '1' == $now->format( 'w' ) ) {
						// if monday
						$result = $this->util->send_individual_ad_report( array(
							'subject' => $subject,
							'to' => $recip,
							'id' => $ad_id,
							'period' => $period,
						) );
					}
					break;

				default: // daily
					$result = $this->util->send_individual_ad_report( array(
						'subject' => $subject,
						'to' => $recip,
						'id' => $ad_id,
						'period' => $period,
					) );
			}

			$this->log_report_cron( $debug_string . ': send?: ' . print_r( $result, true ) );
		}

	}

	/**
	 * Pass tracking info to passive cache-busting.
	 *
	 * @param arr $data
	 * @param obj $ad Advanced_Ads_Ad
	 * @return arr $data
	 */
	public function add_passive_cb_for_ad( $data, Advanced_Ads_Ad $ad ) {
		$data['tracking_enabled'] = Advanced_Ads_Tracking_Plugin::get_instance()->check_ad_tracking_enabled( $ad );
		return $data;
	}

	/**
	 * Log scheduled reports if debugging constant `ADVANCED_ADS_TRACKING_CRON_DEBUG` is set in wp-config.php
	 *
	 * @param string $content Message that should be logged
	 */
	public function log_report_cron( $content ){

		if( defined( 'ADVANCED_ADS_TRACKING_CRON_DEBUG' ) && ADVANCED_ADS_TRACKING_CRON_DEBUG ) {

			error_log( $content . "\n", 3, WP_CONTENT_DIR . '/advanced-ads-tracking-cron.csv' );
		}

	}

}
