<?php
/**
 * The video list field
 *
 * @package YITH\TabManager\Metaboxes\Types
 *
 * @var array $field The field.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$empty = array(
	array(
		'name' => '',
		'host' => 'youtube',
		'id'   => '',
		'url'  => '',
	),
);

$videos = is_array( $field['value'] ) && isset( $field['value'][0]['name'] ) ? $field['value'] : $empty;
$data   = $field['data'] ?? array();

$id  = $field['id'] ?? $field['name'];
?>

<div id="<?php echo esc_attr( $id ); ?>"
     class="ywtm-videos-field-wrapper" <?php yith_plugin_fw_html_data_to_string( $data, true ); ?>>
    <div class="ywtm-video-fields-overflow">
        <div class="ywtm-videos-field-list">
			<?php
			$i = 0;
			foreach ( $videos as $video ) :
				?>
                <div class="ywtm-video-single-grid" data-index="<?php echo esc_attr( $i ); ?>">
                    <div class="ywtm-video-single-row yith-plugin-fw--required">
                        <label for="ywtm-video-name-<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Video name', 'yith-woocommerce-tab-manager' ); ?></label>
						<?php
						yith_plugin_fw_get_field(
							array(
								'type'  => 'text',
								'value' => $video['name'],
								'id'    => 'ywtm-video-name-' . esc_attr( $i ),
								'name'  => "{$field['name']}[{$i}][name]",
							),
							true,
							false
						);
						?>
                    </div>
                    <div class="ywtm-video-single-row">
                        <label for="ywtm-video-host-<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Hosting service', 'yith-woocommerce-tab-manager' ); ?></label>
						<?php
						yith_plugin_fw_get_field(
							array(
								'type'    => 'select',
								'class'   => 'yith-plugin-fw-select wc-enhanced-select',
								'options' => array(
									'youtube' => __( 'YouTube', 'yith-woocommerce-tab-manager' ),
									'vimeo'   => __( 'Vimeo', 'yith-woocommerce-tab-manager' ),
								),
								'value'   => $video['host'],
								'id'      => 'ywtm-video-host-' . esc_attr( $i ),
								'name'    => "{$field['name']}[{$i}][host]",
							),
							true,
							false
						);
						?>
                    </div>
                    <div class="ywtm-only-one-required">
                        <div class="ywtm-video-single-row">
                            <label for="ywtm-video-id-<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Video ID', 'yith-woocommerce-tab-manager' ); ?></label>
							<?php
							yith_plugin_fw_get_field(
								array(
									'type'  => 'text',
									'value' => $video['id'],
									'id'    => 'ywtm-video-id-' . esc_attr( $i ),
									'name'  => "{$field['name']}[{$i}][id]",
								),
								true,
								false
							);
							?>
                        </div>
                        <div class="ywtm-video-single-row">
                            <label for="ywtm-video-url-<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Video URL', 'yith-woocommerce-tab-manager' ); ?></label>
							<?php
							yith_plugin_fw_get_field(
								array(
									'type'  => 'text',
									'value' => $video['url'],
									'id'    => 'ywtm-video-url-' . esc_attr( $i ),
									'name'  => "{$field['name']}[{$i}][url]",
								),
								true,
								false
							);
							?>
                        </div>
                    </div>
					<?php
					if ( $i > 0 ) :
						?>
                        <span class="ywtm-video-delete ywtm-delete-btn yith-icon yith-icon-close-alt"></span>
					<?php endif; ?>
                </div>
				<?php
				++ $i;
			endforeach;
			?>
        </div>
    </div>
    <div class="ywtm-video-actions">
        <button class="yith-plugin-fw__button--add ywtm-add-btn ywtm-add-video"><?php esc_html_e( 'Add video', 'yith-woocommerce-tab-manager' ); ?></button>
    </div>
</div>
<script type="text/template" id="tmpl-ywtm-new-video-row-<?php echo esc_attr( $id ); ?>">
    <div class="ywtm-video-single-grid" data-index="{{{data.index}}}">
        <div class="ywtm-video-single-row yith-plugin-fw--required">
            <label for="ywtm-video-name-{{{data.index}}}>"><?php esc_html_e( 'Video name', 'yith-woocommerce-tab-manager' ); ?></label>
			<?php
			yith_plugin_fw_get_field(
				array(
					'type'  => 'text',
					'value' => '',
					'id'    => 'ywtm-video-name-{{{data.index}}}',
					'name'  => "{$field['name']}[{{{data.index}}}][name]",
				),
				true,
				false
			);
			?>
        </div>
        <div class="ywtm-video-single-row">
            <label for="ywtm-video-host-{{{data.index}}}"><?php esc_html_e( 'Hosting service', 'yith-woocommerce-tab-manager' ); ?></label>
			<?php
			yith_plugin_fw_get_field(
				array(
					'type'    => 'select',
					'class'   => 'yith-plugin-fw-select wc-enhanced-select',
					'options' => array(
						'youtube' => __( 'YouTube', 'yith-woocommerce-tab-manager' ),
						'vimeo'   => __( 'Vimeo', 'yith-woocommerce-tab-manager' ),
					),
					'value'   => 'youtube',
					'id'      => 'ywtm-video-host-{{{data.index}}}',
					'name'    => "{$field['name']}[{{{data.index}}}][host]",
				),
				true,
				false
			);
			?>
        </div>
        <div class="ywtm-only-one-required">
            <div class="ywtm-video-single-row">
                <label for="ywtm-video-id-{{{data.index}}}"><?php esc_html_e( 'Video ID', 'yith-woocommerce-tab-manager' ); ?></label>
				<?php
				yith_plugin_fw_get_field(
					array(
						'type'  => 'text',
						'value' => '',
						'id'    => 'ywtm-video-id-{{{data.index}}}',
						'name'  => "{$field['name']}[{{{data.index}}}][id]",
					),
					true,
					false
				);
				?>
            </div>
            <div class="ywtm-video-single-row">
                <label for="ywtm-video-url-{{{data.index}}}"><?php esc_html_e( 'Video URL', 'yith-woocommerce-tab-manager' ); ?></label>
				<?php
				yith_plugin_fw_get_field(
					array(
						'type'  => 'text',
						'value' => '',
						'id'    => 'ywtm-video-url-{{{data.index}}}',
						'name'  => "{$field['name']}[{{{data.index}}}][url]",
					),
					true,
					false
				);
				?>
            </div>
        </div>
        <span class="ywtm-video-delete ywtm-delete-btn yith-icon yith-icon-close-alt"></span>
    </div>
</script>