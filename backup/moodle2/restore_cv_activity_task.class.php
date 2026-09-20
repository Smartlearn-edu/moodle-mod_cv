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
 * Defines restore_cv_activity_task class.
 *
 * @package    mod_cv
 * @category   backup
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/cv/backup/moodle2/restore_cv_stepslib.php');

/**
 * CV restore task that provides all the settings and steps to perform one
 * complete restore of the activity.
 */
class restore_cv_activity_task extends restore_activity_task {
    /**
     * Define (add) particular settings this activity can have.
     */
    protected function define_my_settings() {
    }

    /**
     * Define (add) particular steps this activity can have.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_cv_activity_structure_step('cv_structure', 'cv.xml'));
    }

    /**
     * Define the contents in the activity that must be processed by the link decoder.
     *
     * @return array
     */
    public static function define_decode_contents() {
        return [
            new restore_decode_content('cv', ['intro'], 'cv'),
        ];
    }

    /**
     * Define the decoding rules for links belonging to the activity to be executed by the link decoder.
     *
     * @return array
     */
    public static function define_decode_rules() {
        return [
            new restore_decode_rule('CVINDEX', '/mod/cv/index.php?id=$1', 'course'),
            new restore_decode_rule('CVVIEWBYID', '/mod/cv/view.php?id=$1', 'course_module'),
        ];
    }

    /**
     * Define the restore log rules that will be applied by the restore_logs_processor when restoring cv logs.
     *
     * @return array
     */
    public static function define_restore_log_rules() {
        return [
            new restore_log_rule('cv', 'add', 'view.php?id={course_module}', '{cv}'),
            new restore_log_rule('cv', 'update', 'view.php?id={course_module}', '{cv}'),
            new restore_log_rule('cv', 'view', 'view.php?id={course_module}', '{cv}'),
        ];
    }
}
