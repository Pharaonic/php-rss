# Contributing

Contributions are welcome. Please follow the workflow below before opening a pull request.

## Setup

Fork the repository, then clone your fork:

```bash
git clone https://github.com/YOUR_USERNAME/php-rss.git
cd php-rss
composer install
```

Add the original repository as `upstream`:

```bash
git remote add upstream https://github.com/Pharaonic/php-rss.git
```

## Choose the target branch

Each PHP version has its own branch. Use the branch matching the PHP version you want to support:

```text
8.0.x → PHP 8.0
8.1.x → PHP 8.1
```

Example:

```bash
git checkout 8.1.x
git pull upstream 8.1.x
```

## Create a working branch

Create a branch from the target version branch:

```bash
git checkout -b fix/escape-cloud-path
```

Recommended prefixes:

```text
feature/
fix/
refactor/
test/
docs/
```

## Make your changes

- Keep each change focused.
- Add or update tests for every behavior change. Assert on the generated XML (XPath) rather than on getters alone.
- Keep the source compatible with the branch's PHP version. On `8.1.x`, do not use readonly classes, DNF types, `true`/`null`/`false` standalone types, constants in traits, or any other syntax added after PHP 8.1.
- New namespaced elements belong in `src/Extensions/` as `Extension` implementations; do not add extension-specific methods to `Feed` or `Item`.
- Update `README.md` when the public API or usage changes.
- Update `CHANGELOG.md` under `Unreleased`.

## Backward compatibility

Minor and patch releases must not break the public API: `Feed`, `Item`, the classes in `Elements`, `Extensions`, `Exceptions`, `Support`, the `Extension` contract, and `RssWriter`. Breaking changes are reserved for a new major version. Discuss them in an issue before opening a pull request.

## Run checks

Before opening a pull request:

```bash
composer validate --strict
composer check
```

`composer check` runs the code style check (`composer lint`), static analysis (`composer analyse`), and the test suite (`composer test`). All checks must pass. `composer format` fixes most code style issues automatically.

## Push and open a pull request

```bash
git push origin fix/escape-cloud-path
```

Open the pull request against the same version branch you started from:

```text
fix/escape-cloud-path
        ↓
      8.1.x
```

Do not submit the same change to multiple version branches unless requested.

## Security

Do not report security vulnerabilities through public issues.

See [SECURITY.md](SECURITY.md).
