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
 * Main activity view page for mod_cv.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID.

$cm = get_coursemodule_from_id('cv', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$cv = $DB->get_record('cv', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/cv:view', $context);

// Trigger course module viewed event / completion.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/cv/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($cv->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Retrieve existing submission for the user, if any.
$submission = $DB->get_record('cv_submissions', ['cvid' => $cv->id, 'userid' => $USER->id]);

$rawinput = [];
$aioutput = null;
if ($submission) {
    if (!empty($submission->raw_input)) {
        $rawinput = json_decode($submission->raw_input, true) ?: [];
    }
    if (!empty($submission->ai_output)) {
        $aioutput = json_decode($submission->ai_output, true);
    }
}

// Prepare candidate profile defaults.
$profile = $rawinput['profile'] ?? [
    'name' => fullname($USER),
    'email' => $USER->email,
    'phone' => !empty($USER->phone1) ? $USER->phone1 : '',
    'country' => !empty($USER->country) ? $USER->country : '',
    'degree' => 'bachelors',
];

$degree = $profile['degree'] ?? 'bachelors';
$savedprojects = $rawinput['projects'] ?? [];

// Resolve exam title.
$examtitles = [
    'pmp' => get_string('exam_pmp', 'mod_cv'),
    'capm' => get_string('exam_capm', 'mod_cv'),
    'pmi_acp' => get_string('exam_pmi_acp', 'mod_cv'),
    'pmi_rmp' => get_string('exam_pmi_rmp', 'mod_cv'),
    'pmi_pba' => get_string('exam_pmi_pba', 'mod_cv'),
    'pgmp' => get_string('exam_pgmp', 'mod_cv'),
    'custom' => get_string('exam_custom', 'mod_cv'),
];
$examtitle = $examtitles[$cv->examtype] ?? strtoupper($cv->examtype);

$templatedata = [
    'cmid' => $cm->id,
    'activityname' => format_string($cv->name),
    'intro' => format_module_intro('cv', $cv, $cm->id),
    'course_name' => format_string($course->fullname),
    'exam_title' => $examtitle,
    'contact_hours' => $cv->contacthours,
    'provider_name' => !empty($cv->providername) ? $cv->providername : 'SmartLearn Education',
    'candidate' => $profile,
    'degree_is_bachelors' => ($degree === 'bachelors'),
    'degree_is_secondary' => ($degree === 'secondary'),
    'degree_is_postgrad' => ($degree === 'postgrad'),
    'has_output' => !empty($aioutput),
    'export_url' => (new moodle_url('/mod/cv/export.php', ['id' => $cm->id]))->out(false),
    'initial_data_json' => json_encode([
        'saved_projects' => $savedprojects,
        'ai_output' => $aioutput,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
];

// Initialize AMD Javascript.
$PAGE->requires->js_call_amd('mod_cv/main', 'init', [$cm->id]);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_cv/view', $templatedata);
echo $OUTPUT->footer();
