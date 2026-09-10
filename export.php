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
 * PDF export script for mod_cv.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/pdflib.php');

$id = required_param('id', PARAM_INT); // Course Module ID.

$cm = get_coursemodule_from_id('cv', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$cv = $DB->get_record('cv', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/cv:view', $context);

$submission = $DB->get_record('cv_submissions', ['cvid' => $cv->id, 'userid' => $USER->id]);
if (!$submission || empty($submission->ai_output)) {
    throw new moodle_exception('error_no_output_to_export', 'mod_cv', '', null, 'No processed application data available to export.');
}

$rawinput = json_decode($submission->raw_input, true) ?: [];
$aioutput = json_decode($submission->ai_output, true) ?: [];

$profile = $rawinput['profile'] ?? [
    'name' => fullname($USER),
    'email' => $USER->email,
    'phone' => '',
    'country' => '',
    'degree' => 'bachelors',
    'institution' => '',
    'degree_startdate' => '',
    'degree_enddate' => '',
];

$fieldtype = !empty($cv->fieldtype) ? $cv->fieldtype : \mod_cv\domains::DOMAIN_PMI;
$customcert = !empty($cv->customcert) ? $cv->customcert : '';
$domaintitle = \mod_cv\domains::get_domain_title($fieldtype);
$examtitle = \mod_cv\domains::get_cert_title($fieldtype, $cv->examtype, $customcert);
$providername = !empty($cv->providername) ? $cv->providername : 'SmartLearn Education';

// Initialize Moodle PDF generator (TCPDF).
$pdf = new pdf();
$pdf->SetTitle('Application Dossier - ' . $profile['name']);
$pdf->SetAuthor($profile['name']);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

// Build HTML content for PDF.
$html = '<style>
    h1 { color: #1e3a8a; font-size: 20pt; margin-bottom: 2px; }
    h2 { color: #1e40af; font-size: 13pt; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; margin-top: 14px; margin-bottom: 8px; }
    h3 { color: #0f172a; font-size: 11pt; margin-bottom: 4px; }
    p, td { font-size: 9.5pt; color: #334155; line-height: 1.4; }
    .table-info { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .table-info td { padding: 4px 8px; background-color: #f8fafc; border: 1px solid #e2e8f0; }
    .label { font-weight: bold; color: #475569; width: 30%; }
    .box { background-color: #f1f5f9; border-left: 3px solid #2563eb; padding: 8px; margin-bottom: 12px; }
    .project-card { border: 1px solid #cbd5e1; padding: 10px; margin-bottom: 14px; background-color: #ffffff; }
</style>';

$html .= '<h1>Application & Project Experience Dossier</h1>';
$html .= '<p style="color: #64748b; font-size: 9pt;">Official Professional Dossier for ' . htmlspecialchars($examtitle) . ' &bull; ' . htmlspecialchars($domaintitle) . '</p>';

// Candidate Section.
$html .= '<h2>1. Candidate Profile</h2>';
$html .= '<table class="table-info">';
$html .= '<tr><td class="label">Full Name:</td><td>' . htmlspecialchars($profile['name']) . '</td></tr>';
$html .= '<tr><td class="label">Email Address:</td><td>' . htmlspecialchars($profile['email']) . '</td></tr>';
if (!empty($profile['phone'])) {
    $html .= '<tr><td class="label">Phone Number:</td><td>' . htmlspecialchars($profile['phone']) . '</td></tr>';
}
if (!empty($profile['country'])) {
    $html .= '<tr><td class="label">Country:</td><td>' . htmlspecialchars($profile['country']) . '</td></tr>';
}
$html .= '<tr><td class="label">Highest Education Level:</td><td>' . htmlspecialchars(ucfirst($profile['degree'] ?? 'bachelors')) . '</td></tr>';
if (!empty($profile['institution'])) {
    $html .= '<tr><td class="label">Institution / University:</td><td>' . htmlspecialchars($profile['institution']) . '</td></tr>';
}
if (!empty($profile['degree_startdate']) || !empty($profile['degree_enddate'])) {
    $degstart = $profile['degree_startdate'] ?? '';
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $degstart, $m)) {
        $degstart = $m[3] . '/' . $m[2] . '/' . $m[1];
    }
    $degend = $profile['degree_enddate'] ?? '';
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $degend, $m)) {
        $degend = $m[3] . '/' . $m[2] . '/' . $m[1];
    }
    $degdates = trim($degstart . ($degend ? ' to ' . $degend : ''));
    if (!empty($degdates)) {
        $html .= '<tr><td class="label">Degree Dates / Graduation:</td><td>' . htmlspecialchars($degdates) . '</td></tr>';
    }
}
$html .= '</table>';

// Education Section.
$savedcourse = $rawinput['course'] ?? [];
$coursename = !empty($savedcourse['name']) ? $savedcourse['name'] : $course->fullname;
$html .= '<h2>2. Qualifying Course & Professional Education</h2>';
$html .= '<table class="table-info">';
$html .= '<tr><td class="label">Course Title:</td><td>' . htmlspecialchars($coursename) . '</td></tr>';
if (!empty($savedcourse['startdate']) || !empty($savedcourse['enddate'])) {
    $cstart = $savedcourse['startdate'] ?? '';
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $cstart, $m)) {
        $cstart = $m[3] . '/' . $m[2] . '/' . $m[1];
    }
    $cend = $savedcourse['enddate'] ?? '';
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $cend, $m)) {
        $cend = $m[3] . '/' . $m[2] . '/' . $m[1];
    }
    $cdates = trim($cstart . ($cend ? ' to ' . $cend : ''));
    if (!empty($cdates)) {
        $html .= '<tr><td class="label">Course Dates:</td><td>' . htmlspecialchars($cdates) . '</td></tr>';
    }
}
$html .= '<tr><td class="label">Professional Track:</td><td>' . htmlspecialchars($domaintitle) . '</td></tr>';
$html .= '<tr><td class="label">Target Certification:</td><td>' . htmlspecialchars($examtitle) . '</td></tr>';
$html .= '<tr><td class="label">Qualifying Contact Hours:</td><td>' . htmlspecialchars($cv->contacthours) . ' Hours</td></tr>';
$html .= '<tr><td class="label">Education Provider:</td><td>' . htmlspecialchars($providername) . '</td></tr>';
$html .= '</table>';

// Executive Summary if present.
if (!empty($aioutput['summary'])) {
    $html .= '<h2>3. Professional Summary</h2>';
    $html .= '<div class="box"><p>' . nl2br(htmlspecialchars($aioutput['summary'])) . '</p></div>';
}

// Project Experience.
$html .= '<h2>4. Project Experience Write-Ups</h2>';
$savedprojects = $rawinput['projects'] ?? [];

if (!empty($aioutput['projects']) && is_array($aioutput['projects'])) {
    foreach ($aioutput['projects'] as $index => $proj) {
        $projnum = $index + 1;
        $rawproj = $savedprojects[$index] ?? [];

        $html .= '<div class="project-card">';
        $html .= '<h3>Project #' . $projnum . ': ' . htmlspecialchars($proj['title'] ?? ($rawproj['title'] ?? 'Untitled Project')) . '</h3>';

        $metaitems = [];
        $role = !empty($proj['role']) ? $proj['role'] : ($rawproj['role'] ?? '');
        if (!empty($role)) {
            $metaitems[] = '<strong>Role:</strong> ' . htmlspecialchars($role);
        }
        if (!empty($rawproj['jobtitle'])) {
            $metaitems[] = '<strong>Job Title:</strong> ' . htmlspecialchars($rawproj['jobtitle']);
        }
        if (!empty($rawproj['industry'])) {
            $metaitems[] = '<strong>Industry:</strong> ' . htmlspecialchars($rawproj['industry']);
        }
        if (!empty($rawproj['organization'])) {
            $metaitems[] = '<strong>Organization:</strong> ' . htmlspecialchars($rawproj['organization']);
        }
        if (!empty($rawproj['startdate'])) {
            $startformatted = $rawproj['startdate'];
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $startformatted, $m)) {
                $startformatted = $m[3] . '/' . $m[2] . '/' . $m[1];
            }
            $endformatted = !empty($rawproj['iscurrent']) ? 'Ongoing (Present)' : ($rawproj['enddate'] ?? '');
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $endformatted, $m)) {
                $endformatted = $m[3] . '/' . $m[2] . '/' . $m[1];
            }
            $metaitems[] = '<strong>Dates:</strong> ' . htmlspecialchars($startformatted . ' to ' . $endformatted);
        }
        if (!empty($rawproj['methodology'])) {
            $metaitems[] = '<strong>Methodology:</strong> ' . htmlspecialchars(ucfirst($rawproj['methodology']));
        }

        if (!empty($metaitems)) {
            $html .= '<p style="color: #475569; font-size: 8.5pt; margin-bottom: 6px;">' . implode(' &bull; ', $metaitems) . '</p>';
        }

        $formatteddesc = $proj['formatted_description'] ?? $proj['description'] ?? '';
        if (is_string($formatteddesc)) {
            $html .= '<p>' . nl2br(htmlspecialchars($formatteddesc)) . '</p>';
        }
        $html .= '</div>';
    }
} else if (is_string($aioutput)) {
    $html .= '<div class="box"><p>' . nl2br(htmlspecialchars($aioutput)) . '</p></div>';
}

$pdf->writeHTML($html, true, false, true, false, '');

$sanitizedname = preg_replace('/[^A-Za-z0-9_\-]/', '_', $profile['name']);
$filename = 'Application_Dossier_' . $sanitizedname . '.pdf';
$pdf->Output($filename, 'D');
exit();
