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

    return true;
}
