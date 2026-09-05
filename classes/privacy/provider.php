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

namespace mod_cv\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use context;
use context_module;

/**
 * Privacy provider implementation for mod_cv.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Return metadata information about mod_cv data storage.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'cv_submissions',
            [
                'userid' => 'privacy:metadata:cv_submissions:userid',
                'cvid' => 'privacy:metadata:cv_submissions:cvid',
                'raw_input' => 'privacy:metadata:cv_submissions:raw_input',
                'ai_output' => 'privacy:metadata:cv_submissions:ai_output',
                'timecreated' => 'privacy:metadata:cv_submissions:timecreated',
                'timemodified' => 'privacy:metadata:cv_submissions:timemodified',
            ],
            'privacy:metadata:cv_submissions'
        );

        $collection->add_external_location_link(
            'n8n',
            [
                'name' => 'privacy:metadata:cv_submissions:raw_input',
                'projects' => 'privacy:metadata:cv_submissions:raw_input',
            ],
            'privacy:metadata:n8n'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {cv} cv ON cv.id = cm.instance
                  JOIN {cv_submissions} s ON s.cvid = cv.id
                 WHERE s.userid = :userid";

        $params = [
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'cv',
            'userid' => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }

        $sql = "SELECT s.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {cv} cv ON cv.id = cm.instance
                  JOIN {cv_submissions} s ON s.cvid = cv.id
                 WHERE cm.id = :cmid";

        $params = [
            'modname' => 'cv',
            'cmid' => $context->instanceid,
        ];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified context list.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id('cv', $context->instanceid);
            if (!$cm) {
                continue;
            }

            $submission = $DB->get_record('cv_submissions', [
                'cvid' => $cm->instance,
                'userid' => $userid,
            ]);

            if ($submission) {
                $data = (object) [
                    'raw_input' => json_decode($submission->raw_input, true),
                    'ai_output' => json_decode($submission->ai_output, true),
                    'status' => $submission->status,
                    'timecreated' => transform::datetime($submission->timecreated),
                    'timemodified' => transform::datetime($submission->timemodified),
                ];

                writer::with_context($context)->export_data([get_string('pluginname', 'mod_cv')], $data);
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        global $DB;

        if (!$context instanceof context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('cv', $context->instanceid);
        if (!$cm) {
            return;
        }

        $DB->delete_records('cv_submissions', ['cvid' => $cm->instance]);
    }

    /**
     * Delete all user data for the specified users in a context.
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('cv', $context->instanceid);
        if (!$cm) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        [$insql, $inparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $inparams['cvid'] = $cm->instance;

        $DB->delete_records_select('cv_submissions', "cvid = :cvid AND userid $insql", $inparams);
    }

    /**
     * Delete all user data for the specified context list.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id('cv', $context->instanceid);
            if (!$cm) {
                continue;
            }

            $DB->delete_records('cv_submissions', [
                'cvid' => $cm->instance,
                'userid' => $userid,
            ]);
        }
    }
}
