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
$timestarted  = optional_param('timestarted', 0,PARAM_INT);
$timesubmitted = optional_param('timesubmitted', 0,PARAM_INT);
$attempt = optional_param('attempt',0,PARAM_INT);

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

#if(has_capability('mod/katest:viewreports', $PAGE->context)){
#    echo 'Grade reports coming soon';

#} else{


    echo $output->heading($katest->name.' results for '.$USER->firstname.' '.$USER->lastname);

    $results = null;
    if($timestarted && $timesubmitted){
        $timestarted = gmdate('Y-m-d\TH:i:s\Z',$timestarted);
        $timesubmitted = gmdate('Y-m-d\TH:i:s\Z',$timesubmitted);
        $results = get_khan_results($katest, $kaskills, $timestarted, $timesubmitted,$attempt);
        $transaction = $DB->start_delegated_transaction();
        foreach($results as $skillname=>$result){

            if(!$record = $DB->get_record('katest_results',(array)$result)){
                $DB->insert_record('katest_results',$result);
            }
        }
        $transaction->allow_commit();
    }

    if(!$results){
        $results = $DB->get_records('katest_results',array(
            'katestid'=>$katest->id,
            'userid'=>$USER->id,
            'katestattempt'=>$attempt));
    }
    $grade = get_grade_data($results, $katest, $kaskills);
    $page = new \mod_katest\output\results($results, $grade);
    echo $output->render($page);
//}
// Finish the page.
echo $output->footer();
