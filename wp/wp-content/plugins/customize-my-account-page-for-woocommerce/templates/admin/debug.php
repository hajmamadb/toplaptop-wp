<?php
/**
 * Debug tab content page.
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

	<form method="post" action="options.php" id="tgwc-debug-form">
		<input type="hidden" name="tgwc_page" value="debug" />

		<?php
			/**
			 * Nonces, actions and referrers.
			 */
			settings_fields( 'tgwc' );
		?>
		<table class="form-table">
			<tbody>
				<!-- Enable debug -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Enable Debug', 'customize-my-account-page-for-woocommerce' ); ?>
						<span data-toggle="tgwc-tooltip"
							title="<?php esc_attr_e( 'Enable Debug', 'customize-my-account-page-for-woocommerce' ); ?>">
							<i class="fas fa-question-circle"></i>
						</span>
					</th>
					<td>
						<label for="tgwc_enable_debug">
							<input type="checkbox" id="tgwc_enable_debug" <?php checked( $debug['enable_debug'] ); ?>
								name="tgwc_debug_settings[enable_debug]" value="yes" />
							<span class="description">
								<?php esc_html_e( 'Enable debug mode.', 'customize-my-account-page-for-woocommerce' ); ?>
							</span>
						</label>
					</td>
				</tr>
				<!-- ./ Enable debug -->
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Frontend Libraries', 'customize-my-account-page-for-woocommerce' ); ?></h2>
		<p><?php esc_html_e( 'You can enable/disable javascript libraries which are loaded in WooCommerce MyAccount Page to resolve library conflict with other plugins.', 'customize-my-account-page-for-woocommerce'); ?></p>

		<table class="form-table">
			<tbody>

				<!-- Fontawesome -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Fontawesome', 'customize-my-account-page-for-woocommerce' ); ?>
						<span data-toggle="tgwc-tooltip"
							title="<?php esc_attr_e( 'Fontawesome', 'customize-my-account-page-for-woocommerce' ); ?>">
							<i class="fas fa-question-circle"></i>
						</span>
					</th>
					<td>
						<label for="tgwc-frontend-fontawesome-css">
							<input type="checkbox" id="tgwc-frontend-fontawesome-css" <?php checked( $debug['frontend']['fontawesome']['css'] ); ?> name="tgwc_debug_settings[frontend][fontawesome][css]" />
							<span style="margin-right: 25px;">
								<?php esc_html_e( 'CSS', 'customize-my-account-page-for-woocommerce'); ?>
							</span>
						</label>

						<label for="tgwc-frontend-fontawesome-js">
							<input type="checkbox" id="tgwc-frontend-fontawesome-js" <?php checked( $debug['frontend']['fontawesome']['js'] ); ?> name="tgwc_debug_settings[frontend][fontawesome][js]" />
							<span>
								<?php esc_html_e( 'JS', 'customize-my-account-page-for-woocommerce'); ?>
							</span>
						</label>
					</td>
				</tr>
				<!-- ./ Fontawesome -->

				<!-- Dropzone -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Dropzone', 'customize-my-account-page-for-woocommerce' ); ?>
						<span data-toggle="tgwc-tooltip"
							title="<?php esc_attr_e( 'Dropzone', 'customize-my-account-page-for-woocommerce' ); ?>">
							<i class="fas fa-question-circle"></i>
						</span>
					</th>
					<td>
						<label for="tgwc-frontend-dropzone-css">
							<input type="checkbox" id="tgwc-frontend-dropzone-css" <?php checked( $debug['frontend']['dropzone']['css'] ); ?> name="tgwc_debug_settings[frontend][dropzone][css]" />
							<span style="margin-right: 25px;">
								<?php esc_html_e( 'CSS', 'customize-my-account-page-for-woocommerce'); ?>
							</span>
						</label>

						<label for="tgwc-frontend-dropzone-js">
						<input type="checkbox" id="tgwc-frontend-dropzone-js" <?php checked( $debug['frontend']['dropzone']['js'] ); ?> name="tgwc_debug_settings[frontend][dropzone][js]" />
							<span>
								<?php esc_html_e( 'JS', 'customize-my-account-page-for-woocommerce'); ?>
							</span>
						</label>
					</td>
				</tr>
				<!-- ./ Dropzone -->

				<!-- jQuery Scroll Tabs -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'jQuery Scroll Tabs', 'customize-my-account-page-for-woocommerce' ); ?>
						<span data-toggle="tgwc-tooltip"
							title="<?php esc_attr_e( 'jQuery Scroll Tabs', 'customize-my-account-page-for-woocommerce' ); ?>">
							<i class="fas fa-question-circle"></i>
						</span>
					</th>
					<td>
						<label for="tgwc-frontend-jqueryscrolltabs-css">
							<input type="checkbox" id="tgwc-frontend-jqueryscrolltabs-css" <?php checked( $debug['frontend']['jqueryscrolltabs']['css'] ); ?> name="tgwc_debug_settings[frontend][jqueryscrolltabs][css]" />
							<span style="margin-right: 25px;">
								<?php esc_html_e( 'CSS', 'customize-my-account-page-for-woocommerce'); ?>
							</span>
						</label>

						<label for="tgwc-frontend-jqueryscrolltabs-js">
						<input type="checkbox" id="tgwc-frontend-jqueryscrolltabs-js" <?php checked( $debug['frontend']['jqueryscrolltabs']['js'] ); ?> name="tgwc_debug_settings[frontend][jqueryscrolltabs][js]" />
							<span>
								<?php esc_html_e( 'JS', 'customize-my-account-page-for-woocommerce'); ?>
							</span>
						</label>
					</td>
				</tr>
				<!-- ./ jQuery Scroll Tabs -->
			</tbody>
		</table>
	<form>

	<p>
		<button type="submit" name="submit" id="tgwc-submit"
			class="button button-primary">
			<?php esc_html_e( 'Save Changes', 'customize-my-account-page-for-woocommerce' ); ?>
		</button>


		<button type="submit" name="tgwc_reset_debug" class="button"
			id="tgwc-reset" value="reset">
			<?php esc_html_e( 'Restore Defaults', 'customize-my-account-page-for-woocommerce' ); ?>
		</button>
	</p>
</div>
<?php
