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
 * Upgrade steps for mod_cv.
 *
 * @package    mod_cv
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_cv upgrade from an earlier version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_cv_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026090900) {
        // Define field customprompt to be added to cv table.
        $table = new xmldb_table('cv');
        $field = new xmldb_field('customprompt', XMLDB_TYPE_TEXT, null, null, null, null, null, 'webhookurl');

        // Conditionally launch add field customprompt.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Savepoint reached.
        upgrade_mod_savepoint(true, 2026090900, 'cv');
    }

    if ($oldversion < 2026091000) {
        $table = new xmldb_table('cv');

        // Define field fieldtype to be added to cv table.
        $fieldtype = new xmldb_field('fieldtype', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'pmi', 'examtype');
        if (!$dbman->field_exists($table, $fieldtype)) {
            $dbman->add_field($table, $fieldtype);
        }

        // Define field customcert to be added to cv table.
        $customcert = new xmldb_field('customcert', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'fieldtype');
        if (!$dbman->field_exists($table, $customcert)) {
            $dbman->add_field($table, $customcert);
        }

        // Savepoint reached.
        upgrade_mod_savepoint(true, 2026091000, 'cv');
    }

    if ($oldversion < 2026091002) {
        $table = new xmldb_table('cv');

        // Define field aiprovider to be added to cv table.
        $aiprovider = new xmldb_field('aiprovider', XMLDB_TYPE_CHAR, '30', null, null, null, 'default', 'customcert');
        if (!$dbman->field_exists($table, $aiprovider)) {
            $dbman->add_field($table, $aiprovider);
        }

        // Savepoint reached.
        upgrade_mod_savepoint(true, 2026091002, 'cv');
    }

    if ($oldversion < 2026092000) {
        $table = new xmldb_table('cv');

        // Define field showcandidateinfo to be added to cv table.
        $showcandidateinfo = new xmldb_field(
            'showcandidateinfo',
            XMLDB_TYPE_INTEGER,
            '1',
            null,
            XMLDB_NOTNULL,
            null,
            '1',
            'customprompt'
        );
        if (!$dbman->field_exists($table, $showcandidateinfo)) {
            $dbman->add_field($table, $showcandidateinfo);
        }

        // Define field showcourseeducation to be added to cv table.
        $showcourseeducation = new xmldb_field(
            'showcourseeducation',
            XMLDB_TYPE_INTEGER,
            '1',
            null,
            XMLDB_NOTNULL,
            null,
            '1',
            'showcandidateinfo'
        );
        if (!$dbman->field_exists($table, $showcourseeducation)) {
            $dbman->add_field($table, $showcourseeducation);
        }

        // Savepoint reached.
        upgrade_mod_savepoint(true, 2026092000, 'cv');
    }

    if ($oldversion < 2026092001) {
        $table = new xmldb_table('cv');

        // Define field showreview to be added to cv table.
        $showreview = new xmldb_field(
            'showreview',
            XMLDB_TYPE_INTEGER,
            '1',
            null,
            XMLDB_NOTNULL,
            null,
            '1',
            'showcourseeducation'
        );
        if (!$dbman->field_exists($table, $showreview)) {
            $dbman->add_field($table, $showreview);
        }

        // Define field showsummary to be added to cv table.
        $showsummary = new xmldb_field(
            'showsummary',
            XMLDB_TYPE_INTEGER,
            '1',
            null,
            XMLDB_NOTNULL,
            null,
            '1',
            'showreview'
        );
        if (!$dbman->field_exists($table, $showsummary)) {
            $dbman->add_field($table, $showsummary);
        }

        // Savepoint reached.
        upgrade_mod_savepoint(true, 2026092001, 'cv');
    }

    if ($oldversion < 2026092002) {
        $tablecv = new xmldb_table('cv');

        // Define field maxattempts to be added to cv table.
        $maxattempts = new xmldb_field(
            'maxattempts',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0',
            'showsummary'
        );
        if (!$dbman->field_exists($tablecv, $maxattempts)) {
            $dbman->add_field($tablecv, $maxattempts);
        }

        $tablesubmissions = new xmldb_table('cv_submissions');

        // Define field attempts to be added to cv_submissions table.
        $attempts = new xmldb_field(
            'attempts',
            XMLDB_TYPE_INTEGER,
            '10',
            null,
            XMLDB_NOTNULL,
            null,
            '0',
            'status'
        );
        if (!$dbman->field_exists($tablesubmissions, $attempts)) {
            $dbman->add_field($tablesubmissions, $attempts);
        }

        // Initialize attempts = 1 for any existing submissions that already have completed output.
        $DB->execute("UPDATE {cv_submissions} SET attempts = 1 WHERE attempts = 0 AND status = 'completed'");

        // Savepoint reached.
        upgrade_mod_savepoint(true, 2026092002, 'cv');
    }

    if ($oldversion < 2026092004) {
        $tablecv = new xmldb_table('cv');

        // Define field projectfields to be added to cv table.
        $projectfields = new xmldb_field(
            'projectfields',
            XMLDB_TYPE_TEXT,
            null,
            null,
            null,
            null,
            null,
            'maxattempts'
        );
        if (!$dbman->field_exists($tablecv, $projectfields)) {
            $dbman->add_field($tablecv, $projectfields);
        }

        // Savepoint reached.
        upgrade_mod_savepoint(true, 2026092004, 'cv');
    }

    return true;
}
