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
 * Redirect the user to the appropriate submission related page
 *
 * @package   mod_katest
 * @category  grade
 * @copyright 2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "../../../config.php");
require_once(__DIR__ . "/lib.php");
require_once(__DIR__ . "/locallib.php");

$id = required_param('id', PARAM_INT); // cm ID, or
$k  = optional_param('k', 0, PARAM_INT);  // katest instance ID
$itemnumber = optional_param('itemnumber', 0, PARAM_INT);
$itemid = optional_param('itemid', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT); // Graded user ID (optional).
$timestarted  = optional_param('timestarted', 0,PARAM_INT);
$timesubmitted = optional_param('timesubmitted', 0,PARAM_INT);
$attempt = optional_param('attempt',0,PARAM_INT);

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

$kaskills = $DB->get_records('katest_skills',array('katestid'=>$katest->id));
if($timestarted && $timesubmitted && data_submitted()){
    $timestarted = gmdate('Y-m-d\TH:i:s\Z',$timestarted);
    $timesubmitted = gmdate('Y-m-d\TH:i:s\Z',time()+30);
    $results = get_khan_results($katest, $kaskills, $timestarted, $timesubmitted,$attempt);
    $transaction = $DB->start_delegated_transaction();
    foreach($results as $skillname=>$result){

        if(!$record = $DB->get_record('katest_results',(array)$result)){
            $DB->insert_record('katest_results',$result);
        }
    }
    $transaction->allow_commit();
    katest_update_grades($katest, $results, $USER->id);
    redirect('results.php?user='.$USER->id.'&k='.$katest->id.'&id='.$course->id);
} elseif($userid){
  // In the simplest case just redirect to the view page.
  redirect('results.php?user='.$userid.'&k='.$katest->id.'&id='.$course->id);
} else{
  redirect('results.php?user=0&k='.$katest->id.'&id='.$course->id);
}
