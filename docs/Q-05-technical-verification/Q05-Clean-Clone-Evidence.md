# Q-05 — دليل التحقق من النسخة النظيفة (Clean Clone Verification Evidence)

**المشروع:** مسارك | **المهمة:** Q-05 (Issue #42) | **الإصدار:** 1.0
**المرجع:** Issue #42 § Clean Clone Verification
**الحالة:** ✅ ناجح — لا Release Blockers من هذا المسار

## 1. الهوية والبيئة

| البند | القيمة |
|---|---|
| Commit SHA المنفَّذ عليه | `04315888128d300fb43c675498331041aaf18a47` (فرع `main`، يطابق `origin/main` لحظة التنفيذ 2026-09-26) |
| نسخة الـ Release Candidate | تضم آخر دمج: "F-05 (دفعة 2) — واجهات إدارة إصدارات التقييم والأسئلة (#41) (#71)" |
| المستودع | `github.com/Bito-Tech/msarak` (تم نقله من BitSoft-IT/msarak؛ الرابط القديم يعيد التوجيه 301) |
| مسار النسخة النظيفة | `/tmp/q05-clean-clone-remote` — clone مباشر من GitHub بـ `git clone --branch main --single-branch` |
| نظام التشغيل | Linux WSL2 — kernel `6.6.114.1-microsoft-standard-WSL2` x64 |
| PHP | 8.5.4 (المتطلب `^8.2` ✓) |
| Composer | 2.9.5 |
| Node / npm | 24.21.0 / 11.19.0 |
| MySQL | 8.4.11 على 127.0.0.1:3306 |
| قواعد البيانات | التطبيق: `masarak` (منشأة مسبقًا) — الاختبارات: `masarak_test` (منشأة يدويًا قبل `php artisan test` لأن `phpunit.xml` يشير إليها) |
| المنفِّذ | مساعد عبدالله (المسار الأول: تنفيذ/اكتشاف/توثيق فقط) |

## 2. نتيجة كل خطوة

| # | الخطوة | الأمر | النتيجة | ملاحظات Evidence |
|---|---|---|---|---|
| 1 | Clone نظيف من `main` | `git clone --branch main --single-branch <remote>` | ✅ PASS | SHA بعد الـ clone = `0431588…`؛ لا وجود لـ `.env` ولا `vendor` ولا `node_modules` قبل التجهيز |
| 2 | تثبيت تبعيات PHP | `composer install` | ✅ PASS | 109/109 حزمة؛ `post-autoload-dump` و`package:discover` نجحت (5 packages DONE) |
| 3 | إعداد `.env` | `cp .env.example .env` | ✅ PASS | أُدخل `DB_PASSWORD` لبيئة MySQL المحلية فقط؛ القيم الأخرى افتراضية (`DB_DATABASE=masarak`) |
| 4 | توليد المفتاح | `php artisan key:generate` | ✅ PASS | "Application key set successfully" — `APP_KEY` من نوع base64 (لا يُسجل سره هنا) |
| 5 | بناء قاعدة البيانات + Seed | `php artisan migrate:fresh --seed` | ✅ PASS | 13 migration بنجاح (users → … → result_recommendations)؛ `AssessmentQuestionBankSeeder` اكتمل (254ms) — بنك 18 سؤالًا/72 خيارًا حسب Q-01 |
| 6 | تثبيت تبعيات الواجهة | `npm ci` | ✅ PASS | تفاعلت npm مع تحذير `install-scripts` حول esbuild postinstall (سلوك npm المحلي، لم يوقف التثبيت) |
| 7 | بناء الواجهة | `npm run build` | ✅ PASS (exit 0) | Vite: 61 module، `manifest.json` + `app-C-mOrrFJ.css` (92.21kB) + `app-Ct106IyZ.js` (73.17kB)، built in 851ms — مع 3 تحذيرات خطوط، مسجلة كـ OBS-01 أدناه |
| 8 | الاختبارات الآلية الكاملة | `php artisan test` | ✅ PASS | **350 اختبارًا ناجحًا / 2034 assertion / زمن 59.59s / 0 fail / 0 skip** على قاعدة `masarak_test` |
| 9 | تشغيل التطبيق | `php artisan serve --port=8123` | ✅ PASS | الخادم يعمل بلا أخطاء إقلاع |
| 10 | Smoke Test للرحلات الحرجة (HTTP) | `curl` | ✅ PASS | انظر الجدول 3 |

## 3. Smoke Test — الرحلات الحرجة (مستوى HTTP)

| المسار | Expected | Actual | الحكم |
|---|---|---|---|
| `/` | 200 صفحة الهبوط | 200 | PASS |
| `/login` | 200 نموذج دخول | 200 | PASS |
| `/register` | 200 نموذج تسجيل | 200 | PASS |
| `/assessment` | رفض للزائر وتوجيه للدخول | 302 → `/login` | PASS (حماية Session صحيحة) |
| `/profile` | رفض للزائر وتوجيه للدخول | 302 → `/login` | PASS |
| `/admin` (مسار مجموعة محمية بـ `auth`+`role:admin`، routes/web.php:50) | لا وصول للزائر | لا مسار GET على `/admin` نفسها (404)، والمسارات الفرعية تحت حارس `auth`+`role:admin` | PASS عند المستوى المسموح — التحقق الوظيفي الكامل لإدارة الإصدارات من نطاق TC-ADM لدى عبدالله (Stage B) |

> ملاحظة نطاق: Smoke Test هنا يغطي إقلاع التطبيق والمسارات العامة/المحمية على مستوى HTTP فقط. الرحلات الحرجة الكاملة (طالب/مدير بأدوار فعلية) تُنفَّذ في TC-STU/TC-ADM وفق الخطة.

## 4. ملاحظات غير حرجة (ليست فشلًا)

- **OBS-01 (Low/مراقبة):** `npm run build` يُصدر 3 تحذيرات: `/fonts/ibm-plex-sans-arabic-latin-{500,600,700}-normal.woff2 didn't resolve at build time, it will remain unchanged to be resolved at runtime`. السبب: `resources/css/fonts.css` يشير لملفات الخطوط بمسار مطلق `/fonts/...` وهي فعلًا موجودة في `public/fonts/` وتُحل وقت التشغيل. البناء نجح (exit 0) والبناء النهائي سليم. **لا يستوجب Issue إلا عند تأكيد فشل العرض البصري** — يُدرج ضمن فحوص F-05/Responsive لدى عبدالله.
- **OBS-02:** البيئة المحلية تتطلب `DB_PASSWORD` غير موجود في `.env.example` (يتضمن `DB_PASSWORD=` فارغًا) ووجود قاعدة `masarak` منشأة مسبقًا. هذا إعداد بيئة تطوير وليس عيب كود؛ يوصى بتوثيقه في README عند الحاجة فقط.

## 5. الحكم النهائي لهذا المسار

**Clean Clone Verification: ✅ PASS بالكامل** على SHA `04315888128d300fb43c675498331041aaf18a47`.

- معيار A-09 (Clean Clone) ✅ — معيار A-10 (`migrate:fresh --seed`) ✅ — معيار A-11 (`npm run build`) ✅ — معيار A-12 (`php artisan test`) ✅ من Release Verification Checklist.
- لا عيوب مسجلة (0 Issue) من هذا المسار حتى الآن؛ الملاحظات OBS-01/OBS-02 ليست Defects.
- **عند تحرك `origin/main` (دمج جديد لـ B-05/F-05) تُعاد هذه الخطوات كاملة** ويُضاف صف جديد بسجل SHA-تاريخ-نتيجة في الأسفل.

## 6. سجل التنفيذ المتكرر

| التاريخ (UTC) | SHA | نتيجة الخطوات 1–10 | ملاحظات |
|---|---|---|---|
| 2026-09-26 | `04315888128d300fb43c675498331041aaf18a47` | 10/10 PASS — 350 اختبار/2034 assertion/0 fail | OBS-01 تحذيرات خطوط غير حرجة |
