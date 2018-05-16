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
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$k  = optional_param('k', 0, PARAM_INT);  // katest instance ID
$preview = optional_param('preview',0,PARAM_INT); // bool to allow admin to preview quiz

// tokens returned from authenticating with KA
$oauth_token = optional_param('oauth_token',null,PARAM_RAW);
$oauth_token_secret = optional_param('oauth_token_secret',null,PARAM_RAW);
$oauth_verifier = optional_param('oauth_verifier',null,PARAM_RAW);

if ($id) {
    $cm     = get_coursemodule_from_id('katest', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $katest = $DB->get_record('katest', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($k) {
    $katest = $DB->get_record('katest', array('id' => $k), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $katest->course), '*', MUST_EXIST);
    $cm     = get_coursemodule_from_instance('katest', $katest->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

$katest_id = $katest->id;
// after authenticating with khan academy, store tokens in $SESSION, this should
// happen after user has entered quiz password if required, and clicked the
// Khan Academy login button in quiz
if($oauth_token and $oauth_token_secret and $oauth_verifier){
    $consumer_obj = get_config('katest');
    $args = array(
        'api_root'=>'http://www.khanacademy.org/',
        'oauth_consumer_key'=>$consumer_obj->consumer_key,
        'oauth_consumer_secret'=>$consumer_obj->consumer_secret,
        'request_token_api'=>'https://www.khanacademy.org/api/auth2/request_token',
        'access_token_api'=>'https://www.khanacademy.org/api/auth2/access_token',
        'oauth_callback'=>"{$CFG->wwwroot}/mod/katest/view.php?id={$id}"
    );
    $khanacademy = new katest_oauth($args);
    $tokens = $khanacademy->get_access_token($oauth_token,$oauth_token_secret,$oauth_verifier);
    if(isset($SESSION->khanacademy_tokens)){
        $SESSION->khanacademy_tokens->$katest_id = $tokens;
    } else{
        $SESSION->khanacademy_tokens = new stdClass;
        $SESSION->khanacademy_tokens->$katest_id = $tokens;
    }

}

require_login($course, true, $cm);

if(has_capability('mod/katest:viewreports', $PAGE->context) && $preview == 0){
    redirect('results.php?user=0&k='.$katest->id.'&id='.$course->id);
}


$event = \mod_katest\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $katest);
$event->trigger();

// $PAGE->requires->jquery();
// $PAGE->requires->js('/mod/katest/script.js');

// Print the page header.
$PAGE->set_url('/mod/katest/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($katest->name));
$PAGE->set_heading(format_string($course->fullname));


/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('katest-'.$somevar);
 */

// Output starts here.
$output = $PAGE->get_renderer('mod_katest');
echo $output->header();
echo $output->heading($katest->name);

// Conditions to show the intro can change to look for own settings or whatever.
if ($katest->intro) {
    echo $output->box(format_module_intro('katest', $katest, $cm->id), 'generalbox mod_introbox', 'katestintro');
}

if($katest->password && data_submitted()){ //data_submitted means POST request
    $password = optional_param('password',null,PARAM_RAW);
    $page = katest_choose_renderer($katest,$id,$password);
    echo $output->render($page);
} else{
    $page = katest_choose_renderer($katest,$id);
    echo $output->render($page);
}
// Finish the page.
echo $output->footer();
