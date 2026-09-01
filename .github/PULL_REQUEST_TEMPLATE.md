## Summary

<!-- What does this PR change and why? -->

## Branch checklist

- [ ] I branched from latest `main` (`git pull origin main` before `git checkout -b …`)
- [ ] I did **not** push directly to `main`
- [ ] Branch name follows convention (`feature/…`, `fix/…`, or `docs/…`)
- [ ] No secrets or gitignored files committed (`database.local.php`, `LOGIN_CREDENTIALS.txt`, etc.)

## Testing

- [ ] `php scripts/migrate.php` succeeds (if schema changed)
- [ ] App loads locally (`php -S 127.0.0.1:8000 -t public public/router.php`)
- [ ] `php scripts/smoke_check.php http://127.0.0.1:8000` passes (if UI/routes touched)

## Screenshots (if UI changed)

<!-- Paste before/after screenshots or note "N/A" -->
