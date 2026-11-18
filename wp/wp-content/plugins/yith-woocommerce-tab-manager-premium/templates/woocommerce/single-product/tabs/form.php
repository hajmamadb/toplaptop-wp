<?php
/**
 * This template show the tab contact form content
 *
 * @package YITH\TabManager\Templates
 *
 * @var array  $form_element The form element to show
 * @var string $extra_content The extra content to show
 */

global $product;
wp_enqueue_style( 'yith-plugin-ui' );
?>
<div class="ywtm_contact_form_wrapper">
	<div class="ywtm_additional_content">
		<?php echo wp_kses_post( apply_shortcodes( $extra_content ) ); ?>
	</div>
	<form method="post" class="ywtm_contact_form_content">
		<div class="ywtm_contact_form_first_row">
			<?php
			foreach ( $form_element as $key => $field ) :
				if ( in_array( $key, array( 'name', 'email', 'webaddr' ), true ) && isset( $field['enabled'] ) ) :
					$label = isset( $field['required'] ) ? $field['label'] . '*' : $field['label'];
					?>
					<div class="ywtm_contact_form_field <?php echo esc_attr( $key ); ?> <?php echo isset( $field['required'] ) ? esc_attr( 'ywtm-field-required' ) : ''; ?>">
						<label for="ywtm_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
						<input id="ywtm_<?php echo esc_attr( $key ); ?>"
								name="ywtm_<?php echo esc_attr( $key ); ?>"
								type="<?php echo 'email' === $key ? 'email' : 'text'; ?>"
								placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
								autocomplete="off"
						/>
					</div>
					<?php
				endif;
endforeach;
			?>
		</div>
		<?php
		if ( isset( $form_element['subj']['enabled'] ) ) :
			$label = isset( $form_element['subj']['required'] ) ? $form_element['subj']['label'] . '*' : $form_element['subj']['label'];
			?>
			<div class="ywtm_contact_form_second_row">
				<div class="ywtm_contact_form_field subject <?php echo isset( $form_element['subj']['required'] ) ? 'ywtm-field-required' : ''; ?>">
					<label for="ywtm_subject"><?php echo esc_html( $label ); ?></label>
					<input id="ywtm_subject"
							name="ywtm_subject"
							type="text"
							autocomplete="off"
							placeholder="<?php echo esc_attr( $form_element['subj']['placeholder'] ); ?>"/>
				</div>
			</div>
		<?php endif; ?>
		<div class="ywtm_contact_form_message ywtm-field-required">
			<label for="ywtm_message"><?php echo esc_attr( $form_element['message']['label'] . '*' ); ?></label>
			<textarea id="ywtm_message"
						name="ywtm_message"
						rows="10"
						placeholder="<?php echo esc_attr( $form_element['message']['placeholder'] ); ?>"></textarea>
		</div>
		<div class="ywtm_contact_form_action">
			<span><?php esc_html_e( '*required fields', 'yith-woocommerce-tab-manager' ); ?></span>
			<input type="submit" class="ywtm_send_form"
					value="<?php esc_html_e( 'Send', 'yith-woocommerce-tab-manager' ); ?>">
		</div>
		<input type="hidden" name="ywtm_product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">
	</form>

</div>
