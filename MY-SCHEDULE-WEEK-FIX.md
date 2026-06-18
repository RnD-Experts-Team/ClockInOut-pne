# My-Schedule Week Range Bug — Investigation & Fix

**Date:** 2026-06-16
**Reporter:** kami@pneunited.com
**Page:** `/my-schedule` (user side)
**Severity:** High — users see the wrong week, so all weekly values (scheduled hours, actual hours, earnings, days worked, tasks) are computed for the wrong date range.

---

## 1. Symptom

On the user-side **My Schedule** page, the week navigation shows:

> **Jun 08 – Jun 14, 2026**

…but the company's real schedule week (the "other screen" the user compares against) is:

> **Jun 09 – Jun 15, 2026**

Because the displayed window is shifted **one day too early**, the data shown (hours, earnings, shifts, tasks) does not match the real schedule week. In particular, any shift/clocking that falls on the **last day of the real week (a Monday, e.g. Jun 15)** is excluded from the window, and the previous Monday (Jun 08) is wrongly pulled in.

---

## 2. Root Cause

`My Schedule` hard-codes the week to start on **Monday** and end on **Sunday**, while the rest of the operation treats the week as starting on **Tuesday** (Tuesday → Monday).

### Proof (computed with the real app clock, today = Tue Jun 16, 2026)

| Week start day | Current week | Previous week |
|----------------|--------------|---------------|
| **Monday** (current code) | Jun 15 – Jun 21 | **Jun 08 – Jun 14** ← *what the user sees* |
| **Tuesday** (desired)     | Jun 16 – Jun 22 | **Jun 09 – Jun 15** ← *what the user wants* |
| Sunday                    | Jun 14 – Jun 20 | Jun 07 – Jun 13 |

The reported pair (sees `Jun 08–14`, wants `Jun 09–15`) matches **exactly** the difference between a Monday-start week and a Tuesday-start week. So this is purely a **week-start-day mismatch**, not a timezone bug and not corrupted data.

### Things ruled out during investigation
- **Timezone corruption** — Ruled out. App timezone is `Canada/Eastern`; raw DB timestamps equal the Carbon-cast values (no double conversion).
- **Bad data in June** — Ruled out. There is currently no June schedule or clocking data in the DB (latest shifts Feb–Mar 2026, latest clockings Apr 2026). The bug is in the **window logic**, independent of data.
- **Display-only formatting bug** — Ruled out. The label is derived from the same `$startDate`/`$endDate` used for all DB queries, so fixing the window fixes both the label *and* the values together.

---

## 3. Where The Bug Lives

### Primary file — `app/Http/Controllers/UserScheduleController.php`

```php
// Lines 18-22 (current — WRONG)
$startDate = $request->input('start_date')
    ? Carbon::parse($request->input('start_date'))->startOfWeek(Carbon::MONDAY)
    : Carbon::now()->startOfWeek(Carbon::MONDAY);

$endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY);
```

This `$startDate`/`$endDate` window drives **every** query on the page:
- `scheduleShifts` — `whereBetween('date', [$startDate, $endDate])` (line 37)
- `taskAssignments` — `whereBetween('due_date', …)` (lines 50-53)
- `clockingRecords` — `whereBetween('clock_in', …)` (lines 60-63)
- the 7-day calendar loop (lines 107-117)
- the navigation label `$currentWeekStart` / `$currentWeekEnd` (blade line 21)

So the single Monday→Tuesday change fixes the label and all five data sets at once.

### Related (same Monday-start convention — review for consistency)
These also use Monday-start. If the whole product should move to a Tuesday-start week, they must change too, otherwise the **admin schedule** and **my-schedule** will disagree:

- `app/Http/Controllers/ScheduleController.php`
  - lines 37-38: `startOfWeek(Carbon::MONDAY)` (admin "create schedule" week grid)
  - line 40: `endOfWeek(Carbon::SUNDAY)`
  - lines 113, 329: `Carbon::parse($request->start_date)->startOfWeek()` (bare — stores `week_start_date`)
  - line 592, 598: bare `startOfWeek()` / `endOfWeek()`
- `resources/views/admin/schedules/show.blade.php` line 147: bare `startOfWeek()`
- Bare `startOfWeek()` / `endOfWeek()` (no argument, defaults to **Monday**) also appears in:
  `AttendanceController.php` (26-27), `MaintenanceRequestController.php` (110-111, 737-738, 876),
  `Admin/ScorecardController.php` (63-76, 148-152), `ReminderController.php` (499),
  `Models/DailyClockEvent.php` (61-62), `Models/Payment.php` (226-227),
  `Services/NotificationService.php` (232), `resources/views/attendance.blade.php` (23, 32).

> ⚠️ **Important:** "My Schedule" must use the **same week-start day as the screen that defines the schedule** (admin schedule). If you only change `UserScheduleController` to Tuesday but leave the admin schedule on Monday, the two screens will be one day apart again. Decide the convention once and apply it everywhere the schedule week is shown.

---

## 4. The Fix

### Option A — Minimal fix (just My Schedule)
Change the week-start day from Monday to **Tuesday** (and end-of-week from Sunday to **Monday**) in `UserScheduleController.php`:

```php
// Lines 18-22 (fixed)
$startDate = $request->input('start_date')
    ? Carbon::parse($request->input('start_date'))->startOfWeek(Carbon::TUESDAY)
    : Carbon::now()->startOfWeek(Carbon::TUESDAY);

$endDate = $startDate->copy()->endOfWeek(Carbon::MONDAY);
```

No blade changes are needed — the label and the calendar both read from `$startDate`/`$endDate`.

**Result for today (Tue Jun 16, 2026):** current week = `Jun 16 – Jun 22`; clicking *Previous week* → `Jun 09 – Jun 15` ✅.

### Option B — Make the week start configurable (recommended long-term)
Hard-coding any specific day repeats this bug the next time the business week changes. Introduce one source of truth, e.g. `config/schedule.php`:

```php
// config/schedule.php
return [
    // Carbon day constant: 0 = Sunday … 2 = Tuesday … 6 = Saturday
    'week_starts_on' => env('SCHEDULE_WEEK_START', Carbon::TUESDAY),
];
```

Then everywhere a schedule week is built:

```php
$weekStart = config('schedule.week_starts_on');
$weekEnd   = ($weekStart + 6) % 7;

$startDate = Carbon::parse($request->input('start_date', 'now'))->startOfWeek($weekStart);
$endDate   = $startDate->copy()->endOfWeek($weekEnd);
```

Apply this in `UserScheduleController` **and** `ScheduleController` (and the other spots listed in §3) so admin and user always agree.

### Option C — Most robust (drive the user view from the stored schedule)
Rather than rebuilding a week window from `now()`, anchor the user's week to the `Schedule.week_start_date` of the schedule they belong to. This makes "My Schedule" follow whatever week the admin actually created, regardless of which weekday that is — eliminating any future drift between the two screens. Larger change; consider after Option A/B stabilizes.

---

## 5. Recommendation

1. **Now:** Apply **Option A** to `UserScheduleController.php` so users immediately see `Jun 09 – Jun 15`. (One-line-per-line, low risk, no DB or blade changes.)
2. **Verify** the admin schedule screen (`ScheduleController`) uses the *same* start day; if not, align it (it currently defaults to Monday). Otherwise the two screens drift apart again.
3. **Soon:** Refactor to **Option B** (`config('schedule.week_starts_on')`) and replace every bare `startOfWeek()`/`startOfWeek(Carbon::MONDAY)` that represents the *schedule/pay week* with the config value, so this can never silently diverge again.

---

## 6. Test Checklist (after applying the fix)

- [ ] Open `/my-schedule` on Tue Jun 16, 2026 → header shows **Jun 16 – Jun 22, 2026**.
- [ ] Click **Previous week** → header shows **Jun 09 – Jun 15, 2026**.
- [ ] Confirm a shift dated **Mon Jun 15** appears inside the **Jun 09 – Jun 15** week (previously it was pushed into the wrong week).
- [ ] Confirm scheduled hours / actual hours / earnings / days worked / tasks all recompute for the Tue→Mon window.
- [ ] Confirm the admin schedule screen for the same week shows the **identical** date range.
- [ ] Spot-check DST boundaries (Mar/Nov) since the app runs in `Canada/Eastern`.
