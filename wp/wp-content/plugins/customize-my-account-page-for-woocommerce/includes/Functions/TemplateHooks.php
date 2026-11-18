<?php
/**
 * Template hooks.
 *
 * @since 0.1.0
 */

 /**
  * My Account.
  */

if ( function_exists( 'add_action' ) ) {
	add_action( 'woocommerce_account_navigation', 'tgwc_account_navigation', 10, 3 );
	add_action( 'woocommerce_account_content', 'tgwc_account_content' );
	add_action( 'tgwc_my_account_menu_item', 'tgwc_display_myaccount_menu_item' );
	add_action( 'tgwc_before_account_navigation_wrap', 'tgwc_user_avatar' );
	add_action( 'get_avatar_url', 'tgwc_avatar_url', 10, 3 );
}
