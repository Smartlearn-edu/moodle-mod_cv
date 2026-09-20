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
 * Defines backup_cv_stepslib class.
 *
 * @package    mod_cv
 * @category   backup
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete CV structure for backup, with file and id annotations.
 */
class backup_cv_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define the structure of the CV backup.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $cv = new backup_nested_element('cv', ['id'], [
            'name', 'intro', 'introformat', 'examtype', 'fieldtype', 'customcert',
            'aiprovider', 'contacthours', 'providername', 'webhookurl', 'customprompt',
            'showcandidateinfo', 'showcourseeducation', 'showreview', 'showsummary',
            'maxattempts', 'timecreated', 'timemodified',
        ]);

        $submissions = new backup_nested_element('submissions');

        $submission = new backup_nested_element('submission', ['id'], [
            'userid', 'raw_input', 'ai_output', 'status', 'attempts',
            'timecreated', 'timemodified',
        ]);

        $cv->add_child($submissions);
        $submissions->add_child($submission);

        $cv->set_source_table('cv', ['id' => backup::VAR_ACTIVITYID]);

        if ($userinfo) {
            $submission->set_source_table('cv_submissions', ['cvid' => backup::VAR_PARENTID]);
            $submission->annotate_ids('user', 'userid');
        }

        $cv->annotate_files('mod_cv', 'intro', null);

        return $this->prepare_activity_structure($cv);
    }
}
