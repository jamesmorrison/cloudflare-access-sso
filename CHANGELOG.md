# Changelog

All notable changes to this project will be documented in this file, per [the Keep a Changelog standard](http://keepachangelog.com/), and will adhere to [Semantic Versioning](http://semver.org/).

## [1.0.3] - 2024-01-26
- Added constant `(bool)` `CF_ACCESS_CREATE_ACCOUNT`: whether an account should be created when authenticated through Cloudflare
- Added constant `(string)` `CF_ACCESS_NEW_USER_ROLE`: the new user role; defaults to subscriber
- Fixed composer dependencies issue; plugin returns error if the plugin needs `composer install` run 🎉
- Added `cloudflare_access_sso_plugin_pre_init` hook (prior to plugin loading) 🎉
- Corrected minor PHPCS compatibility issues (PHP 8.2) and added coding standards (WordPress-Extra) 🎉

## [1.0.2] - 2023-06-23
- Corrected version number throughout to 1.0.2. 🎉

## [1.0.1] - 2023-06-23
- Added example of multiple AUD's to README. 🎉

## [1.0.0] - 2023-06-23
- Updates and enhancements to documentation. 🎉

## [0.1.0] - 2023-02-27
- Initial private release! 🎉
