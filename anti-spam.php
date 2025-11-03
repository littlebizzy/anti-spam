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

// minimum latin character ratio required for english-like detection
if ( ! defined( 'ANTI_SPAM_LATIN_MIN' ) ) {
    define( 'ANTI_SPAM_LATIN_MIN', 0.75 );
}

// minimum comment length required for language analysis
if ( ! defined( 'ANTI_SPAM_MIN_LEN' ) ) {
    define( 'ANTI_SPAM_MIN_LEN', 20 );
}

// block comments that do not look english-like (checks comment text only)
add_filter( 'pre_comment_approved', 'anti_spam_check_comment_language', 10, 2 );

function anti_spam_check_comment_language( $approved, $commentdata ) {
    $content = isset( $commentdata['comment_content'] ) ? (string) $commentdata['comment_content'] : '';

    if ( mb_strlen( $content, 'UTF-8' ) < ANTI_SPAM_MIN_LEN ) {
        return $approved;
    }

    if ( ! anti_spam_looks_english_simple( $content ) ) {
        return 'spam';
    }

    return $approved;
}

// detect english-like text using latin letter ratio (helper function)
function anti_spam_looks_english_simple( $text ) {
    // remove urls and email addresses before analysis
    $clean = preg_replace( '#https?://\S+#ui', '', $text );
    $clean = preg_replace( '/\S+@\S+\.\S+/u', '', $clean );

    // count total unicode letters
    preg_match_all( '/\p{L}/u', $clean, $m_letters );
    $letters_total = isset( $m_letters[0] ) ? count( $m_letters[0] ) : 0;

    // allow text with no letters to prevent false positives and division by zero
    if ( $letters_total === 0 ) {
        return true;
    }

    // count total latin letters in the text
    preg_match_all( '/\p{Latin}/u', $clean, $m_latin );
    $latin_total = isset( $m_latin[0] ) ? count( $m_latin[0] ) : 0;

    // compare latin letter ratio to threshold and return result
    $latin_ratio = $latin_total / $letters_total;

    return ( $latin_ratio >= ANTI_SPAM_LATIN_MIN );
}

// Ref: ChatGPT
