# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## [Unreleased]

### Added

  * Added issue templates and a pull request template.
  * Added a Contributor Code of Conduct.
  * Added new contributing information in `CONTRIBUTING`.
  * Added a Backward Compatibility promise.
  * Added new dev requirements.
    * PHP-CS-Fixer
    * Psalm
  * Added template caching with `symonfy/cache`.

### Changed

  * Library and unit tests completely rewritten.
  * License changed from `GNU GPL v3` to `MIT`.
  * Bumped minimum PHP version requirement to 8.2
  * Updated header docblock in each source file to be more compact.
  * Updated with new coding standards and fixes to issues reported by Psalm.


## [2.0.1] - 2023-12-20

### Added

  * Added new test to push test coverage to 100%

### Changed

  * Updated composer.json for PHP ^8.1 but <8.5
    * Added PHPStan strict rules
  * Updated PHPUnit tests to use the PHPUnit CoversClass and DataProvider attributes.
  * Updated PHPUnit tests to use `self::` instead of `$this->` for PHPUnit related functions
  * Updated workflows for test coverage and Scrutinizer
  * A little cleanup and formatting


## [2.0.0] - 2023-09-22

### Added

  * Added PHPUnit and PHPStan workflows
  * Added security policy, see SECURITY.md

### Changed

  * (BC BREAK) Namespace changes
  * Code cleanup per PHPStan (level: 9, strict, bleeding edge)
  * Restructured for use with composer/packagist


## [1.0.5] - 2019

  * Initial release/github commit.


[unreleased]: https://github.com/ericsizemore/simple_tpl/tree/master
[2.0.1]: https://github.com/ericsizemore/simple_tpl/releases/tag/v2.0.1
[2.0.0]: https://github.com/ericsizemore/simple_tpl/releases/tag/v2.0.0
[1.0.5]: https://github.com/ericsizemore/simple_tpl/releases/tag/v1.0.5
