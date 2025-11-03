<?php
/*
Plugin Name: Anti-Spam
Plugin URI: https://www.littlebizzy.com/plugins/anti-spam
Description: Spam protection for WordPress
Version: 1.0.0
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
Requires PHP: 7.0
Tested up to: 6.7
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Update URI: false
GitHub Plugin URI: littlebizzy/anti-spam
Primary Branch: master
Text Domain: anti-spam
*/

// prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// override wordpress.org with git updater
add_filter( 'gu_override_dot_org', function( $overrides ) {
    $overrides[] = 'anti-spam/anti-spam.php';
    return $overrides;
}, 999 );

// define allowed languages using iso 639-1 codes (comma-separated)
if ( ! defined( 'ANTI_SPAM_LANGS' ) ) {
    define( 'ANTI_SPAM_LANGS', 'en' );
}



// Ref: ChatGPT
