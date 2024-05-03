## CHANGELOG
A not so exhaustive list of changes for each release.

For a more detailed listing of changes between each version, 
you can use the following url: https://github.com/ericsizemore/simple_tpl/compare/v1.0.5...v2.0.1. 

Simply replace the version numbers depending on which set of changes you wish to see.

### 3.0.0 (-dev)
  * Bumped minimum PHP version requirement to 8.2
  * Updated header docblock in each source file to be more compact.
  * Updated with new coding standards and fixes to issues reported by Psalm.
  * Added new dev requirements.
    * PHP-CS-Fixer
    * Psalm

### 2.0.1 (2023-12-20)
  * Updated composer.json for PHP ^8.1 but <8.5
    * Added PHPStan strict rules
  * Updated PHPUnit tests to use the PHPUnit CoversClass and DataProvider attributes.
  * Updated PHPUnit tests to use `self::` instead of `$this->` for PHPUnit related functions
  * Updated workflows for test coverage and Scrutinizer
  * Added new test to push test coverage to 100%
  * A little cleanup and formatting

### 2.0.0 (2023-09-22)
  * BC BREAK: Namespace changes
  * Code cleanup per PHPStan (level: 9, strict, bleeding edge)
  * Restructured for use with composer/packagist
  * Added PHPUnit and PHPStan workflows
  * Added security policy, see SECURITY.md

### 1.0.5 (2019)

  * Initial release/github commit
