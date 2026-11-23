# BetZone — Classic Green Betting Admin Panel

A fully working **Laravel 12 + MySQL** betting platform with the exact sidebar structure from the reference screenshot, a five-level hierarchy, coin balances, shares, match/session commission, sports betting, ledgers and cash transactions.

Casino and Matka are intentionally **not included**.

---

## Live demo

| | |
|---|---|
| **Admin panel** | <http://127.0.0.1:8090/admin> |
| **Storefront** | <http://127.0.0.1:8090> |
| **Master / Agent panel** | <http://127.0.0.1:8090/agent> |
| **Health check** | <http://127.0.0.1:8090/up> |
| **MySQL** | `127.0.0.1:3307` · database `betzone` |

### Server deployment

If the deployed site shows `Connection refused` for MySQL, the server `.env` still points at the local development port `3307`. Use the production template and troubleshooting guide:

- [.env.production.example](.env.production.example)
- [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)
- [database/nativefriendly_joss.sql](database/nativefriendly_joss.sql) — creates and imports the production database
- [database/README.md](database/README.md)
- Run `php scripts/check-db.php` on the server to diagnose the connection.
- If the panel looks unstyled and CSS returns **403**, run `bash scripts/fix-permissions.sh` on the server. CSS/JS must be `644` and public directories `755`.

### Login credentials

| Role | Username | Password | Code |
|---|---|---|---|
| Admin Master | `admin` | `Admin@12345` | SAD88852 |
| Mini Master | `mini1` | `Agent@12345` | MINI1001 |
| Master Master | `master1` | `Agent@12345` | MAST1001 |
| Super Master | `super1` | `Agent@12345` | SUP1001 |
| Agent Master | `agent1` | `Agent@12345` | AG1001 |
| Agent Master | `agent2` | `Agent2@12345` | AG1002 |
| Client Master | `demo` | `Client@12345` | C5842 |
| Client Master | `rahul` | `Rahul@12345` | C7255 |
| Client Master | `priya` | `Priya@12345` | C3134 |
| Client Master | `vikram` | `Vikram@12345` | C1707 |
| Client Master | `karan` | `Karan@12345` | C3085 · blocked |

The Admin Master has **2,199,109 coins**, **6 direct members**, **95% share**, **5% company share**, **2% match commission** and **3% session commission** — matching the dashboard layout.

Full demo account list: [DEMO-CREDENTIALS.md](docs/DEMO-CREDENTIALS.md).

---

## Exact admin sidebar

```
DASHBOARD
ADMIN DETAILS
  MINI MASTER
  MASTER MASTER
  SUPER MASTER
  AGENT MASTER
  CLIENT MASTER
SPORTS BETTING
  INPLAY GAMES
  COMPLETED GAMES
  CURRENT BETS
LEDGER
  PROFIT/LOSS
  MY LEDGER
  MINI LEDGER
  MASTER LEDGER
  SUPER LEDGER
  AGENT LEDGER
  CLIENT LEDGER
CASH TRANSACTION
  MINI MASTER
  MASTER MASTER
  SUPER MASTER
  AGENT MASTER
  CLIENT MASTER
COMM. REPORT
REPORTS
  SECURE CODE REPORT
SETTING
```

No Casino, no Matka, no extra sidebar entries. Groups are collapsible and stay open on the active section.

---

## Dashboard

- Tiles: **Mini Admin**, **Sport's Details**, **Ledger**, **Cash Transaction**, **Settings**, **Logout**.
- User card: code + `YOU ARE ADMIN MASTER`.
- Stats: **Coins**, **Members**, **My Share**, **Company Share**, **Match Commission**, **Session Commission**, **Rules**.
- **Active Matches** table with Name, Open Date, Competition, Inplay and View.
- All data is dynamic from MySQL.

![Classic dashboard](docs/screenshots/classic-dashboard.png)

---

## Admin Details — five-level hierarchy

Each level has the same reference-style details page:

- Mini Master → **MINI ADMIN DETAILS**
- Master Master → **MASTER DETAILS**
- Super Master → **SUPER MASTER DETAILS**
- Agent Master → **AGENT DETAILS**
- Client Master → **CLIENT DETAILS**

Columns: **#, CODE, NAME, parent (ADMIN/MINI/MASTER/SUPER/AGENT), D.O.J, PASSWORD, SHARE, COMM % (TYPE/MATCH/SESSION), CHIPS, STATUS**; client pages replace SHARE with **EXPO**.

Each row has **two separate controls**:

- **Share icon (⤴)** opens **Partnership Details**:
  - Match Share, Match Commission
  - Session Commission
  - Casino Share, Casino Commission
  - Matka Share, Matka Commission
  - Green header with username + X/Close.
- **⋮ action menu** opens the operation popup:
  - **DEPOSIT** / **WITHDRAW** — wallet form with amount and note.
  - **LOGIN REPORT** — IP/user-agent login history.
  - **ACCOUNT OPERATIONS** — audit log.
  - **INACTIVE** — toggle active/inactive.
  - **BLOCK ACTIONS** — toggle blocked/active.
  - **EDIT** — hierarchy edit form.
  - **STATEMENT** — account wallet statement.
  - **RESET PASSWORD** — generates a new 5-character password.
  - **AGENT COMMISSION REPORT** — on Agent Master rows.
  - **DELETE** — deletes leaf accounts; accounts with downline or bets are protected.

**+ Update** opens the level-specific **Update Limit** page:

- Mini Master Update Limit
- Master Update Limit
- Super Master Update Limit
- Agent Update Limit
- Client Update Limit

Columns: **CODE, NAME, C.CHIP, ADD / MINUS LIMIT, ACTION**. Enter an amount and press **+ Add** or **− Minus**; the chips update immediately through the wallet ledger.

**Delete Selected** supports bulk deletion with the same protection. Demo data is imported across all five levels; admin can delete the new leaf demo accounts.

![Mini Admin details](docs/screenshots/classic-hierarchy-mini.png)

![Partnership details](docs/screenshots/classic-partnership-details.png)

![Action popup](docs/screenshots/classic-hierarchy-popup.png)

![Update limit](docs/screenshots/classic-update-limit.png)

![Client details](docs/screenshots/classic-hierarchy-client.png)

The create forms match the reference screenshot field-for-field:

- **NAME**, **REFERENCE**, **COINS**, **PASSWORD** (pre-filled 5-character password, visible).
- **MY COINS**, **MY MATCH SHARE**, **MY COMMISSION TYPE**, **MY MATCH COMM**, **MY SESSION COMM**, **MY CASINO COMM**, **MY MATKA COMM** shown read-only from the logged-in Admin Master.
- Target level fields: **MATCH SHARE**, **COMMISSION TYPE**, **MATCH COMM**, **SESSION COMM**, **CASINO COMM**, **MATKA COMM**.
- Submit stays disabled until all required fields are filled.
- Username/email are generated automatically from the name; the success message shows the login username and password.
- Opening coins are credited through the wallet ledger.

### Sequence-wise role creation

The complete creation chain is:

**Main Super Admin (admin)** → **Mini Master** → **Master Master** → **Super Master** → **Agent Master** → **Client Master**

- Main Super Admin creates Mini Master from the admin panel.
- Mini / Master / Super / Agent log into `/agent` and use **My Network** to create the next level.
- Each level can create only its own child level.
- Child share, match, session, casino and matka commission cannot exceed the parent's values.
- Opening coins are transferred from the parent's wallet to the child (parent balance is capped).
- Every new account gets an auto username, 5-character password, code and secure code.
- Commission accrual continues through all ancestors and is visible in the Agent Commission Report.

### One common master panel

All Mini / Master / Super / Agent masters log into the **same `/agent` panel**, using the **same classic green design and colours as the Main Super Admin panel**. There is no dark panel and no separate panel per level.

- Admin → any master row → **PANEL ACCESS**
- Toggle modules:
  - My Network
  - Create Downline
  - Matches / Betting
  - My Bets
  - My Players
  - Downline Bets
  - Commission
  - Wallet
  - My Profile
- Hidden modules are also route-blocked with `403`.
- **Reset to role defaults** restores all modules.
- Masters can open **Matches**, view markets and place bets from their own wallet. **Client Master** can do the same from the storefront match pages.

![Panel Access](docs/screenshots/admin-panel-access.png)

![Classic master dashboard](docs/screenshots/agent-classic-dashboard.png)

![Master matches](docs/screenshots/agent-matches.png)

![Master bet placement](docs/screenshots/agent-place-bet.png)

### Downward-only visibility

- Mini Master sees its full downline: Masters, Supers, Agents and Clients.
- Master sees Supers, Agents and Clients below it.
- Super sees Agents and Clients below it.
- Agent sees its Clients below it.
- A master **cannot** open upline or sibling account details.
- My Players also lists every client across the downline, not just direct children.

![My Network](docs/screenshots/agent-my-network.png)

The master-panel create form mirrors the Main Super Admin form field-for-field:

- **NAME, MY COINS, PASSWORD**
- **REFERENCE, COINS**
- **MATCH AND SHARE INFO**
- Editable **MY MATCH SHARE, MY COMMISSION TYPE, MY MATCH COMM, MY SESSION COMM, MY CASINO COMM, MY MATKA COMM** — saving updates the master's own terms and becomes the child cap
- Target **MATCH SHARE, COMMISSION TYPE, MATCH COMM, SESSION COMM, CASINO COMM, MATKA COMM**
- Submit stays disabled until all required fields are filled.
- Parent share/commission caps and wallet transfer are still enforced.

### Editable downline accounts

Every downline account has an **Edit** action with all account fields editable:

- Name, username, email
- Reference and password (blank keeps current)
- Coins/balance — increase is transferred from the master wallet, decrease is returned
- Share, commission type, match/session/casino/matka commission
- Status (active / inactive / blocked)
- Parent is fixed to preserve the hierarchy sequence

In create forms, MY terms are editable and update the current master's own account. In edit forms, parent terms are a fixed cap reference; the account's own fields are fully editable.

![Edit all fields](docs/screenshots/agent-edit-fields.png)

![Create next level](docs/screenshots/agent-create-master.png)

![Create Mini Admin](docs/screenshots/classic-create-mini-admin.png)

![Client master list](docs/screenshots/classic-client-master.png)

---

## Active Games

Sports Betting → **Inplay Games** opens the **ACTIVE GAMES** page:

- Start/End date filter, search by team or competition.
- Sortable **Date & Time**.
- Table: **#, NAME, DATE & TIME, COMPETITION NAME, DETAILS**.
- Row **⌄ menu**: MATCH BET, SESSION BET, COMPLETED SESSION, REJECTED BETS.
- **View** opens the live match betting board.

![Active Games](docs/screenshots/classic-active-games.png)

![Active Games menu](docs/screenshots/classic-active-games-menu.png)

Dashboard → **Active Matches** has the same row action menu and View link.

![Dashboard match menu](docs/screenshots/classic-dashboard-match-menu.png)

### Live Match Board

View opens a live board matching the sportsbook layout:

- Green match header + refresh/print.
- **My Book / Tii Book** tabs.
- Team **LAGAI / KHAI** table.
- **FANCY (COMM.)** sessions with NOT/YES, session limits and suspended rows.
- **CLIENTS SESSION PL** table with session amount, commission, total amount, my share and net PL.
- **MATCH STATS** with place time, user, runner, bet type, price, value and amount.
- **COMPLETED SESSIONS** with winner, commission, PL and Total Pl / Bets actions.
- Refresh controls for live data.

![Live match board](docs/screenshots/classic-live-match.png)

## Sports Betting

- **Inplay Games** — live/upcoming matches with the Active Games controls.
- **Completed Games** — settled/cancelled matches.
- **Current Bets** — open tickets with player, selection, stake and ticket detail.

Completed Games row popup:

- **MATCH AND SESSION PL REPORT** — ticket-wise match and session report with totals.
- **MATCH AND SESSION PL** — market-wise PL summary.
- **MATCH BET** — completed match tickets.
- **SESSION BET** — session tickets.
- **COMPLETED SESSION** — settled/void session tickets.
- **REJECTED BETS** — void/rejected tickets.

![Completed Games menu](docs/screenshots/classic-completed-games-menu.png)

![Match and Session PL Report](docs/screenshots/classic-pl-report.png)

![Match and Session PL](docs/screenshots/classic-pl-summary.png)

- **Match Bets / Session Bet / Completed Session / Rejected Bets** — per-match filtered ticket views.
- **Master Update Limit** — per-match master limits with Add/Minus.

![Match control](docs/screenshots/classic-match-control.png)

---

## Ledger

- **Profit/Loss → MATCH LEDGER** — date filter, TOTAL, and per-match DATE / EVENT NAME / WINNER / CR / DR.
- **MY LEDGER** — LENA, DENA, BALANCE summary with DATE / DR / CR / BALANCE / PAYMENT TYPE / REMARK rows.
- **MINI / MASTER / SUPER / AGENT / CLIENT LEDGER** — three-column boards:
  - **LENA** green panel with total and account balances.
  - **DENA** red panel with total and debit balances.
  - **CLEAR** blue panel for zero-balance accounts.
  - Each account has a ledger icon opening its statement.

![Match Ledger](docs/screenshots/classic-match-ledger.png)

![My Ledger](docs/screenshots/classic-my-ledger.png)

![Mini Master Ledger](docs/screenshots/classic-mini-master-ledger.png)

---

## Cash Transaction

Each level has its own transaction form: **Mini Master, Master Master, Super Master, Agent Master, Client Master**.

- Green `{LEVEL} TRANSACTION` header with Back.
- Fields: **CLIENT, COLLECTION, DATE, AMOUNT, PAYMENT TYPE, COMMENT** and Submit.
- Client dropdown is dynamic — it lists clients under that level's downline (Client Master lists all clients).
- Payment types: CASH, ONLINE, BANK, UPI.
- Submitting credits the client's coin wallet and writes a wallet ledger entry plus an Account Operation audit row.
- The same form is reused for all five levels with dynamic data.

![Mini Master transaction](docs/screenshots/classic-cash-mini.png)

---

## Comm. Report

- **AGENT COMMISSION REPORT** with date range, Agent Username, Apply and Reset History.
- Two-level table: **MILA HAI** and **DIYA HAI**, each split into **M.COMM, S.COMM, C.COMM, MATKA.COMM, T.COMM**.
- Action column opens the filtered agent report.
- Green received totals and red given totals in the **TOTAL** row.
- Dynamic from commission entries and each agent's parent share.

![Agent commission report](docs/screenshots/classic-agent-commission.png)

## Reports → Secure Code Report

- **SECURE CODE REPORT** header with Back.
- Search Code input with blue Search button.
- Table: **USERNAME, OTP, ACTION**.
- Empty state with envelope and **NO DATA**.
- OTP is the account's secure code; Action regenerates it.

![Secure code report](docs/screenshots/classic-secure-code.png)

## Setting

The Setting page has the same five green function tiles:

- **Statement**
- **Account Operations**
- **Profit & Loss**
- **Casino Profit Loss**
- **Search Client**

![Settings tiles](docs/screenshots/classic-settings.png)

### Statement

- Admin Master coin statement with **Date, Description, Old.Bal, CR, DR, COMM+, COMM−, Balance**.
- Filters: **All**, **P/L**, **Limit** (15/25/50/100 rows).
- 22 demo statement rows are imported by the seeder.
- Super Admin can delete **one row** (× button) or **multiple rows** (checkbox + Delete Selected).
- Pagination and demo badges are included.

![Statement](docs/screenshots/classic-statement.png)

### Account Operation

- Audit log with **Date | Operation | Description**.
- Tracks password changes, account creation/updates, coin credits/debits, secure-code changes and other admin actions.
- Operation badges and pagination.
- Demo audit rows are seeded for the initial view.

![Account operation](docs/screenshots/classic-account-operation.png)

### Match Profit Loss

- Green **MATCH PROFIT LOSS** header with Back.
- Total at the top right.
- Columns: **TITLE, PL, COMM+, COMM−, NET PL**.
- One row per match with date/time in the title, pagination included.

![Match profit loss](docs/screenshots/classic-match-profit-loss.png)

### Casino Profit Loss

- Tile and report page are present to match the reference.
- Casino gameplay is not enabled in this sportsbook build, so the report shows zero/empty data.

### Search Client

- **Search Code** input with the blue Search button.
- Searches by client code, username, name, reference or secure code.
- Results table with coins, agent, bets and profile link.

![Search client](docs/screenshots/classic-search-client.png)

### Platform settings

Site-level settings remain available at `/admin/settings/platform` (brand, limits, cashier limits, rules, announcement, admin share/commission and odds engine).

---

## Sub-master and agent panel

Mini Master, Master Master, Super Master and Agent Master accounts can log in from the same `/login` page and land on the downline panel:

- Dashboard with downline players, sub-agents, turnover, pending/paid commission.
- My Players — create clients under that account.
- Downline Bets — every ticket from the entire downline.
- Commission — accrual ledger.
- Wallet — commission payout and withdrawal request.
- My Profile.

Access is isolated: sub-masters cannot open the Admin Master panel.

---

## Commission system

- Each hierarchy account has a **match commission rate**.
- When a downline bet settles, commission accrues to the direct agent and **every master above** (mini/master/super/admin) at their own configured rate.
- Turnover and net-loss commission bases are supported.
- Commission entries are idempotent per agent + ticket.
- Admin Master's share/company/match/session terms appear on the dashboard.
- Commission payouts credit the account wallet with a ledger entry.
- Void bets do not generate commission.

---

## Client storefront

- Register/login with welcome bonus.
- Sports listing, live/upcoming filters.
- Match markets, dynamic odds polling.
- Bet slip with stake validation and AJAX placement.
- Dashboard, bet history, wallet, deposit/withdraw requests and account settings.

---

## Tech stack

| Layer | Choice |
|---|---|
| Framework | Laravel 12.72 |
| PHP | 8.5 |
| Database | MySQL 9.6 |
| Views | Blade + classic green CSS |
| JS | Vanilla JS, no build step |
| Tests | PHPUnit 11 |

---

## Run

```bash
cd betzone
./scripts/start-local.sh
```

Stop:

```bash
./scripts/stop-local.sh
```

The script starts the isolated MySQL on `127.0.0.1:3307`, starts Laravel on port `8090`, and runs the odds ticker.

### Rebuild demo data

```bash
php artisan migrate:fresh --seed
```

### Tests

```bash
php artisan test
```

**44 tests passed (394 assertions)** — menu sequence, hierarchy creation, coins, cash credit/debit, secure codes, commission accrual, payouts and client betting flows.

![Mobile dashboard](docs/screenshots/classic-dashboard-mobile.png)

---

## Database

- `users` — hierarchy, coins, shares, commissions, secure codes
- `sports`, `matches`, `markets`, `selections`
- `bets`
- `wallet_transactions`
- `agent_commissions`
- `operation_logs`
- `login_logs`
- `match_limits`
- `deposit_requests`, `withdrawal_requests`
- `settings`

MySQL local credentials:

```
host: 127.0.0.1
port: 3307
database: betzone
user: betzone
password: betzone_secret
```

Schema-only dump: [`database/betzone-schema.sql`](database/betzone-schema.sql)

---

## Notes

- This is a local demo. No real payment gateway or real money is connected.
- Casino and Matka games are deliberately excluded. Their commission columns remain on hierarchy accounts because they are part of the required account configuration fields.
- Change all demo passwords and `APP_KEY`, disable debug and use HTTPS before any production deployment.
- Betting is subject to local licensing and responsible-gaming requirements.
