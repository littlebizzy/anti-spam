# Anti-Spam

Spam protection for WordPress

## Description

Anti-Spam provides lightweight spam protection for native WordPress comments and bbPress forums without captchas, external APIs, tracking, browser fingerprinting, or IP-based blocking. All checks run locally within WordPress, with no remote reputation services or third-party data collection.

For WordPress comments, the plugin adds a hidden honeypot field, a minimum form completion time check, and a short-lived single-use token stored through the WordPress Transients API. Each token stores the server-generated form timestamp so submitted timestamps cannot be replaced to bypass the timing check. Submissions that fail these early form checks are rejected before normal comment processing, while content that fails the secondary language-ratio check is sent to the WordPress spam queue.

For bbPress, the plugin adds honeypot and timestamp fields to standard new-topic and new-reply forms while leaving topic and reply edit forms unchanged. bbPress continues to handle its own native nonce validation, and suspicious forum submissions are assigned the native bbPress spam status so they remain compatible with its normal moderation and insertion workflow.

Content analysis is intentionally simple and conservative. URLs and email addresses are removed before analysis, short submissions are excluded by default to reduce false positives, and the remaining Unicode letters are evaluated according to their Latin-script ratio. This acts as a secondary heuristic rather than dictionary-based or remote language detection.

The plugin has no settings screen. Its defaults can be adjusted by defining the following PHP constants before the plugin loads:

| Constant | Default | Purpose |
| --- | --- | --- |
| `ANTI_SPAM_NONCE_TTL` | `3600` | Sets the native WordPress comment token lifetime in seconds. |
| `ANTI_SPAM_MIN_FILL_TIME` | `3` | Sets the minimum form completion time in seconds for WordPress comments and bbPress submissions. |
| `ANTI_SPAM_MIN_LEN` | `20` | Sets the minimum content length required before the language-ratio check runs. |
| `ANTI_SPAM_LATIN_MIN` | `0.75` | Sets the minimum proportion of Unicode letters that must use the Latin script. |
| `ANTI_SPAM_LANGS` | `en` | Currently unused and reserved for possible future language support. |
| `ANTI_SPAM_HONEYPOT_FIELD` | `anti_spam_hp` | Sets the honeypot field name used by WordPress comment and bbPress forms. |
| `ANTI_SPAM_TIMESTAMP_FIELD` | `anti_spam_ts` | Sets the timestamp field name used by WordPress comment and bbPress forms. |
| `ANTI_SPAM_NONCE_FIELD` | `anti_spam_nonce` | Sets the single-use token field name used by native WordPress comment forms. |

## Changelog

### 2.0.6
- increased the WordPress comment token lifetime from 15 minutes to 1 hour to reduce legitimate submission failures
- explicitly rejected WordPress comment tokens older than the configured lifetime

### 2.0.5
- added a safe content-length fallback when the PHP `mbstring` extension is unavailable

### 2.0.4
- stored each WordPress comment token’s server-generated timestamp in its transient and required submitted timestamps to match it
- added strict token format and transient structure validation before accepting comment submissions
- delayed token deletion until successful comment insertion so failed WordPress validation does not consume the token

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