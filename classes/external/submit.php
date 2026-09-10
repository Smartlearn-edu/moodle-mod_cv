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

namespace mod_cv\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use context_module;
use moodle_exception;

/**
 * External web service for submitting candidate data to n8n and receiving AI output.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submit extends external_api {
    /**
     * Define parameters for submit web service.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'profile' => new external_single_structure([
                'name' => new external_value(PARAM_TEXT, 'Candidate full name'),
                'email' => new external_value(PARAM_EMAIL, 'Candidate email'),
                'phone' => new external_value(PARAM_TEXT, 'Candidate phone', VALUE_DEFAULT, ''),
                'country' => new external_value(PARAM_TEXT, 'Candidate country', VALUE_DEFAULT, ''),
                'degree' => new external_value(PARAM_ALPHAEXT, 'Education degree level', VALUE_DEFAULT, 'bachelors'),
                'institution' => new external_value(PARAM_TEXT, 'College/University/Institution name', VALUE_DEFAULT, ''),
                'degree_startdate' => new external_value(PARAM_TEXT, 'Degree start date', VALUE_DEFAULT, ''),
                'degree_enddate' => new external_value(PARAM_TEXT, 'Degree end/graduation date', VALUE_DEFAULT, ''),
            ]),
            'course_info' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Course ID', VALUE_DEFAULT, 0),
                'name' => new external_value(PARAM_TEXT, 'Course title/fullname', VALUE_DEFAULT, ''),
                'startdate' => new external_value(PARAM_TEXT, 'Course start date', VALUE_DEFAULT, ''),
                'enddate' => new external_value(PARAM_TEXT, 'Course end/completion date', VALUE_DEFAULT, ''),
            ], 'Course information', VALUE_DEFAULT, []),
            'projects' => new external_multiple_structure(
                new external_single_structure([
                    'title' => new external_value(PARAM_TEXT, 'Project title/name'),
                    'industry' => new external_value(PARAM_TEXT, 'Project industry', VALUE_DEFAULT, ''),
                    'organization' => new external_value(PARAM_TEXT, 'Organization name', VALUE_DEFAULT, ''),
                    'jobtitle' => new external_value(PARAM_TEXT, 'Job title', VALUE_DEFAULT, ''),
                    'role' => new external_value(PARAM_TEXT, 'Role on project', VALUE_DEFAULT, ''),
                    'startdate' => new external_value(PARAM_TEXT, 'Start date (Day/Month/Year or YYYY-MM-DD)', VALUE_DEFAULT, ''),
                    'enddate' => new external_value(PARAM_TEXT, 'End date (Day/Month/Year or YYYY-MM-DD)', VALUE_DEFAULT, ''),
                    'iscurrent' => new external_value(PARAM_BOOL, 'Whether project is ongoing', VALUE_DEFAULT, false),
                    'methodology' => new external_value(
                        PARAM_TEXT,
                        'Methodology (predictive, agile, hybrid)',
                        VALUE_DEFAULT,
                        'predictive'
                    ),
                    'objective' => new external_value(PARAM_RAW, 'Project objective', VALUE_DEFAULT, ''),
                    'scope' => new external_value(PARAM_RAW, 'Project scope', VALUE_DEFAULT, ''),
                    'responsibilities' => new external_value(PARAM_RAW, 'My responsibilities', VALUE_DEFAULT, ''),
                    'deliverables' => new external_value(PARAM_RAW, 'Key deliverables', VALUE_DEFAULT, ''),
                    'stakeholders' => new external_value(PARAM_RAW, 'Stakeholders managed', VALUE_DEFAULT, ''),
                    'teamresources' => new external_value(PARAM_RAW, 'Team and resources managed', VALUE_DEFAULT, ''),
                    'challenges' => new external_value(PARAM_RAW, 'Challenges, risks, issues managed', VALUE_DEFAULT, ''),
                    'changes' => new external_value(PARAM_RAW, 'Changes managed', VALUE_DEFAULT, ''),
                    'outcomes' => new external_value(PARAM_RAW, 'Project outcomes', VALUE_DEFAULT, ''),
                    'measurableresults' => new external_value(PARAM_RAW, 'Measurable results', VALUE_DEFAULT, ''),
                    'closure' => new external_value(PARAM_RAW, 'Project closure / handover', VALUE_DEFAULT, ''),
                    'additionalinfo' => new external_value(PARAM_RAW, 'Additional information', VALUE_DEFAULT, ''),
                    'notes' => new external_value(PARAM_RAW, 'Legacy tasks and notes', VALUE_DEFAULT, ''),
                ])
            ),
        ]);
    }

    /**
     * Process data, call n8n webhook, save submission, and return results.
     *
     * @param int $cmid
     * @param array $profile
     * @param array $projects
     * @param array $course_info
     * @return array
     */
    public static function execute(int $cmid, array $profile, array $projects, array $course_info = []): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'profile' => $profile,
            'projects' => $projects,
            'course_info' => $course_info,
        ]);

        $cm = get_coursemodule_from_id('cv', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/cv:submit', $context);

        $cv = $DB->get_record('cv', ['id' => $cm->instance], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

        // Determine webhook URL.
        $webhookurl = !empty($cv->webhookurl) ? $cv->webhookurl : get_config('mod_cv', 'default_webhook_url');
        if (empty($webhookurl)) {
            throw new moodle_exception('error_no_webhook', 'mod_cv', '', null, 'No n8n webhook URL configured.');
        }

        $authtoken = get_config('mod_cv', 'default_auth_token');

        $now = time();

        $coursedata = !empty($params['course_info']['name']) ? $params['course_info'] : [
            'id' => (int) $course->id,
            'name' => $course->fullname,
            'startdate' => '',
            'enddate' => '',
        ];

        $rawjson = json_encode([
            'profile' => $params['profile'],
            'course' => $coursedata,
            'projects' => $params['projects'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $submission = $DB->get_record('cv_submissions', ['cvid' => $cv->id, 'userid' => $USER->id]);
        if ($submission) {
            $submission->raw_input = $rawjson;
            $submission->status = 'pending';
            $submission->timemodified = $now;
            $DB->update_record('cv_submissions', $submission);
        } else {
            $submission = new \stdClass();
            $submission->cvid = $cv->id;
            $submission->userid = $USER->id;
            $submission->raw_input = $rawjson;
            $submission->ai_output = '';
            $submission->status = 'pending';
            $submission->timecreated = $now;
            $submission->timemodified = $now;
            $submission->id = $DB->insert_record('cv_submissions', $submission);
        }

        $callbackurl = (new \moodle_url('/mod/cv/callback.php'))->out(false);

        $fieldtype = !empty($cv->fieldtype) ? $cv->fieldtype : \mod_cv\domains::DOMAIN_PMI;
        $customcert = !empty($cv->customcert) ? $cv->customcert : '';
        $domainprompt = \mod_cv\domains::get_default_prompt($fieldtype, $cv->examtype);
        $defaultprompt = get_config('mod_cv', 'default_prompt');

        if (!empty($cv->customprompt)) {
            $prompt = $cv->customprompt;
        } else if (!empty($defaultprompt)) {
            $prompt = $defaultprompt;
        } else {
            $prompt = $domainprompt;
        }

        $domaintitle = \mod_cv\domains::get_domain_title($fieldtype);
        $certtitle = \mod_cv\domains::get_cert_title($fieldtype, $cv->examtype, $customcert);

        // Construct payload for n8n.
        $payload = [
            'submission_id' => (int) $submission->id,
            'callback_url' => $callbackurl,
            'token' => $authtoken,
            'cmid' => (int) $cm->id,
            'userid' => (int) $USER->id,
            'prompt' => $prompt,
            'custom_prompt' => $prompt,
            'domain' => [
                'type' => $fieldtype,
                'title' => $domaintitle,
            ],
            'exam' => [
                'type' => $cv->examtype,
                'title' => $certtitle,
                'custom_cert' => $customcert,
                'contact_hours' => (int) $cv->contacthours,
                'provider' => $cv->providername ?? 'SmartLearn Education',
            ],
            'course' => [
                'id' => (int) ($coursedata['id'] ?: $course->id),
                'fullname' => $coursedata['name'] ?: $course->fullname,
                'shortname' => $course->shortname,
                'startdate' => $coursedata['startdate'] ?? '',
                'enddate' => $coursedata['enddate'] ?? '',
            ],
            'candidate' => $params['profile'],
            'projects' => $params['projects'],
        ];

        // Send to n8n webhook.
        $airesponse = \mod_cv\n8n_client::send($webhookurl, $payload, $authtoken);

        // Check if n8n returned immediate synchronous output or asynchronous acknowledgment.
        $iscompleted = false;
        $outputjson = '';

        if (!empty($airesponse['summary']) || !empty($airesponse['projects'])) {
            $iscompleted = true;
            $outputjson = json_encode($airesponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $submission->ai_output = $outputjson;
            $submission->status = 'completed';
            $submission->timemodified = time();
            $DB->update_record('cv_submissions', $submission);
        }

        return [
            'status' => true,
            'message' => $iscompleted ? get_string('status_saved', 'mod_cv') : get_string('status_pending', 'mod_cv'),
            'outputjson' => $outputjson,
        ];
    }

    /**
     * Define return structure for submit web service.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Success indicator'),
            'message' => new external_value(PARAM_TEXT, 'Status message'),
            'outputjson' => new external_value(PARAM_RAW, 'JSON encoded AI response from n8n'),
        ]);
    }
}
