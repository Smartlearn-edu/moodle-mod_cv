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
 * Defines restore_cv_stepslib class.
 *
 * @package    mod_cv
 * @category   backup
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one CV activity.
 */
class restore_cv_activity_structure_step extends restore_activity_structure_step {
    /**
     * Define the structure of the restore workflow.
     *
     * @return array
     */
    protected function define_structure() {
        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('cv', '/activity/cv');
        if ($userinfo) {
            $paths[] = new restore_path_element('cv_submission', '/activity/cv/submissions/submission');
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process an activity instance restore.
     *
     * @param array $data The data from the XML file.
     */
    protected function process_cv($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->get_courseid();

        $newitemid = $DB->insert_record('cv', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process a submission restore.
     *
     * @param array $data The data from the XML file.
     */
    protected function process_cv_submission($data) {
        global $DB;

        $data = (object) $data;
        $data->cvid = $this->get_new_parentid('cv');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $DB->insert_record('cv_submissions', $data);
    }

    /**
     * Post-execution actions for files.
     */
    protected function after_execute() {
        $this->add_related_files('mod_cv', 'intro', null);
    }
}
