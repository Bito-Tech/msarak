# v1.0.0 Release Baseline

**Status:** PROVISIONAL — NOT RELEASE CANDIDATE  
**Owner:** H-05  
**Final RC SHA:** PENDING

## Current Integration Baseline

يُسجل أحدث `main` عند بداية Stage A كمرجع فقط.

هذا الـSHA لا يعتبر Release Candidate نهائيًا.

## Technical Baseline

- Laravel: 12.x
- PHP requirement: 8.2+
- Database: MySQL 8.4.x
- Frontend: Blade + Tailwind CSS
- Client logic: JavaScript / Fetch API
- Build: Vite
- Authentication: Laravel Session + Cookies + CSRF
- Assessment: v1.2
- Specializations Source of Truth:
  `resources/data/specializations.json`
- Dependency locks:
  - `composer.lock`
  - `package-lock.json`

## Release-scoped Pending Work

- UX-02
- BRAND-01
- Q-05 final verification and retests
- C-05 final UAT/content readiness
- Required Release Blocker fixes if discovered

## Final RC Fields

تملأ فقط بعد Code Freeze:

- Final RC SHA: `PENDING`
- PHP: `PENDING`
- Composer: `PENDING`
- Node.js: `PENDING`
- npm: `PENDING`
- MySQL: `PENDING`
- Laravel exact version: `PENDING`
- Tests: `PENDING`
- Assertions: `PENDING`
- Skipped: `PENDING`
- Build: `PENDING`
- Clean Clone: `PENDING`

## Freeze Rule

بعد تثبيت Final RC SHA لا يسمح بأي تغيير على الإصدار إلا لإصلاح Release Blocker.

أي تغيير على `main` بعد Final Verification يلغي اعتماد RC السابق ويتطلب إعادة الفحوص المتأثرة.
