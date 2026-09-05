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

$string['pluginname'] = 'PMI Application & CV Builder';
$string['modulename'] = 'PMI Application & CV Builder';
$string['modulenameplural'] = 'PMI Application & CV Builders';
$string['modulename_help'] = 'The PMI Application & CV Builder helps students assemble their certification application project experience, process it with AI via an n8n webhook, review the formatted application, and export a clean PDF.';
$string['pluginadministration'] = 'PMI Application & CV Builder administration';
$string['cv:addinstance'] = 'Add a new PMI Application & CV Builder activity';
$string['cv:view'] = 'View PMI Application & CV Builder';
$string['cv:submit'] = 'Submit data for AI processing';

// Admin settings.
$string['settings_n8n_heading'] = 'n8n AI Integration Settings';
$string['settings_n8n_heading_desc'] = 'Configure the default n8n webhook connection for processing student applications with AI.';
$string['default_webhook_url'] = 'Default n8n Webhook URL';
$string['default_webhook_url_desc'] = 'The default n8n webhook endpoint to send the student payload to.';
$string['default_auth_token'] = 'Bearer Auth Token';
$string['default_auth_token_desc'] = 'Optional Bearer token or secret to pass in the Authorization header to n8n.';

// Activity settings form.
$string['activity_settings'] = 'Exam & Certification Configuration';
$string['exam_type'] = 'Target PMI Certification';
$string['exam_type_help'] = 'Select the certification this course prepares the student for.';
$string['exam_type_desc'] = 'Select the certification this course prepares the student for.';
$string['exam_pmp'] = 'PMP® - Project Management Professional';
$string['exam_capm'] = 'CAPM® - Certified Associate in Project Management';
$string['exam_pmi_acp'] = 'PMI-ACP® - Agile Certified Practitioner';
$string['exam_pmi_rmp'] = 'PMI-RMP® - Risk Management Professional';
$string['exam_pmi_pba'] = 'PMI-PBA® - Professional in Business Analysis';
$string['exam_pgmp'] = 'PgMP® - Program Management Professional';
$string['exam_custom'] = 'General / Custom CV';
$string['contact_hours'] = 'Qualifying Contact Hours';
$string['contact_hours_help'] = 'Number of contact/training hours granted upon course completion (e.g., 35 for PMP, 21 for PMI-ACP).';
$string['provider_name'] = 'Training Provider Name';
$string['provider_name_help'] = 'The organization name appearing on the formal PMI application education record.';
$string['webhook_url_override'] = 'Webhook URL Override';
$string['webhook_url_override_help'] = 'Leave empty to use the site-wide default n8n webhook URL.';

// Student UI strings.
$string['header_badge'] = 'PMI Application Assistant';
$string['header_subtitle'] = 'Assemble your course learning outcomes and project experience into an official PMI application write-up.';
$string['step_personal_title'] = '1. Candidate Information';
$string['candidate_name'] = 'Full Name';
$string['candidate_email'] = 'Email';
$string['candidate_phone'] = 'Phone';
$string['candidate_country'] = 'Country';
$string['degree_level'] = 'Highest Level of Academic Education';
$string['degree_secondary'] = 'Secondary degree (high school diploma, associate’s degree)';
$string['degree_bachelors'] = 'Four-year degree (bachelor’s degree or global equivalent)';
$string['degree_postgrad'] = 'Postgraduate degree (master’s or doctorate)';

$string['step_course_title'] = '2. Qualifying Course Education';
$string['course_fullname'] = 'Course Title';
$string['course_contact_hours'] = 'Contact Hours';
$string['course_provider'] = 'Education Provider';

$string['step_projects_title'] = '3. Project Experience Entries';
$string['step_projects_desc'] = 'List the projects you have worked on. The AI will format your descriptions to match PMI standards.';
$string['btn_add_project'] = 'Add Another Project';
$string['btn_remove_project'] = 'Remove';
$string['project_number'] = 'Project #';
$string['project_title'] = 'Project Title';
$string['project_title_placeholder'] = 'e.g. ERP System Migration';
$string['project_role'] = 'Your Role on the Project';
$string['project_role_placeholder'] = 'e.g. Project Manager, Scrum Master';
$string['project_methodology'] = 'Primary Methodology';
$string['methodology_predictive'] = 'Predictive / Traditional (Waterfall)';
$string['methodology_agile'] = 'Agile';
$string['methodology_hybrid'] = 'Hybrid';
$string['project_start_date'] = 'Start Date (MM/YYYY)';
$string['project_end_date'] = 'End Date (MM/YYYY)';
$string['project_is_current'] = 'Project is currently ongoing';
$string['project_notes'] = 'Project Summary & Your Tasks';
$string['project_notes_placeholder'] = 'Describe the project objective, key deliverables, what you did, tools used, and the final outcome...';

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
