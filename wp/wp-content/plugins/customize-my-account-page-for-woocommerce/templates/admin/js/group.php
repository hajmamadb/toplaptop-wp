<?php
/**
 * Group JS template.
 *
 * @since 0.1.0
 */

defined( 'ABSPATH' ) || exit;
?>
<script type="text/template" id="tmpl-tgwc-group">
	<div id="{{data.slug}}" class="tgwc-tabs-panel">
		<div class="tgwc-tabs-panel-header">
			<h2>{{data.text}}</h2>
			<i>{{data.type.substring(0, 1).toUpperCase() + data.type.substring(1)}}</i>
			<button class="tgwc-button tgwc-button--small tgwc-button--danger tgwc-delete-endpoints">
				{{data.i18n.remove}}
			</button>
		</div>

		<input type="hidden"
			name="tgwc_endpoints[{{data.slug}}][type]"
			value="{{data.type}}" />

		<table class="form-table">
			<tbody>
				<!-- Enable -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Enable', 'customize-my-account-page-for-woocommerce' ); ?>
						<span title="<?php esc_attr_e( 'Group enable', 'customize-my-account-page-for-woocommerce' ); ?>">
							<i class="fas fa-question-circle"></i>
						</span>
					</th>
					<td>
						<input type="checkbox"
							checked
							id="tgwc_endpoints[{{data.slug}}][enable]"
							name="tgwc_endpoints[{{data.slug}}][enable]" />
						<label for="tgwc_endpoints[{{data.slug}}][enable]">
							{{data.i18n.enable}} {{data.type.substring(0, 1).toUpperCase() + data.type.substring(1)}}
						</label>
					</td>
				</tr>
				<!-- ./ Enable -->

				<!-- Label -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Label', 'customize-my-account-page-for-woocommerce' ); ?>
						<span data-toggle="tgwc-tooltip" title="<?php esc_attr_e( 'Group label', 'customize-my-account-page-for-woocommerce' ); ?>">
							<i class="fas fa-question-circle"></i>
						</span>
					</th>
					<td>
						<input type="text"
							name="tgwc_endpoints[{{data.slug}}][label]"
							value="{{data.text}}" />
					</td>
				</tr>
				<!-- ./ Label -->

				<!-- Icon -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Icon', 'customize-my-account-page-for-woocommerce' ); ?>
						<span data-toggle="tgwc-tooltip" title="<?php esc_attr_e( 'Group icon', 'customize-my-account-page-for-woocommerce' ); ?>">
							<i class="fas fa-question-circle"></i>
						</span>
					</th>
					<td>
						<select style="width: 200px;"
							name="tgwc_endpoints[{{data.slug}}][icon]">
						</select>
					</td>
				</tr>
				<!-- ./ Icon -->

				<!-- Class -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Class', 'customize-my-account-page-for-woocommerce' ); ?>
						<span data-toggle="tgwc-tooltip" title="<?php esc_attr_e( 'Add CSS class', 'customize-my-account-page-for-woocommerce' ); ?>">
							<i class="fas fa-question-circle"></i>
						</span>
					</th>
					<td>
						<input type="text"
							name="tgwc_endpoints[{{data.slug}}][class]"
							value="" />
					</td>
				</tr>
				<!-- ./ Class -->

				<!-- User roles -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'User roles', 'customize-my-account-page-for-woocommerce' ); ?>
						<span data-toggle="tgwc-tooltip" title="<?php esc_attr_e( 'Display for selected user role only', 'customize-my-account-page-for-woocommerce' ); ?>">
							<i class="fas fa-question-circle"></i>
						</span>
					</th>
					<td>
						<select name="tgwc_endpoints[{{data.slug}}][user_role]"
							style="width: 50%;">
						</select>
					</td>
				</tr>
				<!-- ./ User roles -->
			</tbody>
		</table>
	</div>
</script>
<?php
