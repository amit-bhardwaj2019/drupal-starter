# IntlDate

## Requirements
 - [Intl](https://www.php.net/manual/en/book.intl.php) PHP extension

## Installation

```
drush en intl_date
```

## Configuration

Visit `admin/config/regional/intl-date-time` and configure the date patterns (per language if needed).

## Usage

When configuring a Views date / timestamp field or when displaying entity
display modes, there is a new formatter available: Intl Default
It behaves very similarly to core formatter, but uses date patterns
that uses symbols from [Unicode ICU](https://unicode-org.github.io/icu/userguide/format_parse/datetime/#formatting-dates).

## Developers

A Twig filter is present, usage:
```
Example with custom format: {{ 1626255230|intl_format_date('yyyy LLLL') }}
Example with a preset: {{ 1626255230|intl_format_date_pattern('medium') }}
```

## Why should I use this module?

This means for example you can distinguish standalone month name (`LLLL`) and
month name with a year (`MMMM`), in some languages, like Mongolian, these are
translated differently, with core formatter, it's impossible to translate
perfectly. Moreover you have localized scripts, so for example Farsi will use
proper Persian numbers, not latin ones.
