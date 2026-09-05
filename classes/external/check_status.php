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
use core_external\external_single_structure;
use core_external\external_value;
use context_module;

/**
 * External web service for checking asynchronous processing status of a CV submission.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class check_status extends external_api {
    /**
     * Define parameters for check_status web service.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
        ]);
    }

    /**
     * Check current status and return output if ready.
     *
     * @param int $cmid
     * @return array
     */
    public static function execute(int $cmid): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
        ]);

        $cm = get_coursemodule_from_id('cv', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/cv:view', $context);

        $cv = $DB->get_record('cv', ['id' => $cm->instance], '*', MUST_EXIST);
        $submission = $DB->get_record('cv_submissions', ['cvid' => $cv->id, 'userid' => $USER->id]);

        if (!$submission) {
            return [
                'status' => 'none',
                'has_output' => false,
                'outputjson' => '',
            ];
        }

        $hasoutput = !empty($submission->ai_output) && ($submission->status === 'completed' || $submission->status === 'processed');

        return [
            'status' => $submission->status,
            'has_output' => $hasoutput,
            'outputjson' => $hasoutput ? $submission->ai_output : '',
        ];
    }

    /**
     * Define return structure for check_status web service.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Submission status (pending, completed, draft, none)'),
            'has_output' => new external_value(PARAM_BOOL, 'Whether AI output is available'),
            'outputjson' => new external_value(PARAM_RAW, 'JSON encoded AI response from n8n', VALUE_DEFAULT, ''),
        ]);
    }
}
