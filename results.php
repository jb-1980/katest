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

$id = required_param('id', PARAM_INT); // Course ID, or
$k  = required_param('k', PARAM_INT);  // katest instance ID
$user = optional_param('user',0,PARAM_INT); // user results, 0 is all users
$attempt = optional_param('attempt',0,PARAM_INT); // user attempt, 0 is all attempts

$katest = $DB->get_record('katest', array('id' => $k), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $katest->course), '*', MUST_EXIST);
$cm     = get_coursemodule_from_instance('katest', $katest->id, $course->id, false, MUST_EXIST);

require_login($course, true, $cm);

// Get list of skills on quiz
$kaskills = $DB->get_records('katest_skills',array('katestid'=>$katest->id));
// Print the page header.
$PAGE->set_url('/mod/katest/results.php', array('id'=>$id,'k'=>$k));
$PAGE->set_title(format_string($katest->name));
$PAGE->set_heading(format_string($course->fullname));


// Output starts here.
$output = $PAGE->get_renderer('mod_katest');
echo $output->header();

if(has_capability('mod/katest:viewreports', $PAGE->context)){
    if($user === 0){
      $users = get_enrolled_users($PAGE->context);
      $grading_info = grade_get_grades($id, 'mod', 'katest', $k, array_keys($users));
      $grades = array();
      foreach($grading_info->items[0]->grades as $key=> $grade){
          if($grade->grade){
            $user_data = new stdClass;
            $user_data->id = $key;
            $user_data->name = $users[$key]->firstname.' '.$users[$key]->lastname;
            $user_data->grade = $grade->grade;
            $grades[$key] = $user_data;
          }
      }

      $page = new \mod_katest\output\results_admin($grades, $id, $k, $cm->id);
      echo $output->render($page);

    } else{
      $user_record = $DB->get_record('user',array('id'=>$user));
      echo $output->heading($katest->name.' results for '.$user_record->firstname.' '.$user_record->lastname);

      $results = $DB->get_records('katest_results',array('katestid'=>$k,'userid'=>$user));
      $page = new \mod_katest\output\results($results, $katest, $kaskills);
      echo $output->render($page);
    }

} else{


    echo $output->heading($katest->name.' results for '.$USER->firstname.' '.$USER->lastname);

    $results = $DB->get_records('katest_results',array('katestid'=>$k,'userid'=>$USER->id));
    //$grade = get_grade_data($results, $katest, $kaskills);
    $page = new \mod_katest\output\results($results, $katest, $kaskills);
    echo $output->render($page);
}
// Finish the page.
echo $output->footer();
