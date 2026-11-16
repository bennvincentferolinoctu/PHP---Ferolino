# Agriculture Information System (AP3 Midterm)

PHP 8 + Bootstrap 5 web app on a Supabase PostgreSQL database.

## 1. Database (Supabase)
1. Create a Supabase project.
2. SQL Editor: run `sql/schema.sql`, then `sql/seed.sql`.
3. Project Settings > Database > Connection string: copy host, port, user, password.

Tables have RLS enabled with no policies, so the public Supabase Data API cannot read them. The PHP app connects as the database role and is unaffected.

## 2. Run locally
Requires PHP 8.1+ with `pdo_pgsql`.
```
cp .env.example .env      # fill in the Supabase values
php -S localhost:8080 -t public
```
Open http://localhost:8080

Default logins (change them in Users after first login):
- admin / Admin@123 (Administrator: full access, delete, user management)
- staff / Staff@123 (Staff: create, read, update; no delete, no Users page)

## 3. Deploy
`Dockerfile` is included (php:8.3-apache, document root `public/`). On Render/Railway/Fly, deploy from the repo and set DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_SSLMODE as environment variables. Never commit `.env`.

## Layout
- `public/` web root. One page per job: `farmers.php` (list), `farmer_form.php` (add/edit), `farmer_view.php`, `farmer_delete.php`; the same four files for `farm`, `crop`, `planting` and `harvest`. Also `login.php`, `logout.php`, `index.php` (dashboard), `reports.php`, `users.php`, `user_form.php`.
- `includes/config.php` reads `.env` and connects to the database
- `includes/functions.php` shared helpers (queries, validation, form fields, pagination)
- `includes/header.php`, `includes/footer.php` page layout; `includes/init.php` is loaded first by every page
- `sql/` schema and seed; `tools/gen_seed.py` regenerates the seed
