<?php
/**
 * Endpoint vertical tab.
 *
 * @since 0.1.0
 */

defined( 'ABSPATH' ) || exit;

$children_count = isset( $children ) ? count( $children ) : 0;
?>
<li class="dd-item <?php echo esc_attr( $type ); ?>"
	data-id="<?php echo esc_attr( $slug ); ?>"
	data-type="<?php echo esc_attr( $type ); ?>" />
	<span class="dd-handle dd3-handle"></span>
	<a class="dd3-content tgwc-sidenav-tab-anchor" href="#<?php echo esc_attr( $slug ); ?>">
		<b>[<?php echo esc_html( ucwords( substr( $type, 0, 1 ) ) ); ?>]</b> <?php echo esc_html( $label ); ?>
	</a>

	<?php if ( isset( $children ) ) : ?>
	<ul class="dd-list">
		<?php foreach ( $children as $slug => $child ) : ?>
		<li class="dd-item <?php echo esc_attr( $child['type'] ); ?>"
			data-id="<?php echo esc_attr( $slug ); ?>"
			data-type="<?php echo esc_attr( $child['type'] ); ?>">
			<span class="dd-handle dd3-handle"></span>
			<a class="dd3-content tgwc-sidenav-tab-anchor" href="#<?php echo esc_attr( $slug ); ?>">
				<b>[<?php echo esc_html( ucwords( substr( $child['type'], 0, 1 ) ) ); ?>]</b> <?php echo esc_html( $child['label'] ); ?>
			</a>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
</li>
<?php
