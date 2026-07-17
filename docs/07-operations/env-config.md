# Environment Config v1

## Laravel

- `APP_NAME`
- `APP_ENV`
- `APP_KEY`
- `APP_DEBUG=false` untuk production.
- `APP_URL`
- `LOG_CHANNEL`

## Database

- `DB_CONNECTION=pgsql`
- `DB_HOST`
- `DB_PORT=5432`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

## Cache dan Queue

- `CACHE_STORE`
- `QUEUE_CONNECTION`
- `REDIS_HOST`
- `REDIS_PORT`
- `REDIS_PASSWORD`

## Inertia SSR

- `INERTIA_SSR_ENABLED=true` untuk production public SSR.
- SSR process wajib punya service manager.

## Mail dan Storage

- `MAIL_MAILER`
- `MAIL_HOST`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `FILESYSTEM_DISK`
- Storage credential sesuai provider.

## Security

- Secret tidak boleh commit ke Git.
- `.env.example` hanya berisi placeholder.
- Production debug wajib off.
