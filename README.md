# Content Studio for FreshRSS

[English](README.md) · [Türkçe](README.tr.md)

A focused reading and viewing interface for FreshRSS: **News, YouTube, Newsletters and Blogs** in one user extension.

Five appearances — **Minimalist, Brutalism, Luxury, Blue Face and Nautilus** — each offer Day, Night and Dark. The appearance selector has a complete frame using the selected theme's trim and corner geometry. Metallic borders are flat; Brutalism retains its offset shadows.

![Theme overview](docs/themes.svg)

## Why Content Studio?

RSS brings your sources together; Content Studio gives each kind of content a place to read or watch comfortably. Browse headlines in a compact stream, read longer posts in a side panel, and keep video viewing in FreshRSS. The goal is a calmer personal reading space with expressive themes and control over your own subscriptions—not another hosted service.

## Features

- Four configurable streams, newest publication first. Map your own existing FreshRSS categories; no bundled subscriptions or fixed category IDs.
- Compact article cards with covers, favorites, topic labels, source links, optional Linkding bookmarks and dates.
- News expands in the stream. Blogs and newsletters open in a focused side reader (full width on phones). Only one reader is open at a time.
- YouTube grid, deliberate embedded playback, and a watch screen opened in a new tab. Players are created only when selected and removed when closed.
- Inline YouTube/Vimeo article videos, inline article images, duplicate cover suppression and horizontally scrollable tables.
- Optional public full-text retrieval with per-source XPath, explicit allowed hosts and a 24-hour cache. Audio players, advertising markers and common promotional blocks are removed.
- Adjustable duplicate-news grouping and keyword filters, without deleting entries. Priority phrases override unwanted phrases. Both lists start empty.
- Optional podcast/transcript title filter.
- Optional Linkding save links and MeTube download queue with manually selected quality. No automatic downloading.
- Extension interface and settings in **English, Turkish, German, French, Spanish and Simplified Chinese**. Automatic language falls back to English. FreshRSS itself controls its own menus and article languages.

## Install

Download the [installable ZIP](https://github.com/emrebasaran1/freshrss-content-studio/releases/download/v0.1.0/content-studio-0.1.0.zip) from the [releases page](https://github.com/emrebasaran1/freshrss-content-studio/releases). Extract it first; use the extension folder inside.

Requirements: **FreshRSS 1.30.1** (tested), PHP 8.1+ with DOM, mbstring and cURL. Use the normal/reading feed view. The global overview is not supported by the enhanced cards.

1. Copy the folder `xExtension-ContentStudio` into your FreshRSS `extensions/` directory. The folder must contain `metadata.json` directly.
2. In FreshRSS, open **Settings → Extensions**, enable **Content Studio**, then open its settings.
3. Select an existing category for each of News, YouTube, Newsletters and Blogs. Different streams need different categories. Leave a selection blank to hide that stream. Manage feeds normally in FreshRSS.
4. Save. Reload the feed page. Choose an appearance and Day, Night or Dark.

For Docker, mount the extension folder at `/var/www/FreshRSS/extensions/xExtension-ContentStudio` or copy it to your existing persistent extension mount. Do not replace the whole extensions directory.

Disable other layout extensions, including Youlag, on the same categories: this release supplies its own card/grid/reader UI and does **not** require Youlag.

## Optional services

**Linkding:** enter your own new-bookmark page URL, for example `https://bookmarks.example.org/bookmarks/new`. The button opens that service in a new tab with the selected article's URL and title. Authentication stays in Linkding; no API token is stored here.

**MeTube:** enter your own base URL. Downloads are queued through its `/add` API only after pressing the queue button. A same-origin reverse proxy or a correctly configured CORS policy is required. Do not expose a downloader publicly just for this extension. Chosen resolution is a ceiling: availability depends on the source and downloader. MeTube integration is optional; its live API was not part of the isolated release test.

**Full text:** off by default. Enable it and list exact public HTTPS hostnames, including redirect destinations, for sources you trust. No cookies, subscriber credentials or authenticated proxy are included. Some sites require a specific article XPath. A simple XPath or a host-to-XPath JSON object is accepted:

```json
{
  "example.org": "//article",
  "www.example.org": "//div[@id='article-body']",
  "default": "//article | //main"
}
```

In News, request full text using its button. In Blogs/Newsletters, opening an article also requests it if enabled. On failure, the feed's existing content remains available. Only the extracted result is cached in a namespaced entry attribute; original feed content is not overwritten. Reader cache lives with the entry, expires after 24 hours, and is removed when the entry is deleted. Subscriber-only detection is conservative and cannot identify every paywall.

## Limits and privacy

- Duplicate detection compares titles of **loaded entries**, within 36 hours when dates are available. It is a transparent lexical heuristic, not semantic AI, and can miss paraphrases or group unrelated headlines. A grouped card exposes the other source links. Source-specific views and searches are not grouped. It never changes read/favorite state or deletes entries.
- Full text does not execute page JavaScript, bypass subscriptions or guarantee extraction from every site. It refuses private/reserved IPs, IP literals, non-HTTPS sources and unapproved redirect hosts. Source replies are capped at 3 MiB and four attempts within a 16-second fetch deadline (DNS lookup timing is controlled by the OS).
- Embedded videos can fail because of publisher restrictions, regional availability, cookies or Safari content blockers. Premium behavior depends on the embedded player and browser, not this extension. YouTube Music or browser blocking rules are outside this package.
- Channel/source icons use the favicon FreshRSS already provides; this does not scrape private channel profiles or export subscriptions.
- No telemetry, trackers, external fonts, model APIs or scheduled workers are included. Reading a source loads its images/videos; full-text requests contact the explicitly allowed source. Bookmark/download actions contact the service you configure.
- Appearance choices live in this browser's local storage; category/service preferences use FreshRSS per-user extension settings.
- No research or market-data module, server addresses, account credentials, subscription cookies, database, private feeds or runtime caches are distributed.

## Uninstall

Disable Content Studio in FreshRSS, then remove its extension folder. Feeds, articles, favorites and categories stay intact. Browser appearance preferences use `content-studio-theme` and `content-studio-tone`; delete these local-storage keys if desired. Cached full-text attributes may remain until entries are purged; the extension does not erase article records.

## Development and verification

```sh
node tests/core.test.js
php tests/reader.test.php
php tests/controller.test.php
php tests/settings.test.php
python3 tools/check_release.py
```

The PHP content tests need DOM and mbstring. For validation with the actual FreshRSS sanitizer:

```sh
php tests/reader.test.php /path/to/FreshRSS
```

Run PHP syntax checks on all PHP/PHTML files before release. `tests/seed-demo.php` is **only for disposable installations** and writes synthetic demo categories/articles to a user named `demo`. Never run it on a personal or production database.

See [test report](docs/TEST-REPORT.md), [release guide](docs/RELEASING.md) and [security notes](SECURITY.md).

## Attribution and license

GPL-3.0-or-later; see [LICENSE](LICENSE) and [NOTICE](NOTICE).

The focused viewing/reading idea was inspired by [Youlag](https://github.com/civilblur/youlag). The distribution uses an independent interface/controller and does not include Youlag's minified assets. The theme CSS was adapted from the original personal Content Hub appearance stylesheet. FreshRSS is a separate project; see its [extension documentation](https://freshrss.github.io/FreshRSS/en/developers/03_Backend/05_Extensions.html).

Watch-inspired theme names describe visual references only. No watch photos, logos, proprietary fonts or affiliation with watch manufacturers are included.
