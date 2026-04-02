# Usuários de demonstração

Estes acessos são criados pelo seeder **`Database\Seeders\TalentsSeeder`** ao rodar migrate + seed.

## Com Docker (forma correta neste projeto)

O `.env` usa `DB_HOST=postgres` (rede Docker). Rode o **Artisan dentro do container** `app`, não no PHP do Windows:

```bash
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

Ou em um comando: `docker compose exec app php artisan migrate --seed`

A aplicação fica em **http://localhost:8080** (porta definida por `APP_PORT` no `.env`).

Se você rodar `php artisan` no PowerShell com XAMPP, aparece `could not find driver` — o PHP local não tem extensão PostgreSQL; use os comandos acima.

## Credenciais

| Perfil | E-mail | Senha | Papel | Onde acessa |
|--------|--------|--------|--------|----------------|
| **Admin Talents** | `admin@talents.local` | `password` | Super administrador (`super_admin`) | `/admin` — painel Talents (empresas, planos, templates NR-1, config IA, etc.) |
| **RH demo (empresa)** | `rh@empresa.local` | `password` | Administrador da empresa (`company_admin`) | `/client` — painel da **Empresa Demo** (pesquisas, resultados, setores, etc.) |

## Contexto do seed

- **Empresa:** Empresa Demo (CNPJ fictício `00.000.000/0001-99` no seed).
- **Usuário admin** não possui `company_id` (acesso global Talents).
- **Usuário RH** está vinculado à empresa demo e ao plano seed **NR1 Pro**.

## Segurança

As senhas acima são **apenas para desenvolvimento**. Em ambiente de produção, altere as senhas ou remova estes usuários após o primeiro deploy.
