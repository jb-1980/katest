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
 * Prints a particular instance of katest
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_katest
 * @copyright  2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

$cmid = optional_param('id', 0, PARAM_INT);

$consumer_obj = get_config('katest');
$args = array(
    'api_root'=>'http://www.khanacademy.org/',
    'oauth_consumer_key'=>$consumer_obj->consumer_key,
    'oauth_consumer_secret'=>$consumer_obj->consumer_secret,
    'request_token_api'=>'http://www.khanacademy.org/api/auth/request_token',
    'access_token_api'=>'http://www.khanacademy.org/api/auth/access_token',
    'oauth_callback'=>"{$CFG->wwwroot}/mod/katest/view.php?id={$cmid}"
);
$khanacademy = new khan_oauth($args);
$khanacademy->request_token();
