# Q-05 — قائمة فحص الانحدار (Regression Checklist)

**المشروع:** مسارك | **المهمة:** Q-05 (Issue #42) | **الإصدار:** 1.0
**الحالة:** Stage A — مسودة للتنفيذ في Stage B على Release Candidate
**المرجع:** Issue #42 § Regression / § Security Regression
**النتيجة المسجلة لكل بند:** PASS / FAIL / N-A + Commit SHA + تاريخ التنفيذ + دليل عند الفشل.

> تُنفَّذ بعد كل إصلاح مؤثر وبعد استقرار الحزمة، وتشمل الوظائف السابقة من الحزم 1–4 كما حددها Issue #42.

## 1. Regression للحزم السابقة (وظائف رئيسية)

| # | البند | المصدر | Expected |
|---|---|---|---|
| R-01 | Registration / Login / Logout | حزمة التأسيس | تدفقات تعمل، Session تُنشأ وتُنهي صحيحًا |
| R-02 | Password flow إن كان منفصلًا | الحزم السابقة | يعمل إن كان منفذًا؛ يسجل N-A وإلا |
| R-03 | Roles / Authorization | B-*/F-* | أدوار Student/Admin مطبقة Server-side |
| R-04 | Specializations guide | حزمة التوجيه | محتوى/مسارات التخصصات تعمل وتعرض صحيحًا |
| R-05 | Assessment Version reading | B-05 | قراءة النسخة المنشورة الحالية فقط للطالب |
| R-06 | Start / Resume Session | Q-03 Journeys | بدء واستكمال بلا فقد أو تكرار بيانات |
| R-07 | Save / Update Answers | Q-03 Journeys | حفظ/تحديث الإجابات يعملان مع التحقق |
| R-08 | Completion validation | Q-03 Journeys | لا يُقبل إكمال ناقص؛ يقبل المكتمل |
| R-09 | Scoring v1.2 | Unit: ScoringService | نتيجة مطابقة لقواعد v1.2 المعتمدة |
| R-10 | Recommendations | Unit: RecommendationService | توصيات مطابقة للمدخلات المرجعية |
| R-11 | Result | F-04 (Issue #36/#61) | صفحة النتيجة والتوصيات تعرض صحيحًا |
| R-12 | Profile | H-04 | بيانات الملف الشخصي وقابلية التحديث |
| R-13 | Result History | H-04/B-* | سجل النتائج التاريخية كامل ومرتب |
| R-14 | Ownership / IDOR | H-04 Security | لا وصول لبيانات مستخدم آخر (انظر S-03..S-05) |
| R-15 | Responsive critical flows | F-05 | الرحلات الحرجة تعمل على الشاشات المحمولة |
| R-16 | Assessment Journey Tests (Q-03) | `php artisan test` | مجموعة الاختبارات الآلية للرحلات خضراء |
| R-17 | Database Integrity Tests (Q-02) | `php artisan test` | اختبارات سلامة قاعدة البيانات خضراء |

## 2. Security Regression

| # | البند | Expected |
|---|---|---|
| S-01 | Admin routes محمية Server-side | رفض مباشر عبر HTTP وليس فقط إخفاء بالواجهة |
| S-02 | Student لا يصل إلى Admin resources | 403/401 على نقاط الإدارة كلها |
| S-03 | Session ownership ما زالت صحيحة | لا استخدام لجلسة مستخدم آخر |
| S-04 | Answer ownership ما زالت صحيحة | لا تعديل إجابات تخص غير المالك |
| S-05 | Result ownership ما زالت صحيحة | لا قراءة نتائج تخص غير المالك |
| S-06 | لا Mass Assignment غير مقصود في المسارات الجديدة | الحقول المحمية لا تُملأ من payload |
| S-07 | لا يقبل Backend هوية المالك من Client | المعرف المشتق من المصادقة وحده |
| S-08 | الأخطاء لا تكشف بيانات حساسة | لا stack trace/تسريب في رسائل الخطأ |

## 3. شروط تشغيل القائمة

1. تُشغَّل كاملة بعد كل دفعة إصلاح مؤثرة (B-05/F-05 fixes).
2. أي FAIL → تسجيل Issue فوري وفق Defect Lifecycle في Issue #42 مع Expected/Actual وSeverity والبيئة والـ SHA.
3. لا يُغلق بند FAIL قبل Retest ناجح من Q-05 وتشغيل Regression عند الحاجة.
4. Critical/High غير المحلول = Release Blocker يُرفع إلى H-05.
