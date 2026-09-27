# Q-05 — قائمة تحقق الإصدار (Release Verification Checklist)

**المشروع:** مسارك | **المهمة:** Q-05 (Issue #42) | **الإصدار:** 1.0
**الحالة:** Stage A — مسودة؛ تُملأ النتائج في Stage B وعلى Final Verification
**المرجع:** Issue #42 § معايير القبول / § التحقق قبل PR / § الربط مع H-05
**المستلم النهائي:** H-05 (Release Gate) — لا تعيد H-05 تنفيذ Q-05 بل تستهلك هذه المخرجات.

## 1. معايير قبول Q-05 (من Issue #42)

| # | المعيار | الحالة | الدليل / ملاحظة |
|---|---|---|---|
| A-01 | جميع الاختبارات الآلية ناجحة | ☐ | ناتج `php artisan test` + SHA |
| A-02 | الرحلات الحرجة ناجحة (طالب/مدير/زائر) | ☐ | سجل TC-STU/TC-ADM |
| A-03 | Admin Authorization ناجح | ☐ | سجل TC-SEC |
| A-04 | Assessment Versions وPublish مختبَران | ☐ | سجل TC-VER (9 بنود) |
| A-05 | Statistics تم التحقق منها | ☐ | سجل TC-STAT (6 بنود) |
| A-06 | Historical Results ثابتة | ☐ | TC-VER-07/09 + TC-STU-06 |
| A-07 | Security Regression ناجح | ☐ | قائمة S-01..S-08 |
| A-08 | Regression للحزم السابقة مكتمل | ☐ | قائمة R-01..R-17 |
| A-09 | Clean Clone ناجح | ☑ | `Q05-Clean-Clone-Evidence.md` — 10/10 خطوات PASS على SHA `0431588` (2026-09-26) |
| A-10 | `migrate:fresh --seed` ناجح | ☑ | 13 migration + AssessmentQuestionBankSeeder — ضمن Evidence خطوة 5 |
| A-11 | `npm run build` ناجح | ☑ | exit 0، تحذيرات خطوط OBS-01 غير حرجة — خطوة 7 |
| A-12 | `php artisan test` ناجح | ☑ | 350 اختبار / 2034 assertion / 0 فشل — خطوة 8 |
| A-13 | العيوب مسجلة ومصنفة | ☐ | Defect Log + Issues |
| A-14 | العيوب المصححة أعيد اختبارها | ☐ | Retest لكل عيب مغلق |
| A-15 | لا يوجد Critical defect غير محلول | ☐ | Defect Log |
| A-16 | Release Blockers موثقة بوضوح | ☐ | قسم 4 أدناه |
| A-17 | Technical Verification Evidence جاهزة لـ H-05 | ☐ | هذا المجلد كاملًا |

## 2. التحقق قبل PR / قبل التسليم إلى H-05

| # | خطوة | الحالة |
|---|---|---|
| V-01 | مراجعة Test Plan (Q05-System-Test-Plan.md) | ☐ |
| V-02 | مراجعة Regression Checklist (Q05-Regression-Checklist.md) | ☐ |
| V-03 | تشغيل Full Test Suite | ☐ |
| V-04 | تشغيل Security Regression | ☐ |
| V-05 | تشغيل Clean Clone Verification | ☐ |
| V-06 | مراجعة Bugs وRetest | ☐ |
| V-07 | ربط Evidence بالاختبارات | ☐ |
| V-08 | التأكد من عدم وجود Critical مفتوح | ☐ |
| V-09 | التأكد من نجاح CI | ☐ |

## 3. حزمة التسليم إلى H-05 (Final Verification Output)

تسلم Q-05 إلى H-05 العناصر الستة التالية مكتملة في هذا المجلد:

1. **Technical Verification status** — حالة كل مجموعة اختبار (TC-*) مع PASS/FAIL وSHA.
2. **Regression status** — R-01..R-17 وS-01..S-08 موقعة بتاريخ ونتيجة.
3. **Clean Clone result** — مستند Evidence خطوة-بخطوة.
4. **Open Defects** — جدول برقم Issue، Severity، الحالة، المسؤول.
5. **Security verification result** — نتائج TC-SEC + Security Regression.
6. **Release blockers** — أي Critical/High مفتوح أو بند قبول غير محقق، مرفوع بتوصية واضحة.

## 4. بوابة القرار

- لا توصي Q-05 بالإصدار ما لم تتحقق A-01..A-17 جميعًا.
- القرار النهائي (إصدار/تأجيل) من صلاحية H-05 في Release Gate؛ Q-05 لا تقرر تأجيل Critical/High منفردة بل توثّقها وترفعها.
- قرار تغيير Requirements أثناء الاختبار خارج نطاق Q-05 تمامًا.

## 5. سجل التنفيذ

| التاريخ | SHA | المنفذ | العناصر المنفذة | النتيجة |
|---|---|---|---|---|
| 2026-09-26 | `0431588` | مساعد Q-05 (مسار التنفيذ الآلي) | Clean Clone كامل + TC-AUTO + Smoke HTTP | PASS — انظر Q05-Clean-Clone-Evidence.md |
| _(تُملأ بقية العناصر أثناء Stage B)_ | | | | |
