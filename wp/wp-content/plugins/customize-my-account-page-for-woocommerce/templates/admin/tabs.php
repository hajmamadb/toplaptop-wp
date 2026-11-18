<?php
/**
 * Customize page tabs list.g
 *
 * @since 0.1.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="tgwc-header">
	<div class="tgwc-brand">
		<?php printf( '<img src="%s" />', esc_url( TGWC()->plugin_url() . '/assets/images/wooCommerce-customize-my-account-logo.png' ) ); ?>
	</div>

	<div class="wrap">
		<?php settings_errors( 'tgwc' ); ?>
	</div>
	<!-- Add empty h2 tag to display fix the move of the notices. -->
	<h2></h2>

	<nav class="nav-tab-wrapper">
	<?php
	foreach ( $tabs as $tab_slug => $tab_name ) {
		$class = ( $tab_selected === $tab_slug ) ? ' nav-tab-active' : '';
		printf(
			'<a class="nav-tab%s" href="?page=tgwc-customize-my-account-page&tab=%s">%s</a>',
			esc_attr( $class ),
			esc_attr( $tab_slug ),
			esc_html( $tab_name )
		);
	}
	?>
	</nav>
</div>
<?php
