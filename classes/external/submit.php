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
            ]),
            'projects' => new external_multiple_structure(
                new external_single_structure([
                    'title' => new external_value(PARAM_TEXT, 'Project title'),
                    'role' => new external_value(PARAM_TEXT, 'Role on project'),
                    'methodology' => new external_value(
                        PARAM_ALPHA,
                        'Methodology (predictive, agile, hybrid)',
                        VALUE_DEFAULT,
                        'predictive'
                    ),
                    'startdate' => new external_value(PARAM_TEXT, 'Start date (MM/YYYY)'),
                    'enddate' => new external_value(PARAM_TEXT, 'End date (MM/YYYY)', VALUE_DEFAULT, ''),
                    'iscurrent' => new external_value(PARAM_BOOL, 'Whether project is ongoing', VALUE_DEFAULT, false),
                    'notes' => new external_value(PARAM_RAW, 'Project raw tasks and notes'),
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
     * @return array
     */
    public static function execute(int $cmid, array $profile, array $projects): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'profile' => $profile,
            'projects' => $projects,
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

        // Construct payload for n8n.
        $payload = [
            'token' => $authtoken,
            'exam' => [
                'type' => $cv->examtype,
                'contact_hours' => (int) $cv->contacthours,
                'provider' => $cv->providername ?? 'SmartLearn Education',
            ],
            'course' => [
                'id' => (int) $course->id,
                'fullname' => $course->fullname,
                'shortname' => $course->shortname,
            ],
            'candidate' => $params['profile'],
            'projects' => $params['projects'],
        ];

        // Send to n8n webhook.
        $airesponse = \mod_cv\n8n_client::send($webhookurl, $payload, $authtoken);

        // Save submission to database.
        $now = time();
        $rawjson = json_encode(['profile' => $params['profile'], 'projects' => $params['projects']]);
        $outputjson = json_encode($airesponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $existing = $DB->get_record('cv_submissions', ['cvid' => $cv->id, 'userid' => $USER->id]);
        if ($existing) {
            $existing->raw_input = $rawjson;
            $existing->ai_output = $outputjson;
            $existing->status = 'processed';
            $existing->timemodified = $now;
            $DB->update_record('cv_submissions', $existing);
        } else {
            $record = new \stdClass();
            $record->cvid = $cv->id;
            $record->userid = $USER->id;
            $record->raw_input = $rawjson;
            $record->ai_output = $outputjson;
            $record->status = 'processed';
            $record->timecreated = $now;
            $record->timemodified = $now;
            $DB->insert_record('cv_submissions', $record);
        }

        return [
            'status' => true,
            'message' => get_string('status_saved', 'mod_cv'),
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
