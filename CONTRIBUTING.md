# Contributing

Thanks for taking a look.

## Scope
Keep the project simple enough for café owners on shared hosting. Prefer small, focused changes over big refactors.

## Setup
1. PHP 8+ with `pdo_mysql`, `curl`, `mbstring`, `fileinfo`
2. MySQL or MariaDB
3. `php scripts/cli_install.php` then serve `public/`

## Guidelines
- No new runtime Composer deps unless absolutely necessary
- Keep assets local (no CDN)
- Match the existing dark RTL UI
- Persian + English UI strings live in `lang/`
- Demo catalog stays in `sql/menu_data.php`; private menus go in `sql/menu_data.local.php` (gitignored)

## Pull requests
- Describe the why in a few lines
- Include a screenshot if the UI changes
- Don’t commit `.env`, uploads, or `menu_data.local.php`
