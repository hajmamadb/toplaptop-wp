<?php
/**
 * This class manage the plugin email
 *
 * @package YITH\TabManager\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The class manage the the email
 */
class YITH_Tab_Manager_Ask_Info_Email extends WC_Email {
	/**
	 * Default custom message.
	 *
	 * @var string
	 */
	public $custom_message = '';

	/**
	 * Default email language
	 *
	 * @var string
	 */
	public $previous_language = '';


	/**
	 * Enable CC
	 *
	 * @var boolean
	 */
	public $enable_cc = false;

	/**
	 * The constructor
	 */
	public function __construct() {
		$this->id             = 'yith_tab_manager_send_info';
		$this->customer_email = false;

		$this->template_base = YWTM_TEMPLATE_PATH . '/woocommerce/';
		$this->template_html = 'emails/ask-product-info.php';

		$this->placeholders = array_merge(
			array(
				'{product_name}' => '',
			),
			$this->placeholders
		);

		$this->set_default_params();
		add_action( 'send_tab_manager_email_notification', array( $this, 'trigger' ), 15, 2 );
		add_action( 'yith_ywtm_email_before_sending', array( $this, 'switch_language_to_translate_email' ), 10, 2 );
		add_action( 'yith_ywtm_email_after_sending', array( $this, 'switch_language_after_translating_email' ) );
		add_filter( 'woocommerce_email_styles', array( $this, 'add_custom_styles' ), 9999, 2 );
		parent::__construct();
	}


	/**
	 * Set default params.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function set_default_params() {

		$this->title          = __( '[YITH Tab Manager] Product info request', 'yith-woocommerce-tab-manager' );
		$this->description    = __( 'This email is sent when a customer clicks "Send" on the Tab Manager contact form.', 'yith-woocommerce-tab-manager' );
		$this->heading        = esc_html_x( 'You’ve got a message about the item "{product_name}"!', '[Admin panel] Email heading/subject', 'yith-woocommerce-tab-manager' );
		$this->subject        = esc_html_x( '{site_title} You have a new information request', '[Admin panel] Email heading/subject', 'yith-woocommerce-tab-manager' );
		$this->custom_message = esc_html_x(
			"Hi administrator,\nYou have just received an email about the following product:\n",
			'[Admin panel] Email content',
			'yith-woocommerce-tab-manager'
		);
		$this->recipient      = '' === $this->get_option( 'recipient' ) ? get_option( 'admin_email' ) : $this->get_option( 'recipient' );
		$this->enable_cc      = 'yes' === $this->get_option( 'enable_cc' );
		$this->email_type     = 'html';
	}

	/**
	 * Initialize placeholders before sending.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function init_placeholders_before_sending() {
		if ( isset( $this->object['info']['product'] ) && $this->object['info']['product'] instanceof WC_Product ) {
			$this->placeholders['{product_name}'] = $this->object['info']['product']->get_name();
			$this->placeholders                   = apply_filters( 'yith_ywtm_email_placeholders', $this->placeholders, $this );
		}
	}

	/**
	 * Trigger.
	 *
	 * @param array $args_obj The email arguments.
	 * @param bool  $output Check if the function should return a value.
	 *
	 * @return void|bool
	 * @since  2.0.0
	 */
	public function trigger( array $args_obj, bool $output = false ) {

		$this->object = $args_obj;

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return false;
		}

		/**
		 * DO_ACTION: yith_ywtm_email_before_sending
		 *
		 * Adds an action before email is sent.
		 *
		 * @param WC_Email $this The current email.
		 * @param string   $review The customer email.
		 */
		do_action( 'yith_ywtm_email_before_sending', $this, $this->customer_email );

		$this->init_placeholders_before_sending();
		$status = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

		/**
		 * DO_ACTION: yith_ywtm_email_after_sending
		 *
		 * Adds an action after email is sent.
		 *
		 * @param WC_Email $this The current email.
		 * @param string   $review The customer email.
		 */
		do_action( 'yith_ywtm_email_after_sending', $this, $this->customer_email );

		if ( $output ) {
			return $status;
		} else {
			$message = yith_plugin_fw_get_component(
				array(
					'type'        => 'notice',
					'dismissible' => false,
					'notice_type' => $status ? 'success' : 'warning',
					'message'     => $status ? __( 'Email sent successfully', 'yith-woocommerce-tab-manager' ) : __( 'An error occurred while sending your email', 'yith-woocommerce-tab-manager' ),
				),
				false
			);

			wp_send_json_success( $message );
		}
	}

	/**
	 * Get valid recipients.
	 *
	 * @return string
	 */
	public function get_recipient() {

		$recipient  = apply_filters( 'woocommerce_email_recipient_' . $this->id, $this->recipient, $this->object, $this );
		$recipients = array_map( 'trim', explode( ',', $recipient ) );
		if ( $this->enable_cc && isset( $this->object['info']['details']['email'] ) ) {
			$recipients[] = $this->object['info']['details']['email'];
		}
		$recipients = array_filter( $recipients, 'is_email' );

		return implode( ', ', $recipients );
	}

	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_subject() {
		if ( ! empty( $this->object['info']['details']['subject'] ) ) {
			$subject = $this->object['info']['details']['subject'];
		} else {
			$subject = $this->format_string( $this->get_option( 'subject', $this->get_default_subject() ) );
		}

		return apply_filters( 'woocommerce_email_subject_' . $this->id, $subject, $this->object, $this );
	}

	/**
	 * Return content from the custom_message field.
	 *
	 * @return string
	 * @since  2.0.0
	 */
	public function get_custom_message(): string {

		$custom_message = $this->get_option( 'custom_message', $this->custom_message );

		return $this->format_string( $custom_message );
	}

	/**
	 * Get content html.
	 *
	 * @return string
	 * @since  2.0.0
	 */
	public function get_content_html(): string {
		$params = array(
			'email_heading'  => $this->get_heading(),
			'sent_to_admin'  => ! $this->is_customer_email(),
			'plain_text'     => false,
			'email'          => $this,
			'custom_message' => $this->get_custom_message(),
		);

		return wc_get_template_html( $this->template_html, $params, '', $this->template_base );
	}

	/**
	 * Switch language to translate the email.
	 *
	 * @param YITH_Tab_Manager_Ask_Info_Email $email The email.
	 * @param bool                            $is_customer_email Customer email flag.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function switch_language_to_translate_email( $email, $is_customer_email ) {
		global $sitepress;

		if ( $is_customer_email && $sitepress ) {
			$language = $email->object['language'] ?? false;
			if ( $language ) {
				$current_language = $sitepress->get_current_language();

				if ( $language !== $current_language ) {
					$email->previous_language = $current_language;
					$sitepress->switch_lang( $language );
					$email->set_default_params();
				}
			}
		}
	}

	/**
	 * Switch language to the previous language.
	 *
	 * @param YITH_Tab_Manager_Ask_Info_Email $email The email.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function switch_language_after_translating_email( $email ) {
		global $sitepress;

		if ( $email->previous_language && $sitepress ) {
			$sitepress->switch_lang( $email->previous_language );
			$email->previous_language = '';
		}
	}

	/**
	 * Return email type.
	 * Allow only HTML and Multipart
	 *
	 * @return string
	 * @since  2.0.0
	 */
	public function get_email_type(): string {

		return 'html';
	}

	/**
	 * Init form fields to display in WC admin pages
	 *
	 * @return void
	 * @since  1.0
	 */
	public function init_form_fields() {
		$description       =
			'<span class="yith-ywtm-emails__email__placeholders">' .
			'<span class="yith-ywtm-emails__email__placeholders__label">' . esc_html_x( 'Available placeholders', '[Admin panel] Email placeholder text', 'yith-woocommerce-tab-manager' ) . ':</span>' .
			'<code>' . implode( '</code> <code>', array_keys( $this->placeholders ) ) . '</code>' .
			'</span>';
		$this->form_fields = array(
			'enabled'           => array(
				'title'   => __( 'Enable/Disable', 'yith-woocommerce-tab-manager' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'yith-woocommerce-tab-manager' ),
				'default' => 'yes',
			),
			'recipient'         => array(
				'title'       => __( 'Recipient(s)', 'yith-woocommerce-tab-manager' ),
				'type'        => 'text',
				/* translators: %s default admin email */
				'description' => sprintf( __( 'Enter recipients (separated by commas) for this email. Defaults to <code>%s</code>', 'yith-woocommerce-tab-manager' ), esc_attr( get_option( 'admin_email' ) ) ),
				'placeholder' => '',
				'default'     => '',
			),
			'subject'           => array(
				'title'       => __( 'Subject', 'yith-woocommerce-tab-manager' ),
				'type'        => 'text',
				'description' => __( 'Set the email subject to use if the customer does not enter a subject in the contact form.', 'yith-woocommerce-tab-manager' ) . '<br/>' . $description,
				'placeholder' => $this->subject,
				'default'     => '',
			),
			'heading'           => array(
				'title'       => __( 'Email Heading', 'yith-woocommerce-tab-manager' ),
				'type'        => 'text',
				/* translators: %s is the default heading */
				'description' => sprintf( __( 'This field lets you change the main heading in email notification. Leave it blank to use default heading type: <code>%s</code>.', 'yith-woocommerce-tab-manager' ), $this->heading ),
				'placeholder' => '',
				'default'     => '',
			),
			'enable_cc'         => array(
				'title'       => __( 'Send CC copy', 'yith-woocommerce-tab-manager' ),
				'type'        => 'checkbox',
				'description' => __( 'Send a carbon copy to the user', 'yith-woocommerce-tab-manager' ),
				'default'     => 'no',
			),
			'email-description' => array(
				'title'       => __( 'Email Description', 'yith-woocommerce-tab-manager' ),
				'type'        => 'textarea',
				'placeholder' => '',
				'default'     => $this->custom_message,
			),
		);
	}

	/**
	 * Admin Panel Options Processing.
	 */
	public function process_admin_options() {
		$post_data           = $this->get_post_data();
		$recipient_to_update = ! empty( $post_data['woocommerce_yith_tab_manager_send_info_recipient'] ) ? $post_data['woocommerce_yith_tab_manager_send_info_recipient'] : $this->recipient;
		$panel_option        = get_option( 'ywtm_contact_recipient', get_option( 'admin_email' ) );

		if ( $recipient_to_update !== $panel_option ) {
			update_option( 'ywtm_contact_recipient', $recipient_to_update );
		}
		parent::process_admin_options();
	}

	/**
	 * Add the custom style for the email
	 *
	 * @param string                          $css The css.
	 * @param YITH_Tab_Manager_Ask_Info_Email $email The email.
	 *
	 * @return string
	 */
	public function add_custom_styles( $css, $email =false) {
		if ( $email instanceof WC_Email && $this->id === $email->id ) {
			$css .= '
				.ywtm_ask_info_user_details_wrapper{
					display:flex;
					background: #f6f6f6;
				    padding: 20px;
				    margin-top: 20px;
                    margin-bottom: 20px;
                    flex-direction: column;
				    gap:20px;
				}
				.ywtm_ask_info_user_details_wrapper h3{
					margin:0!important;
				}
				ul.ywtm_ask_info_user_details{
					list-style: none;
				    padding: 0;
				    margin: 0;
				}
				.ywtm_ask_info_product_image{
					max-width:80px;
				}
				.ywtm_ask_info_email_product{
					display: flex;
	                flex-direction: row;
	                align-items: center;
	                gap:5px;
				}';
		}

		return $css;
	}
}

return new YITH_Tab_Manager_Ask_Info_Email();
