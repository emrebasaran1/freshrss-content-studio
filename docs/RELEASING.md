# Publishing to GitHub

This folder is the repository root for [emrebasaran1/freshrss-content-studio](https://github.com/emrebasaran1/freshrss-content-studio).

1. Update the version, changelog and installation links for the release.
2. Upload/commit this folder's source and documentation. Do not upload any personal FreshRSS `data/` directory, account settings, real OPML exports or source article cache.
3. Run the checks in `README.md` and `python3 tools/check_release.py`.
4. Review `NOTICE`, `LICENSE`, installation instructions and `docs/TEST-REPORT.md`.
5. Create tag/release `v0.1.0` and attach the installable `content-studio-0.1.0.zip` from `dist/`.
6. Enable GitHub private vulnerability reporting if desired. An issue template is included.

The installable ZIP contains `xExtension-ContentStudio/` and the license/notices. The repository includes source, tests and maintenance documentation. It contains no credentials, subscriptions or runtime data. The SVG theme overview is a synthetic illustration, not a screenshot of a personal account.

The extension has not been submitted to the FreshRSS extension catalog. Browser/provider restrictions on embedded playback and source-specific extraction remain documented limitations.
