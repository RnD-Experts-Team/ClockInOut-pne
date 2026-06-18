# Scorecard TOTAL vs My-Schedule "Weekly Earnings" — Why They Differ

**Date:** 2026-06-16
**Reported by:** kami@pneunited.com
**Example user:** Ashraf (`ashrafghanem093@gmail.com`, user id **8**, hourly pay **$18.00**)
**Week examined:** **Jun 09 – Jun 15, 2026** (Tuesday → Monday)

> ⚠️ **This document is investigation only — no code has been changed.** It explains exactly where the numbers come from and what we *would* change, so we can agree before touching anything.

---

## 1. Symptom

| Screen | Value shown | What it means |
|--------|------------:|---------------|
| **Admin → Scorecard** | **$843.94** (TOTAL column) | hourly pay **+** payments to company **+** fuel cost |
| **User → My Schedule** (Weekly Earnings card) | **$359.48** | hourly pay **only** |

The user expects My-Schedule to reflect the same total as the Scorecard, but it is missing **$484.46**.

---

## 2. The numbers are 100% reconciled

I recomputed both screens from the database for the **same week (Jun 09–15)** for Ashraf:

```
SCORECARD:   hours=19.97  hourlyPay=359.48  payments=391.96  fuel=92.50  TOTAL=843.94
MY-SCHEDULE: actualHours=19:58 (=19.97h)  daysWorked=3       weeklyEarnings=359.48
```

The three clockings in that week:

| Clocking | Date | Hours | Miles | Fuel (miles×$0.50) | Purchase (→ "payments to company") |
|---|---|---:|---:|---:|---:|
| #523 | Jun 10 | 2.33 | 8 | $4.00 | $64.42 |
| #525 | Jun 11→12 | 9.60 | 72 | $36.00 | $327.54 |
| #526 | Jun 12→13 | 8.04 | 105 | $52.50 | — |
| **Total** | | **19.97** | **185** | **$92.50** | **$391.96** |

- Hourly pay = 19.97 h × $18.00 = **$359.48**
- Scorecard TOTAL = 359.48 + 391.96 + 92.50 = **$843.94**
- The gap = payments to company (391.96) + fuel cost (92.50) = **$484.46**

**Conclusion: there is no data error and no "lost" money.** The two screens are simply *defined to compute different things*. My-Schedule deliberately shows only the hourly-pay portion.

---

## 3. Where each number is produced

### A) Scorecard TOTAL — `app/Http/Controllers/Admin/ScorecardController.php`

`calculateUserScorecard()`, lines ~104-125:

```php
foreach ($clockings as $clocking) {
    if ($clocking->clock_in && $clocking->clock_out) {
        $hoursWorked = $clockIn->diffInHours($clockOut, false);   // hours
        $totalHours  += $hoursWorked;
        $miles        = $clocking->miles_out - $clocking->miles_in;
        $totalFuelCost += $miles * $gasPaymentRate;               // FUEL
    }
    $totalPaymentsToCompany += $clocking->purchase_cost;          // PAYMENTS  (note: outside the if)
}

$totalHourlyPay = $totalHours * $hourlyRate;                      // HOURLY PAY
$total = $totalHourlyPay + $totalPaymentsToCompany + $totalFuelCost;   // <-- the $843.94
```

So **TOTAL = hourly pay + payments-to-company + fuel cost**.

### B) My-Schedule "Weekly Earnings" — `app/Http/Controllers/UserScheduleController.php`

Lines ~72-90:

```php
foreach ($clockingRecords as $clocking) {
    if ($clocking->clock_in && $clocking->clock_out) {
        $diffInSeconds = $clockOut->timestamp - $clockIn->timestamp;
        if ($diffInSeconds > 0) {
            $actualWorkedSeconds += $diffInSeconds;
            $daysWorked++;
            if ($user->hourly_pay) {
                $hoursDecimal   = $diffInSeconds / 3600;
                $weeklyEarnings += ($hoursDecimal * $user->hourly_pay);  // <-- ONLY hourly pay
            }
        }
    }
}
```

So **Weekly Earnings = hours × hourly pay only.** It never reads `purchase_cost` or the mileage/fuel values.

---

## 4. What each money component actually means

| Component | DB source | Meaning |
|---|---|---|
| **Hourly pay** | `hours × users.hourly_pay` | What the worker earns for time worked. |
| **Fuel cost** | `(miles_out − miles_in) × Configuration gas rate (0.50)` | Mileage/gas reimbursement for the trip. |
| **Payments made to company** | `clockings.purchase_cost` | Money associated with purchases/collections recorded on the shift. |

> ❗ **Business decision needed before coding.** The Scorecard simply *adds* all three into one `TOTAL`. Whether the user's "Weekly Earnings" should also be the **sum of all three** is a business question:
> - If "Weekly Earnings" means *take-home for the week*, then "Payments made **to** company" is money flowing **to the company**, not to the worker — adding it as the worker's earnings may be wrong.
> - If the card is meant to mirror the Scorecard's "TOTAL settlement figure", then it should add all three to match $843.94.
>
> **We must confirm which meaning is intended.** The fix differs depending on the answer.

---

## 5. Other discrepancies found (so the totals don't drift even after we align components)

Even once we decide which components to include, these subtle differences can still make the two screens disagree by a few cents/hours. Document/decide each:

1. **Hours method differs.**
   - Scorecard: `$clockIn->diffInHours($clockOut, false)`
   - My-Schedule: `($clockOut->timestamp - $clockIn->timestamp) / 3600`
   - They are *approximately* equal but not guaranteed identical across DST. Pick one method and reuse it.

2. **`clock_out IS NULL` handling differs.**
   - Scorecard adds `purchase_cost` **even when the shift has no `clock_out`** (the `$totalPaymentsToCompany +=` line is *outside* the `if`).
   - My-Schedule **excludes** open shifts entirely (`->whereNotNull('clock_out')`, line 64).
   - Result: an in-progress shift's purchase is counted by the Scorecard but invisible to My-Schedule.

3. **Date window differs.**
   - My-Schedule: fixed **Tue→Mon** week (just fixed in `MY-SCHEDULE-WEEK-FIX.md`).
   - Scorecard: user-selected filter (All Time / this_week / this_month / custom). Its `this_week`/`last_week` use bare `startOfWeek()` = **Monday**, so the Scorecard's *quick filters* are still Monday-based and will **not** line up with My-Schedule's Tue→Mon week. For the screenshot the admin used a custom/explicit range, which is why they matched here.

4. **`purchase_cost` may be NULL.** Several clockings have empty `purchase_cost`. Summing NULL is treated as 0 in PHP, but confirm the column default/cast to avoid surprises.

5. **Minor bug (pre-existing):** `UserScheduleController.php` line ~102 throws a deprecation — `floor((($actualWorkedSeconds / $daysWorked) % 3600) / 60)` does `%` on a float. Should cast to int: `(int)($actualWorkedSeconds / $daysWorked) % 3600`. Not causing the $ discrepancy, but worth fixing while we're here.

---

## 6. Options to fix (pick after the §4 business decision)

### Option 1 — Make "Weekly Earnings" mirror the Scorecard TOTAL
Add fuel + payments to the weekly calculation in `UserScheduleController.php`. Also relax the `whereNotNull('clock_out')` rule (or keep it) to match the Scorecard's handling, and switch the hours method to `diffInHours` so the two screens use identical math.

*Files to change:* `app/Http/Controllers/UserScheduleController.php` (calculation) and `resources/views/user/schedule/index.blade.php` (the card already exists; optionally add separate Fuel / Payments cards so the user sees the breakdown instead of one lump sum).

### Option 2 — Keep "Weekly Earnings" = hourly pay, but ADD separate cards
Leave `weeklyEarnings` as hourly pay, and add **two new cards** ("Fuel Reimbursement", "Payments to Company") plus a **"Week Total"** card that sums all three — so the user sees the same $843.94 *and* understands the breakdown. This is clearer and avoids mislabeling company money as personal earnings.
*Files to change:* `UserScheduleController.php` (compute `weeklyFuelCost`, `weeklyPayments`, `weeklyTotal`), `resources/views/user/schedule/index.blade.php` (add cards), and the three `resources/lang/{en,es,ar}/messages.php` files (new labels).

### Option 3 — Centralize the formula (recommended long-term)
Extract one shared method (e.g. `ClockingTotals::forUser($user, $start, $end)`) returning `{hours, hourlyPay, fuel, payments, total}`, and have **both** the Scorecard and My-Schedule call it. This guarantees they can never drift again and fixes items 1–4 in §5 in one place.

---

## 7. Recommendation

1. **Confirm the business meaning** (§4): should the user's card show *take-home pay* or the *full settlement total*? → My recommendation: **Option 2** (keep hourly pay labeled as earnings, add a clear "Week Total" + breakdown cards). It matches the $843.94 the admin sees without mislabeling company money as personal income.
2. Then, to prevent future drift, refactor toward **Option 3** (one shared calculator) and align the hours method + `clock_out` handling + week-start day (Scorecard quick filters are still Monday-based — see §5.3).
3. Fix the line ~102 deprecation while editing the file.

---

## 8. Files that would change (summary)

| File | Why |
|---|---|
| `app/Http/Controllers/UserScheduleController.php` | Add fuel + payments (+ total) to the weekly calc; align hours method & clock_out handling; fix line ~102 deprecation |
| `resources/views/user/schedule/index.blade.php` | Add breakdown / total card(s) |
| `resources/lang/en/messages.php`, `es/messages.php`, `ar/messages.php` | New card labels |
| `app/Http/Controllers/Admin/ScorecardController.php` | (Option 3 only) call the shared calculator; optionally align quick-filter week start to Tuesday |
| *(new)* shared totals helper/service | (Option 3 only) single source of truth |

---

## 9. Verification checklist (after the fix)

- [ ] For Ashraf, week Jun 09–15: My-Schedule total = **$843.94** (or the agreed figure) and matches the Scorecard for the same explicit range.
- [ ] Hourly pay still reads **$359.48**; fuel **$92.50**; payments **$391.96**.
- [ ] An in-progress shift (no `clock_out`) is handled the same way on both screens.
- [ ] Scorecard "This Week"/"Last Week" quick filters use the same Tue→Mon boundary as My-Schedule (if we decide to align them).
- [ ] No deprecation warning from `UserScheduleController` line ~102.
- [ ] Spot-check a user with NULL `purchase_cost` and zero miles.
