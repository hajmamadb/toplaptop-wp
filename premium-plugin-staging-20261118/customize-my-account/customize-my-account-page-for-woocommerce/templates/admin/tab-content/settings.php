<?php
/**
 * Settings tab content page.
 *
 * @since 0.1.0
 */

defined( 'ABSPATH' ) || exit;
?>
<table class="form-table">
	<tbody>
		<!-- Custom avatar -->
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Custom Avatar', 'customize-my-account-page-for-woocommerce' ); ?>
				<span data-toggle="tgwc-tooltip" 
					title="<?php esc_attr_e( 'Custom Avatar', 'customize-my-account-page-for-woocommerce' ); ?>">
					<i class="fas fa-question-circle"></i>
				</span>
			</th>
			<td>
				<label for="tgwc_custom_avatar">
					<input type="checkbox" id="tgwc_custom_avatar" <?php checked( $settings['custom_avatar'] ); ?> name="tgwc_settings[custom_avatar]" />
					<span class="description">
						<?php esc_html_e( 'Allows user to upload custom avatar.', 'customize-my-account-page-for-woocommerce' ); ?>
					</span>
				</label>
			</td>
		</tr>
		<!-- ./ Custom avatar -->

		<!-- Endpoint Icon -->
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Endpoint Icon', 'customize-my-account-page-for-woocommerce' ); ?>
				<span data-toggle="tgwc-tooltip"
					title="<?php esc_attr_e( 'Endpoint Icon', 'customize-my-account-page-for-woocommerce' ); ?>">
					<i class="fas fa-question-circle"></i>
				</span>
			</th>
			<td>
				<label for="tgwc_icon">
					<input type="checkbox" id="tgwc_icon" <?php checked( $settings['icon'] ); ?> name="tgwc_settings[icon]" />
					<span class="description">
						<?php esc_html_e( 'Enable/Disable endpoint icon.', 'customize-my-account-page-for-woocommerce' ); ?>
					</span>
				</label>
			</td>
		</tr>
		<!-- ./ Endpoint Icon -->

		<!-- Endpoint icon position -->
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Endpoint icon position', 'customize-my-account-page-for-woocommerce' ); ?>
				<span data-toggle="tgwc-tooltip"
					title="<?php esc_attr_e( 'Endpoint icon position', 'customize-my-account-page-for-woocommerce' ); ?>">
					<i class="fas fa-question-circle"></i>
				</span>
			</th>
			<td>
				<select name="tgwc_settings[icon_position]">
					<option value="left"
						<?php selected( $settings['icon_position'], 'left' ); ?>>
						<?php esc_html_e( 'Left', 'customize-my-account-page-for-woocommerce' ); ?>
					</option>
					<option value="right"
						<?php selected( $settings['icon_position'], 'right' ); ?>>
						<?php esc_html_e( 'Right', 'customize-my-account-page-for-woocommerce' ); ?>
					</option>
				</select>
			</td>
		</tr>
		<!-- ./ Endpoint icon position -->

		<!-- Default endpoint -->
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Default Endpoint', 'customize-my-account-page-for-woocommerce' ); ?>
				<span data-toggle="tgwc-tooltip" 
					title="<?php esc_attr_e( 'Default endpoint', 'customize-my-account-page-for-woocommerce' ); ?>">
					<i class="fas fa-question-circle"></i>
				</span>
			</th>
			<td>
				<select name="tgwc_settings[default_endpoint]">
				<?php
				foreach ( tgwc_get_endpoints_by_type( 'endpoint' ) as $account_key => $account_item ) {
					$selected = selected( $account_key, $settings['default_endpoint'], false );
					printf(
						'<option %s value=%s>%s</option>',
						esc_attr( $selected ),
						esc_attr( $account_key ),
						esc_html( $account_item['label'] )
					);
				}
				?>
				</select>
			</td>
		</tr>
		<!-- ./ Default endpoint -->
	</tbody>
</table>
<?php
