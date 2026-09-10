<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Arabic language strings for mod_cv.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'مساعد طلبات الاعتماد وبناء السيرة الذاتية';
$string['modulename'] = 'مساعد طلبات الاعتماد وبناء السيرة الذاتية';
$string['modulenameplural'] = 'أنشطة مساعد طلبات الاعتماد وبناء السيرة الذاتية';
$string['modulename_help'] = 'يساعد هذا النشاط الطلاب في تجميع مؤهلاتهم وخبرات مشاريعهم وصياغتها بالذكاء الاصطناعي عبر n8n ومراجعتها وتصديرها كملف PDF رسمي.';
$string['pluginadministration'] = 'إدارة مساعد طلبات الاعتماد وبناء السيرة الذاتية';
$string['cv:addinstance'] = 'إضافة نشاط جديد لمساعد طلبات الاعتماد';
$string['cv:view'] = 'معاينة نشاط مساعد طلبات الاعتماد';
$string['cv:submit'] = 'إرسال البيانات للمعالجة بالذكاء الاصطناعي';

// Admin settings.
$string['settings_n8n_heading'] = 'إعدادات التكامل مع n8n والذكاء الاصطناعي';
$string['settings_n8n_heading_desc'] = 'تهيئة رابط n8n Webhook الافتراضي لمعالجة طلبات الطلاب بالذكاء الاصطناعي.';
$string['default_webhook_url'] = 'رابط Webhook الافتراضي لـ n8n';
$string['default_webhook_url_desc'] = 'رابط webhook الافتراضي لإرسال بيانات الطالب إليه.';
$string['default_auth_token'] = 'رمز التحقق (Bearer Token)';
$string['default_auth_token_desc'] = 'رمز سري اختياري للتفويض يُرسل مع الطلب إلى n8n.';
$string['default_prompt'] = 'البرومبت الافتراضي للنظام';
$string['default_prompt_desc'] = 'التعليمات الافتراضية للذكاء الاصطناعي التي تُرسل إلى n8n في حال عدم تحديد برومبت مخصص داخل النشاط.';

// Activity settings form.
$string['activity_settings'] = 'إعدادات المسار المهني والشهادة';
$string['field_type'] = 'المجال / المسار المهني';
$string['field_type_help'] = 'اختر المجال أو القطاع المهني الذي يتبعه هذا النشاط. يوفر كل مجال خيارات شهادات مخصصة وبرومبت ذكاء اصطناعي متخصص مدمج تلقائياً.';
$string['field_pmi'] = 'إدارة المشاريع (PMI®)';
$string['field_medical'] = 'القطاع الصحي والطبي';
$string['field_computer'] = 'تقنية المعلومات وهندسة الحاسوب';
$string['field_languages'] = 'اللغات والترجمة';
$string['field_business'] = 'إدارة الأعمال والمالية';
$string['field_general'] = 'عام / مسار مهني مخصص';

$string['exam_type'] = 'الشهادة المستهدفة / المسار';
$string['exam_type_help'] = 'اختر الشهادة المهنية أو التخصص المستهدف الذي يؤهل هذا المقرر الطالب له.';
$string['exam_type_desc'] = 'اختر الشهادة المهنية أو التخصص المستهدف الذي يؤهل هذا المقرر الطالب له.';

// PMI Certifications.
$string['exam_pmp'] = 'PMP® - إدارة المشاريع الاحترافية';
$string['exam_capm'] = 'CAPM® - مساعد معتمد في إدارة المشاريع';
$string['exam_pmi_acp'] = 'PMI-ACP® - الممارس المعتمد لإدارة المشاريع الرشيقة';
$string['exam_pmi_rmp'] = 'PMI-RMP® - محترف إدارة المخاطر';
$string['exam_pmi_pba'] = 'PMI-PBA® - محترف تحليل الأعمال';
$string['exam_pgmp'] = 'PgMP® - محترف إدارة البرامج';
$string['exam_pmi_custom'] = 'مسار مخصص في إدارة المشاريع';

// Medical Certifications.
$string['exam_med_board'] = 'البورد الطبي / ترخيص الممارسة الطبية';
$string['exam_med_rn'] = 'التمريض الإكلينيكي المعتمد (RN)';
$string['exam_med_bls_acls'] = 'الإنعاش القلبي والرعاية الحرجة (BLS / ACLS)';
$string['exam_med_fellow'] = 'الزمالة الطبية السريرية والتخصص الدقيق';
$string['exam_med_bps'] = 'بورد التخصصات الصيدلانية (BPS)';
$string['exam_med_admin'] = 'إدارة الجودة والمنشآت الصحية (CPHQ)';
$string['exam_med_custom'] = 'مسار طبي / صحي مخصص';

// Computer / IT Certifications.
$string['exam_cs_aws'] = 'هندسة الحوسبة السحابية والديف أوبس (AWS / Cloud)';
$string['exam_cs_cisco'] = 'هندسة الشبكات المعتمدة (Cisco CCNA/CCNP)';
$string['exam_cs_security'] = 'الأمن السيبراني وحماية البيانات (Security+ / CEH)';
$string['exam_cs_kubernetes'] = 'الحاويات وهندسة كوبرنيتيس (CKA / CKAD)';
$string['exam_cs_data_ai'] = 'علوم البيانات والذكاء الاصطناعي (Data Science & AI)';
$string['exam_cs_fullstack'] = 'تطوير البرمجيات المتكاملة (Full-Stack Engineering)';
$string['exam_cs_custom'] = 'مسار تقني مخصص';

// Language Certifications.
$string['exam_lang_ielts_toefl'] = 'إتقان اللغة الإنجليزية (IELTS / TOEFL)';
$string['exam_lang_translator'] = 'الترجمة المهنية والقانونية المعتمدة';
$string['exam_lang_cefr'] = 'المستوى اللغوي المتقدم (CEFR C1 / C2)';
$string['exam_lang_tefl'] = 'تدريس اللغة الإنجليزية المعتمد (TEFL / TESOL)';
$string['exam_lang_custom'] = 'مسار لغات مخصص';

// Business Certifications.
$string['exam_biz_cfa'] = 'CFA® - محلل مالي معتمد';
$string['exam_biz_cpa'] = 'CPA - محاسب قانوني معتمد';
$string['exam_biz_shrm'] = 'إدارة الموارد البشرية الاحترافية (SHRM)';
$string['exam_biz_sixsigma'] = 'منهجية لين ستة سيجما (Lean Six Sigma)';
$string['exam_biz_cma'] = 'CMA - محاسب إداري معتمد';
$string['exam_biz_custom'] = 'مسار أعمال مخصص';

// General Certifications.
$string['exam_gen_cv'] = 'سيرة ذاتية مهنية شاملة';
$string['exam_gen_portfolio'] = 'سجل الخبرات والمحفظة المهنية';
$string['exam_gen_custom'] = 'مسار مهني مخصص آخر';
$string['exam_custom'] = 'سيرة ذاتية عامة / مخصصة';

$string['custom_cert_name'] = 'عنوان الشهادة أو المسار المخصص';
$string['custom_cert_name_help'] = 'اختياري. إذا اخترت مساراً مخصصاً أو أردت تخصيص اسم الشهادة الظاهرة للطالب، اكتبه هنا.';

$string['contact_hours'] = 'ساعات الاتصال المعتمدة';
$string['contact_hours_help'] = 'عدد الساعات التدريبية الممنوحة عند إتمام الدورة (مثلاً 35 ساعة لـ PMP).';
$string['provider_name'] = 'اسم الجهة التعليمية المزودة';
$string['provider_name_help'] = 'اسم المنظمة أو المعهد التعليمي الذي سيظهر في طلب الاعتماد الرسمي.';
$string['webhook_url_override'] = 'تجاوز رابط Webhook';
$string['webhook_url_override_help'] = 'اتركه فارغاً لاستخدام الرابط العام المحدد في إعدادات الموقع.';
$string['custom_prompt'] = 'البرومبت المخصص للنشاط (اختياري)';
$string['custom_prompt_help'] = 'يمكنك كتابة برومبت وتعليمات مخصصة للذكاء الاصطناعي لهذا الكورس. إذا تُرك فارغاً سيتم تطبيق البرومبت الجاهز المتخصص للمجال والشهادة المختارة تلقائياً.';

// Student UI strings.
$string['header_badge'] = 'مساعد طلبات الاعتماد';
$string['header_subtitle'] = 'قم بتجميع مؤهلاتك ومخرجات التعلم وخبرات مشاريعك لصياغة طلب رسمي وسيرة ذاتية باحترافية.';
$string['step_personal_title'] = '1. معلومات المرشح (Candidate Information)';
$string['candidate_name'] = 'الاسم الكامل';
$string['candidate_email'] = 'البريد الإلكتروني';
$string['candidate_phone'] = 'رقم الهاتف';
$string['candidate_country'] = 'الدولة';
$string['degree_level'] = 'أعلى مؤهل أكاديمي';
$string['degree_secondary'] = 'شهادة ثانوية (أو دبلوم متوسط)';
$string['degree_bachelors'] = 'شهادة جامعية 4 سنوات (بكالوريوس أو ما يعادلها)';
$string['degree_postgrad'] = 'دراسات عليا (ماجستير أو دكتوراه)';
$string['degree_institution'] = 'اسم الكلية / المعهد / الجامعة';
$string['degree_institution_placeholder'] = 'مثال: كلية الهندسة / جامعة القاهرة';
$string['degree_startdate'] = 'تاريخ بدء المؤهل';
$string['degree_enddate'] = 'تاريخ التخرج (انتهاء المؤهل)';

$string['step_course_title'] = '2. بيانات التعليم والدورة المؤهلة (Qualifying Course Education)';
$string['select_course'] = 'الدورة التدريبية المؤهلة';
$string['current_course'] = 'الدورة الحالية';
$string['course_completed'] = 'مكتملة';
$string['course_fullname'] = 'اسم الدورة التدريبية';
$string['course_dates'] = 'تواريخ الدورة التدريبية';
$string['course_startdate'] = 'تاريخ بدء الدورة';
$string['course_enddate'] = 'تاريخ انتهاء الدورة';
$string['course_contact_hours'] = 'ساعات الاتصال';
$string['course_provider'] = 'الجهة المزودة للتعليم';

$string['step_projects_title'] = '3. إدخال خبرات المشاريع (Project Experience Entries)';
$string['step_projects_desc'] = 'أدخل تفاصيل المشاريع التي شاركت بها. سيقوم الذكاء الاصطناعي بإعادة صياغتها وفق معايير PMI.';
$string['btn_add_project'] = 'إضافة مشروع آخر';
$string['btn_remove_project'] = 'حذف';
$string['project_number'] = 'مشروع #';

$string['section_basic_info'] = 'البيانات الأساسية للمشروع';
$string['section_timeline'] = 'الجدول الزمني للمشروع';
$string['section_experience'] = 'الخبرات والمسؤوليات والمخرجات الأساسية';
$string['section_governance'] = 'إدارة المعنيين والموارد والحوكمة';

$string['field_project_name'] = 'اسم المشروع';
$string['placeholder_project_name'] = 'Enter project name';
$string['field_industry'] = 'مجال العمل / القطاع';
$string['placeholder_industry'] = 'Ex.: Construction, IT, Healthcare';
$string['field_organization'] = 'اسم المنظمة / المؤسسة';
$string['placeholder_organization'] = 'Enter organization name';
$string['field_job_title'] = 'المسمى الوظيفي';
$string['placeholder_job_title'] = 'Ex.: Engineer, Manager, Analyst';
$string['field_project_role'] = 'دورك في المشروع';
$string['placeholder_project_role'] = 'Ex.: Project Manager, Project Lead, Coordinator';
$string['field_start_date'] = 'تاريخ بداية المشروع';
$string['placeholder_start_date'] = 'Enter start date';
$string['field_end_date'] = 'تاريخ نهاية المشروع';
$string['placeholder_end_date'] = 'Enter end date';
$string['project_is_current'] = 'المشروع مستمر حالياً (Project is ongoing)';
$string['field_methodology'] = 'منهجية إدارة المشروع';
$string['placeholder_methodology'] = 'Ex.: Predictive, Agile, Hybrid';
$string['methodology_predictive'] = 'تنبؤية / شلالية (Predictive / Waterfall)';
$string['methodology_agile'] = 'أجايل / رشيقة (Agile)';
$string['methodology_hybrid'] = 'هجينة (Hybrid)';

$string['field_objective'] = 'هدف المشروع';
$string['placeholder_objective'] = 'Ex.: Improve efficiency, launch product';
$string['field_scope'] = 'نطاق المشروع';
$string['placeholder_scope'] = 'Ex.: Major work & boundaries';
$string['field_responsibilities'] = 'مسؤولياتي في المشروع';
$string['placeholder_responsibilities'] = 'Ex.: Plan, lead, manage, control';
$string['field_deliverables'] = 'المخرجات والنتائج الرئيسية';
$string['placeholder_deliverables'] = 'Ex.: System, facility, product';
$string['field_stakeholders'] = 'إدارة المعنيين (Stakeholders Managed)';
$string['placeholder_stakeholders'] = 'Ex.: Client, team, suppliers';
$string['field_team_resources'] = 'إدارة الفريق والموارد (Team & Resources Managed)';
$string['placeholder_team_resources'] = 'Ex.: Team, budget, equipment';
$string['field_challenges'] = 'إدارة التحديات والمخاطر والمشاكل';
$string['placeholder_challenges'] = 'Ex.: Risks, delays, issues';
$string['field_changes'] = 'إدارة التغييرات (Changes Managed)';
$string['placeholder_changes'] = 'Ex.: Scope, schedule, requirements';
$string['field_outcomes'] = 'مخرجات ونتائج المشروع';
$string['placeholder_outcomes'] = 'Ex.: Cost savings, efficiency, satisfaction';
$string['field_measurable_results'] = 'نتائج قابلة للقياس (Measurable Results)';
$string['placeholder_measurable_results'] = 'Ex.: 20% cost reduction, 15% faster delivery';
$string['field_closure'] = 'إغلاق المشروع وتسليمه (Project Closure / Handover)';
$string['placeholder_closure'] = 'Ex.: Acceptance, handover, closeout';
$string['field_additional_info'] = 'معلومات إضافية (Additional Information)';
$string['placeholder_additional_info'] = 'Ex.: Other relevant details';

$string['step_action_title'] = '4. التوليد والمراجعة';
$string['btn_generate_ai'] = 'المعالجة بالذكاء الاصطناعي عبر n8n';
$string['generating_message'] = 'جارٍ إرسال البيانات إلى n8n وتوليد طلب PMI المتوافق... يرجى الانتظار.';
$string['generation_error'] = 'فشل في توليد الطلب. يرجى التحقق من اتصال n8n webhook.';
$string['btn_download_pdf'] = 'تحميل ملف PDF للطلب';
$string['btn_copy_field'] = 'نسخ';
$string['copied_to_clipboard'] = 'تم النسخ إلى الحافظة!';
$string['preview_heading'] = 'مراجعة الطلب المصاغ بالذكاء الاصطناعي';
$string['summary_heading'] = 'الملخص المهني';
$string['no_projects_added'] = 'يرجى إضافة مشروع واحد على الأقل قبل المتابعة.';
$string['status_saved'] = 'تم حفظ المسودة بنجاح.';
$string['status_pending'] = 'تم إرسال طلبك للمعالجة بالخلفية عبر n8n.';
$string['error_no_webhook'] = 'لم يتم تهيئة رابط n8n webhook. يرجى ضبطه في إعدادات النشاط أو إدارة الموقع.';
$string['error_n8n_request'] = 'خطأ في الاتصال مع n8n: {$a}';
$string['error_no_output_to_export'] = 'لا توجد بيانات جاهزة للتصدير حالياً. يرجى المعالجة أولاً.';

// Privacy strings.
$string['privacy:metadata:cv_submissions'] = 'يخزن خبرات المشاريع وبيانات السيرة الذاتية المعالجة بالذكاء الاصطناعي.';
$string['privacy:metadata:cv_submissions:userid'] = 'معرف المستخدم صاحب الطلب.';
$string['privacy:metadata:cv_submissions:cvid'] = 'معرف نشاط mod_cv.';
$string['privacy:metadata:cv_submissions:raw_input'] = 'البيانات المدخلة من الطالب وخبرات المشاريع.';
$string['privacy:metadata:cv_submissions:ai_output'] = 'النصوص المصاغة بالذكاء الاصطناعي المستلمة من n8n.';
$string['privacy:metadata:cv_submissions:timecreated'] = 'وقت إنشاء السجل.';
$string['privacy:metadata:cv_submissions:timemodified'] = 'وقت آخر تعديل.';
$string['privacy:metadata:n8n'] = 'يتم إرسال بيانات المشاريع إلى n8n webhook للمعالجة بالذكاء الاصطناعي.';
