# Equipment Tracker — Feature Scope

> **Status:** ✅ Implemented — all tasks complete  
> **Last Updated:** 2026-04-09

---

## 1. What the Client Needs

A dedicated **Equipment Tracker** page that lets the admin see, filter, and analyze every piece of equipment across all stores — how many times it was fixed, who fixed it, how much **labor** was spent on it, and how much was spent on **purchases/parts**.

---

## 2. How the Current System Works (Context)

```
Store
 └── MaintenanceRequest  (a ticket: "this equipment broke")
      ├── equipment_with_issue      ← free-text today, will link to equipment registry
      ├── equipment_id (NEW FK)     ← will point to the equipment table
      ├── costs                     ← legacy total cost field (not used for tracker math)
      ├── how_we_fixed_it
      ├── status                    ← on_hold / in_progress / complete
      │
      ├── ← InvoiceCard sessions (technician labor)
      │        via invoice_card_maintenance_requests (pivot)
      │        ├── task_status  ← pending / in_progress / completed
      │        ├── completed_at
      │        └── InvoiceCard fields used for labor math:
      │             ├── labor_hours
      │             ├── accumulated_labor_hours    ← carries over from previous sessions
      │             ├── labor_cost                 ← (labor_hours + accumulated) × hourly_pay
      │             ├── driving_time_payment       ← pay for drive time to store
      │             └── mileage_payment            ← pay for miles driven to store
      │
      ├── ← InvoiceCardMaterials (technician on-site purchases)
      │        └── invoice_card_materials.maintenance_request_id = this MR
      │        └── invoice_card_materials.cost
      │
      └── ← Payments (admin / vendor charges)
               └── payments.maintenance_request_id = this MR
               └── payments.cost
               └── PaymentEquipmentItems (parts line items inside the payment)
```

The **"store card"** referred to is the `InvoiceCard` show page  
(`Modules/Invoice/resources/views/cards/show.blade.php`).  
Each card = one technician visit to a store.  
One card can have multiple maintenance requests (multiple repairs in one visit).

---

## 3. Equipment Registry

### 3.1 Source of Equipment List
- The client will provide the official equipment list.
- We will **seed** that list from existing distinct values in `maintenance_requests.equipment_with_issue` (e.g. "Walk-in Cooler", "Cres Cor").
- **"Others"** is a valid equipment record — it represents any item not in the list.  
  It is treated the same as every other equipment (has its own row, can accumulate costs).

### 3.2 Linking Existing Maintenance Requests
- When the equipment table is seeded, we will run a **matching pass** on existing `maintenance_requests`:  
  if `equipment_with_issue` matches (case-insensitive) a name in the `equipment` table, set `equipment_id` automatically.
- Records that do not match any known equipment will have `equipment_id = null` — they appear under **"Unregistered"** on the tracker.

### 3.3 Who Manages the Registry
- **Admin only** — a simple CRUD UI on the equipment management page.
- Separate page from the tracker (or a tab/modal inside it — TBD at UI stage).

---

## 4. Labor Cost Formula (Critical — Read Carefully)

### 4.1 Single-Session Case (technician finishes in one visit)

One `InvoiceCard` → contains N maintenance requests (via `invoice_card_maintenance_requests`).

```
Repair time share   = invoice_card.labor_hours          ÷ N   (hours)
Labor share per MR  = invoice_card.labor_cost          ÷ N   (cost)
Drive time share    = invoice_card.driving_time_payment ÷ N
Mileage share       = invoice_card.mileage_payment      ÷ N

Total repair time   = Repair time share                       (hours shown on tracker)
Total labor for MR  = Labor share + Drive time share + Mileage share
```

N = COUNT of rows in `invoice_card_maintenance_requests` for that `invoice_card_id`.

### 4.2 Multi-Session Case (technician comes back)

When a repair is not finished:
- Session 1 → `InvoiceCard A`, `task_status = 'in_progress'` or `'pending'`
- Session 2 → `InvoiceCard B` (new card, new visit), `task_status = 'completed'`
- `InvoiceCard B.accumulated_labor_hours` = already includes hours from Session 1
- `InvoiceCard B.labor_cost` = `(labor_hours + accumulated_labor_hours) × hourly_pay`  
  → **already the total labor for the whole job**

**Rule: for a multi-session MR, the FINAL card (where `task_status = 'completed'`) already contains the full accumulated labor cost and total hours. Use only that card's share.**

For intermediate (non-completed) sessions, the hours and labor cost are already baked into the final card's `accumulated_labor_hours`.  
**Do NOT sum labor_cost or labor_hours across all cards for the same MR — this would double-count.**

#### Safe Formula Per MR:

```
For each (invoice_card, maintenance_request) pair:
  - If task_status = 'completed' on this card for this MR:
      → repair_time   = (card.labor_hours + card.accumulated_labor_hours) ÷ N_final  (hours)
      → cost_contrib  = (card.labor_cost + card.driving_time_payment + card.mileage_payment) ÷ N_final
      (N_final = count of MRs on this FINAL card)
  - If task_status ≠ 'completed' (intermediate session):
      → hours and labor cost already rolled into final card → skip both
      → BUT driving_time_payment and mileage_payment for THIS visit happened and are NOT in the final card
         → still include: (card.driving_time_payment + card.mileage_payment) ÷ N_intermediate

Total repair time for MR = SUM of repair_time contributions (hours)
Total labor for MR       = SUM of cost contributions across all cards
```

> ⚠️ **Risk Note:** If a repair has never been marked completed (still in_progress permanently),  
> we fall back to the latest card: repair_time = `card.labor_hours ÷ N`, labor = `card.labor_cost ÷ N`. This edge case must be handled gracefully.

---

## 5. Purchase Cost Formula

Two sources are combined:

### 5.1 Admin Payments (vendor/company invoices)
```
Source: payments table
Filter: payments.maintenance_request_id = this MR's id
Value:  SUM(payments.cost)
```
This covers contractor payments, parts ordered by admin, etc.

### 5.2 Technician On-Site Purchases
```
Source: invoice_card_materials table
Filter: invoice_card_materials.maintenance_request_id = this MR's id
Value:  SUM(invoice_card_materials.cost)
```
This covers parts or materials the technician bought while on-site.

### 5.3 Total Purchase per MR
```
Total Purchase = SUM(payments.cost) + SUM(invoice_card_materials.cost)
```

---

## 6. Database Design

### New Table: `equipment`

```sql
equipment
---------
id                   BIGINT PK AUTO_INCREMENT
name                 VARCHAR(255)          -- "Walk-in Cooler", "Cres Cor", "Others"
store_id             BIGINT FK → stores(id) nullable  -- null = global/any store
type                 VARCHAR(100)  nullable -- "Refrigeration", "HVAC", "POS", "Plumbing", etc.
serial_number        VARCHAR(100)  nullable
model                VARCHAR(100)  nullable
notes                TEXT          nullable
is_active            BOOLEAN       default true
timestamps
```

> Equipment is **global by default** (`store_id = null`). Some equipment may be scoped to a specific store when needed (`store_id` set). The "Others" record is one single global record (`store_id = null`).

### Modified Table: `maintenance_requests`

Add ONE nullable column:

```sql
equipment_id   BIGINT UNSIGNED NULL
               FOREIGN KEY → equipment(id) ON DELETE SET NULL
```

`equipment_with_issue` (free text) is kept as-is — no data is lost.

---

## 7. Equipment Tracker Page — UI Design

### Filters Bar
| Filter | Type | Notes |
|--------|------|-------|
| Store | Dropdown | `stores` table |
| Date Range | from / to date picker | filters on `maintenance_requests.request_date` |
| Equipment Type | Dropdown | `equipment.type` |
| Show Unregistered | Toggle | show MRs with `equipment_id = null` |

### Summary Cards (top of page)
```
[ Total Equipment Tracked ]  [ Total Fix Events ]
[ Total Labor Cost ]         [ Total Purchase Cost ]   [ Grand Total ]
```

### Main Table
| Column | Source |
|--------|--------|
| Equipment Name | `equipment.name` |
| Type | `equipment.type` |
| Store | `stores.name` |
| # of Fixes | COUNT of linked `maintenance_requests` |
| Total Repair Time | SUM of hours formula from section 4 (displayed as e.g. "4h 30m") |
| Labor Cost | formula from section 4 |
| Purchase Cost | formula from section 5 |
| Total Cost | Labor + Purchase |
| Last Fixed | MAX `request_date` where MR status = complete |
| Export row | included in CSV |
| Actions | View details |

### Equipment Detail Page (drill-down)
Per equipment — shows a timeline of every maintenance request:
| Column | Source |
|--------|--------|
| Ticket # | `maintenance_requests.entry_number` |
| Date | `request_date` |
| Store | store |
| Description | `description_of_issue` |
| How Fixed | `how_we_fixed_it` |
| Technician(s) | user names from `invoice_cards` on this MR |
| Repair Time | calculated hours (section 4) displayed as "Xh Ym" |
| Labor Cost | calculated cost share |
| Purchases | admin payments + technician materials |
| Total | Labor Cost + Purchases |
| Status | MR status |

Totals at top: total fixes, total labor, total purchases, grand total.

### CSV Export
- Export button on the tracker index page.
- Exports the **filtered** table (respects all active filters).
- Columns: Equipment Name, Type, Store, # Fixes, Total Repair Time (hours), Labor Cost, Purchase Cost, Total Cost, Last Fixed Date.
- Detail-level CSV option on the detail page (one row per maintenance request for that equipment).

---

## 8. Files to Create / Modify

### New Files

| File | Purpose |
|------|---------|
| `database/migrations/xxxx_create_equipment_table.php` | New equipment registry table |
| `database/migrations/xxxx_add_equipment_id_to_maintenance_requests.php` | Add FK |
| `database/seeders/EquipmentSeeder.php` | Seed from client list + auto-match existing MRs |
| `app/Models/Equipment.php` | Eloquent model |
| `app/Http/Controllers/EquipmentTrackerController.php` | index, show, store, update, destroy, export |
| `app/Services/EquipmentLaborService.php` | Labor cost calculation logic (section 4) |
| `resources/views/admin/equipment/index.blade.php` | Tracker list page |
| `resources/views/admin/equipment/show.blade.php` | Equipment detail page |

### Modified Files

| File | What Changes |
|------|-------------|
| `routes/web.php` | Add equipment tracker routes + export route |
| `app/Models/MaintenanceRequest.php` | Add `equipment()` BelongsTo |
| `app/Models/Store.php` | Add `equipment()` HasMany |
| `resources/views/admin/maintenance-requests/show.blade.php` | Show + edit linked equipment |
| `resources/views/admin/payments/create.blade.php` | (no change needed — already scoped to MR) |

---

## 9. Build Order

```
Phase 1 — Data Foundation
  ├── Migration: create equipment table
  ├── Migration: add equipment_id to maintenance_requests
  ├── Model: Equipment.php with relationships
  ├── Update: MaintenanceRequest.php + Store.php
  └── Seeder: seed client list + auto-match existing MR free-text

Phase 2 — Equipment Registry CRUD
  ├── Controller methods: index, create, store, edit, update, destroy
  ├── Routes
  └── Basic admin views

Phase 3 — Labor Service
  ├── EquipmentLaborService.php (implements section 4 formula)
  └── Unit tests for edge cases (multi-session, never-completed)

Phase 4 — Tracker Page
  ├── index view: filters + summary cards + table
  ├── show view: per-equipment detail with MR timeline
  └── CSV export (index + detail level)

Phase 5 — Auto-Link & Cleanup
  ├── On new MR creation: auto-set equipment_id by matching equipment_with_issue text against equipment table
  ├── No match found → auto-assign to the "Others" equipment record (never null after creation)
  └── Admin can correct a wrong auto-assignment via the Equipment detail page (re-assign MR to different equipment)
```

---

## 10. Confirmed Decisions

- [x] Equipment registry = new `equipment` table
- [x] `equipment_id` on `maintenance_requests` is nullable (backward compatible)
- [x] "Others" is a real equipment record, not a special type
- [x] Existing MRs auto-matched to equipment by text on seeding; unmatched MRs auto-assigned to "Others"
- [x] New MRs: auto-assigned on creation, no manual dropdown on MR form
- [x] Admin can re-assign an MR to a different equipment from the Equipment detail page
- [x] Labor = proportional split per MR on card (drive time + mileage + in-store hours+cost), accumulated sessions use final card only
- [x] Repair time (hours) tracked and displayed alongside labor cost
- [x] Purchases = `payments.cost` (admin) + `invoice_card_materials.cost` (technician), both filtered by `maintenance_request_id`
- [x] CSV export on tracker page
- [x] Native requests: OUT OF SCOPE

## 11. All Decisions Confirmed

- [x] Equipment is **global** — `store_id = null` for shared equipment; store-specific only when explicitly needed
- [x] Equipment type list seeded from existing DB values now; client will supply expanded list later — no enum, stored as plain string
- [x] **One global "Others"** record (`store_id = null`) — all unmatched MRs link to it regardless of store
- [x] Equipment merge tool: **optional enhancement** — not in initial build scope

---

*Task 1 scope fully locked.*

---
---

# Task 2 — Manual Fix Records (Technician-Created from InvoiceCard)

> **Status:** Draft v1 — open for discussion  
> **Last Updated:** 2026-04-08

---

## T2.1 What the Client Needs

When a technician is at a store and fixes something that has **no Cognito form / no MaintenanceRequest already created**, they need to be able to log that fix directly from within their InvoiceCard session.

This repair must still:
- Count toward the Equipment Tracker (Task 1)
- Have labor time distributed properly across the card (same formula as section 4)
- Be linkable to an equipment record
- Optionally have parts/materials attached to it

---

## T2.2 Architecture Decision: Extend `maintenance_requests`, NOT a New Table

**Decision: Add a `source` column to `maintenance_requests`.**

### Why not a new table:
- The Equipment Tracker (Task 1) aggregates everything through `maintenance_requests`. A separate table would force every query in Task 1 to become a UNION query — doubling complexity across labor formulas, purchase formulas, equipment linking, and CSV export.
- The `invoice_card_maintenance_requests` pivot already connects `invoice_cards` ↔ `maintenance_requests`. No new pivot needed.
- Precedent already exists: the `not_in_cognito` boolean on `maintenance_requests` proves the codebase already handles non-Cognito records in the same table.

### What changes in `maintenance_requests`:

**New column:**
```sql
source   ENUM('cognito', 'manual')  NOT NULL  DEFAULT 'cognito'
```

**Columns that must become nullable** (for manual records — Cognito-specific):
```sql
form_id                   VARCHAR  → nullable
webhook_id                VARCHAR  → nullable  (currently UNIQUE — index must allow nulls)
entry_number              INTEGER  → nullable
requester_id              FK       → nullable
reviewed_by_manager_id    FK       → nullable
urgency_level_id          FK       → nullable
```

> `equipment_with_issue` stays required — the technician must describe what they fixed.  
> `description_of_issue` stays required — they must explain what happened.

**New optional column:**
```sql
created_by_user_id   BIGINT UNSIGNED NULL  FK → users(id) ON DELETE SET NULL
```
Stores which technician created the manual record (the `user_id` from the InvoiceCard).

---

## T2.3 Guard Rails for Cognito Sync

Manual records must NEVER be processed by the Cognito webhook sync logic.

- All Cognito sync code must check: `WHERE source = 'cognito'` (or `source IS NULL` for old records before the migration).
- The `not_in_cognito` flag stays untouched — it tracks a different concern (Cognito records that failed to sync).
- When searching for a maintenance request by `webhook_id`, always filter `source = 'cognito'` first.

---

## T2.4 User Workflow (from InvoiceCard)

```
Technician is on InvoiceCard show page (store visit in progress)
  └── Sees "Tasks" list of assigned MaintenanceRequests
  └── Clicks "+ Add Manual Fix"
       └── Modal / simple form opens:
            ├── Equipment (dropdown → equipment table, store pre-filtered)
            ├── Description of issue (text, required)
            ├── How we fixed it (text, optional at creation — can fill later)
            └── Submit
                └── Creates MaintenanceRequest (source = 'manual', status = 'in_progress')
                └── Auto-links it to the current InvoiceCard via pivot
                └── Sets equipment_id based on dropdown selection
                └── Appears immediately in the card's task list
```

After submission:
- The manual MR behaves exactly like a Cognito MR within the card: it gets labor time distributed to it, materials can be attached to it, its `task_status` can be updated to `completed`.
- It will appear in the Equipment Tracker automatically once the card is marked done.

---

## T2.5 Status Flow for Manual Records

Manual records start with `status = 'in_progress'` (no point in `on_hold` — the technician is already there).

Status transitions allowed:
```
in_progress → complete   (when technician marks task_status = 'completed' on the card)
in_progress → on_hold    (if they can't finish and will return)
```

The existing `StatusHistory` model logs transitions — no change needed there.

---

## T2.6 Admin Visibility

Manual records must be clearly distinguished from Cognito records in the admin UI:

- In the maintenance requests index: add a **"Manual"** badge/tag on rows where `source = 'manual'`.
- In the maintenance requests show page: show "Created manually by [technician name]" instead of Cognito form details.
- In the Equipment Tracker: manual fix records are included in all aggregations — they are treated identically to Cognito records.

---

## T2.7 Database Changes Summary

### Modified Table: `maintenance_requests`
| Change | Type | Details |
|--------|------|---------|
| Add `source` | New column | `ENUM('cognito','manual') DEFAULT 'cognito'` |
| Add `created_by_user_id` | New column | `BIGINT NULL FK → users(id)` |
| Make `form_id` nullable | Alter column | Was `VARCHAR NOT NULL` |
| Make `webhook_id` nullable | Alter column | Was `VARCHAR UNIQUE NOT NULL` → allow null in unique index |
| Make `entry_number` nullable | Alter column | Was `INTEGER NOT NULL` |
| Make `requester_id` nullable | Alter column | Was FK NOT NULL |
| Make `reviewed_by_manager_id` nullable | Alter column | Was FK NOT NULL |
| Make `urgency_level_id` nullable | Alter column | Was FK NOT NULL |

---

## T2.8 Files to Create / Modify

### New Files
| File | Purpose |
|------|---------|
| `database/migrations/xxxx_add_source_and_manual_fields_to_maintenance_requests.php` | All column changes from T2.7 in one migration |

### Modified Files
| File | What Changes |
|------|-------------|
| `app/Models/MaintenanceRequest.php` | Add `source`, `created_by_user_id` to `$fillable`; add `createdByUser()` BelongsTo |
| `Modules/Invoice/resources/views/cards/show.blade.php` | Add "+ Add Manual Fix" button + modal |
| `Modules/Invoice/app/Http/Controllers/InvoiceCardController.php` | Add `storeManualFix()` method |
| `Modules/Invoice/routes/web.php` (or main routes) | Add POST route for manual fix creation |
| `resources/views/admin/maintenance-requests/index.blade.php` | Show "Manual" badge on `source = 'manual'` rows |
| `resources/views/admin/maintenance-requests/show.blade.php` | Show manual record details (no Cognito fields) |
| All Cognito sync code | Add `source = 'cognito'` guard |

---

## T2.9 Build Order

```
Phase 1 — Schema
  ├── Migration: add source, created_by_user_id, make Cognito columns nullable
  └── Update MaintenanceRequest model ($fillable, relationships)

Phase 2 — Backend
  ├── storeManualFix() controller method on InvoiceCard
  │    ├── Validate: store_id, equipment_id, description_of_issue
  │    ├── Create MaintenanceRequest (source=manual, status=in_progress)
  │    ├── Attach to invoice_card via pivot
  │    └── Auto-set equipment_id
  └── Add guard to Cognito sync: skip source='manual' records

Phase 3 — UI
  ├── "+ Add Manual Fix" button on InvoiceCard show page
  ├── Modal form (equipment dropdown, description, optional how_we_fixed_it)
  ├── Inline edit on InvoiceCard task list: technician can update description + how_we_fixed_it
  ├── Admin can also edit via maintenance-requests show page
  ├── "Manual" badge on maintenance-requests index (visible alongside Cognito tickets)
  └── Conditional display on maintenance-requests show (hide Cognito fields for manual records)
```

---

## T2.10 Confirmed Decisions

- [x] Technician can edit their own manual fix (description, how_we_fixed_it) from the InvoiceCard; admin can edit from the maintenance-requests show page
- [x] Manual fixes appear in the maintenance requests index alongside Cognito tickets (with a "Manual" badge to distinguish them)
- [x] Card completion does NOT auto-complete manual fixes — technician must explicitly mark each task `completed` on the card before submitting

## T2.11 Open Questions

*All questions resolved. Task 2 scope is locked. Ready for task breakdown.*

---
---

# Task 3 — Invoice Card Status Report (Finance View)

> **Status:** Draft v1 — open for discussion  
> **Last Updated:** 2026-04-08

---

## T3.1 The Problem

InvoiceCards have a `start_time` and an `end_time`.  
A technician can **start** a card in one month and **finish** it in the next.

Example:
- Card started: March 28 → `status = in_progress`
- Card completed: April 3 → `status = completed`, `end_time = April 3`

The finance department invoices by **month**. This cross-month card creates ambiguity:
- Which month does the labor cost belong to?
- What is the total outstanding (started but not finished) going into a new month?
- Which completed cards still have no invoice generated?

The client needs a **dedicated report page** that answers all of these questions at a glance.

---

## T3.2 No Schema Changes Needed

All required data already exists:

| Data Point | Source |
|-----------|--------|
| Card start date | `invoice_cards.start_time` |
| Card end date | `invoice_cards.end_time` (null if still open) |
| Card status | `invoice_cards.status` → `in_progress`, `completed`, `not_done` |
| Labor cost | `invoice_cards.labor_cost` |
| Materials cost | `invoice_cards.materials_cost` |
| Mileage payment | `invoice_cards.mileage_payment` |
| Total cost | `invoice_cards.total_cost` |
| Invoice generated? | EXISTS in `invoices` table via `invoices.invoice_card_id` |
| Technician | `invoice_cards.user_id` → `users` |
| Store | `invoice_cards.store_id` → `stores` |

> This is a **pure reporting feature** — read-only, no migrations, no model changes.

---

## T3.3 Report Page Design

### Filters Bar
| Filter | Type | Notes |
|--------|------|-------|
| Month / Year | Month picker | Primary filter — defaults to current month |
| Store | Dropdown | `stores` table |
| Technician | Dropdown | `users` where role = technician/user |
| Status | Dropdown | All / Completed / In Progress / Not Done |
| Invoice Status | Dropdown | All / Invoiced / Pending Invoice |

---

### Summary Cards (top of page)

```
[ Total Cards This Period ]   [ Completed ]   [ Still Open (In Progress + Not Done) ]

[ Total Cost — Completed ]    [ Outstanding Cost — Still Open ]    [ Total Invoiced ]   [ Pending Invoice ]
```

---

### Main Table: All Cards

| Column | Source | Notes |
|--------|--------|-------|
| # | `invoice_cards.id` | |
| Technician | `users.name` | |
| Store | `stores.name` or `store_number` | |
| Start Date | `invoice_cards.start_time` | date + time |
| End Date | `invoice_cards.end_time` | "—" if still open |
| Status | `invoice_cards.status` | badge: Completed / In Progress / Not Done |
| ⚠ Cross-Month | derived | flag shown when `MONTH(start_time) ≠ MONTH(end_time)` |
| Labor Cost | `invoice_cards.labor_cost` | |
| Materials | `invoice_cards.materials_cost` | |
| Mileage | `invoice_cards.mileage_payment` | |
| Total Cost | `invoice_cards.total_cost` | |
| Invoice | derived | "Invoiced" badge or "Pending" badge |
| Actions | — | View card link |

**Cross-Month flag logic:**
```
cross_month = (end_time IS NOT NULL AND MONTH(start_time) != MONTH(end_time))
           OR (end_time IS NULL AND MONTH(start_time) != MONTH(NOW()))
```
These rows are highlighted (e.g. yellow background) to immediately draw finance attention.

---

### Tab / Section Split

The page has two clearly separated sections (tabs or collapsible panels):

**Tab 1 — Completed Cards**
- Cards where `status = 'completed'`
- Shows total cost, whether invoice exists
- Sub-badge: "Invoiced" (green) or "Needs Invoice" (orange)
- Cross-month cards flagged with ⚠

**Tab 2 — Open Cards (Not Finished)**
- Cards where `status = 'in_progress'` OR `status = 'not_done'`
- These represent **financial exposure**: work done / partially done, not yet billed
- Shows current labor cost accumulated so far (even if card not closed)
- Cross-month cards flagged with ⚠
- "Days Open" column: `NOW() - start_time` in days

---

### CSV Export
- One export button per tab (Completed / Open)
- Columns match the main table
- Cross-month flag included as a `yes/no` column

---

## T3.4 Key Business Rules

1. **Cross-month cards are highlighted, not blocked.** The report surfaces them so finance can decide — it does not prevent the system from processing them.

2. **"Not Done" cards are financial exposure too.** A `not_done` card means the technician went, could not finish, and the card will be reopened. The current partial cost (labor + mileage for that visit) is already recorded and must be visible.

3. **Invoice status is read from the `invoices` table.** A card is "Invoiced" if a row exists in `invoices` where `invoices.invoice_card_id = invoice_cards.id`. A card is "Pending Invoice" if no such row exists and `status = 'completed'`.

4. **The report filters by the card's `start_time` month by default.** The finance team works month by month. We default to the current month so they immediately see what is open this month.

5. **Open cards are sorted by `Days Open` descending** — the longest-open cards appear first so finance can chase them.

---

## T3.5 Files to Create / Modify

### New Files
| File | Purpose |
|------|---------|
| `Modules/Invoice/resources/views/cards/report.blade.php` | The report page view |

### Modified Files
| File | What Changes |
|------|-------------|
| `Modules/Invoice/app/Http/Controllers/InvoiceCardController.php` | Add `report()` method |
| `Modules/Invoice/routes/web.php` (or main `routes/web.php`) | Add GET route for report page |

---

## T3.6 Build Order

```
Phase 1 — Controller
  ├── report() method in InvoiceCardController
  ├── Query: all cards filtered by month/store/technician
  ├── Compute cross_month flag, days_open, invoice_status per card
  └── Pass summary totals to view

Phase 2 — View
  ├── Filters bar (month picker, store, technician, status, invoice status)
  ├── Summary cards row
  ├── Tab 1: Completed cards table + CSV export
  └── Tab 2: Open cards table + CSV export

Phase 3 — CSV Export
  └── Export controller action (reuses same query, streams CSV)
```

---

## T3.7 All Decisions Confirmed

- [x] Report is **admin-only** — technicians do not see this page
- [x] Cross-month cards: full cost attributed to the **closing month** (`end_time` month). No proportional day-splitting.
- [x] No quick-action invoice button on this report — admin uses the existing invoice flow

---

*Task 3 scope fully locked.*
