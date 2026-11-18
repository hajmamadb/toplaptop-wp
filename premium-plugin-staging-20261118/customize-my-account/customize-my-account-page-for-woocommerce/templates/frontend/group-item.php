<?php
/**
 * Frontend group item.
 *
 * @since 0.1.0
 */

defined( 'ABSPATH' ) || exit;
?>
<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $slug ) ); ?>">
	<a href="#"
		data-endpoint="<?php echo esc_attr( $slug ); ?>">
		<?php echo esc_html( $label ); ?>

	<?php if ( tgwc_is_endpoint_icon_enabled() ) : ?>
		<i class="<?php echo esc_attr( $icon ); ?>"></i>
	<?php endif; ?>
	</a>

<?php if ( isset( $children ) ) : ?>
	<ul>
		<?php
		foreach ( $children as $child_slug => $child ) {
			do_action( "tgwc_myaccount_menu_item_{$child_slug}", $child_slug );
			do_action( 'tgwc_my_account_menu_item', $child_slug );
		}
		?>
	</ul>
<?php endif; ?>
</li>
<?php

