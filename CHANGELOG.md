# Changelog

## 0.2.2 — 2026-11-09

- Phones: the date separator's faint category watermark no longer overlaps long dates.
- English README shows an English interface; the Turkish README keeps Turkish screenshots.

## 0.2.1 — 2026-11-09

- Fix: article and video covers were hidden on FreshRSS 1.30, which renders them as a header item; FreshRSS's loading stripes no longer show around covers.
- YouTube grid flows continuously instead of breaking into half-empty rows at each day separator.
- Luxury: selected buttons keep dark green lettering on their gold fill.
- Brutalism: stream links are square with heavy rules, like the other controls.
- README screenshots now show real subscriptions across all four themes and three streams.

## 0.2.0 — 2026-11-09

- Design refresh ported from the matured personal setup.
- One framed top panel: stream links first, then the appearance caption, Day/Night/Dark and the theme row; two-column layout on phones.
- Four appearances: Nautilus is retired (a saved Nautilus choice opens as Luxury). Luxury uses symmetric 20 px corners.
- Minimalist and Blue Face sit on a slowly drifting light canvas (paused for reduced motion); Blue Face Night/Dark cards are frosted glass.
- Blue Face Day: text inside blue boxes is white, text on the page background stays black; white FreshRSS logo.
- Inter typography when installed, otherwise the platform UI font. No font files are bundled.
- Mobile: browser status bar and toolbar follow the active theme colour; settings menu drops down under its gear; FreshRSS's fixed bottom bar is hidden; slimmer reader frame so embedded YouTube players show their own settings controls.
- FreshRSS 1.30 date separators sit on the page canvas instead of a dark band.
- Fix: the interface no longer silently fails to appear when FreshRSS loads its page context after the extension script.

## 0.1.0 — 2026-10-20

- Initial portable extension with four user-mapped streams.
- Five appearance families, three modes and matching full appearance frames.
- Focused article reader, deliberate YouTube player, topic/favorite/source controls.
- Lexical duplicate grouping, configurable keyword filtering, optional Linkding/MeTube.
- Public full-text extraction with host allowlist, bounded requests and cache.
- English, Turkish, German, French, Spanish and Simplified Chinese interfaces.
