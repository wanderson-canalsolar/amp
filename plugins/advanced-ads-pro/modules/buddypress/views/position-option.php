<?php
/**
 * Render position option for the BuddyPress Content placement
 *
 * @var string $placement_slug slug of the placement
 * @var array $buddypress_positions available positions (hooks)
 * @var string $current currently selected position
 * @var int $index value of index option
 */
?><div class="advads-option">
<span><?php esc_html_e( 'position', 'advanced-ads-pro' ); ?></span>
	<div>
<select name="advads[placements][<?php echo esc_attr( $placement_slug ); ?>][options][buddypress_hook]">
	<?php
	foreach ( $buddypress_positions as $_group => $_positions ) :
		// display option directly if it is not an array itself, otherwise, display an optgroup tag
		if ( is_array( $_positions ) ) :
			?>
			<optgroup label="<?php echo esc_html( $_group ); ?>">
				<?php foreach ( $_positions as $_position ) : ?>
					<option <?php selected( $_position, $current ); ?>><?php echo esc_html( $_position ); ?></option>
				<?php endforeach; ?>
			</optgroup>
		<?php else : ?>
			<option value="<?php echo esc_attr( $_group ); ?>" <?php selected( $_group, $current ); ?>><?php echo esc_html( $_positions ); ?></option>
		<?php endif; ?>
	<?php endforeach; ?>
</select>
		<p>
		<?php
		printf(
			// translators: %s is an HTML input element.
			__( 'Inject at %s. entry', 'advanced-ads-pro' ),
			'<input type="number" required="required" min="1" name="advads[placements][' . esc_attr( $placement_slug ) . '][options][pro_buddypress_pages_index]" value="' . absint( $index ) . '"/>'
		);
		?>
			</p>
	</div>
</div>
