# Changelog

## [unreleased]

### Fixed

- Make sure the stream is closed even on error in `parseFromDoc()` ([#14]).

[#14]: https://github.com/club-1/sphinx-inventory-parser/pull/14

## [v1.2.0] - 2024-01-25

### Removed

- Drop support for PHP 7.3 ([#6]).

### Fixed

- Throw an exception early on zlib error instead of logging the error and
  continuing to parse corrupted data ([#11]).

### Added

- Add a fuzzer setup based on [nikic/PHP-Fuzzer] with targets for `parseHeader()`
  and `parseObjectsV2()` and a corpus seeded from the unit test's data ([#6],
  [#8]).

### Changed

- Stream reading errors in `parseObjects()` and `parseHeader()` are silenced,
  an exception is still thrown but it now contains the error message.
- Set Zlib inflate stream filter in `parseObjects()` instead of `parseHeader()`.
  This allows to at least correctly parse the header even if the rest is
  corrupted ([#10]).

[nikic/PHP-Fuzzer]: https://github.com/nikic/PHP-Fuzzer
[#6]: https://github.com/club-1/sphinx-inventory-parser/pull/6
[#8]: https://github.com/club-1/sphinx-inventory-parser/issues/8
[#10]: https://github.com/club-1/sphinx-inventory-parser/pull/10
[#11]: https://github.com/club-1/sphinx-inventory-parser/pull/11

## [v1.1.1] - 2024-01-13

### Fixed

- Fix a crash with zero length location in an object line ([#7]).

### Added

- Include the Changelog in the documentation.

[#7]: https://github.com/club-1/sphinx-inventory-parser/pull/7

## [v1.1.0] - 2023-05-14

### Added

- Run tests on Windows and MacOS in GitHub actions.
- Support Windows style line endings (`\r\n`).
- Add [`SphinxInventoryParser::parseFromDoc()`][parseFromDoc] static method as
  an even simpler way to use the parser for the most frequent use case.

[parseFromDoc]: https://club-1.github.io/sphinx-inventory-parser/api.html#SphinxInventoryParser::parseFromDoc

## [v1.0.0] - 2023-05-07

First stable release.

[unreleased]: https://github.com/club-1/sphinx-inventory-parser/compare/v1.2.0...HEAD
[v1.2.0]: https://github.com/club-1/sphinx-inventory-parser/releases/tag/v1.2.0
[v1.1.1]: https://github.com/club-1/sphinx-inventory-parser/releases/tag/v1.1.1
[v1.1.0]: https://github.com/club-1/sphinx-inventory-parser/releases/tag/v1.1.0
[v1.0.0]: https://github.com/club-1/sphinx-inventory-parser/releases/tag/v1.0.0

