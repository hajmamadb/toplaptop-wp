<?php
/**
 * This template show the tab download content
 *
 * @package YITH\TabManager\Templates
 *
 * @var array  $files The file list to show
 * @var string $extra_content The extra content to show
 */

$file_types = apply_filters( 'ywtm_file_types_to_open_in_browser', array( 'pdf' ) );

?>
<div class="ywtm_downloads_wrapper">
	<div class="ywtm_additional_content">
		<?php echo wp_kses_post( apply_shortcodes( $extra_content ) ); ?>
	</div>
	<div class="ywtm_downloads_content">
		<?php
		foreach ( $files as $file ) :
			/**
			 * APPLY_FILTERS: ywtm_change_url_path
			 *
			 * The filter allow to edit the file url.
			 *
			 * @param string $file_url the options.
			 *
			 * @return string
			 */
			$file['file'] = apply_filters( 'ywtm_change_url_path', $file['file'] );
			$file_name    = $file['name'];
			$ext          = wp_check_filetype( $file['file'] );
			$file_id      = attachment_url_to_postid( $file['file'] );
			if ( ! is_null( $file_id ) ) {
				$file_path = get_attached_file( $file_id );
				$size      = floor( wp_filesize( $file_path ) / 1000 );
				$file_info = sprintf( '(%1$s, %2$sKB)', $ext['ext'], $size );
			} else {
				$file_info = sprintf( '(%1$s)', $ext['ext'] );
			}

			$download = true;
			if ( isset( $ext['ext'] ) && ( in_array( strtolower( $ext['ext'] ), $file_types, true ) ) ) {
				$download = false;
			}


			?>
			<div class="ywtm_download_item">
				<div class="ywtm_download_file_info">
					<div class="ywtm_download_file_info_name">
						<?php echo wp_kses_post( $file_name ); ?>
						<span class="ywtm_dowload_file_size">
						<?php echo esc_html( $file_info ); ?>
					</span>
					</div>
					<div class="ywtm_dowload_file_info_desc">
						<?php echo wp_kses_post( $file['desc'] ); ?>
					</div>
				</div>
				<div class="ywtm_download_action">
					<a class="ywtm_download_btn"
						href="<?php echo esc_url( $file['file'] ); ?>" <?php echo $download ? 'download' : ''; ?>
						target="_blank">
						<svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24"
							xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round"
									d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"></path>
						</svg>
						<?php esc_html_e( 'Download', 'yith-woocommerce-tab-manager' ); ?>
					</a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>