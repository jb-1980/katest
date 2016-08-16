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
 * This is the external API for this plugin.
 *
 * @package    mod_katest
 * @copyright  2016 Joseph Gilgen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_katest;

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/webservice/externallib.php");
require_once(dirname(dirname(__FILE__)).'/locallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_format_value;
use external_single_structure;
use external_multiple_structure;
use invalid_parameter_exception;

/**
 * This is the external API for this plugin.
 *
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Parameters for delete_attempt function
     *
     * @return external_function_parameters
     */
    public static function delete_attempt_parameters() {
        return new external_function_parameters(
            array(
              'userid' => new external_value(PARAM_INT, 'some user id'),
              'katestid' => new external_value(PARAM_INT, 'the katest module id'),
              'attemptid' => new external_value(PARAM_INT, 'the attempt number')
            )
        );
    }

    /**
     * Expose to AJAX
     * @return boolean
     */
    public static function delete_attempt_is_allowed_from_ajax() {
        return true;
    }

    /**
     * Collect data from Khan Academy using their API, and post a grade.
     */
    public static function delete_attempt($userid,$katestid,$attemptid) {
        global $DB;

        $params = self::validate_parameters(self::delete_attempt_parameters(),
            array('userid'   => $userid,
                  'katestid' => $katestid,
                  'attemptid'=> $attemptid));

        debugging(print_r($params,true));
        $DB->delete_records('katest_results', array(
            'userid'      => $params['userid'],
            'katestid'    => $params['katestid'],
            'katestattempt'=> $params['attemptid']
          ));
    }

    /**
     * Wrap the core function get_site_info.
     *
     * @return external_description
     */
    public static function delete_attempt_returns() {
        return null;
    }
}
