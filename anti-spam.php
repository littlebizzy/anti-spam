<?php
/*
Plugin Name: Anti-Spam
Plugin URI: https://www.littlebizzy.com/plugins/anti-spam
Description: Spam protection for WordPress
Version: 1.3.0
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
Requires PHP: 7.0
Tested up to: 6.8
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

// define honeypot field name
if ( ! defined( 'ANTI_SPAM_HONEYPOT_FIELD' ) ) {
    define( 'ANTI_SPAM_HONEYPOT_FIELD', 'anti_spam_hp' );
}

// define timestamp field name
if ( ! defined( 'ANTI_SPAM_TIMESTAMP_FIELD' ) ) {
    define( 'ANTI_SPAM_TIMESTAMP_FIELD', 'anti_spam_ts' );
}

// define minimum form fill time in seconds
if ( ! defined( 'ANTI_SPAM_MIN_FILL_TIME' ) ) {
    define( 'ANTI_SPAM_MIN_FILL_TIME', 3 );
}

// define allowed languages using iso 639-1 codes (comma-separated)
if ( ! defined( 'ANTI_SPAM_LANGS' ) ) {
    define( 'ANTI_SPAM_LANGS', 'en' );
}

// define minimum latin character ratio required for english-like detection
if ( ! defined( 'ANTI_SPAM_LATIN_MIN' ) ) {
    define( 'ANTI_SPAM_LATIN_MIN', 0.75 );
}

// define minimum comment length required for language analysis
if ( ! defined( 'ANTI_SPAM_MIN_LEN' ) ) {
    define( 'ANTI_SPAM_MIN_LEN', 20 );
}

// output honeypot and timestamp fields in comment forms
add_action( 'comment_form_after_fields', 'anti_spam_output_fields' );
add_action( 'comment_form_logged_in_after', 'anti_spam_output_fields' );

function anti_spam_output_fields() {
    echo '<p style="display:none !important;">';
    echo '<label for="' . esc_attr( ANTI_SPAM_HONEYPOT_FIELD ) . '">leave this field empty</label>';
    echo '<input type="text" name="' . esc_attr( ANTI_SPAM_HONEYPOT_FIELD ) . '" value="" autocomplete="off" tabindex="-1" />';
    echo '</p>';
    echo '<input type="hidden" name="' . esc_attr( ANTI_SPAM_TIMESTAMP_FIELD ) . '" value="' . esc_attr( time() ) . '" />';
}

// early honeypot and timing check for comments
add_filter( 'preprocess_comment', 'anti_spam_check_comment_submission', 1 );

function anti_spam_check_comment_submission( $commentdata ) {
    // honeypot check
    if ( ! empty( $_POST[ ANTI_SPAM_HONEYPOT_FIELD ] ) ) {
        wp_die();
    }

    // minimum fill time check
    if ( isset( $_POST[ ANTI_SPAM_TIMESTAMP_FIELD ] ) ) {
        $elapsed = time() - (int) $_POST[ ANTI_SPAM_TIMESTAMP_FIELD ];
        if ( $elapsed < ANTI_SPAM_MIN_FILL_TIME ) {
            wp_die();
        }
    }

    return $commentdata;
}

// block comments that do not look english-like (checks comment text only)
add_filter( 'pre_comment_approved', 'anti_spam_check_comment_language', 10, 2 );

function anti_spam_check_comment_language( $approved, $commentdata ) {
    // get comment text
    $content = isset( $commentdata['comment_content'] ) ? (string) $commentdata['comment_content'] : '';

    // normalize utf-8
    $content = wp_check_invalid_utf8( $content );

    // skip short comments
    if ( mb_strlen( $content, 'UTF-8' ) < ANTI_SPAM_MIN_LEN ) {
        return $approved;
    }

    // check for english-like text
    if ( ! anti_spam_looks_english_simple( $content ) ) {
        return 'spam';
    }

    // approve if text is acceptable
    return $approved;
}

// block new bbPress topics and replies that do not look english-like
// Note: These hooks check the $args array right before the post is inserted into the database.
if ( function_exists( 'bbp_get_topic_post_type' ) ) {
    add_filter( 'bbp_new_topic_pre_insert', 'anti_spam_check_bbpress_post', 1 );
}
if ( function_exists( 'bbp_get_reply_post_type' ) ) {
    add_filter( 'bbp_new_reply_pre_insert', 'anti_spam_check_bbpress_post', 1 );
}

function anti_spam_check_bbpress_post( $args ) {
    // honeypot check
    if ( ! empty( $_POST[ ANTI_SPAM_HONEYPOT_FIELD ] ) ) {
        $args['post_status'] = 'spam';
        return $args;
    }

    // minimum fill time check
    if ( isset( $_POST[ ANTI_SPAM_TIMESTAMP_FIELD ] ) ) {
        $elapsed = time() - (int) $_POST[ ANTI_SPAM_TIMESTAMP_FIELD ];
        if ( $elapsed < ANTI_SPAM_MIN_FILL_TIME ) {
            $args['post_status'] = 'spam';
            return $args;
        }
    }

    // get post content (topic/reply text)
    $content = isset( $args['post_content'] ) ? (string) $args['post_content'] : '';

    // normalize utf-8
    $content = wp_check_invalid_utf8( $content );

    // skip short posts/replies
    if ( mb_strlen( $content, 'UTF-8' ) < ANTI_SPAM_MIN_LEN ) {
        return $args;
    }

    // check for english-like text, set status to 'spam' if check fails
    if ( ! anti_spam_looks_english_simple( $content ) ) {
        $args['post_status'] = 'spam';
    }

    // return the (possibly modified) arguments array
    return $args;
}

// detect english-like text using latin letter ratio (helper function)
function anti_spam_looks_english_simple( $text ) {
    // normalize utf-8
    $text = wp_check_invalid_utf8( $text );

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
