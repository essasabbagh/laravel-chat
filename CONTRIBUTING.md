# Contributing

Contributions are welcome and will be fully credited.

## Pull Requests

- **Use a feature branch** — don't commit directly to `main`.
- **Add tests** — every feature needs test coverage. We use PHPUnit with Orchestra Testbench.
- **Update docs** — every feature needs a doc page in `docs/`.
- **Run the checks** before pushing:
  ```bash
  composer lint     # vendor/bin/pint --test
  composer analyse  # vendor/bin/phpstan analyse
  composer test     # vendor/bin/phpunit
  ```
- **One PR = one feature or fix.** Keep changes focused.

## Issues

If you find a bug or have a feature request, open an issue. Include:
- Laravel version, PHP version, package version
- Steps to reproduce (or a failing test)

## Development Setup

```bash
git clone <your-fork>
cd laravel-chat
composer install
vendor/bin/phpunit
```