# Change log

## [2.2.1] - 2025.06.02:
### Refactored
- Minor code optimization.

## [2.2.0] - 2025.05.29:
### Refactored
- Replaced custom SQL query with OpenCart standard method for improved compatibility with extensions that override core behavior.

## [2.1.2] - 2025.02.11:
### Added
- OC 4.x compatibility.
### Internal
- Minor code changes and improvements.

## [2.1.1] - 2024.10.09:
### Removed
- Internal RTL support (since `dir="rtl"` is enough).

## [2.1.0] - 2024.01.29:
### Changed
- Admin internals.
### Fixed
- Cache system.
### Removed
- OpenCart 2.x support.

## [2.0.1] - 2023.12.08:
### Fixed
- Cache issue.

## [2.0.0] - 2023.10.16:
### Added
- Force product price to appear as if it were minimum (e.g., `From $100`), even if the product has no options and no price range.
- SQL caching.
### Fixed
- Problems with the calculations of the combination of the default prefixes `-` and `+` with the prefix `=`.
### Internal
- Core functions was rewrited from the scratch to better compatibility and fix *childhood diseases*.
- Option *quality* validation - a nonsensical jumble of prefixes will not be calculated. This refers to the aforementioned combination of standard prefixes with the equality prefix in one option.

## [1.7.1] - 2023.10.10:
### Internal
- Minor code changes to improve compatibility.

## [1.7.0] - 2023.10.06:
### Added
- Availability to count out-of-stock options.
### Internal
- Code improvements.

## [1.6.5] - 2023.04.08:
### Internal
- Code improvements.

## [1.6.4] - 2023.02.20:
### Fixed
- Wrong names of the module events.

## [1.6.3] - 2023.01.16:
### Fixed
- Minor fixes.

## [1.6.2] - 2022.12.03:
### Fixed
- Minimum price value if the last (or not first) checkbox options has no price.
### Internal
- Code improvements.

## [1.6.1] - 2021.11.10:
### Fixed
- Settings.

## [1.6.0] - 2021.11.10:
### Internal
- Code refactoring.

## [1.5.2] - 2021.10.29:
### Internal
- Code improvements.

## [1.5.1] - 2021.08.18:
### Internal
- Code improvements.

## [1.5.0] - 2021.05.24:
### Added:
- Support of RTL languages.
### Internal
- Code improvements.

## [1.4.5] - 2021.04.20:
### Added:
- Wrapped price range prefixes in span tag with "price-range" class to customize.

## [1.4.4] - 2021.04.19:
### Internal:
- Minor fixes.

## [1.4.3] - 2021.02.16:
### Internal:
- Improved language loading for OC 2.x.

## [1.4.2] - 2021.02.12:
### Fixed:
- Potential issue after add new languages after the module.

## [1.4.1] - 2020.11.04:
### Improved:
- Changed colors of enable/disable buttons in module settings.

## [1.4.0] (2020.06.29:
### Added
- Compatibility with the rules of the latest version of Option Equals Sign extension (checkbox-options with the equal sign).
### Fixed
- Multioption calculation.

## [1.3.0] - 2020.06.11:
### Added
- Compatibility with 2.2.x versions.
### Changed
- Event code
### Fixed
- Language issue on From/Upto modes.

## [1.2.0] - 2020.04.03:
### Fixed
- Code refactoring.

## [1.1.0] - 2020.03.02:
### Fixed
- Code improvements.

## [1.0.1] - 2020.03.02:
### Fixed
- Minor fix.

## [1.0.0] - 2020.02.20:
### Added
- First release.
