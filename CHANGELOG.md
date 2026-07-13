# Changelog

All notable changes to `ccast/tagixo-primix` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added

- **Parity with the Filament SDK** — Theme Builder lazy model pages: `getBuildUrl` for the Body button resolves the special page (created on demand via `resolveModelPageTarget`) and `isBodyConfigured` drives the button state; `PageResource` no longer applies the `userManaged` scope globally, and `PagesTable` gains a "Type" `SelectFilter` (Pages / Model templates).
