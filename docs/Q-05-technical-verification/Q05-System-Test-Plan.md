# Q-05 — خطة الاختبار النظامي (System Test Plan)

**المشروع:** مسارك — منصة التوجيه الأكاديمي والمهني لطلاب المرحلة الثانوية في اليمن
**المهمة:** Q-05 — التحقق التقني الشامل وقبول الإصدار (Issue #42)
**الإصدار:** 1.0
**الحالة:** Stage A — مسودة جاهزة للتنفيذ في Stage B
**المرجع الأساسي:** وصف Issue #42 ومعايير قبوله
**المراجع الأساسي:** `Alhareith`
**Technical Review عند الحاجة:** `Malek711` (Backend / Statistics)، `ayman-albaidahi` (Frontend)

## 1. الغرض والنطاق

تحدد هذه الخطة طريقة تنفيذ التحقق التقني النهائي للنظام قبل الإصدار: System Testing وIntegration Testing وRegression Testing وSecurity Regression وClean Clone Verification، وإنتاج Evidence معتمدة تستهلكها H-05 في Release Gate.

**داخل النطاق:** التحقق المستقل على مستوى النظام والتكامل، تشغيل الاختبارات الآلية، تسجيل العيوب وإعادة اختبارها، توثيق Evidence.

**خارج النطاق:** تنفيذ UAT (C-05)، إصلاح كود الإنتاج بدل أصحاب الـIssues (B-05 للـBackend وF-05 للـAdmin UI)، إنشاء Release Tag (H-05)، تغيير Requirements أثناء الاختبار.

## 2. البيئات وأدوات التنفيذ

| البند | القيمة |
|---|---|
| نظام التشغيل | Linux (WSL2) — kernel 6.6.114.1-microsoft-standard |
| PHP | 8.5.4 (مطلوب: ^8.2) |
| Composer | 2.9.5 |
| Node / npm | v24.21.0 / 11.19.0 |
| قاعدة البيانات (تطبيق) | MySQL على 127.0.0.1:3306 — اسم `masarak` |
| قاعدة البيانات (اختبارات) | MySQL — اسم `masarak_test` وفق `phpunit.xml` |
| أوامر التشغيل | `php artisan test` — `npm ci && npm run build` — `php artisan migrate:fresh --seed` |
| قاعدة الكود | فرع `main` على `github.com/Bito-Tech/msarak` — Release Candidate commit يُسجل عند كل تنفيذ |

**قاعدة تسجيل النتائج:** كل تنفيذ يسجل Commit SHA المستخدم، والبيئة، وExpected/Actual، وحكم PASS/FAIL، والدليل (ناتج الأمر أو مسار الشاشة أو رقم الاختبار الفاشل).

## 3. رحلات النظام الحرجة (Test Basis)

### 3.1 رحلة الطالب — TC-STU
الخط المتتابع المطلوب التحقق منه:

`Register/Login → Assessment → Save → Resume → Complete → Scoring → Recommendation → Result → Profile → Result History → Historical Result`

| Case | السيناريو | Expected Result |
|---|---|---|
| TC-STU-01 | تسجيل حساب جديد ثم تسجيل الدخول | Session صالحة وRedirect صحيح للتدفق |
| TC-STU-02 | بدء Assessment على Version المنشورة الحالية (v1.2) | تحميل الأسئلة المنشورة فقط، لا Draft |
| TC-STU-03 | حفظ إجابة ثم الخروج والعودة (Resume) | الإجابات محفوظة والـ Session تُستكمل بلا تكرار أو فقد |
| TC-STU-04 | إكمال التقييم (Complete) مع تحقق الاكتمال | رفض الإكمال الناقص، وقبول المكتمل |
| TC-STU-05 | Scoring v1.2 ثم توليد Recommendation وResult | النتيجة مطابقة لمنطق v1.2 والمعتمدة في C-03/C-04 |
| TC-STU-06 | عرض Profile ثم Result History ثم Historical Result | النتائج التاريخية تُعرض كما حُسبت وقتها دون إعادة حساب |
| TC-STU-07 | تغيير معرفات Session/Result يدويًا (IDOR) | رفض — لا كشف بيانات لغير المالك |

### 3.2 رحلة المدير — TC-ADM

`Admin Login → Versions → Draft → Questions → Validation → Publish → Statistics`

| Case | السيناريو | Expected Result |
|---|---|---|
| TC-ADM-01 | تسجيل دخول مدير والوصول للوحة الإدارة | نجاح للمدير ورفض كامل لغير المدير (Server-side) |
| TC-ADM-02 | إنشاء Draft Version وتعديله | Draft قابل للتعديل ولا يظهر للطلاب |
| TC-ADM-03 | إضافة/تعديل أسئلة داخل Draft | الحفظ والتحقق من صحة البيانات يعملان |
| TC-ADM-04 | محاولة Publish لـ Draft غير صالح (تعارضات/بيانات ناقصة) | Publish مرفوض والنظام يبقى على Version السابقة — لا Publish جزئي |
| TC-ADM-05 | Publish لنسخة صالحة كاملة | نجاح ذري، وتصبح Version الحالية للجلسات الجديدة |
| TC-ADM-06 | تعديل Published Version بما يغير المعنى | مرفوض وفق قواعد B-05 |
| TC-ADM-07 | عرض Statistics بعد عمليات معروفة مسبقًا | الأرقام تطابق الحساب اليدوي، لا Double Counting |
| TC-ADM-08 | Empty State للـ Statistics عند عدم وجود بيانات | صفحة تعمل بلا أخطاء وتعرض الحالة الفارغة |

### 3.3 رحلات الأمان — TC-SEC

| Case | السيناريو | Expected Result |
|---|---|---|
| TC-SEC-01 | طالب يحاول الوصول إلى Admin routes/resources | 403/401 — الحماية Server-side لا عبر الواجهة فقط |
| TC-SEC-02 | مستخدم غير موثّق يحاول الوصول لمسارات محمية | رفض مع إعادة التوجيه لتسجيل الدخول |
| TC-SEC-03 | User A يستخدم Session/معرّفات User B | رفض — التحقق من الملكية في الـ Backend |
| TC-SEC-04 | User A يقرأ Result خاص بـ User B | رفض |
| TC-SEC-05 | تغيير IDs في الطلبات لكشف بيانات | لا تكشف بيانات لأجل المعرفات وحدها |
| TC-SEC-06 | إرسال `user_id`/هوية مالك من الـ Client في payload | يتجاهلها الـ Backend أو يرفضها — لا Mass Assignment |
| TC-SEC-07 | رسائل أخطاء 4xx/5xx والـ Validation | لا تكشف stack trace أو بيانات حساسة في الإنتاج |

## 4. اختبار Assessment Versions وPublish — TC-VER

مطابقًا لقائمة Issue #42 وتُنفَّذ عبر واجهة المدير وAPI معًا:

1. إنشاء Draft.
2. تعديل Draft.
3. رفض Draft غير صالح عند Publish.
4. نجاح Publish صالح.
5. لا يحدث Publish جزئي (فشل واحد = لا تغيير نهائي).
6. Published المستخدم لا يُعدَّل بما يغير المعنى.
7. Session قديمة تبقى على Version الأصلية (ثبات أثناء العمل).
8. Session جديدة تستخدم Version المنشورة الحالية.
9. Result تاريخية لا يُعاد حسابها بعد Publish جديد.

## 5. اختبار Statistics — TC-STAT

1. Metrics تطابق بيانات محسوبة يدويًا مسبقًا (Test Data مرجعي).
2. الأرقام تتغير بصورة صحيحة عند إضافة بيانات اختبار جديدة.
3. لا Double Counting (نفس Session/Result تُحتسب مرة واحدة).
4. لا تُعاد حساب أي Result لأجل Statistics.
5. لا تكشف بيانات طالب فردية غير لازمة.
6. Empty State عند عدم وجود بيانات.

## 6. الاختبارات الآلية — TC-AUTO

يجب أن تنجح كاملة على Release Candidate:

- Unit Tests (`tests/Unit`) — بما فيها ScoringService وRecommendationService.
- Feature Tests (`tests/Feature`) — Journey/Auth/Admin/Database.
- Integration Tests الموجودة (Q-03 Assessment Journey Tests).
- Security/Authorization Tests (بما فيها H-04 Profile/Result Security).
- Regression Suite كاملة عبر `php artisan test`.
- نجاح `npm run build` دون أخطاء أو تحذيرات حرجة.

## 7. Integration Testing — B-05 ↔ F-05 — TC-INT

- Backend Contracts (B-05) ↔ Admin UI (F-05): تناقض الطلب/الاستجابة، حقول مفقودة، أكواد أخطاء.
- Publish من الواجهة ينعكس فعلًا على الجلسات الجديدة (اختبار متكامل وليس على مستوى طبقة واحدة).
- Statistics المعروضة في F-05 مصدرها B-05 دون Business Logic في Frontend.
- Forbidden states وError states تظهر كما صُممت في F-05 عند رفض الـ Backend.

## 8. توزيع الأدوار (تقسيم العمل المتفق عليه)

| المسار | المسؤول | البنود |
|---|---|---|
| الاختبارات المعقدة (أمان/رحلات/إحصائيات) | عبدالله | TC-STU، TC-ADM، TC-SEC، TC-VER، TC-STAT، TC-INT |
| التحضير والتنفيذ الآلي والتوثيق | المساعد (هذه الوثيقة) | Stage A docs، TC-AUTO، Clean Clone، Defect Log، Evidence |

**قاعدة العمل:** الاستقلالية التامة — لا ينتظر أحد الآخر؛ والاكتفاء بالتسجيل والتوثيق عند الفشل مع فتح Issue فوري بالخطأ والـ log والبيئة، دون إصلاح كود.

## 9. Evidence المطلوبة لكل مجموعة

| مجموعة الاختبار | الدليل المقدم |
|---|---|
| TC-AUTO | ناتج `php artisan test` كامل (tests/assertions/time) + ناتج `npm run build` مع SHA |
| Clean Clone | مستند `Q05-Clean-Clone-Evidence.md` بنتيجة كل خطوة |
| TC-STU/TC-ADM | جدول حالة لكل Case + لقطات/سجلات للرحلات |
| TC-SEC | طلبات/استجابات فعلية (curl أو screenshots) تثبت الرفض |
| TC-VER/TC-STAT | بيانات مرجعية محسوبة يدويًا + مقارنة النتائج |
| العيوب | Issue برقم + Severity + خطوات إعادة الإنتاج + Expected/Actual |

## 10. Defect Lifecycle المعتمدة

لكل عيب: 1) تسجيل Issue. 2) خطوات إعادة الإنتاج. 3) Expected/Actual. 4) Severity. 5) تعيين المسؤول (B-05/F-05 حسب المجال). 6) الإصلاح بواسطة صاحب المجال. 7) Retest بواسطة Q-05. 8) Regression عند الحاجة. 9) إغلاق بعد نجاح التحقق.

**Severity:** `Critical` = تسريب بيانات أو تعطيل رحلة أساسية/الإصدار؛ `High` = خلل رئيسي في Security/Business Logic/Integration؛ `Medium` = خلل مؤثر مع workaround؛ `Low` = خلل محدود أو بصري. لا تقرر Q-05 تأجيل Critical/High منفردة؛ تسجلها وترفعها إلى Release Gate في H-05.

## 11. التوازي والمراحل

- **Stage A** (بالتوازي مع H-05 Stage A وB-05 وF-05 Stage A وC-05 Stage A): هذه الخطة + Regression Checklist + Release Verification Checklist + تجهيز Test Data وحالات Admin/Versions/Statistics/Clean Clone.
- **Stage B** (يبدأ تدريجيًا عند توفر B-05 وF-05 وبقية Release Candidate): البنود TC-STU وحتى TC-SEC وRegression وClean Clone والتسليم إلى H-05.
- **Final Verification:** يكتمل قبل H-05 Release Gate، بتسليم: حالة Technical Verification، حالة Regression، نتيجة Clean Clone، العيوب المفتوحة، نتيجة Security، Release Blockers، وروابط Evidence.

## 12. بوابة الخروج من Q-05

الإغلاق لا يتم إلا عند: اكتمال System/Integration/Regression Verification، نجاح Security Regression، نجاح Clean Clone، اكتمال Retest، عدم وجود Critical defect غير محلول، توثيق جميع Release Blockers، وتسليم Technical Verification Evidence إلى H-05.
