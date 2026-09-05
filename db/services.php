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
 * Web service function definitions for mod_cv.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_cv_submit' => [
        'classname' => 'mod_cv\external\submit',
        'methodname' => 'execute',
        'description' => 'Submit candidate data to n8n and receive AI output',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/cv:submit',
    ],
    'mod_cv_check_status' => [
        'classname' => 'mod_cv\external\check_status',
        'methodname' => 'execute',
        'description' => 'Check asynchronous processing status of a CV submission',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'mod/cv:view',
    ],
];
