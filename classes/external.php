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
require_once("../../locallib.php");

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
     * Parameters for post_grade function
     *
     * @return external_function_parameters
     */
    public static function post_grade_parameters() {
        return new external_function_parameters(
            array(
              'userid' => new external_value(PARAM_INT, 'some user id'),
              'access_token' => new external_value(PARAM_ALPHANUM, 'Khan Oauth access token'),
              'access_token_secret' => new external_value(PARAM_ALPHANUM, 'Khan Oauth access token secret'),
              'timesubmitted' => new external_value(PARAM_INT, 'UTC Unix timestamp')

            )
        );
    }

    /**
     * Expose to AJAX
     * @return boolean
     */
    public static function post_grade_is_allowed_from_ajax() {
        return true;
    }

    /**
     * Collect data from Khan Academy using their API, and post a grade.
     */
    public static function post_grade($userid,$timesubmitted) {
        global $DB;

        $params = self::validate_parameters(self:post_grade_parameters(),
            array('userid' => $userid, 'timesubmitted'=> $timesubmitted));


        $user = $DB->get_record('user',array('id',$params['userid']));
        $kapi = new khan_oauth($)

    }

    /**
     * Wrap the core function get_site_info.
     *
     * @return external_description
     */
    public static function post_grade_returns() {
        return null;
    }
}
