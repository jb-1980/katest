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
              'katestid' => new external_value(PARAM_INT, 'the katest module id'),
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
    public static function post_grade($userid,$katestid,$timesubmitted) {
        global $DB;

        $params = self::validate_parameters(self:post_grade_parameters(),
            array('userid' => $userid,
                  'katestid' => $katestid,
                  'timesubmitted'=> $timesubmitted));

        // get user object from database to update grades for
        $user = $DB->get_record('user',array('id',$params['userid']));

        // get data from Khan Academy and use to create a grade.

        // 1. Create khan academy auth object
        $consumer_obj = get_config('katest');
        $args = array(
            'api_root'=>'http://www.khanacademy.org/',
            'oauth_consumer_key'=>$consumer_obj->consumer_key,
            'oauth_consumer_secret'=>$consumer_obj->consumer_secret,
            'request_token_api'=>'http://www.khanacademy.org/api/auth/request_token',
            'access_token_api'=>'http://www.khanacademy.org/api/auth/access_token',
            'oauth_callback'=>"{$CFG->wwwroot}/mod/katest/view.php?id={$id}"
        );
        $khanacademy = new khan_oauth($args);

        // 2. Get list of skills on quiz
        $kaskills = $DB->get_records('katest_skills',array('katestid'=>$params->katestid));

        // 3. Get data for each skill
        $url = 'https://www.khanacadey.org/api/v2/user/'
        $tokens = $SESSION->
        foreach($kaskills as $k=>$skill){

        }

        $response = $khanacademy->request('GET', $url, $params = array(), $token = '', $secret = '')
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
