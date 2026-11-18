<?php
/**
 * Link JS template.
 *
 * @since 0.1.0
 */

defined( 'ABSPATH' ) || exit;
?>
<script type="text/template" id="tmpl-tgwc-link">
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
						<span title="<?php esc_attr_e( 'Link enable', 'customize-my-account-page-for-woocommerce' ); ?>">
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

				<!-- URL-->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'URL', 'customize-my-account-page-for-woocommerce' ); ?>
						<span data-toggle="tgwc-tooltip" title="<?php esc_attr_e( 'Link URL', 'customize-my-account-page-for-woocommerce' ); ?>">
							<i class="fas fa-question-circle"></i>
						</span>
					</th>
					<td>
						<input type="text"
							value = "#"
							name="tgwc_endpoints[{{data.slug}}][url]" />
					</td>
				</tr>
				<!-- ./ URL -->

				<!-- Label -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Label', 'customize-my-account-page-for-woocommerce' ); ?>
						<span data-toggle="tgwc-tooltip" title="<?php esc_attr_e( 'Link label', 'customize-my-account-page-for-woocommerce' ); ?>">
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
						<span title="<?php esc_attr_e( 'Link icon', 'customize-my-account-page-for-woocommerce' ); ?>">
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
							name="tgwc_endpoints[{{data.slug}}][class]" />
					</td>
				</tr>
				<!-- ./ Class -->

				<!-- User roles -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'user roles', 'customize-my-account-page-for-woocommerce' ); ?>
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

				<!-- Open in new tab -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Open in new tab?', 'customize-my-account-page-for-woocommerce' ); ?>
						<span data-toggle="tgwc-tooltip" title="<?php esc_attr_e( 'Open in new tab', 'customize-my-account-page-for-woocommerce' ); ?>">
							<i class="fas fa-question-circle"></i>
						</span>
					</th>
					<td>
						<input type="checkbox"
							name="tgwc_endpoints[{{data.slug}}][new_tab]" />
					</td>
				</tr>
				<!-- ./ Open in new tab -->
			</tbody>
		</table>
	</div>
</script>
<?php
