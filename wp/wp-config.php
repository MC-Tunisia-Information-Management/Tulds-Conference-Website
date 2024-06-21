<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ajwsiwgd_wp843' );

/** Database username */
define( 'DB_USER', 'ajwsiwgd_wp843' );

/** Database password */
define( 'DB_PASSWORD', 'B]873w(9Sp' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'sapztyvx9zwaoncega6acurzv6uqeuqrchn1sd6xp812vub6zehhzbbon1ccdrok' );
define( 'SECURE_AUTH_KEY',  '89vbuie6qbdikreyjzrvq52iuhu43k39n6ocq90kgsgcj0xqzfvgha5qmr2nrr7z' );
define( 'LOGGED_IN_KEY',    'cnsvnidjykulfcp642pqypfvr8fpw4wlzjiodtxeh1227w4usmx42nbttaddmgal' );
define( 'NONCE_KEY',        'qg70sbltzf44qov0r3sfkohhdgak2veqhlanifbstmgdgwev07dq1zbfeelr3upq' );
define( 'AUTH_SALT',        'ubbmjcqiibcwoactqgybwk3bpmzes6rwkdpo8teplr0fgvyy2tj3behiouyppnhw' );
define( 'SECURE_AUTH_SALT', 'ijory8jncfdvllj2lvf4ltl9lq5utuheohzs6dvgib34zq1x6v2mmevan1cerffl' );
define( 'LOGGED_IN_SALT',   'kgpakiics1dvwby9z56igdjikzicmu5zfmgvjdabiyyeu1x0gulrwkhhsdmue3xs' );
define( 'NONCE_SALT',       'ndatb95v941pubjolapbjolhiiuilrnlblkl3lluhwcx7gwuj63hxvksarxg5n5w' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wphl_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
