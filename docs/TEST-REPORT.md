# Release validation — 0.1.0

Validation date: 2026-10-20. All integration data was synthetic in an isolated FreshRSS 1.30.1 / SQLite container. No personal database, cookies or subscription feed export was copied.

## Passed

- PHP syntax for the extension, settings form and controller.
- JavaScript syntax for the independent frontend and core helpers.
- Eleven behavior checks: video ID validation, similar/different headlines, changed numeric values, time windows and keyword priority.
- Six controller boundary cases: authorized cached content, unauthorized access, invalid CSRF, wrong HTTP method, disabled reader and out-of-scope category.
- Reader validation: private/reserved/shared IP rejection, URL credentials/ports/schemes, exact host allowlist, relative paths, ad/script/event removal, retained inline images and YouTube embeds, rejected arbitrary iframes and subscriber-only metadata.
- Extraction using the actual FreshRSS HTML sanitizer.
- Real allowed-host HTTPS request and extraction from the public example domain.
- Actual reader controller invoked through the isolated FreshRSS authentication, CSRF, entry lookup, fetch, sanitizer and per-entry cache path; returned valid extracted JSON.
- Six-language settings-save checks, Chinese locale fallback, raw XPath JSON/URL preservation and duplicate-category rejection.
- Package scanner: deployment/private references absent, excluded modules absent, six translation dictionaries with identical non-empty keys.
- Real FreshRSS extension enabled in an isolated installation with category IDs unrelated to a personal setup.
- Browser: four-stream navigation, five-theme selector, news grouping and other-source dialog, blog side reader with feed body and scrollable table, settings page and Turkish settings save after fixing an API compatibility issue.
- Browser: Spanish and Simplified Chinese settings saved and their translated save buttons verified.
- Browser: all 15 theme/mode combinations have a complete 2 px appearance border, matching edge colours and the intended corner geometry.
- Browser: YouTube player dialog creates an approved iframe with the expected referrer policy and removes the iframe when closed. Live video playback itself was not asserted.
- Live Homepage: four edges of the appearance container verified with the selected theme; the portable extension was not installed into the personal FreshRSS instance.

## Not verified / limits

- Actual video stream playback and the final full-text click-to-display flow are not claimed as passed end-to-end. The player dialog/iframe lifecycle and backend reader request were tested separately. Publisher/browser restrictions still apply.
- No actual MeTube job or Linkding save was submitted. These optional services require deployment-specific authentication/CORS settings.
- A physical iPhone/Safari and other FreshRSS versions/base themes were not tested. CSS has responsive breakpoints and native dialog fallbacks; older browsers may render the special corner shape as ordinary rounded corners.
- Publisher-specific full text and subscriber content are not guaranteed. Configure an allowed host and suitable XPath for each source.
- Lexical duplicate detection works within loaded pages, not an entire historical database or a semantic AI service.

This is a publication-ready **0.1.0 initial release**, with these validation limits stated openly rather than a claim of universal compatibility.
