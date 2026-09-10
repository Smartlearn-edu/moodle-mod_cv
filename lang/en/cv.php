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
 * Language strings for mod_cv.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Application & CV Builder';
$string['modulename'] = 'Application & CV Builder';
$string['modulenameplural'] = 'Application & CV Builders';
$string['modulename_help'] = 'The Application & CV Builder helps students assemble their qualifications, certifications, and project experiences, process them with AI via an n8n webhook, review the formatted write-ups, and export a clean PDF dossier.';
$string['pluginadministration'] = 'Application & CV Builder administration';
$string['cv:addinstance'] = 'Add a new Application & CV Builder activity';
$string['cv:view'] = 'View Application & CV Builder';
$string['cv:submit'] = 'Submit data for AI processing';

// Admin settings.
$string['settings_n8n_heading'] = 'n8n AI Integration Settings';
$string['settings_n8n_heading_desc'] = 'Configure the default n8n webhook connection for processing student applications with AI.';
$string['default_webhook_url'] = 'Default n8n Webhook URL';
$string['default_webhook_url_desc'] = 'The default n8n webhook endpoint to send the student payload to.';
$string['default_auth_token'] = 'Bearer Auth Token';
$string['default_auth_token_desc'] = 'Optional Bearer token or secret to pass in the Authorization header to n8n.';

// Activity settings form.
$string['activity_settings'] = 'Certification & Track Configuration';
$string['field_type'] = 'Professional Track / Domain';
$string['field_type_help'] = 'Select the professional field or industry track this activity is tailored for. Each domain provides tailored certification options and specialized pre-made AI prompts.';
$string['field_pmi'] = 'Project Management (PMI®)';
$string['field_medical'] = 'Healthcare & Medical';
$string['field_computer'] = 'Information Technology & Computing';
$string['field_languages'] = 'Languages & Translation';
$string['field_business'] = 'Business & Finance';
$string['field_general'] = 'General Professional / Other';

$string['exam_type'] = 'Target Certification / Track';
$string['exam_type_help'] = 'Select the target certification or professional track this course prepares the student for.';
$string['exam_type_desc'] = 'Select the target certification or professional track this course prepares the student for.';

// PMI Certifications.
$string['exam_pmp'] = 'PMP® - Project Management Professional';
$string['exam_capm'] = 'CAPM® - Certified Associate in Project Management';
$string['exam_pmi_acp'] = 'PMI-ACP® - Agile Certified Practitioner';
$string['exam_pmi_rmp'] = 'PMI-RMP® - Risk Management Professional';
$string['exam_pmi_pba'] = 'PMI-PBA® - Professional in Business Analysis';
$string['exam_pgmp'] = 'PgMP® - Program Management Professional';
$string['exam_pmi_custom'] = 'Custom PMI Track';

// Medical Certifications.
$string['exam_med_board'] = 'Medical Licensing / Specialty Board Certification';
$string['exam_med_rn'] = 'Registered Nurse (RN) / Clinical Nursing Practice';
$string['exam_med_bls_acls'] = 'BLS / ACLS / Resuscitation Specialist';
$string['exam_med_fellow'] = 'Clinical Fellowship / Sub-specialty Board';
$string['exam_med_bps'] = 'Board of Pharmacy Specialties (BPS)';
$string['exam_med_admin'] = 'Healthcare Quality & Administration (CPHQ)';
$string['exam_med_custom'] = 'Custom Medical Track';

// Computer / IT Certifications.
$string['exam_cs_aws'] = 'AWS Cloud Solutions Architect / DevOps';
$string['exam_cs_cisco'] = 'Cisco Certified Network Associate/Professional (CCNA/CCNP)';
$string['exam_cs_security'] = 'CompTIA Security+ / Cyber Security Specialist';
$string['exam_cs_kubernetes'] = 'Kubernetes & Cloud Native (CKA/CKAD)';
$string['exam_cs_data_ai'] = 'Data Science & AI / Machine Learning Engineer';
$string['exam_cs_fullstack'] = 'Full-Stack Software Engineering';
$string['exam_cs_custom'] = 'Custom IT Track';

// Language Certifications.
$string['exam_lang_ielts_toefl'] = 'IELTS / TOEFL English Proficiency Preparation';
$string['exam_lang_translator'] = 'Certified Legal / Technical Translator';
$string['exam_lang_cefr'] = 'CEFR C1 / C2 Advanced Language Proficiency';
$string['exam_lang_tefl'] = 'TEFL / TESOL English Teaching Certification';
$string['exam_lang_custom'] = 'Custom Language Track';

// Business Certifications.
$string['exam_biz_cfa'] = 'CFA® - Chartered Financial Analyst';
$string['exam_biz_cpa'] = 'CPA - Certified Public Accountant';
$string['exam_biz_shrm'] = 'SHRM-CP / SHRM-SCP (Human Resources)';
$string['exam_biz_sixsigma'] = 'Lean Six Sigma (Green / Black Belt)';
$string['exam_biz_cma'] = 'CMA - Certified Management Accountant';
$string['exam_biz_custom'] = 'Custom Business Track';

// General Certifications.
$string['exam_gen_cv'] = 'General Professional CV & Resume';
$string['exam_gen_portfolio'] = 'Career Experience & Project Portfolio';
$string['exam_gen_custom'] = 'Custom Track / Other';
$string['exam_custom'] = 'General / Custom CV';

$string['custom_cert_name'] = 'Custom Track / Certification Title';
$string['custom_cert_name_help'] = 'Optional. If you selected a custom option or wish to override the certification display name, enter it here.';

$string['contact_hours'] = 'Qualifying Contact Hours';
$string['contact_hours_help'] = 'Number of contact/training hours granted upon course completion (e.g., 35 for PMP, 21 for PMI-ACP).';
$string['provider_name'] = 'Training Provider Name';
$string['provider_name_help'] = 'The organization name appearing on the formal application education record.';
$string['webhook_url_override'] = 'Webhook URL Override';
$string['webhook_url_override_help'] = 'Leave empty to use the site-wide default n8n webhook URL.';
$string['custom_prompt'] = 'Custom AI Prompt (Optional)';
$string['custom_prompt_help'] = 'Optionally enter custom instructions or a system prompt for the AI tailored to this course. If left empty, the built-in pre-made prompt for the selected domain track will be used automatically.';
$string['default_prompt'] = 'Default System AI Prompt';
$string['default_prompt_desc'] = 'Default AI instructions to send to n8n if no custom prompt is set on the activity instance.';

// Student UI strings.
$string['header_badge'] = 'Application Assistant';
$string['header_subtitle'] = 'Assemble your qualifications, learning outcomes, and project experience into an official application dossier.';
$string['step_personal_title'] = '1. Candidate Information';
$string['candidate_name'] = 'Full Name';
$string['candidate_email'] = 'Email';
$string['candidate_phone'] = 'Phone';
$string['candidate_country'] = 'Country';
$string['degree_level'] = 'Highest Level of Academic Education';
$string['degree_secondary'] = 'Secondary degree (high school diploma, associate’s degree)';
$string['degree_bachelors'] = 'Four-year degree (bachelor’s degree or global equivalent)';
$string['degree_postgrad'] = 'Postgraduate degree (master’s or doctorate)';
$string['degree_institution'] = 'College / University / Institution Name';
$string['degree_institution_placeholder'] = 'e.g. Faculty of Engineering / Cairo University';
$string['degree_startdate'] = 'Degree Start Date';
$string['degree_enddate'] = 'Graduation Date (Degree End Date)';

$string['step_course_title'] = '2. Qualifying Course Education';
$string['select_course'] = 'Qualifying Course';
$string['current_course'] = 'Current Course';
$string['course_completed'] = 'Completed';
$string['course_fullname'] = 'Course Title';
$string['course_dates'] = 'Course Dates';
$string['course_startdate'] = 'Course Start Date';
$string['course_enddate'] = 'Course End Date';
$string['course_contact_hours'] = 'Contact Hours';
$string['course_provider'] = 'Education Provider';

$string['step_projects_title'] = '3. Project Experience Entries';
$string['step_projects_desc'] = 'List the projects you have worked on. The AI will format your descriptions to match PMI standards.';
$string['btn_add_project'] = 'Add Another Project';
$string['btn_remove_project'] = 'Remove';
$string['project_number'] = 'Project #';
$string['section_basic_info'] = 'Basic Project Information';
$string['section_timeline'] = 'Project Timeline';
$string['section_experience'] = 'Core Experience & Deliverables';
$string['section_governance'] = 'Stakeholders, Resources & Governance';
$string['field_project_name'] = 'Project Name';
$string['placeholder_project_name'] = 'Enter project name';
$string['field_industry'] = 'Industry';
$string['placeholder_industry'] = 'Ex.: Construction, IT, Healthcare';
$string['field_organization'] = 'Organization';
$string['placeholder_organization'] = 'Enter organization name';
$string['field_job_title'] = 'Job Title';
$string['placeholder_job_title'] = 'Ex.: Engineer, Manager, Analyst';
$string['field_project_role'] = 'Project Role';
$string['placeholder_project_role'] = 'Ex.: Project Manager, Project Lead, Coordinator';
$string['field_start_date'] = 'Project Start Date';
$string['placeholder_start_date'] = 'Enter start date';
$string['field_end_date'] = 'Project End Date';
$string['placeholder_end_date'] = 'Enter end date';
$string['project_is_current'] = 'Project is currently ongoing';
$string['field_methodology'] = 'Project Approach / Methodology';
$string['placeholder_methodology'] = 'Ex.: Predictive, Agile, Hybrid';
$string['methodology_predictive'] = 'Predictive (Waterfall)';
$string['methodology_agile'] = 'Agile';
$string['methodology_hybrid'] = 'Hybrid';
$string['field_objective'] = 'Project Objective';
$string['placeholder_objective'] = 'Ex.: Improve efficiency, launch product';
$string['field_scope'] = 'Project Scope';
$string['placeholder_scope'] = 'Ex.: Major work & boundaries';
$string['field_responsibilities'] = 'My Responsibilities';
$string['placeholder_responsibilities'] = 'Ex.: Plan, lead, manage, control';
$string['field_deliverables'] = 'Key Deliverables';
$string['placeholder_deliverables'] = 'Ex.: System, facility, product';
$string['field_stakeholders'] = 'Stakeholders Managed';
$string['placeholder_stakeholders'] = 'Ex.: Client, team, suppliers';
$string['field_team_resources'] = 'Team & Resources Managed';
$string['placeholder_team_resources'] = 'Ex.: Team, budget, equipment';
$string['field_challenges'] = 'Challenges / Risks / Issues Managed';
$string['placeholder_challenges'] = 'Ex.: Risks, delays, issues';
$string['field_changes'] = 'Changes Managed';
$string['placeholder_changes'] = 'Ex.: Scope, schedule, requirements';
$string['field_outcomes'] = 'Project Outcomes';
$string['placeholder_outcomes'] = 'Ex.: Cost savings, efficiency, satisfaction';
$string['field_measurable_results'] = 'Measurable Results';
$string['placeholder_measurable_results'] = 'Ex.: 20% cost reduction, 15% faster delivery';
$string['field_closure'] = 'Project Closure / Handover';
$string['placeholder_closure'] = 'Ex.: Acceptance, handover, closeout';
$string['field_additional_info'] = 'Additional Information';
$string['placeholder_additional_info'] = 'Ex.: Other relevant details';

$string['step_action_title'] = '4. Generate & Review';
$string['btn_generate_ai'] = 'Process with AI via n8n';
$string['generating_message'] = 'Sending your data to n8n and generating your PMI-compliant application... Please wait.';
$string['generation_error'] = 'Failed to generate AI application. Please verify the n8n webhook connection.';
$string['btn_download_pdf'] = 'Download Application PDF';
$string['btn_copy_field'] = 'Copy';
$string['copied_to_clipboard'] = 'Copied to clipboard!';
$string['preview_heading'] = 'AI-Formatted Application Review';
$string['summary_heading'] = 'Executive Summary';
$string['no_projects_added'] = 'Please add at least one project before processing.';
$string['status_saved'] = 'Draft saved successfully.';
$string['status_pending'] = 'Your application has been dispatched to n8n and is being processed in the background.';
$string['error_no_webhook'] = 'No n8n webhook URL is configured. Please configure it in the activity settings or site administration.';
$string['error_n8n_request'] = 'n8n communication error: {$a}';
$string['error_no_output_to_export'] = 'No processed application data available to export. Please generate with AI first.';

// Privacy strings.
$string['privacy:metadata:cv_submissions'] = 'Stores candidate project experience and AI-processed CV/application data.';
$string['privacy:metadata:cv_submissions:userid'] = 'The ID of the user submitting the CV or application.';
$string['privacy:metadata:cv_submissions:cvid'] = 'The ID of the activity module instance.';
$string['privacy:metadata:cv_submissions:raw_input'] = 'The raw candidate profile and project experience entered by the student.';
$string['privacy:metadata:cv_submissions:ai_output'] = 'The AI-enhanced structured application text received from n8n.';
$string['privacy:metadata:cv_submissions:timecreated'] = 'The timestamp when the submission was created.';
$string['privacy:metadata:cv_submissions:timemodified'] = 'The timestamp when the submission was last modified.';
$string['privacy:metadata:n8n'] = 'The plugin sends candidate details and project experience to an external n8n webhook for AI processing.';
