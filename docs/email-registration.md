# Registration and password recovery

Set `email_registration` in `config/auth.php`, or set `EMAIL_REGISTRATION=true` in `.env`. The default is `false`.

- `true`: new accounts receive an activation email and cannot log in until they follow its signed link. Registration does not ask for a recovery code. Forgot Password sends a single-use reset link, valid for 60 minutes. Profile password changes require the current password.
- `false`: registration asks for a recovery code and immediately logs the user in. Forgot Password requires their email and recovery code.

Run the new migration before enabling the setting:

```sh
php artisan migrate --path=database/migrations/2026_09_29_170000_add_activation_pending_to_users_table.php --force
php artisan config:clear
```

If production uses cached configuration, run `php artisan config:cache` after changing the setting. Configure the existing Laravel mail transport and sender in `config/mail.php` / `.env`, and set `APP_URL` to the public HTTPS origin. Emails are sent synchronously; no queue worker is required. A log/array mailer will not deliver email to an inbox.

Existing accounts are not forced through activation. Pending accounts can still activate/resend if email registration is later turned off. A password reset never activates a pending account or enables a disabled account. Resending activation and requesting/resetting passwords are rate limited. Activation links expire after `auth.activation_expire` minutes. Used activation links cannot re-enable an account subsequently disabled by staff. The old `/verify/{token}` remember-token endpoint has been removed.

Before switching back to recovery codes, email-registered users should set a recovery code in their profile while logged in; they do not have one by default. Keep email recovery enabled until those users have done so.
