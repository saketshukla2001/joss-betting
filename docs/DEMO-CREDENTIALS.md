# BetZone Demo Login Credentials

All accounts use the same login page: <http://127.0.0.1:8090/login>

| Level | Usernames | Password | Codes |
|---|---|---|---|
| Main Super Admin | `admin` | `Admin@12345` | SAD88852 |
| Mini Master | `mini1`, `mini2`, `mini3`, `mini4`, `mini6` | `Agent@12345` | MINI1001, MINI1002, MINI1003, MINI1004, MINI1006 |
| Mini Master (inactive) | `mini5` | `Agent@12345` | MINI1005 |
| Master Master | `master1`, `master2`, `master3`, `master4` | `Agent@12345` | MAST1001–MAST1004 |
| Master Master (blocked) | `master5` | `Agent@12345` | MAST1005 |
| Super Master | `super1`, `super2`, `super3`, `super4` | `Agent@12345` | SUP1001–SUP1004 |
| Super Master (inactive) | `super5` | `Agent@12345` | SUP1005 |
| Agent Master | `agent1`, `agent3`, `agent4`, `agent5` | `Agent@12345` | AG1001, AG1003–AG1005 |
| Agent Master | `agent2` | `Agent2@12345` | AG1002 |
| Client | `demo`, `client6`, `client7`, `client8`, `client10` | `Client@12345` | C5842, C6001–C6003, C6005 |
| Client | `rahul` | `Rahul@12345` | C7255 |
| Client | `priya` | `Priya@12345` | C3134 |
| Client | `vikram` | `Vikram@12345` | C1707 |
| Client | `karan` (blocked) | `Karan@12345` | C3085 |
| Client (inactive) | `client9` | `Client@12345` | C6004 |

Sequence:

1. `admin` (Main Super Admin) creates Mini Masters.
2. `mini1` logs in → My Network → creates Master Master.
3. `master1` logs in → My Network → creates Super Master.
4. `super1` logs in → My Network → creates Agent Master.
5. `agent1` logs in → My Network → creates Client Master.

All master levels use the same classic-green `/agent` panel. Main Super Admin controls their modules from the row's **PANEL ACCESS** action. Masters and Client Masters can open matches and place bets.

Notes:

- Mini/Master/Super/Agent accounts land on the agent panel at `/agent`.
- Client accounts land on the player dashboard at `/dashboard`.
- Inactive and blocked demo accounts are intentionally kept to show the status column; use the **INACTIVE** / **BLOCK ACTIONS** popup actions to reactivate them.
- Admin can reset any password from the row action popup; the new 5-character password is shown once in the success message.
