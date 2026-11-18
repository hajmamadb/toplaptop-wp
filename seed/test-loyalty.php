<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
global $wpdb;
$product = wc_get_product(wc_get_product_id_by_sku('TLT-U1335-TOUCH'));
$reward_id = (int) get_option('tlt_loyalty_reward_id');
if (!$product || !$reward_id) { WP_CLI::error('An eligible product and configured loyalty reward are required.'); }
foreach ($wpdb->get_results('SHOW TABLE STATUS', ARRAY_A) as $table) {
    if (str_starts_with($table['Name'], $wpdb->prefix) && $table['Engine'] !== 'InnoDB') {
        WP_CLI::error('Transactional storage is required for all store tables.');
    }
}
add_filter('pre_wp_mail', '__return_true', PHP_INT_MAX);
add_filter('wlr_before_send_email', '__return_false', PHP_INT_MAX);
add_filter('pre_http_request', static fn() => new WP_Error('tlt_qa_offline', 'Network disabled during local QA.'), PHP_INT_MAX);
$wpdb->query('START TRANSACTION');
$checks = array();
$session = null;
try {
    $login = 'tlt-loyalty-qa-' . strtolower(wp_generate_password(12, false));
    $email = $login . '@example.invalid';
    $user_id = wp_insert_user(array('user_login' => $login, 'user_pass' => wp_generate_password(32), 'user_email' => $email, 'role' => 'customer'));
    if (is_wp_error($user_id)) { throw new RuntimeException($user_id->get_error_message()); }
    wp_set_current_user($user_id);
    WC()->initialize_session();
    WC()->initialize_cart();
    $session = WC()->session;
    $balance = static fn() => (int) \Wlr\App\Helpers\Rewards::getInstance()->getPointBalanceByEmail($email);
    $make_order = static function () use ($user_id, $email, $product) {
        $order = wc_create_order(array('customer_id' => $user_id, 'status' => 'pending'));
        if (is_wp_error($order)) { throw new RuntimeException($order->get_error_message()); }
        $order->set_billing_email($email);
        $order->set_created_via('tlt-local-qa');
        $order->add_product($product, 1, array('subtotal' => 100000000, 'total' => 100000000));
        $order->calculate_totals(false);
        $order->save();
        return $order;
    };
    $refunded_order = $make_order();
    $checks['pending_order_earns_nothing'] = $balance() === 0;
    $refunded_order->update_status('completed');
    $checks['completed_purchase_earns_100_points'] = $balance() === 100;
    do_action('woocommerce_order_status_changed', $refunded_order->get_id(), 'pending', 'completed', $refunded_order);
    $checks['repeated_completion_does_not_double_points'] = $balance() === 100;
    $refund = wc_create_refund(array('order_id' => $refunded_order->get_id(), 'amount' => $refunded_order->get_total(), 'reason' => 'Local transactional QA', 'refund_payment' => false, 'restock_items' => false));
    if (is_wp_error($refund)) { throw new RuntimeException($refund->get_error_message()); }
    $refunded_order = wc_get_order($refunded_order->get_id());
    $checks['full_refund_reverses_points'] = $balance() === 0;
    do_action('woocommerce_order_status_changed', $refunded_order->get_id(), 'completed', 'refunded', $refunded_order);
    $checks['repeated_refund_does_not_double_debit'] = $balance() === 0;
    $cancelled_order = $make_order();
    $cancelled_order->update_status('completed');
    $checks['second_purchase_earns_points'] = $balance() === 100;
    $cancelled_order->update_status('cancelled');
    $checks['cancellation_reverses_points'] = $balance() === 0;
    $redeem_order = $make_order();
    $redeem_order->update_status('completed');
    if (!defined('DOING_AJAX')) { define('DOING_AJAX', true); }
    $die_handler = static fn() => static function () { throw new RuntimeException('tlt_qa_ajax_end'); };
    add_filter('wp_die_ajax_handler', $die_handler, PHP_INT_MAX);
    $redeem = static function ($points) use ($reward_id) {
        $_POST = array('wlr_nonce' => wp_create_nonce('wlr_reward_nonce'), 'reward_id' => $reward_id, 'type' => 'reward', 'points' => $points);
        $_REQUEST = $_POST;
        ob_start();
        try {
            (new \Wlr\App\Controllers\Site\Main())->applyReward();
        } catch (RuntimeException $error) {
            if ($error->getMessage() !== 'tlt_qa_ajax_end') { ob_end_clean(); throw $error; }
        }
        return json_decode(ob_get_clean(), true);
    };
    $minimum_response = $redeem(99);
    $checks['redemption_minimum_is_enforced'] = isset($minimum_response['success']) && !$minimum_response['success'] && $balance() === 100;
    $response = $redeem(100);
    $checks['native_redemption_succeeds'] = !empty($response['success']);
    $coupon_code = (string) $session->get('wlr_discount_code', '');
    $coupon = new WC_Coupon($coupon_code);
    $checks['redemption_creates_one_million_toman_coupon'] = $coupon->get_id() > 0 && $coupon->get_discount_type() === 'fixed_cart' && (float) $coupon->get_amount() === 1000000.0;
    $checks['coupon_is_personal_and_single_use'] = $coupon->get_email_restrictions() === array($email) && $coupon->get_usage_limit() === 1 && $coupon->get_usage_limit_per_user() === 1;
    $checks['redemption_debits_100_points'] = $balance() === 0;
    remove_filter('wp_die_ajax_handler', $die_handler, PHP_INT_MAX);
} finally {
    if ($session) { remove_action('shutdown', array($session, 'save_data'), 20); }
    wp_set_current_user(0);
    $_POST = array();
    $_REQUEST = array();
    $wpdb->query('ROLLBACK');
}
WP_CLI::line(wp_json_encode($checks, JSON_PRETTY_PRINT));
if (in_array(false, $checks, true)) { WP_CLI::error('One or more loyalty checks failed; all QA database changes were rolled back.'); }
WP_CLI::success('Native earning, refund, cancellation and redemption verified; all QA database changes rolled back.');
