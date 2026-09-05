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
 * Asynchronous webhook callback endpoint for receiving processed AI data from n8n.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_DEBUG_DISPLAY', true);

require_once(__DIR__ . '/../../config.php');

header('Content-Type: application/json; charset=utf-8');

// Only allow POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Only POST is accepted.']);
    exit;
}

// Read and decode JSON body.
$rawinput = file_get_contents('php://input');
$data = json_decode($rawinput, true);

if (empty($data) || !is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload.']);
    exit;
}

// Verify security token if configured in Moodle.
$expectedtoken = get_config('mod_cv', 'default_auth_token');
if (!empty($expectedtoken)) {
    $providedtoken = '';

    // Check Authorization header.
    $authheader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.*)$/i', $authheader, $matches)) {
        $providedtoken = trim($matches[1]);
    } else if (!empty($data['token'])) {
        $providedtoken = trim($data['token']);
    }

    if ($providedtoken !== trim($expectedtoken)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Invalid security token.']);
        exit;
    }
}

// Validate submission ID.
$submissionid = isset($data['submission_id']) ? (int) $data['submission_id'] : 0;
if ($submissionid <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid submission_id.']);
    exit;
}

$submission = $DB->get_record('cv_submissions', ['id' => $submissionid]);
if (!$submission) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Submission record not found.']);
    exit;
}

// Extract AI output.
$aioutput = $data['ai_output'] ?? $data['output'] ?? null;
if (empty($aioutput)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing ai_output data.']);
    exit;
}

// Format output as clean JSON string.
$outputjson = is_array($aioutput)
    ? json_encode($aioutput, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    : (string) $aioutput;

// Update submission record.
$submission->ai_output = $outputjson;
$submission->status = 'completed';
$submission->timemodified = time();
$DB->update_record('cv_submissions', $submission);

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'AI output successfully received and saved for submission #' . $submissionid,
]);
exit;
