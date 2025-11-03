# Anti-Spam

Spam protection for WordPress

## Changelog

### 1.0.0
- initial release with English-language comment filter using Latin character ratio defined by `ANTI_SPAM_LATIN_MIN`
- allows only English-like text based on `ANTI_SPAM_LANGS` using lightweight Unicode analysis
- ignores URLs and email addresses that may exist in comments before analysis
- ignores short comments under 20 characters defined by `ANTI_SPAM_MIN_LEN` to reduce false positives
- supports PHP 7.0 to 8.3
- supports Multisite
- supports Git Updater
