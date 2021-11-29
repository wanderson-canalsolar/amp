<?php
/**
 * The metabox functionality of the plugin
 * php version 5.2.4
 *
 * @category   SemrushSwa
 * @package    SemrushSwa
 * @subpackage SemrushSwa/metabox
 * @author     SEMrush CY LTD <apps@semrush.com>
 * @license    GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link       https://www.semrush.com/
 */

/**
 * The metabox functionality of the plugin.
 *
 * Adds metabox area with div.
 *
 * @category   SemrushSwa
 * @package    SemrushSwa
 * @subpackage SemrushSwa/metabox
 * @author     SEMrush CY LTD <apps@semrush.com>
 * @license    GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link       https://www.semrush.com/
 */
class SemrushSwa_MetaBox {

	/**
	 * Meta box initialization.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
	}

	/**
	 * Return post types array where to display this metabox.
	 *
	 * @return array
	 * @since  1.0.4
	 */
	private function get_metabox_post_types() {
		$default_post_types  = array( 'post', 'page', 'product' );
		$filtered_post_types = apply_filters(
			'semrush_seo_writing_assistant_post_types',
			$default_post_types
		);

		return empty( $filtered_post_types )
			? $default_post_types
			: $filtered_post_types;
	}

	/**
	 * Customize metabox context
	 * 
	 * @return string
	 * @since  1.1.0
	 */
	private function get_metabox_context() {
		$default_context          = 'advanced';
		$filtered_metabox_context = apply_filters(
			'semrush_seo_writing_assistant_metabox_context',
			$default_context
		);

		return empty( $filtered_metabox_context )
			? $default_context
			: $filtered_metabox_context;
	}

	/**
	 * Adds the meta box.
	 * 
	 * @return void
	 */
	public function add_metabox() {
		$swa_docid_src = get_home_url( null, '/' ) . get_the_ID() . wp_salt();
		$swa_docid     = md5( $swa_docid_src );
		$swa_docurl    = get_edit_post_link( 0, 'raw' );

		if ( $swa_docid && $swa_docurl ) {
			add_thickbox();

			$custom_script_url = getenv( 'SEMRUSH_SWA_PLUGIN_SCRIPT_URL' );

			wp_enqueue_script(
				'swa_wordpress_js',
				$custom_script_url
					? $custom_script_url 
					: '//www.semrush.com/swa/addon/nocache/js/wordpress.js',
				array(),
				SEMRUSH_SEO_WRITING_ASSISTANT_VERSION,
				true
			);

			add_meta_box(
				'swa-meta-box',
				__( 'Semrush SEO Writing Assistant' ),
				array( $this, 'render_metabox' ),
				$this->get_metabox_post_types(),
				$this->get_metabox_context(),
				'default',
				array(
					'swa_docid'  => $swa_docid,
					'swa_docurl' => $swa_docurl,
				)
			);
		}
	}

	/**
	 * Renders the meta box.
	 *
	 * @param WP_Post $post    The current post.
	 * @param array   $metabox With metabox id, title, callback, and args elements.
	 *
	 * @return void
	 */
	public function render_metabox( $post, $metabox ) {
		$swa_docid  = $metabox['args']['swa_docid'];
		$swa_docurl = $metabox['args']['swa_docurl'];

		echo '<div id="swa-container" data-swa-docurl="' . esc_url( $swa_docurl ) . '" data-swa-docid="' . esc_attr( $swa_docid ) . '"></div>';
	}

}
