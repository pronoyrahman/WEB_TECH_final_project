# TravelVista

A travel guide website built for **Web Technologies — Project 01**.

Scouts write up destinations they have visited, admins review and publish them,
and travellers search the archive, keep a wishlist, estimate trip costs and
leave notes. Non-registered visitors get a public home page only.

Plain **PHP 8 · MySQL/MariaDB · PDO · vanilla JavaScript · one global
stylesheet**, laid out as **MVC**. No frameworks, no Composer, no npm, no build
step.

---

## Requirements

| | |
|---|---|
| PHP | 8.1 or newer, with `pdo_mysql`, `fileinfo` and `mbstring` |
| Database | MySQL 8 or MariaDB 10.4+ |
| Server | Apache (XAMPP/WAMP/Laragon) or PHP's built-in server |

Nothing else. There are no dependencies to install.

## Setup

**1. Put the project where your server can see it** — e.g. `xampp/htdocs/travelvista`.
The code works from any sub-folder; it works out its own URL prefix.

**2. Point it at your database.** Open [`config/config.php`](config/config.php)
and edit the top block if your credentials differ from the XAMPP defaults:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'travelvista');
define('DB_USER', 'root');
define('DB_PASS', '');
```

**3. Create the schema.** Import `database/schema.sql` through phpMyAdmin, or:

```bash
mysql -u root -p < database/schema.sql
```

**4. Load the demo data** (recommended — it gives you accounts in every role,
ten published destinations, a review queue and some comments):

```bash
mysql -u root -p travelvista < database/seed.sql
```

**5. Open it.** With Apache, visit `http://localhost/travelvista/`. Or run PHP's
own server from the project folder:

```bash
php -S 127.0.0.1:8000
```

### Demo accounts

Every seeded account uses the password `password`.

| Email | Role | Status |
|---|---|---|
| `admin@travelvista.test` | admin | verified |
| `rina@travelvista.test` | scout | verified |
| `tomas@travelvista.test` | scout | verified |
| `priya@travelvista.test` | scout | **pending** |
| `jonah@travelvista.test` | user | verified |
| `leila@travelvista.test` | user | verified |
| `sam@travelvista.test` | user | **pending** |

The pending accounts are there on purpose, so you can watch the admin verify
someone and see the site unlock for them.

---

## How the MVC pieces fit together

```
index.php               sends the visitor to view/index.php

config/                 configuration and the request bootstrap
  config.php              database credentials, paths, domain vocabulary
  init.php                required first by EVERY page: session, models, auth
  helpers.php             escaping, URLs, flash messages, CSRF, JSON, formatting
  auth.php                sessions, "remember me", the role gates
  upload.php              image validation and storage

model/                  data — one class per table, all PDO, all prepared
  db.php                  the PDO connection and the run/all/one/value helpers
  user_model.php          post_model.php        post_request_model.php
  wishlist_model.php      comment_model.php     cost_model.php

control/                request handlers — forms post here, then redirect
  register_control.php    login_control.php     logout_control.php
  profile_control.php     scout_request_control.php
  admin_user_control.php  admin_post_control.php

api/                    AJAX endpoints — every one answers application/json
  posts_search.php        posts_filter.php      check_email.php
  wishlist_add.php        wishlist_remove.php
  comments_add.php        comments_delete.php   cost_estimate.php
  scout_request_delete.php
  admin_verify_user.php   admin_approve_request.php

view/                   pages — this is what the browser actually requests
  partials/               header, footer, flash, dispatch card, the two sidenavs
  index.php login.php register.php pending.php profile.php wishlist.php
  browse.php post.php
  scout_dashboard.php scout_request_form.php scout_requests.php scout_published.php
  admin_dashboard.php admin_users.php admin_requests.php admin_review.php
  admin_posts.php admin_post_edit.php admin_comments.php

css/global.css          the single global stylesheet for the whole site
js/                     app.js validation.js browse.js comments.js
                        cost.js scout.js wishlist.js admin.js
images/genre/           the flat SVG cover used when a post has no upload
uploads/                avatars/ and posts/ — written at runtime
database/               schema.sql and seed.php
```

The flow is always the same:

```
browser  ->  view/*.php        reads through a model, prints HTML
         ->  control/*.php     validates, writes through a model, redirects back
         ->  api/*.php         validates, writes through a model, returns JSON
```

No view ever writes to the database, and no controller ever prints HTML.

---

## Where each PRD task lives

### Task 1 — Auth, registration, profile, home, wishlist

| Requirement | Where |
|---|---|
| Registration for all three roles, `is_verified = 0` | `view/register.php` → `control/register_control.php` |
| Password ≥ 8 chars, unique email, hashed | `control/register_control.php`, `User::create()` |
| Login creating `$_SESSION['user_id'|'name'|'role']` | `control/login_control.php`, `auth_login()` |
| "Remember me" — hashed token + 30-day cookie | `auth_remember()` / `auth_restore_from_cookie()` |
| Profile: name, email, picture, password change | `view/profile.php` → `control/profile_control.php` |
| Verification notice for unapproved accounts | `view/pending.php`, `require_verified()` |
| Logout destroying session and cookie | `control/logout_control.php`, `auth_logout()` |
| Role-aware navbar | `view/partials/header.php` |
| Home page — three states | `view/index.php` |
| Wishlist add / view / remove | `api/wishlist_add.php`, `view/wishlist.php`, `api/wishlist_remove.php` |

### Task 2 — Scout post requests

| Requirement | Where |
|---|---|
| Scout gate (`role='scout'` and verified) | `require_role('scout')` |
| Create a post request into `post_requests` | `view/scout_request_form.php` → `control/scout_request_control.php` |
| My requests, with edit/delete only while pending | `view/scout_requests.php` |
| Edit a pending request | same form, `?id=` |
| Delete over AJAX with confirmation | `api/scout_request_delete.php` |
| View approved posts (read-only) | `view/scout_published.php` |
| Request changes to a published post | same form, `?change=` → `original_post_id` |
| Image upload to `uploads/posts/` | `config/upload.php` |

### Task 3 — Admin dashboard

| Requirement | Where |
|---|---|
| Admin gate | `require_role('admin')` |
| Dashboard counts | `view/admin_dashboard.php` |
| Add / verify / change role / delete users | `view/admin_users.php` → `control/admin_user_control.php` |
| Verify toggle over AJAX | `api/admin_verify_user.php` |
| Moderation queue and full review | `view/admin_requests.php`, `view/admin_review.php` |
| Approve → move request into `posts` | `PostRequest::publish()`, `api/admin_approve_request.php` |
| Reject with a reason | `control/admin_post_control.php` |
| Edit / delete any post | `view/admin_post_edit.php`, `control/admin_post_control.php` |
| Delete any comment | `view/admin_comments.php` → `api/comments_delete.php` |

### Task 4 — Browse, search, comments, cost

| Requirement | Where |
|---|---|
| Browse published posts as cards | `view/browse.php` |
| Post detail with full record and images | `view/post.php` |
| Live search on keystroke | `api/posts_search.php` + `js/browse.js` |
| Country / genre / cost filters over AJAX | `api/posts_filter.php` + `js/browse.js` |
| View, post and delete comments | `view/post.php`, `api/comments_add.php`, `api/comments_delete.php` |
| Probable cost + calculator | `api/cost_estimate.php`, `js/cost.js`, `CostEstimate::calculate()` |

---

## The cost estimate

Every post carries a base cost in `cost_estimates`. When a scout does not give
one, the PRD's mapping supplies it: **low = $500, medium = $1,500, high = $3,000**
for one traveller for one week.

The trip total is worked out the same way in PHP and in JavaScript, so the
figure on screen never disagrees with the one the server returns:

```
weeks = days / 7
party = 1 + (travellers - 1) × 0.85     the first traveller pays full
total = base × weeks × party
```

The browser updates the number instantly as the steppers move, then confirms it
against `api/cost_estimate.php`. If that request fails the calculator keeps
working and says so.

---

## Security

| Concern | How it is handled |
|---|---|
| SQL injection | Every query is a prepared statement through `Database::run()`. No SQL is built by concatenating input. Values that cannot be bound (sort order, genre and cost lists) are matched against a fixed whitelist first. |
| XSS | Everything printed goes through `e()` (`htmlspecialchars` with `ENT_QUOTES`). Comment text is stored raw and escaped on output. `TV.escape()` does the same for anything JavaScript writes. |
| CSRF | A per-session token in every form (`csrf_field()`), checked by `csrf_guard()`. AJAX sends it in the body and the `X-CSRF-Token` header; `api_csrf_guard()` checks it. |
| Passwords | `password_hash()` on the way in, `password_verify()` on the way out, re-hashed if PHP's cost changes. Never logged, never echoed. |
| "Remember me" | The cookie holds `id:secret`; only `sha256(secret)` is stored, and the secret is rotated on every use. A leaked database row cannot be replayed. |
| Sessions | `session_regenerate_id(true)` on login, HttpOnly + SameSite=Lax cookies, `session_start()` before any auth check. |
| Authorisation | `require_login()`, `require_verified()` and `require_role()` on pages; `api_require_role()` on endpoints. Ownership is re-checked on every write — a scout can only edit their own pending requests, a traveller can only delete their own comment. |
| Uploads | MIME type read from the file's own bytes with `finfo`, not from the browser; size capped; the stored filename is generated, never taken from the upload; `uploads/.htaccess` turns off PHP execution. |
| Direct access | `config/`, `model/` and `database/` carry an `.htaccess` that denies HTTP requests. |

---

## Validation

Every form is validated **twice**, and the server side is the one that counts.

* **Client** — `js/validation.js`. Fields declare what they need in HTML
  (`data-rules="required email"`, `data-min="8"`, `data-match="password"`), so
  adding a rule needs no new JavaScript. It also drives the password strength
  bar, the character counters, the image preview and the live "is this email
  taken?" check.
* **Server** — the matching controller, before any write. `Post::validate()`
  holds the destination rules shared by the scout form and the admin editor.
  Errors and the submitted values survive the redirect, so the form comes back
  filled in with the messages inline.

---

## Design

The site is built around one idea: a destination here is not a listing, it is a
**dispatch filed by a scout and catalogued**. That shows up in three places.

* **The ledger.** Wherever a destination appears — a browse card, a wishlist
  row, a moderation table, the detail page — it carries the same ruled
  monospace block of country, medium and cost. It is the thing that makes the
  public site and the back office read as one product.
* **The cost meter.** `low / medium / high` is drawn as a three-segment brass
  meter rather than written as a word, turning an enum into an instrument
  reading you can compare at a glance across a grid.
* **File numbers.** Records are referred to as `TV-0042` and `REQ-0007` in
  monospace, which is how a scout and an admin actually refer to them across
  the review screens.

Deep petrol green for the chrome, cool chart-paper grey for the working
surface, brass for anything actionable. Type is Bricolage Grotesque for
display, Instrument Sans for text and IBM Plex Mono for data. Cover art is flat
geometric SVG, one per genre, used whenever a post has no uploaded image.

All styling is in the single global stylesheet `css/global.css`, and all
behaviour is in `js/`. There is no inline `style` or `on*` attribute doing
layout or logic work.

---

## Notes on the schema

The PRD's tables are used as given. Three columns were added, and nothing was
dropped or renamed:

* `users.remember_token` — required by the PRD's own "Remember Me" requirement.
* `posts.country_representation` and `posts.image` — the PRD asks the detail
  page to show country representation and images, which need somewhere to live.
* `post_requests.original_post_id` — added exactly as the PRD suggests, so a
  change request can point at the post it would amend. `post_requests.admin_note`
  carries the rejection reason.

Foreign keys are `ON DELETE CASCADE`, so deleting a user takes their posts,
requests, wishlist rows and comments with them, and deleting a post takes its
comments, wishlist rows and cost estimate — which is what the PRD asks for.

---

## Troubleshooting

**"Database unavailable"** — MySQL is not running, or the credentials in
`config/config.php` are wrong. The page tells you which host and database it
tried.

**Images do not upload** — check that `uploads/avatars/` and `uploads/posts/`
are writable by the web server.

**Styles or scripts 404** — `BASE_URL` in `config/config.php` does not match the
folder the project sits in under `htdocs`. The folder is `TravelVista`, so the
line reads `define('BASE_URL', '/TravelVista');`. Rename the folder and you must
change that line to match.

**Everything is unverified** — that is the intended first-run state. Sign in as
`admin@travelvista.test` and verify the accounts from **Admin → Users**.
