# KuStore Deployment Handoff

Last updated: 2026-10-04

This file is a handoff for continuing the KuStore deployment/debugging work. Read this before making changes.

## Project

- Repository: https://github.com/kuartal-id/kustore
- Production domain: https://kustore.id
- Framework: Laravel 13.34.0
- PHP project requirement: ^8.4
- Hostinger server CLI PHP 8.4 binary: /opt/alt/php84/usr/bin/php
- Hostinger default SSH PHP is 8.3.33
- Git deployment branch: main
- Git deployment root: ~/domains/kustore.id/public_html
- Laravel project root currently contains app/, artisan, bootstrap/, config/, database/, resources/, routes/, storage/, vendor/, and public/

## Important Git history

- PR #2 merged the reconciled KuStore MVP into main.
- PR #3 fixed Laravel 13 / PHP 8.4 compatibility.
- PR #4 added the Laravel public entry point.
- Current main deployment commit observed on Hostinger: e59545287ebe4a69766a749096cfe1d5c1dd61c8 ("Fix Laravel public entry point for deployment (#4)").

PR #4 added:
- public/index.php
- public/.htaccess

The public entry point is:

<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());

## Current server/runtime facts

Hostinger's website currently returns:

403 Forbidden
"Access to this resource on the server is denied!"

The repository deployment itself has succeeded at least once after reconnecting the GitHub repository. A previous deployment attempt failed during:

composer install --prefer-dist --quiet --no-interaction

but a later attempt succeeded. Do not assume Composer is the current root cause.

The Laravel application was previously returning HTTP 500 when executed through public/index.php. The Laravel log identified the concrete cause:

SQLSTATE[42S02]: Base table or view not found:
1146 Table 'u375048224_kustore.sessions' doesn't exist

The session migration was then created and migrated directly on the server:

/opt/alt/php84/usr/bin/php artisan make:session-table
/opt/alt/php84/usr/bin/php artisan migrate --force

The migration created:
2026_10_04_124411_create_sessions_table

and it completed successfully.

The following commands also completed successfully on the server:

/opt/alt/php84/usr/bin/php artisan optimize:clear
/opt/alt/php84/usr/bin/php artisan config:cache
/opt/alt/php84/usr/bin/php artisan route:cache
/opt/alt/php84/usr/bin/php artisan view:cache

Therefore, the missing sessions table was fixed on that server state.

## Database

Database configuration is private and must not be copied into Git.

Database name:
u375048224_kustore

Database username:
u375048224_kustore

DB_HOST is localhost.

Do NOT ask for or commit the database password.

Migrations that were successfully run before the sessions issue:

- create_users_table
- create_stores_table
- create_store_links_table
- create_products_table
- create_orders_tables
- create_order_items_table
- add_oauth_identity_columns_to_users_table
- create_cache_table

A cache migration was also created directly on the server:
2026_10_04_122652_create_cache_table

The sessions migration created directly on the server is:
2026_10_04_124411_create_sessions_table

CRITICAL: These server-created migration files are not necessarily present in GitHub main. Before the next production deployment, verify whether the cache and sessions migration files exist in the repository. If they do not, add them properly to Git. Otherwise a future deployment may remove them.

## Storage/runtime directories

Hostinger Git deployments have previously removed empty/untracked Laravel runtime directories.

The following directories were manually recreated on the server:

- bootstrap/cache
- storage/framework/views
- storage/framework/cache
- storage/framework/sessions
- storage/logs

Permissions were set with:

chmod -R 775 bootstrap/cache
chmod -R 775 storage

CRITICAL: These runtime directories should be represented in Git using Laravel-style .gitignore placeholder files so future deployments do not lose the required directory structure. Inspect the repository first and do not duplicate existing .gitignore files.

Likely required placeholders include:

bootstrap/cache/.gitignore
storage/app/.gitignore
storage/app/private/.gitignore
storage/framework/.gitignore
storage/framework/cache/.gitignore
storage/framework/cache/data/.gitignore
storage/framework/sessions/.gitignore
storage/framework/testing/.gitignore
storage/framework/views/.gitignore
storage/logs/.gitignore

Use the repository's existing conventions if these already exist.

## .env

A production .env exists only on the server and must remain untracked.

It contains APP_KEY and production database credentials.

Relevant non-secret settings include:

APP_NAME=KuStore
APP_URL=https://kustore.id
KUSTORE_PAYMENT_PROVIDER=manual
KUSTORE_CURRENCY=IDR

OAuth configuration exists for:
- Google
- Kuartal ID

Do not commit .env or any client secret.

## Current architectural issue to investigate

The current 403 is NOT currently proven to be a Laravel application error.

The Laravel project has a standard public/ directory:

public/
  index.php
  .htaccess

The deployment root is:

~/domains/kustore.id/public_html

The website currently returns a server-level 403 after deployment.

IMPORTANT: Previous troubleshooting repeatedly assumed that the website's document root must be changed to public_html/public. That assumption was NOT verified against the actual Hostinger configuration and should not be repeated without evidence.

A temporary root .htaccess was previously created to rewrite public_html to public/:

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^$ public/ [L]
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

This caused HTTP 500 and was deleted. DO NOT recreate it blindly.

The root .htaccess was verified absent afterward.

A static test file placed at public/test-kustore.txt previously returned a Laravel "This Page Does Not Exist" response rather than a raw static file response. That was useful evidence that some requests were reaching Laravel, but the hosting configuration has since been redeployed/reconnected, so this should be re-tested rather than assumed.

## What the next engineer/AI should do

1. Inspect the actual Hostinger website/document-root/deployment configuration rather than assuming it.
2. Determine why the deployed public_html root returns 403 while Laravel's public/ directory contains index.php and .htaccess.
3. Check whether Hostinger supports a document root of public_html/public while Git deployment remains rooted at public_html. Do not change this unless the actual Hostinger UI/configuration confirms it.
4. If Hostinger cannot use public_html/public as the document root, implement a safe Laravel-compatible deployment structure appropriate for Hostinger. Do not use the previously tested root rewrite blindly.
5. Verify the actual deployed filesystem after the successful reconnect/deployment.
6. Verify public/index.php and public/.htaccess exist on the deployed server.
7. Check Apache/Hostinger error logs if available. A server-level 403 should be diagnosed from the hosting layer before changing Laravel code.
8. Once the site serves correctly, test:
   - /
   - /register
   - /login
9. Then make the repository self-contained by committing the missing cache/session migrations and required runtime .gitignore placeholders.
10. Avoid storing any production secrets in GitHub.

## Authentication/application routes

Main web routes include:

- /
- /login
- /register
- /logout
- /auth/kuartal
- /auth/kuartal/callback
- /auth/google
- /auth/google/callback
- /dashboard
- /dashboard/store
- /dashboard/links
- /dashboard/products
- /dashboard/orders
- /{username}
- /{username}/product/{product}
- /{username}/product/{product}/checkout

## Payment

KuStore currently uses a manual payment provider:

KUSTORE_PAYMENT_PROVIDER=manual

Checkout creates pending orders and must not pretend payment has been completed.

## Key warning

Do not reset, delete, or rebuild the production database casually. It already contains the migration history and may contain application data.

Do not ask the user for passwords or OAuth secrets.

Do not force-push main.

Prefer a new branch + PR for repository fixes.

## Current objective

Get https://kustore.id serving the Laravel application reliably on Hostinger, then commit the server-side fixes that must survive future Git deployments.

The last known browser result after a successful Hostinger deployment was:

403 Forbidden
Access to this resource on the server is denied!
