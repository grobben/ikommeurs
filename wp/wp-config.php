<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'ikom_basic');

/** MySQL database username */
define('DB_USER', 'ikom_basic');

/** MySQL database password */
define('DB_PASSWORD', '1k0mDB-Basic');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ']),[H?NM(|-g{h:+da.-@U9g[(=3GM~;<d$+3HwN=~EVoLv- sXRyJ)/gj;+to7b');
define('SECURE_AUTH_KEY',  '(-._+Id%;uPoaP+r-Cx.tm!b= R,vEb_@`KARGF<--m1H__Wk=8Vy|_m1+`h`a>2');
define('LOGGED_IN_KEY',    '+Rqz+&~=f~)l|33h4JSux_T.]*F;|b/TYGpSkX;*vOXN?c*0cZ[6 F:)aMV~W3^e');
define('NONCE_KEY',        '0^[K:OAY;]S?Pj:x+Btw}i-airc{P]K9J)w$Cf0W4?7aA0{W]y Y<c#sT:<&)#4O');
define('AUTH_SALT',        '(_,u7dqYB2.x2u{BC5N*>!^|BlQ2cf:P(zix>FwN|z`p`$zX`yj[Q;|6@x;:)-S^');
define('SECURE_AUTH_SALT', '!V6%WdAbRCg*7{dYt3rha$91xB&_/-.bCj/o-_RRv%@B.i+7sx1U*-b?T+.|,AeR');
define('LOGGED_IN_SALT',   '-8C0i (ng(bDCNZpImG|<<Ox:GEKRZqXgZ-uCQ:mjEes{bf,mg?=u?C2_G<~!pOW');
define('NONCE_SALT',       'pav|4G>7GpOYz-z<JnUn`mbyJ]?fRc[Zc?B!tb-1-}%vi-n8;%[}g9$,ch;Rv=#J');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
