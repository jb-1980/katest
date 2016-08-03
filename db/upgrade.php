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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file keeps track of upgrades to the katest module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_katest
 * @copyright  2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute katest upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_katest_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    /*
     *
     */

    if ($oldversion < 2016072801) {

        // Define table katest to be updated.
        $katest = new xmldb_table('katest');

        // Adding fields to table katest.
        $password = new xmldb_field('password', XMLDB_TYPE_CHAR, '255', null, null, null, null);

        // Conditionally launch add field.
        if (!$dbman->field_exists($katest, $password)) {
            $dbman->add_field($katest, $password);
        }

        // Define table katest_results to be created.
        $katest_results = new xmldb_table('katest_results');

        // Adding fields to table katest_results.
        $katest_results->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $katest_results->add_field('katestid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $katest_results->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $katest_results->add_field('skillname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $katest_results->add_field('hintused', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $katest_results->add_field('timetaken', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $katest_results->add_field('correct', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $katest_results->add_field('timedone', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $katest_results->add_field('ip_address', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table katest_results.
        $katest_results->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table katest_results.
        $katest_results->add_index('katestid_and_userid', XMLDB_INDEX_NOTUNIQUE, array('katestid', 'userid'));

        // Conditionally launch create table for katest.
        if (!$dbman->table_exists($katest_results)) {
            $dbman->create_table($katest_results);
        }

        // Katest savepoint reached.
        upgrade_mod_savepoint(true, 2016072801, 'katest');
    }

    if ($oldversion < 2016080300) {

        // Define table katest to be updated.
        $katest = new xmldb_table('katest');

        // Adding fields to table katest.
        $attempts = new xmldb_field('attempts', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'attempts');
        // Conditionally launch add field.
        if (!$dbman->field_exists($katest, $attempts)) {
            $dbman->add_field($katest, $attempts);
        }

        // Katest savepoint reached.
        upgrade_mod_savepoint(true, 2016080300, 'katest');
    }
    return true;
}
