<?php
/**
 * Template functions
 */

if ( ! function_exists( 'tgwc_account_navigation' ) ) {
	/**
	 * Custom WooCommerce My Account Navigation.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function tgwc_account_navigation() {
		$customize        = tgwc_get_customizer_values();
		$menu_style       = $customize['wrapper']['menu_style'];
		$sidebar_position = $customize['wrapper']['sidebar_position'];
		$settings         = TGWC()->get_settings()->get_settings();

		$settings['nav_class'] = array(
			'tgwc-woocommerce-MyAccount-navigation',
			"tgwc-navbar-{$menu_style}",
		);

		if ( 'sidebar' === $menu_style ) {
			$settings['nav_class'][] = "tgwc-navbar-sidebar-{$sidebar_position}";
		}

		$settings['nav_class'] = apply_filters( 'tgwc_nav_class', $settings['nav_class'] );
		$settings['nav_class'] = implode( ' ', $settings['nav_class'] );

		wc_get_template(
			'frontend/navigation.php',
			$settings,
			TGWC_TEMPLATE_PATH,
			TGWC_TEMPLATE_PATH
		);
	}
}

/**
 * Display account menu item.
 *
 * @since 0.1.0
 *
 * @param string $slug  Endpoint slug.
 *
 * @return void
 */
function tgwc_display_myaccount_menu_item( $slug ) {
	$endpoint = \tgwc_get_endpoint( $slug );

	if ( false === $endpoint ) {
		return;
	}

	$account_menu_types = array(
		'endpoint',
		'link',
		'group',
	);

	$type = $endpoint['type'];
	if ( in_array( $type, $account_menu_types, true ) ) {
		$endpoint['slug'] = $slug;
		wc_get_template(
			"frontend/{$type}-item.php",
			$endpoint,
			TGWC_TEMPLATE_PATH,
			TGWC_TEMPLATE_PATH
		);
	}
}

if ( ! function_exists( 'tgwc_account_content' ) ) {
	/**
	 * Custom WooCommerce My Account Content.
	 *
	 * Override the woocommerce_account_content().
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function tgwc_account_content() {
		global $wp;

		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $slug => $value ) {
				// TODO: Inform the user that the following text cannot be used as slugs.
				if ( in_array( $slug, array( 'pagename', 'page', 'page_id', 'preview', 'dashboard' ), true  ) ) {
					continue;
				}

				if ( has_action( 'woocommerce_account_' . $slug . '_endpoint' ) ) {
					do_action( 'woocommerce_account_' . $slug . '_endpoint', $value );
					tgwc_display_acccount_content( $slug );
					return;
				}

				if ( tgwc_display_acccount_content( $slug ) ) {
					return;
				}
			}
		}

		$default_endpoint = tgwc_get_default_endpoint();
		if ( ! isset( $wp->query_vars['dashboard'] )
			&& has_action( 'woocommerce_account_' . $default_endpoint . '_endpoint' ) ) {
			do_action( 'woocommerce_account_' . $default_endpoint . '_endpoint', '' );
			tgwc_display_acccount_content( $default_endpoint );
		} elseif ( isset( $wp->query_vars['dashboard'] ) || 'dashboard' === $default_endpoint ) {
			tgwc_display_dashboard();
		} else {
			tgwc_display_acccount_content( $default_endpoint );
		}
	}
}

if ( ! function_exists( 'tgwc_display_dashboard' ) ) {
	/**
	 * Display dashboard when it is not set to default endpoint.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function tgwc_display_dashboard() {
		$endpoints = tgwc_get_endpoints_flat();

		if ( ! isset( $endpoints['edit-account'] ) ) {
			wc_get_template(
				'myaccount/dashboard.php',
				array(
					'current_user' => get_user_by( 'id', get_current_user_id() ),
				)
			);
		}

		$endpoint = $endpoints['dashboard'];

		if ( ! $endpoint['enable'] ) {
			return;
		}

		wc_get_template(
			'myaccount/dashboard.php',
			array(
				'current_user' => get_user_by( 'id', get_current_user_id() ),
			)
		);

		$content = trim( $endpoint['content'] );
		if ( ! empty( $content ) ) {
			tgwc_display_acccount_content( 'dashboard' );
		}
	}
}

/**
 * Display the account content template.
 *
 * @since 0.1.0
 *
 * @param string $slug Endpoint slug.
 * @return boolean True if the template is found, False otherwise.
 */
function tgwc_display_acccount_content( $slug ) {
	$endpoint = tgwc_get_endpoint( $slug );

	// Bail early if there no such endpoint exists.
	if ( false === $endpoint ) {
		return false;
	}

	$content = trim( $endpoint['content'] );

	$default_class = array_merge( $endpoint['class'], array(
		'tgwc-account_content',
		"tgwc-account_content_{$slug}"
	) );

	if ( $endpoint && $endpoint['enable'] && ! empty( $content ) ) {
		$class             = apply_filters( 'tgwc_account_content_class', $default_class );
		$endpoint['slug']  = $slug;
		$endpoint['class'] = implode( ' ', $class );

		wc_get_template(
			'frontend/account-content.php',
			$endpoint,
			TGWC_TEMPLATE_PATH,
			TGWC_TEMPLATE_PATH
		);
	}

	return true;
}

if ( ! function_exists( 'tgwc_user_avatar' ) ) {
	/**
	 * Display user avatar.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function tgwc_user_avatar() {
		$settings = TGWC()->get_settings()->get_settings();

		// Bail if custom avatar is not enabled.
		if ( ! $settings['custom_avatar'] ) {
			return;
		}

		$avatar_image              = get_user_meta( get_current_user_id(), 'tgwc_avatar_image', true );
		$settings['is_avatar_set'] = $avatar_image ? true : false;

		wc_get_template(
			'frontend/user-avatar.php',
			$settings,
			TGWC_TEMPLATE_PATH,
			TGWC_TEMPLATE_PATH
		);
	}
}

if ( ! function_exists( 'tgwc_avatar_url' ) ) {
	/**
	 * Update the modify the update url.
	 *
	 * @since 0.1.0
	 *
	 * @param string $url           The URL of the avatar.
	 * @param mixed  $id_or_email    The Gravatar to retrieve. Accepts a user ID, Gravatar MD5 hash, user email, WP_User object, WP_Post object, or WP_Comment object.
	 * @param array  $args           Arguments passed to get_avatar_data(), after processing.
	 *
	 * @return string Modify the url avatar.
	 */
	function tgwc_avatar_url( $url, $id_or_email, $args ) {
		// Bail early if the user is not logged in.
		if ( 0 === $id_or_email ) {
			return $url;
		}

		$attach_id  = get_user_meta( $id_or_email, 'tgwc_avatar_image', true );
		$image_size = apply_filters( 'tgwc_myaccount_image_size', 'thumbnail' );
		$image      = wp_get_attachment_image_src( $attach_id, $image_size );

		// Bail early if the image is not set.
		if ( false === $image ) {
			return $url;
		}

		return $image[0];
	}
}
