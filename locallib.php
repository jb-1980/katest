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
 * Internal library of functions for module katest
 *
 * All the katest specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_katest
 * @copyright  2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/lib/grade/grade_item.php');
require_once($CFG->libdir.'/oauthlib.php');

class khan_oauth extends oauth_helper {
    /**
     * Request token for authentication
     * This is the first step to use OAuth, it will return oauth_token and oauth_token_secret
     * @return array
     */
    public function request_token() {
        global $CFG;
        $this->sign_secret = $this->consumer_secret.'&';
        $params = $this->prepare_oauth_parameters($this->request_token_api, array(), 'GET');
        $url = $this->request_token_api;
        if (!empty($params)) {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= http_build_query($params, '', '&');
        }
        return redirect($url);
    }

    public function parse_params($params,$exclude=array()){
        ksort($params);
        $total = array();
        foreach($params as $param=>$value){
            if(in_array($param,$exclude)){
                continue;
            }
            if(is_array($value)){
                if(!empty($value)){
                    sort($value);
                    foreach($value as $k=>$v){
                        $total[] = $param.'='.rawurlencode($v);
                    }
                }
            } else{
                $total[] = $param.'='.rawurlencode($value);
            }
        }
        return $total;
    }
    /**
     * Build parameters list:
     *    oauth_consumer_key="0685bd9184jfhq22",
     *    oauth_nonce="4572616e48616d6d65724c61686176",
     *    oauth_token="ad180jjd733klru7",
     *    oauth_signature_method="HMAC-SHA1",
     *    oauth_signature="wOJIO9A2W5mFwDgiDvZbTSMK%2FPY%3D",
     *    oauth_timestamp="137131200",
     *    oauth_version="1.0"
     *    oauth_verifier="1.0"
     * @param array $param
     * @return string
     */
    function get_signable_parameters($params){
        $total = $this->parse_params($params,$exclude=array('oauth_signature'));
        return implode('&', $total);
    }

    public function setup_oauth_http_header($params) {
        $total = $this->parse_params($params);
        $str = implode(', ', $total);
        $str = 'Authorization: OAuth '.$str;
        $this->http->setHeader('Expect:');
        $this->http->setHeader($str);
    }

    /**
     * Request oauth protected resources
     * @param string $method
     * @param string $url
     * @param string $token
     * @param string $secret
     */
    public function request($method, $url, $params = array(), $token = '', $secret = '') {
        if (empty($token)) {
            $token = $this->access_token;
        }
        if (empty($secret)) {
            $secret = $this->access_token_secret;
        }

        // to access protected resource, sign_secret will always be consumer_secret+token_secret
        $this->sign_secret = $this->consumer_secret.'&'.$secret;

        $oauth_params = $this->prepare_oauth_parameters($url, array('oauth_token'=>$token) + $params, $method);
        $this->setup_oauth_http_header($oauth_params);
        $url_params = $this->parse_params($params);

        $url .= (stripos($url, '?') !== false) ? '&' : '?';
        $url .= implode('&',$url_params);
        $content = call_user_func_array(array($this->http, 'get'), array($url,array(),$this->http_options));
        // reset http header and options to prepare for the next request
        $this->http->resetHeader();
        // return request return value
        return $content;
    }
}

/**
 * Calculate the quiz grade
 *
 * @param stdClass $katest
 * @return string
 */
function get_khan_results($katest, $kaskills, $timestarted, $timesubmitted, $attempt){
    global $USER, $DB, $SESSION;

    // get data from Khan Academy and use to create a grade.

    // 1. Create khan academy auth object
    $consumer_obj = get_config('katest');
    $args = array(
        'api_root'=>'http://www.khanacademy.org/',
        'oauth_consumer_key'=>$consumer_obj->consumer_key,
        'oauth_consumer_secret'=>$consumer_obj->consumer_secret,
        'request_token_api'=>'http://www.khanacademy.org/api/auth/request_token',
        'access_token_api'=>'http://www.khanacademy.org/api/auth/access_token',
    );
    $khanacademy = new khan_oauth($args);

    // 2. Get list of skills on quiz
    $kaskills = $DB->get_records('katest_skills',array('katestid'=>$katest->id));

    // 3. Get data for each skill
    $katest_id = $katest->id;
    $tokens = $SESSION->khanacademy_tokens->$katest_id;

    $params = array('dt_end'=>$timesubmitted,'dt_start'=>$timestarted);
    $token = $tokens['oauth_token'];
    $secret = $tokens['oauth_token_secret'];

    $results = array();
    foreach($kaskills as $k=>$skill){
        $skillname = explode('~',$skill->skillname)[0];
        $url = "http://www.khanacademy.org/api/v1/user/exercises/{$skillname}/log";
        $response = json_decode($khanacademy->request('GET',$url,$params,$token,$secret));

        foreach($response as $key => $val){
            $result = new stdClass;
            $result->katestid = $katest_id;
            $result->userid = $USER->id;
            $result->katestattempt = $attempt;
            $result->problemattempt = $key;
            $result->skillname = $skill->skillname;
            $result->hintused = $val->hint_used ? 1 : 0;
            $result->timetaken = $val->time_taken;
            $result->correct = $val->correct ? 1 : 0;
            $result->timedone = strtotime($val->time_done);
            $result->ip_address = $val->ip_address;
            $results[] = $result;
        }
    }

    return $results;
}

/**
 * calculate the quiz grade and store it in the database
 *
 * @param array $results the results array of the Khan Academy data
 * @param stdClass $katest, the katest object;
 * @param stdClass $kaskills, the kaskills object
 * @return float
 */
function get_grade_data($results, $katest, $kaskills){
    $total = count($kaskills);

    $grades = array();
    foreach($results as $k=>$result){
        if(array_key_exists($result->skillname, $grades)){
            $grades[$result->skillname][] = $result;
        } else {
            $grades[$result->skillname] = array($result);
        }
    }

    $num = 0;
    foreach($grades as $k=>$grade){
        $correct = false;
        foreach($grade as $key=> $g){
            if($g->correct){
                $correct = true;
                break;
            }
        }

        switch (true) {
            case !$correct:
                break;
            case count($grade) == 1:
                $num++;
                break;
            case count($grade) == 2:
                $num += 0.8;
                break;
            case count($grade) == 3:
                $num += 0.5;
        }
    }
    $finalgrade = $total ? $num/$total*$katest->grade : null;

    global $USER;
    if($finalgrade){
        $gradeitem = new grade_item(array(
            'courseid'=>$katest->course,
            'itemmodule'=>'katest',
            'iteminstance'=>$katest->id));
        $gradeitem->update_raw_grade($USER->id, $finalgrade);
    }

    return $finalgrade.'/'.$katest->grade;
}


function katest_choose_renderer($katest, $cid, $password=null){
    global $CFG, $DB, $SESSION, $USER;

    // Check to make sure number of attempts has not been exceeded
    $attempts_sql = "SELECT COUNT(DISTINCT katestattempt)
                       FROM {$CFG->prefix}katest_results
                      WHERE userid = {$USER->id};";
    $num_attempts = $DB->count_records_sql($attempts_sql);

    // If attempts exceeded, return attempts_exceeded screen
    if($katest->attempts && ($num_attempts + 1 > $katest->attempts)){
        return new \mod_katest\output\attempts_exceeded();
    }

    // Everything has been authorized, so we can send them the index page
    if(isset($SESSION->khanacademy_tokens)
         && property_exists($SESSION->khanacademy_tokens,$katest->id)){
        // All authentication has be passed, we can start the test
        return new \mod_katest\output\index($katest,$num_attempts);
    }

    /** Authorization
     *  If a password is required, we will check that is is correct and then
     *  move to Khan Authorization page. Otherwise, we will just go straight
     *  the Khan Authorization page.
     */
    if($katest->password) { // Authenticate password if required
        if($password){
          if($password == $katest->password){
              // password is correct, now let's make sure that we can sync up with khan
              return new \mod_katest\output\khan_authenticate($cid);
          } else{ // incorrect password
              $msg = get_string('error_msg', 'katest');
              return new \mod_katest\output\password($msg);
          }
        } else{ //password has not been submitted, send password page
          return new \mod_katest\output\password();
        }
    } else{ // no password required, so let's just get Khan Authorization
        return new \mod_katest\output\khan_authenticate($cid);
    }
    // Something was missed. This should raise an error
    return null;
}
