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
 * List of all mod_cv activities in a course.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT); // Course ID.

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
require_course_login($course);

$PAGE->set_url('/mod/cv/index.php', ['id' => $id]);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'mod_cv'));

$cvs = get_all_instances_in_course('cv', $course);

if (empty($cvs)) {
    notice(get_string('thereareno', 'moodle', get_string('modulenameplural', 'mod_cv')), new moodle_url('/course/view.php', ['id' => $course->id]));
    exit;
}

$table = new html_table();
$table->head = [
    get_string('name'),
    get_string('exam_type', 'mod_cv'),
    get_string('contact_hours', 'mod_cv'),
];

foreach ($cvs as $cv) {
    $link = html_writer::link(new moodle_url('/mod/cv/view.php', ['id' => $cv->coursemodule]), format_string($cv->name));
    $table->data[] = [
        $link,
        strtoupper($cv->examtype),
        $cv->contacthours . ' Hours',
    ];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
