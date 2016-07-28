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


include_once($CFG->libdir.'/oauthlib.php');
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
                        //print_object($v);
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
