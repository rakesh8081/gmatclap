# gmatclap.com

Website for GMATclap (GMAT coaching and MBA admissions consulting). Hosted on Hostinger shared hosting (Premium plan).

## How the live site works

| Part | Where it lives | What it is |
|---|---|---|
| Website pages | `src/` → built into `public_html/` | Angular 17 app, pre-rendered to plain HTML at build time |
| Enquiry backend + admin | `backend/api/` → `public_html/api/` | Small PHP 8.1 scripts |
| Enquiry data | `domains/gmatclap.com/gmatclap-data/site.sqlite` | SQLite file **outside** `public_html`, created automatically |
| gmatclap.in | `backend/gmatclap.in/.htaccess` | Old WordPress landing page, now 301-redirected to gmatclap.com |

There is no Node server in production. `server.ts` (Angular SSR) is only used during the build to pre-render pages.

### Enquiry forms

The **Enquiry Now** (`/enquiry`) and **Contact Us** (`/contactus`) forms post JSON to `/api/enquiry.php`, which:

- validates name, email and phone
- blocks simple bots (hidden `website` field) and floods (3 per email / 10 per IP per 10 minutes)
- saves the enquiry and emails a notification to gmatclap@gmail.com

### Admin

`https://gmatclap.com/api/admin.php` – view, search, change status, delete, and download all enquiries as CSV.
The password is set on first visit with the one-time `SETUP_CODE` in `backend/api/admin.php`; after that it is stored only as a hash.
`/adminlogin` just forwards to this page.

## Building and deploying

Push to `main`. GitHub Actions (`.github/workflows/build-deploy.yml`) then:

1. runs `npm ci` and `ng build` (every page is pre-rendered to HTML)
2. checks the PHP files
3. assembles `public_html`: the built site + `backend/api/` as `api/` + `deploy/.htaccess`
4. publishes it to the **`deploy` branch** (a record of exactly what went live; never edit it by hand)
5. uploads changed files to Hostinger `public_html` over FTPS, using the repo secrets `FTP_USERNAME` and `FTP_PASSWORD` (a Hostinger FTP account whose folder is gmatclap.com's `public_html`)

If the build fails, nothing is uploaded and the live site is unchanged. Check progress under the repo's **Actions** tab; **Run workflow** there re-deploys without a code change.

After a deploy, clear the cache if a change doesn't show: hPanel → Websites → gmatclap.com → Cache Manager.

Local development:

```bash
npm install
npx ng serve          # http://localhost:4200 (forms need the PHP api, so test them on the live site)
```

Never delete `domains/gmatclap.com/gmatclap-data/` – it holds all enquiries.

## History

- May 2024: original site built by Srinath Pannala ([srinathpannala/gmatclap](https://github.com/srinathpannala/gmatclap)). Forms pointed at a `localhost:8080` Java backend that was never deployed, so no enquiries were saved.
- Nov 2026: PHP enquiry backend + admin page added, forms fixed, hard-coded admin password removed from the frontend, gmatclap.in redirected to gmatclap.com.

The live `main-P3ADUMPJ.js` on the server was patched directly (2026-11-26) to use `/api/enquiry.php`, because the site could not be rebuilt at the time. The next `ng build` from this repo replaces it with the same fix built in.
