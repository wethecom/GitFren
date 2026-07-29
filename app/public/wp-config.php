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
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',          '$G6[(eDydCjD^8L6Ig4-G3^[f`M$}S^H:?Mn7TK;i-hK%bM%-(GhqK{|BU+xArj5' );
define( 'SECURE_AUTH_KEY',   ',YUWI4R:C:WdQbE?Q.EbTTy)aQl,jK(bd@ulN<TcD@tZH~dD}xugZ+&W6_PWRhIc' );
define( 'LOGGED_IN_KEY',     'WAnTT|E}r0#X{@QkKW:fPA4z? p`GbNn)?b!f;p.nLZV%PxY:dA^g~y^dp@CI!/9' );
define( 'NONCE_KEY',         '.jax7sa2`38[eIS~RMScgmF(v@;4QAk*G_5Y0ky&lOYBNm^{>W!Ra/_ZcnOgmELi' );
define( 'AUTH_SALT',         'pV`E`9tk4%:icrO>QZ=b?QyDpMp9l*LnT9rg-{J$<EbS6y8 <hRK8lxfi<m:{8Qy' );
define( 'SECURE_AUTH_SALT',  'E<4(6kS)cy,k}h}W_oK+}6rR!F8FB&4JC[|65]*70Onp`k&0[7)+S3V<YuO*y^.T' );
define( 'LOGGED_IN_SALT',    'b&k3SZ}fw7<rzYDgXf+{D6=Vbl Y!7m30^Q7k$b~+PKO9FgCmPw,QKEHo4y}a3Mh' );
define( 'NONCE_SALT',        '-o:8RD)53hy,j-_FRWs={n)9*H~q4z+_fg,+>V=N8^P[/`7.g| P6gtAxtX;_]U5' );
define( 'WP_CACHE_KEY_SALT', 'R/RS{gbwWw69/XWO;}}W5{Yi+X~3*Qb%g49#7>P]@RyzH.^l&KIyQ>n{C/MmM-l1' );


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

define( 'WP_HOME', 'http://git.local' );
define( 'WP_SITEURL', 'http://git.local' );

// GitFren — paths for repo storage and git binary (change these when moving server)
define( 'GH_REPOS_DIR', dirname( dirname( __DIR__ ) ) . '/repos' );
define( 'GIT_CMD_PATH', 'C:\\Program Files\\Git\\cmd' );

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
