<?php
/**
 * This template show the tab video gallery content
 *
 * @package YITH\TabManager\Templates
 *
 * @var array  $video The video list.
 * @var int    $columns The videos for row .
 * @var string $extra_content the extra content
 */

?>

<div class="ywtm_video_gallery_wrapper">
	<div class="ywtm_additional_content">
		<?php echo wp_kses_post( apply_shortcodes( $extra_content ) ); ?>
	</div>
	<div class="ywtm_video_gallery_content">
		<ul class="ywtm_video_gallery_list products columns-<?php echo esc_attr( $columns ); ?>">
			<?php foreach ( $video as $video_item ) : ?>
				<li class="product ywtm_video_item">
					<?php
					$video_host = $video_item['host'];
					$args       = array(
						'id'    => $video_item['id'],
						'url'   => $video_item['url'],
						'width' => '100%',
						'echo'  => false,
					);

					if ( 'youtube' === $video_host ) {
						echo YIT_Video::youtube( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} elseif ( 'vimeo' === $video_host ) {
						echo YIT_Video::vimeo( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
