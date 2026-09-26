# v1.0.0 Release Checklist

## A — Scope & Governance

- [x] Release target = `v1.0.0`
- [x] Scope Freeze موثق.
- [x] Decision Log مراجع.
- [x] Risk Register مراجع.
- [x] Post-v1 items مفصولة عن Release Scope.
- [ ] جميع Change Requests المؤثرة محسومة نهائيًا.

## B — Implementation

- [x] B-05 merged.
- [x] F-05 merged.
- [x] Q-04 merged.
- [ ] UX-02 merged.
- [ ] BRAND-01 merged.
- [ ] لا يوجد Release-scoped implementation pending.

## C — Technical Verification

- [ ] Q-05 completed.
- [ ] Critical student flow PASS.
- [ ] Critical admin flow PASS.
- [ ] Security Regression PASS.
- [ ] Regression PASS.
- [ ] Defect Retest completed.
- [ ] Q-05 Final Evidence accepted.

## D — UAT / Content

- [ ] C-05 completed.
- [ ] Student UAT accepted.
- [ ] Admin UAT accepted where applicable.
- [ ] Content Audit completed.
- [ ] User Guide completed.
- [ ] FAQ completed.
- [ ] C-05 Final Evidence accepted.

## E — Defects

- [ ] Q-04 findings have final disposition.
- [ ] Seeder immutability defect resolved or formally dispositioned.
- [ ] No open Critical defect.
- [ ] No unresolved High defect without approved defer.
- [ ] Medium/Low deferred defects documented.

## F — Configuration

- [ ] `.env` not tracked.
- [ ] `.env.example` verified.
- [ ] Production debug guidance correct.
- [ ] Dependencies restored from lock files.
- [ ] Database bootstrap works.
- [ ] No secrets or credentials committed.

## G — Final Release Candidate

- [ ] Release-scoped merges complete.
- [ ] Code Freeze declared.
- [ ] Final RC SHA recorded.
- [ ] CI PASS on RC.
- [ ] Final Clean Clone executed on RC.
- [ ] `composer install` PASS.
- [ ] `php artisan key:generate` PASS.
- [ ] `php artisan migrate:fresh --seed` PASS.
- [ ] `npm ci` PASS.
- [ ] `npm run build` PASS.
- [ ] `php artisan test` PASS.
- [ ] Smoke Test PASS.

## H — Documentation

- [ ] README verified against final implementation.
- [ ] Release Baseline finalized.
- [ ] Traceability finalized.
- [ ] Known Issues finalized.
- [ ] Deferred Items finalized.
- [ ] Release Notes finalized.
- [ ] Decision Log final review complete.
- [ ] Risk Register final review complete.

## I — Release Gate

- [ ] Q-05 accepted.
- [ ] C-05 accepted.
- [ ] NFR Evidence reviewed.
- [ ] Security evidence reviewed.
- [ ] No blocker remains.
- [ ] Release Gate decision = GO.

## J — Release

- [ ] `v1.0.0` tag created on Final RC SHA.
- [ ] Tag SHA equals approved RC SHA.
- [ ] GitHub Release created if required.
- [ ] H-05 closed only after tag verification.
