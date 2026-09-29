# InfinityFree Production Deployment

Production URL: https://masarak.42web.io

## Deployment model

Production deployment is handled by `.github/workflows/deploy.yml`.

The workflow deploys only after the existing **CI** workflow completes successfully for a push to `main`. A manual `workflow_dispatch` run is also available.

The workflow:

1. Checks out the exact commit that passed CI.
2. Builds production PHP dependencies with PHP 8.4.
3. Builds the Vite frontend with Node.js 24.
4. Creates the InfinityFree root `.htaccess` that forwards requests to Laravel's `public/` directory.
5. Rejects PHP files larger than 1 MiB before upload.
6. Syncs changed files to `/htdocs/` over FTPS using the deployment state file.

## Required GitHub repository secrets

Configure these under:

`Settings -> Secrets and variables -> Actions -> Repository secrets`

- `INFINITYFREE_FTP_USERNAME`
- `INFINITYFREE_FTP_PASSWORD`

Do not store the hosting password, database password, production `.env`, or `APP_KEY` in the repository.

If the secrets are missing, the workflow exits successfully with a warning and does not deploy.

## Files preserved on the server

The deployment intentionally excludes the production `.env` and Laravel runtime data such as:

- `storage/app/`
- `storage/logs/`
- `storage/framework/cache/`
- `storage/framework/sessions/`
- `storage/framework/views/`

This prevents deployments from overwriting production credentials, sessions, logs, or future user-generated files.

## Database changes

InfinityFree does not provide SSH access for running Artisan migrations in production.

For schema changes:

1. Merge and deploy the application code only after reviewing compatibility.
2. Export or write the required SQL migration separately.
3. Back up the production database.
4. Apply the SQL through phpMyAdmin.
5. Verify the affected application flow.

Never replace the production database during a normal code deployment.

## First automated run

The first automated deployment may take longer because the FTP deploy action must establish its sync state. Later deployments use `.ftp-deploy-sync-state.json` and normally transfer only changed files.

## Rollback

For a code-only rollback, revert the problematic commit on `main`. After CI succeeds, the deployment workflow will sync the reverted files automatically.

Database rollbacks must be handled separately and should always start from a production database backup.
