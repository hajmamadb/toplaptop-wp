<?php
/**
 * The download field
 *
 * @package YITH\TabManager\Metaboxes\Types
 *
 * @var array $field The field.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$data      = $field['data'] ?? array();
$downloads = is_array( $field['value'] ) ? $field['value'] : array(
	array(
		'name' => '',
		'desc' => '',
		'file' => '',
	),
);
$i         = 0;
// translators: %s is the order of file; File 1, File 2.
$prefix = _x( 'File #%s', 'File #1', 'yith-woocommerce-tab-manager' );

$force_open = ! is_array( $field['value'] ) || ( count( $field['value'] ) === 1 && empty( $field['value'][0]['name'] ) );
?>

<div id="<?php echo esc_attr( $field['id'] ); ?>"
     class="ywtm-download-field-wrapper" <?php yith_plugin_fw_html_data_to_string( $data, true ); ?>>
    <div class="ywtm-download-field-list">
		<?php foreach ( $downloads as $download ) : ?>
            <div class="ywtm-custom-field-toggle <?php echo $force_open ? esc_attr( 'force-open' ) : ''; ?>"
                 data-index="<?php echo esc_attr( $i ); ?>"
                 data-prefix="<?php echo esc_attr( $prefix ); ?>">
				<?php
				if ( empty( $download['name'] ) ) {
					// translators: %1$s is the order of file; File 1, File 2.
					$file_title = sprintf( __( 'File #%1$s', 'yith-woocommerce-tab-manager' ), $i + 1 );
				} else {
					// translators: %1$s and %2$s are the order of file and the dimension.
					$file_title = sprintf( __( 'File #%1$s - %2$s', 'yith-woocommerce-tab-manager' ), $i + 1, $download['name'] );
				}
				/**
				 * APPLY_FILTERS: ywtm_change_url_path
				 *
				 * The filter allow to edit the file url.
				 *
				 * @param string $file_url the options.
				 *
				 * @return string
				 */
				$download['file'] = apply_filters( 'ywtm_change_url_path', $download['file'] );
				yith_plugin_fw_get_field(
					array(
						'id'          => "{$field['id']}_{$i}",
						'type'        => 'toggle-element-fixed',
						'title'       => $file_title,
						'name'        => "{$field['name']}[{$i}]",
						'onoff_field' => false,
						'value'       => $download,
						'elements'    => array(
							array(
								'id'    => 'name',
								'type'  => 'text',
								'class' => 'ywtm-file-name',
								'title' => __( 'File name', 'yith-woocommerce-tab-manager' ),
							),
							array(
								'id'    => 'desc',
								'type'  => 'text',
								'title' => __( 'File description', 'yith-woocommerce-tab-manager' ),
							),
							array(
								'id'    => 'file',
								'type'  => 'media',
								'title' => '',
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
                    <span class="ywtm-download-delete ywtm-delete-btn yith-icon yith-icon-close-alt"></span>
				<?php endif; ?>
            </div>
			<?php
			++ $i;
		endforeach;
		?>
    </div>
    <div class="ywtm-download-actions">
        <button class="yith-plugin-fw__button--add ywtm-add-btn ywtm-add-download"><?php esc_html_e( 'Add file', 'yith-woocommerce-tab-manager' ); ?></button>
    </div>
</div>
<script type="text/template" id="tmpl-ywtm-new-download-row-<?php echo esc_attr( $field['id'] ); ?>">
    <div class="ywtm-custom-field-toggle" data-index="{{{data.index}}}"
         data-prefix="<?php echo esc_attr( $prefix ); ?>">
		<?php

		yith_plugin_fw_get_field(
			array(
				'id'          => "{$field['id']}_{{{data.index}}}",
				'type'        => 'toggle-element-fixed',
				'title'       => '',
				'name'        => "{$field['name']}[{{{data.index}}}]",
				'onoff_field' => false,
				'value'       => array(
					'name' => '',
					'desc' => '',
					'file' => '',
				),
				'elements'    => array(
					array(
						'id'    => 'name',
						'type'  => 'text',
						'title' => __( 'File name', 'yith-woocommerce-tab-manager' ),
					),
					array(
						'id'    => 'desc',
						'type'  => 'text',
						'title' => __( 'File description', 'yith-woocommerce-tab-manager' ),
					),
					array(
						'id'    => 'file',
						'type'  => 'media',
						'title' => '',
					),
				),
			),
			true,
			false
		);
		?>
        <span class="ywtm-download-delete ywtm-delete-btn yith-icon yith-icon-close-alt"></span>
    </div>
</script>