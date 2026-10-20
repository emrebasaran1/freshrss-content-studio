# Security and privacy

Do not attach private feed exports, browser cookies, service tokens or server logs to public issues. Report vulnerabilities privately through your GitHub repository's **Security → Report a vulnerability**, if the maintainer enables it.

The public reader requires a logged-in/authorized FreshRSS context, POST, a CSRF token and an entry ID belonging to the current user's configured categories. There is no arbitrary URL input endpoint. Full-text access defaults to disabled and hosts must be explicitly allowed. Only public HTTPS on port 443 is permitted. Every redirect is revalidated, DNS addresses are checked, and the cURL connection is pinned to a validated address; private/reserved and shared-address ranges are refused. OS DNS lookup time is not governed by the HTTP timeout.

Extraction does not execute JavaScript. Inline handlers, scripts, forms, embedded objects and unapproved frames are removed before the FreshRSS sanitizer. The UI sanitizes injected reader HTML again. Linkding and MeTube links are opt-in user settings; only use services you trust. Do not configure a public downloader without its own access controls.

When enabling public full text, all permitted users can cause requests to the allowed sites, so shared-instance administrators should control extension availability and enforce network egress/rate limits appropriate to their deployment. Do not add local network domains. The extension sends no subscription cookies and is not an authentication gateway.

There is no telemetry or background worker. External images and selected videos still contact their publishers. Namespaced article-cache attributes are stored in FreshRSS and expire after 24 hours; disabling the extension does not erase those attributes. Article deletion/purge removes them.
