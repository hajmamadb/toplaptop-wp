<?php
/**
 * The faq field
 *
 * @package YITH\TabManager\Metaboxes\Types
 *
 * @var array $field The field.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$data = $field['data'] ?? array();
$faqs = is_array( $field['value'] ) ? $field['value'] : array(
	array(
		'question' => '',
		'answer'   => '',
	),
);
$i    = 0;

$force_open = ! is_array( $field['value'] ) || ( count( $field['value'] ) === 1 && empty( $field['value'][0]['question'] ) );
?>

<div id="<?php echo esc_attr( $field['id'] ); ?>" class="ywtm-faqs-field-wrapper" <?php yith_plugin_fw_html_data_to_string( $data, true ); ?>>
	<div class="ywtm-faqs-field-list">
		<?php foreach ( $faqs as $faq ) : ?>
			<div class="ywtm-custom-field-toggle <?php echo $force_open ? esc_attr( 'force-open' ) : ''; ?>"
				data-index="<?php echo esc_attr( $i ); ?>">
				<?php
				yith_plugin_fw_get_field(
					array(
						'id'          => "{$field['id']}_{$i}",
						'type'        => 'toggle-element-fixed',
						'title'       => $faq['question'],
						'name'        => "{$field['name']}[{$i}]",
						'onoff_field' => false,
						'value'       => $faq,
						'elements'    => array(
							array(
								'id'    => 'question',
								'type'  => 'textarea',
								'title' => __( 'Question', 'yith-woocommerce-tab-manager' ),
							),
							array(
								'id'    => 'answer',
								'type'  => 'textarea',
								'title' => __( 'Answer', 'yith-woocommerce-tab-manager' ),
							),
						),
					),
					true,
					false
				);
				?>
				<?php
				if ( $i > 0 ) :
					?>
					<span class="ywtm-faq-delete ywtm-delete-btn yith-icon yith-icon-close-alt"></span>
				<?php endif; ?>
			</div>
			<?php
			++$i;
		endforeach;
		?>
	</div>
	<div class="ywtm-faqs-actions">
		<button class="yith-plugin-fw__button--add ywtm-add-btn ywtm-add-faq"><?php esc_html_e( 'Add FAQ', 'yith-woocommerce-tab-manager' ); ?></button>
	</div>
</div>
<script type="text/template" id="tmpl-ywtm-new-faq-row-<?php echo esc_attr( $field['id'] ); ?>">
	<div class="ywtm-custom-field-toggle" data-index="{{{data.index}}}">
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'          => "{$field['id']}_{{{data.index}}}'",
				'type'        => 'toggle-element-fixed',
				'title'       => '',
				'name'        => "{$field['name']}[{{{data.index}}}]",
				'onoff_field' => false,
				'value'       => array(
					'question' => '',
					'answer'   => '',
				),
				'elements'    => array(
					array(
						'id'    => 'question',
						'type'  => 'textarea',
						'title' => __( 'Question', 'yith-woocommerce-tab-manager' ),
					),
					array(
						'id'    => 'answer',
						'type'  => 'textarea',
						'title' => __( 'Answer', 'yith-woocommerce-tab-manager' ),
					),
				),
			),
			true,
			false,
		);
		?>
		<span class="ywtm-faq-delete ywtm-delete-btn yith-icon yith-icon-close-alt"></span>
	</div>
</script>