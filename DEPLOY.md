# Deploy Cutcost on Hetzner

Everything (Laravel, Vue/Inertia assets, MySQL, Redis, queues) runs on one Hetzner VPS with Docker.

> Deployed on Hetzner only (not Vercel).

## Current production (this VPS)

| App | URL |
|-----|-----|
| **Cutcost** | http://88.198.131.114.sslip.io |
| Jobboard (existing) | http://88.198.131.114 (unchanged) |

Cutcost listens on `127.0.0.1:9080` and is also on the `jobboard_default` Docker network.  
Jobboard nginx routes only hostname `88.198.131.114.sslip.io` to Cutcost.

## Auto-deploy from GitHub

Pushing to `main` runs `.github/workflows/deploy.yml`, which rsyncs the repo to `/opt/cutcost` and runs `./deploy.sh`.

Required GitHub secrets:

- `SSH_HOST` = `88.198.131.114`
- `SSH_USER` = `root`
- `SSH_PRIVATE_KEY` = contents of the deploy private key

## Free domain (no purchase)

Use **sslip.io** — it maps your server IP to a hostname automatically.

If your server IP is `88.198.131.114`, your free domain is:

```text
88.198.131.114.sslip.io
```

URL:

```text
http://88.198.131.114.sslip.io
```

Later you can buy a real domain and point it at the same server.

---

## 1. Create the Hetzner server

1. [Hetzner Cloud](https://console.hetzner.cloud/) → **New project** → **Add server**
2. Location: closest to you
3. Image: **Ubuntu 24.04**
4. Type: **CX22** (or any ~2GB+ RAM)
5. Add your SSH key
6. Create → note the **IPv4 address**

Open ports **22**, **80**, and **443** in the Hetzner firewall if you use one.

---

## 2. Install Docker on the server

SSH in:

```bash
ssh root@YOUR.SERVER.IP
```

Then:

```bash
apt-get update
apt-get install -y ca-certificates curl git
curl -fsSL https://get.docker.com | sh
systemctl enable --now docker
```

---

## 3. Put the code on the server

### Option A — Git (recommended)

On your laptop, push this repo to GitHub/GitLab, then on the server:

```bash
cd /opt
git clone https://github.com/YOUR_USER/Cutcost.git cutcost
cd cutcost
```

### Option B — Upload from your laptop

```bash
# from your Mac, in the Cutcost project folder
rsync -avz --exclude node_modules --exclude vendor --exclude .git \
  ./ root@YOUR.SERVER.IP:/opt/cutcost/
```

Then on the server: `cd /opt/cutcost`

---

## 4. Create production `.env`

Create `/opt/cutcost/.env` on the server (it is not in git). Minimum:

```env
APP_NAME=Cutcost
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://88.198.131.114.sslip.io
APP_DOMAIN=88.198.131.114.sslip.io
ACME_EMAIL=you@example.com

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cutcost
DB_USERNAME=cutcost
DB_PASSWORD=strong-password
DB_ROOT_PASSWORD=strong-root-password

SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=redis

STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
STRIPE_PRICE_STARTER=
STRIPE_PRICE_SHOP=
STRIPE_PRICE_STUDIO=
STRIPE_BYPASS_CHECKOUT=false
```

Leave `APP_KEY` empty — `deploy.sh` will generate it.

---

## 5. Deploy

```bash
chmod +x deploy.sh
./deploy.sh
```

First build takes a few minutes. When it finishes, open:

```text
https://YOUR.SERVER.IP.sslip.io
```

Health check:

```bash
curl -fsS https://YOUR.SERVER.IP.sslip.io/up
```

---

## 6. Stripe webhooks (required for payments)

In Stripe Dashboard → Developers → Webhooks → Add endpoint:

```text
https://YOUR.SERVER.IP.sslip.io/stripe/webhook
```

Paste the signing secret into `.env` as `STRIPE_WEBHOOK_SECRET`, then:

```bash
docker compose up -d app queue scheduler
```

---

## Day-to-day commands

```bash
cd /opt/cutcost

# Update after git pull
git pull
./deploy.sh

# Logs
docker compose logs -f app
docker compose logs -f caddy

# Shell into app
docker compose exec app php artisan tinker

# Stop / start
docker compose down
docker compose up -d
```

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| HTTPS fails | Wait 1–2 min; check `docker compose logs caddy`. Port 80/443 must be open. |
| 502 Bad Gateway | `docker compose logs app` — DB password mismatch is common |
| Blank page | Ensure `APP_KEY` is set and `APP_URL` matches the browser URL |
| Assets 404 | Rebuild: `docker compose up -d --build app` |

---

## Moving to a real domain later

1. Point `A` record of your domain to the server IP  
2. Set `APP_URL` and `APP_DOMAIN` in `.env` to that domain  
3. Run `./deploy.sh` again  

Caddy will issue a new certificate automatically.
