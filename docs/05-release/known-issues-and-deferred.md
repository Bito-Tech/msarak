# v1.0.0 Known Issues and Deferred Items

## 1. Findings requiring final disposition

### Q4-C1 — Result JSON exposes `similarity_score`

- Type: Contract Deviation
- Severity: PENDING Q-05
- Status: Open for release disposition
- Release Blocker: TBD
- Decision: PENDING

### Q4-C2 — First completion status differs from contract

- Type: Contract Deviation
- Current behavior: `200`
- Contract: `201` first completion / `200` retry
- Severity: PENDING Q-05
- Release Blocker: TBD
- Decision: PENDING

### Q4-C3 — Result History missing summary fields

Missing:

- `top_domains`
- `recommendation_names`

- Type: Contract Deviation
- Severity: PENDING Q-05
- Release Blocker: TBD
- Decision: PENDING

### Q4-C4 — Result payload missing `top_domains`

- Type: Contract Deviation
- Severity: PENDING Q-05
- Release Blocker: TBD
- Decision: PENDING

### Concurrency verification

- Type: Verification Gap
- Status: NOT VERIFIED
- Confirmed Defect: No
- Decision: Pending Q-05 coverage/disposition

### Assessment Version Seeder immutability

- Type: Confirmed defect / release risk
- Status: Open / pending fix or formal disposition
- Release decision: PENDING
- Required: Fix + Retest, or documented release decision according to severity policy.

---

## 2. Release-scoped work still pending

- UX-02 — Logout UI.
- BRAND-01 — Official brand assets.
- Q-05 — Final Technical Verification.
- C-05 — Final UAT / Content Readiness.
- Any required Release Blocker fix.

---

## 3. Deferred after v1.0.0

### Authenticated password change

Changing password from inside an authenticated account is deferred as a new feature after `v1.0.0`.

### Modal / Escape UX enhancements

- Replace native `window.confirm` with project modal.
- Additional Escape/focus polish for mobile navigation.

Deferred unless Q-05/C-05 proves a related behavior to be a release-blocking defect.

### Post-v1 Technical Improvements

All items explicitly recorded in the Post-v1 Technical Improvements tracking issue remain outside `v1.0.0` unless separately approved through Change Management.

---

## Deferred defect requirements

Any actual defect deferred from `v1.0.0` must record:

- Severity
- Impact
- Reason for defer
- Owner
- Decision
- Target version
