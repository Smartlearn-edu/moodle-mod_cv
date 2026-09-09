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
    'institution' => '',
    'degree_startdate' => '',
    'degree_enddate' => '',
];
$profile['institution'] = $profile['institution'] ?? '';
$profile['degree_startdate'] = $profile['degree_startdate'] ?? '';
$profile['degree_enddate'] = $profile['degree_enddate'] ?? '';

$degree = $profile['degree'] ?? 'bachelors';
$savedprojects = $rawinput['projects'] ?? [];

// Prepare course dates and courses selector.
$savedcourse = $rawinput['course'] ?? [];
$selectedcourseid = !empty($savedcourse['id']) ? (int) $savedcourse['id'] : (int) $course->id;

$currentcoursestart = !empty($course->startdate) ? date('Y-m-d', $course->startdate) : date('Y-m-d', strtotime('-2 months'));
$currentcourseend = !empty($course->enddate) ? date('Y-m-d', $course->enddate) : date('Y-m-d');

$coursestartdate = !empty($savedcourse['startdate']) ? $savedcourse['startdate'] : $currentcoursestart;
$courseenddate = !empty($savedcourse['enddate']) ? $savedcourse['enddate'] : $currentcourseend;

require_once($CFG->dirroot . '/enrol/locallib.php');
$usercourses = enrol_get_my_courses(['id', 'fullname', 'shortname', 'startdate', 'enddate'], 'visible DESC, fullname ASC');

$courseslist = [];
$foundcurrent = false;

foreach ($usercourses as $uc) {
    $iscur = ((int) $uc->id === (int) $course->id);
    $issel = ((int) $uc->id === (int) $selectedcourseid);
    if ($iscur) {
        $foundcurrent = true;
    }

    $cinfo = new completion_info($uc);
    $iscompleted = $cinfo->is_course_complete($USER->id);

    $ucstart = !empty($uc->startdate) ? date('Y-m-d', $uc->startdate) : $currentcoursestart;
    $ucend = !empty($uc->enddate) ? date('Y-m-d', $uc->enddate) : date('Y-m-d');
    if ($iscompleted) {
        $comp = $DB->get_record('course_completions', ['course' => $uc->id, 'userid' => $USER->id]);
        if (!empty($comp->timecompleted)) {
            $ucend = date('Y-m-d', $comp->timecompleted);
        }
    }

    $suffix = '';
    if ($iscur) {
        $suffix = ' (' . get_string('current_course', 'mod_cv') . ')';
    } else if ($iscompleted) {
        $suffix = ' (' . get_string('course_completed', 'mod_cv') . ')';
    }

    $courseslist[] = [
        'id' => $uc->id,
        'fullname' => format_string($uc->fullname),
        'displayname' => format_string($uc->fullname) . $suffix,
        'is_selected' => $issel,
        'startdate' => $ucstart,
        'enddate' => $ucend,
    ];
}

if (!$foundcurrent) {
    array_unshift($courseslist, [
        'id' => $course->id,
        'fullname' => format_string($course->fullname),
        'displayname' => format_string($course->fullname) . ' (' . get_string('current_course', 'mod_cv') . ')',
        'is_selected' => ((int) $selectedcourseid === (int) $course->id),
        'startdate' => $currentcoursestart,
        'enddate' => $currentcourseend,
    ]);
}

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

$status = $submission ? $submission->status : 'draft';
$ispending = ($status === 'pending');

$templatedata = [
    'cmid' => $cm->id,
    'activityname' => format_string($cv->name),
    'intro' => format_module_intro('cv', $cv, $cm->id),
    'course_name' => format_string($course->fullname),
    'courses_list' => $courseslist,
    'course_startdate' => $coursestartdate,
    'course_enddate' => $courseenddate,
    'exam_title' => $examtitle,
    'contact_hours' => $cv->contacthours,
    'provider_name' => !empty($cv->providername) ? $cv->providername : 'SmartLearn Education',
    'candidate' => $profile,
    'degree_is_bachelors' => ($degree === 'bachelors'),
    'degree_is_secondary' => ($degree === 'secondary'),
    'degree_is_postgrad' => ($degree === 'postgrad'),
    'has_output' => !empty($aioutput),
    'is_pending' => $ispending,
    'export_url' => (new moodle_url('/mod/cv/export.php', ['id' => $cm->id]))->out(false),
    'initial_data_json' => json_encode([
        'saved_projects' => $savedprojects,
        'saved_course' => $savedcourse,
        'ai_output' => $aioutput,
        'status' => $status,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
];

// Initialize AMD Javascript.
$PAGE->requires->js_call_amd('mod_cv/main', 'init', [$cm->id]);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_cv/view', $templatedata);
echo $OUTPUT->footer();
