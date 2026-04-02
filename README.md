# Talents — Gestão de Pessoas e NR-1

Plataforma SaaS em **Laravel 12**, **Inertia.js** e **Vue 3** para conformidade com a NR-1 (riscos psicossociais): painel administrativo Talents, painel por empresa, pesquisas anônimas, dashboards, insights, plano de ação e relatórios PDF.

## Requisitos locais

- PHP 8.2+ e Composer
- Node.js 20+
- PostgreSQL 16 e Redis (ou use Docker)

## Instalação rápida (sem Docker)

```bash
cd talents
composer install
cp .env.example .env
php artisan key:generate
# Ajuste DB_* no .env para PostgreSQL ou use sqlite para testes
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

### Usuários de demonstração (após `migrate --seed`)

| Perfil | E-mail | Senha |
|--------|--------|--------|
| Admin Talents | admin@talents.local | password |
| RH empresa demo | rh@empresa.local | password |

## Docker (desenvolvimento / Coolify)

Serviços: `app` (PHP-FPM), `nginx`, `postgres`, `redis`, `queue`.

O arquivo **`.env`** já vem preparado para Docker (`DB_HOST=postgres`, `REDIS_HOST=redis`, `APP_URL=http://localhost:8080`, filas e cache em Redis).

```bash
cd talents
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan migrate --seed --force
npm install && npm run build
```

- **Aplicação:** http://localhost:8080 (`APP_PORT` no `.env`)
- **PostgreSQL no host:** porta `5433` (`FORWARD_DB_PORT`) — usuário/senha `talents` / `secret`, banco `talents`
- **Redis no host:** porta `6380` (`FORWARD_REDIS_PORT`)

O build do Vite (`npm run build`) pode ser feito na máquina host; o `public/build` é montado no container.

## Rotas principais

- `/` — Landing
- `/login` — Autenticação (cadastro público desativado)
- `/admin/*` — Painel Talents (super admin)
- `/client/*` — Painel da empresa
- `/pesquisa/{token}` — Pesquisa anônima (link da campanha)

## Marca e tema

Logo em `public/images/logo.png`. Paleta Tailwind `talents-*` definida em `tailwind.config.js`.

## Testes

```bash
php artisan test
```

## Licença

Proprietário — uso interno do projeto Talents.
