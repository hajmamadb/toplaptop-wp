<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Icons' ) ) :

	final class WPSC_Icons {

		/**
		 * Core icon file names (relative to asset/icons/), read only when first rendered
		 *
		 * @var array
		 */
		private static $files = array(
			'edit'            => 'edit-solid.svg',
			'info-circle'     => 'info-circle-solid.svg',
			'plus-square'     => 'plus-square-solid.svg',
			'trash-alt'       => 'trash-alt-solid.svg',
			'times'           => 'times-solid.svg',
			'close-thin'      => 'close-thin.svg',
			'times-circle'    => 'times-circle-solid.svg',
			'reply'           => 'reply-solid.svg',
			'notes'           => 'notes.svg',
			'prioritize'      => 'prioritize.svg',
			'bars'            => 'bars-solid.svg',
			'chevron-up'      => 'chevron-up-solid.svg',
			'chevron-down'    => 'chevron-down-solid.svg',
			'chevron-left'    => 'chevron-left-solid.svg',
			'chevron-right'   => 'chevron-right-solid.svg',
			'control'         => 'control.svg',
			'cogs'            => 'cogs-solid.svg',
			'history'         => 'history-solid.svg',
			'id-card'         => 'id-card-solid.svg',
			'list-alt'        => 'list-alt-solid.svg',
			'gps-navigation'  => 'gps-navigation.svg',
			'search'          => 'search-solid.svg',
			'sort'            => 'sort-solid.svg',
			'sync'            => 'sync-solid.svg',
			'users'           => 'users-solid.svg',
			'ticket-alt'      => 'ticket-alt-solid.svg',
			'user-tie'        => 'user-tie-solid.svg',
			'headset'         => 'headset-solid.svg',
			'contact-form'    => 'contact-form.svg',
			'envelope'        => 'envelope-regular.svg',
			'widget'          => 'widget.svg',
			'check'           => 'check-solid.svg',
			'copy'            => 'copy-regular.svg',
			'checked'         => 'checked.svg',
			'font'            => 'font-solid.svg',
			'clock'           => 'clock-solid.svg',
			'calendar-times'  => 'calendar-times-solid.svg',
			'calendar-alt'    => 'calendar-alt-regular.svg',
			'trash-restore'   => 'trash-restore-solid.svg',
			'unlock'          => 'unlock-solid.svg',
			'palette'         => 'palette-solid.svg',
			'arrow-right'     => 'arrow-right-solid.svg',
			'arrow-left'      => 'arrow-left-solid.svg',
			'log-out'         => 'log-out.svg',
			'system'          => 'system.svg',
			'subfolder'       => 'subfolder.svg',
			'shield'          => 'shield.svg',
			'tags'            => 'tags.svg',
			'assignment'      => 'assignment.svg',
			'closed'          => 'closed.svg',
			'unread'          => 'unread.svg',
			'users-avatar'    => 'users-avatar.svg',
			'warning-sign'    => 'warning-sign.svg',
			'line-graph'      => 'line-graph.svg',
			'list'            => 'list.svg',
			'pie-chart'       => 'pie-chart.svg',
			'dashboard'       => 'dashboard.svg',
			'wp-logo'         => 'wp-logo.svg',
			'archive'         => 'archive.svg',
			'archive-restore' => 'archive-restore.svg',
		);

		/**
		 * SVG markup cache: icons added by the wpsc_icons filter plus core icons read so far
		 *
		 * @var array|null
		 */
		private static $icons = null;

		/**
		 * Return SVG markup of an icon, reading its file on first use
		 *
		 * @param string $name - icon name.
		 * @return string
		 */
		private static function get_svg( $name ) {

			if ( null === self::$icons ) {
				if ( ! did_action( 'init' ) ) {
					return '';
				}
				self::$icons = (array) apply_filters( 'wpsc_icons', array() );
			}

			if ( ! isset( self::$icons[ $name ] ) && isset( self::$files[ $name ] ) ) {
				self::$icons[ $name ] = (string) file_get_contents( WPSC_ABSPATH . 'asset/icons/' . self::$files[ $name ] ); //phpcs:ignore
			}

			return isset( self::$icons[ $name ] ) ? self::$icons[ $name ] : '';
		}

		/**
		 * Return SVG icon element
		 *
		 * @param string $name - icon name.
		 * @return void
		 */
		public static function get( $name ) {

			$allowed_tags = array(
				'svg'   => array(
					'class'           => true,
					'aria-hidden'     => true,
					'aria-labelledby' => true,
					'role'            => true,
					'xmlns'           => true,
					'width'           => true,
					'height'          => true,
					'viewbox'         => true,
				),
				'g'     => array( 'fill' => true ),
				'title' => array( 'title' => true ),
				'path'  => array(
					'd'    => true,
					'fill' => true,
				),
			);

			echo wp_kses( self::get_svg( $name ), $allowed_tags );
		}
	}
endif;
