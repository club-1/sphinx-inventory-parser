# Changelog

## [unreleased]

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

[unreleased]: https://github.com/club-1/sphinx-inventory-parser/compare/v1.1.1...HEAD
[v1.1.1]: https://github.com/club-1/sphinx-inventory-parser/releases/tag/v1.1.1
[v1.1.0]: https://github.com/club-1/sphinx-inventory-parser/releases/tag/v1.1.0
[v1.0.0]: https://github.com/club-1/sphinx-inventory-parser/releases/tag/v1.0.0

