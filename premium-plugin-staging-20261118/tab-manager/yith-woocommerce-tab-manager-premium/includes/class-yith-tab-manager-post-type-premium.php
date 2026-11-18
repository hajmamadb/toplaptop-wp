<?php
/**
 * The class that manage the post type
 *
 * @package YITH/TabManager\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_Tab_Manager_Post_Type_Premium' ) ) {
	/**
	 * The class for tab manager post type
	 */
	class YITH_Tab_Manager_Post_Type_Premium extends YITH_Tab_Manager_Post_Type {


		/**
		 * The construct of the class
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 2.0.0
		 */
		protected function __construct() {

			parent::__construct();
			add_filter( 'yith_wctm_post_type', array( $this, 'add_post_type_args' ), 20 );
		}

		/**
		 * Add args into post type
		 *
		 * @param array $post_type_args The args.
		 *
		 * @return array
		 * @since 1.1.15
		 */
		public function add_post_type_args( $post_type_args ) {

			$post_type_args['supports'][] = 'excerpt';

			return $post_type_args;
		}
	}

}
