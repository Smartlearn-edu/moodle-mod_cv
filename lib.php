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
 * Core library functions for mod_cv.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add a new instance of mod_cv.
 *
 * @param stdClass $cv
 * @param mod_cv_mod_form $mform
 * @return int The newly created activity ID.
 */
function cv_add_instance(stdClass $cv, $mform = null) {
    global $DB;

    $cv->timecreated = time();
    $cv->timemodified = $cv->timecreated;

    return $DB->insert_record('cv', $cv);
}

/**
 * Update an existing instance of mod_cv.
 *
 * @param stdClass $cv
 * @param mod_cv_mod_form $mform
 * @return bool True on success.
 */
function cv_update_instance(stdClass $cv, $mform = null) {
    global $DB;

    $cv->timemodified = time();
    $cv->id = $cv->instance;

    return $DB->update_record('cv', $cv);
}

/**
 * Delete an instance of mod_cv and associated submissions.
 *
 * @param int $id The activity instance ID.
 * @return bool True on success.
 */
function cv_delete_instance($id) {
    global $DB;

    if (!$DB->record_exists('cv', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('cv_submissions', ['cvid' => $id]);
    $DB->delete_records('cv', ['id' => $id]);

    return true;
}

/**
 * Declare features supported by mod_cv.
 *
 * @param string $feature FEATURE_xx constant for this feature
 * @return bool|null True if feature is supported, null if unknown
 */
function cv_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}
