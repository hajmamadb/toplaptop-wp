<?php
/**
 * This template show the tab faq content
 *
 * @package YITH\TabManager\Templates
 *
 * @var array  $faqs The faq list to show
 * @var string $extra_content The extra content to show
 */

?>
<div class="ywtm_faqs_wrapper">
	<div class="ywtm_additional_content">
		<?php echo wp_kses_post( apply_shortcodes( $extra_content ) ); ?>
	</div>
	<div class="ywtm_faqs_content">
		<?php foreach ( $faqs as $faq ) : ?>
			<div class="ywtm_faq_item closed">
				<div class="ywtm_faq_header">
					<div class="ywtm_faq_icon">
				<span class="ywtm_faq_icon_close">
					<svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24"
						xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
	<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
</svg>
				</span>
						<span class="ywtm_faq_icon_open">
					<svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24"
						xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
	<path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"></path>
</svg>
				</span>
					</div>
					<div class="ywtm_faq_question">
						<?php echo wp_kses_post( $faq['question'] ); ?>
					</div>
				</div>
				<div class="ywtm_faq_answer">
					<?php echo wp_kses_post( $faq['answer'] ); ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
