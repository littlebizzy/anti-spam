# Anti-Spam

Spam protection for WordPress

## Changelog

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
