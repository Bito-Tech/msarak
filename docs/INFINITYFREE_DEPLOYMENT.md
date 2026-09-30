# InfinityFree Production Deployment

Production URL: https://masarak.42web.io

## Deployment model

Production deployment is handled by `.github/workflows/deploy.yml`.

The workflow runs only after the existing **CI** workflow completes successfully for a push to `main`. A manual `workflow_dispatch` run is also available for a controlled retry.

The deployment is intentionally incremental:

1. Read the last successfully deployed commit from `/htdocs/.masarak-deploy-state`.
2. Fall back to the known initial production baseline when no state file exists yet.
3. Build production Composer dependencies and Vite assets in GitHub Actions.
4. Stage only production files changed since the last successful deployment.
5. Include the complete `public/build/` directory only when frontend build inputs changed.
6. Include the complete `vendor/` directory only when Composer dependency metadata changed.
7. Upload the small staged payload with a retry-capable FTP client.
8. Apply tracked-file deletions.
9. Write the new deployment commit SHA only after every upload and deletion succeeds.

This avoids sending thousands of unchanged Laravel and vendor files on every deployment.

## Why the uploader is not the original FTP action

The first automated production attempt used a third-party FTP deployment action with fully encrypted FTPS data transfers. InfinityFree accepted the connection and uploaded for roughly half an hour, but the server later terminated the TLS data channel with `ERR_SSL_TLSV1_ALERT_DECODE_ERROR`.

The current workflow therefore uses `lftp` directly with conservative single-stream transfers and retry/reconnect settings.

It first attempts fully encrypted explicit FTPS. If InfinityFree closes the encrypted data channel repeatedly, the workflow falls back to:

- TLS still required for the FTP control/login channel.
- The deployment data channel may be clear text.
- The payload is restricted to public application code and generated dependencies.
- Production `.env`, database credentials, `APP_KEY`, sessions, logs, user data, and other runtime secrets are never part of the payload.

This fallback protects hosting credentials while avoiding the InfinityFree data-channel TLS failure that blocked the initial deployment.

## Required GitHub repository secrets

Configure these under:

`Settings -> Secrets and variables -> Actions -> Repository secrets`

- `INFINITYFREE_FTP_USERNAME`
- `INFINITYFREE_FTP_PASSWORD`

Do not store the hosting password, database password, production `.env`, or `APP_KEY` in the repository.

Missing FTP secrets are treated as a deployment failure, not a successful skipped deployment.

## Production file scope

Normal tracked application changes may deploy from:

- `app/`
- `bootstrap/`
- `config/`
- `public/`
- `resources/views/`
- `routes/`
- `lang/`
- `artisan`

Generated output is handled separately:

- `public/build/` when Vite inputs changed.
- `vendor/` when `composer.json` or `composer.lock` changed.

The root `.htaccess` forwarding rule is included in every payload so an accidental removal can be repaired automatically.

## Files preserved on the server

The deployment does not overwrite production runtime or secret data, including:

- `.env`
- `storage/app/`
- `storage/logs/`
- `storage/framework/cache/`
- `storage/framework/sessions/`
- `storage/framework/views/`
- production database contents

Source-only and development paths such as tests, documentation, Git metadata, Node modules, and frontend source files are also not uploaded.

## Database changes

InfinityFree does not provide SSH access for running Artisan migrations in production.

For schema changes:

1. Back up the production database.
2. Review the application change for backward compatibility.
3. Prepare the required SQL separately.
4. Apply the SQL through phpMyAdmin at the appropriate point in the release.
5. Deploy or verify the application code.
6. Verify the affected production flow.

Never replace the production database during a normal code deployment.

## Deployment state

The workflow stores the last successful production commit in:

`/htdocs/.masarak-deploy-state`

That file is written only after the payload upload and tracked-file deletions complete successfully.

If it cannot be read, the workflow uses the known initial production baseline and safely rebuilds a larger incremental payload instead of assuming an unverified deployment succeeded.

## Rollback

For a code-only rollback, revert the problematic commit on `main`. After CI succeeds, the workflow computes the production delta and deploys the reverted code.

Database rollbacks are separate operations and should always start from a production database backup.
