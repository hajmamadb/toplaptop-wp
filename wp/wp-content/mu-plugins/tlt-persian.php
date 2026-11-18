<?php
/**
 * تاپ لپتاپ — Persian front-end localization.
 * Converts number formatting to Persian (Farsi) digits on the frontend.
 * Non-invasive: uses core / WooCommerce filters only; no core files modified.
 */

function tlt_to_persian_digits( $value ) {
    return str_replace(
        array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
        array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
        (string) $value
    );
}

// General i18n numbers (dates, counts, etc.).
add_filter( 'number_format_i18n', 'tlt_to_persian_digits', 10, 3 );

// wc_price() formats via PHP number_format() and exposes this filter.
add_filter( 'formatted_woocommerce_price', 'tlt_to_persian_digits', 10, 6 );

// Force the IRR currency symbol to "تومان".
add_filter( 'woocommerce_currency_symbol', function ( $symbol, $currency ) {
    if ( in_array( strtolower( (string) $currency ), array( 'irr' ), true ) ) {
        return 'تومان';
    }
    return $symbol;
}, 10, 2 );
