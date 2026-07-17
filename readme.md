# Anti-Spam

Spam protection for WordPress

## Description

Anti-Spam provides lightweight spam protection for native WordPress comments and bbPress forums without captchas, external APIs, tracking, browser fingerprinting, or IP-based blocking. All checks run locally within WordPress, with no remote reputation services or third-party data collection.

For WordPress comments, the plugin adds a hidden honeypot field, a minimum form completion time check, and a short-lived single-use token stored through the WordPress Transients API. Each token stores the server-generated form timestamp so submitted timestamps cannot be replaced to bypass the timing check. Submissions that fail these early form checks are rejected before normal comment processing, while content that fails the secondary language-ratio check is sent to the WordPress spam queue.

For bbPress, the plugin adds honeypot and timestamp fields to standard new-topic and new-reply forms while leaving topic and reply edit forms unchanged. bbPress continues to handle its own native nonce validation, and suspicious forum submissions are assigned the native bbPress spam status so they remain compatible with its normal moderation and insertion workflow.

Content analysis is intentionally simple and conservative. URLs and email addresses are removed before analysis, short submissions are excluded by default to reduce false positives, and the remaining Unicode letters are evaluated according to their Latin-script ratio. This acts as a secondary heuristic rather than dictionary-based or remote language detection.

The default token lifetime, minimum form completion time, minimum analyzed content length, Latin-script threshold, and form field names can be adjusted through PHP constants defined before the plugin loads. The plugin has no settings screen and is designed to work quietly alongside the existing WordPress and bbPress moderation tools.

## Changelog

### 2.0.4
- stored the server-generated form timestamp with each transient-backed WordPress comment token
- required submitted timestamps to match their stored token timestamps before timing validation
- added strict token format and transient value validation before accepting comment submissions
- delayed single-use token deletion until WordPress successfully inserts the comment so normal validation errors can be corrected and resubmitted

### 2.0.3
- required valid positive timestamp fields for WordPress comment and bbPress submissions
- rejected missing, malformed, future, and unrealistically fast form timestamps
- added matching IDs to honeypot inputs for valid label associations
- `Tested up to:` bumped to 7.0

### 2.0.2
- limited honeypot, timing, and token validation to native WordPress comment-form submissions
- prevented programmatic comment creation through `wp_new_comment()` from requiring plugin form fields

### 2.0.1
- added honeypot and timestamp fields to new bbPress topic and reply forms so the existing checks can run correctly
- removed custom transient-backed token validation so bbPress submissions rely on native bbPress form nonce validation instead
- registered bbPress validation hooks independently of plugin load order
- avoided adding anti-spam fields to bbPress topic and reply edit forms

### 2.0.0
- added single-use form nonce generation and server-side validation for comment and bbPress submission forms using WordPress transient storage (with automatic expiration of 900 seconds)
- added stateful nonce-based request verification alongside existing honeypot and timestamp checks
- added replay and parallel submission protection by rejecting reused or expired nonces and invalidating tokens immediately after first use
- upgraded spam protection from stateless heuristics to modern token-based request authentication
- `Tested up to:` bumped to 6.9

### 1.3.0
- added minimum form fill time validation to block automated submissions
- rejects submissions posted unrealistically fast by e.g. bots or scripts
- combined honeypot and timing checks for early rejection
- supports both WordPress comments and bbPress topics/replies

### 1.2.0
- added honeypot field to comment and bbPress forms to block automated submissions
- implemented early server-side honeypot validation before database writes
- moved bbPress spam checks to an earlier execution priority for faster rejection
- applied honeypot protection to both guest comments and bbPress topics/replies
- kept existing non-english filtering as a secondary heuristic
- no captchas, no ip checks, and no impact on legitimate users
- bumped `Tested up to` header to 6.8

### 1.1.0
- added support for bbPress by applying the anti-spam language filter to new topics and replies

### 1.0.0
- initial release with English-language comment filter using Latin character ratio defined by `ANTI_SPAM_LATIN_MIN`
- allows only English-like text based on `ANTI_SPAM_LANGS` using lightweight Unicode analysis
- ignores URLs and email addresses that may exist in comments before analysis
- ignores short comments under 20 characters defined by `ANTI_SPAM_MIN_LEN` to reduce false positives
- supports PHP 7.0 to 8.3
- supports Multisite
- supports Git Updater
