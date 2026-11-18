<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'toplaptop_wp' );

/** Database username */
define( 'DB_USER', 'wpuser' );

/** Database password */
define( 'DB_PASSWORD', 'WpLocal2026!' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1:3307' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'hk1x[/OI8N}>acJ.!pgy-ps!>UBWaV-ObE+P$CpgQ>Or8o.>?3_[7!D3* NYy7M:' );
define( 'SECURE_AUTH_KEY',   'y/RAD{am)U^cl/|$V #6(}`y&`Gbup*cRFRo?&V2zL=7Q]C%ap*pPe)__;s!*y|V' );
define( 'LOGGED_IN_KEY',     ' D1,RNK23t]sW!x$rgS((rCK:GY2q-8RUi?q trOrwV]?@_t+_1EgI|V Pi=F C>' );
define( 'NONCE_KEY',         'rggd}[L,zgVFZ^N}ZwZtW-|i0KR[1t1u)6aMdX&[1E]w!tX+E7scQrh;}5DA:V^E' );
define( 'AUTH_SALT',         'f#SB*+M,9!]]2(23vx}XWww6`h{3Anyhwk4i5f/16ANlC U294kaLRi+ZyF@.CEe' );
define( 'SECURE_AUTH_SALT',  '6~i=:wF8=.gE19,]om89eT3U]ujpy= ?)/ e$DZ6$RK$pOl9Cyq:I[f|Z&>%??pH' );
define( 'LOGGED_IN_SALT',    '|NOU_O1c:RP9HOm;2/Wbn!)M+GP-K25fcWeL,95Bv@^y[)zpR>,0oh[nOmkqXUGz' );
define( 'NONCE_SALT',        'A=&I@w$6Kjg/MeRS p(iY+,{E*94)3H0j#6|Ljt9Gm-8j?gU8zg!6-hI.ajXHq!|' );
define( 'WP_CACHE_KEY_SALT', 'N_z:@O]J/=<mLnv^zD86<ccvT?:}3r-n[Vpu_aS2-TpS6zD]IPeVdjH;oC;P3a4>' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WPLANG', 'fa_IR' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
