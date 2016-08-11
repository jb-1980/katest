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
 * @copyright 2016 Your Name <your@email.address>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "../../../config.php");
require_once(__DIR__ . "/locallib.php");

$id = required_param('id', PARAM_INT); // Course ID, or
$k  = required_param('k', PARAM_INT);  // katest instance ID
$timestarted  = optional_param('timestarted', 0,PARAM_INT);
$timesubmitted = optional_param('timesubmitted', 0,PARAM_INT);
$attempt = optional_param('attempt',0,PARAM_INT);


if($timestarted && $timesubmitted && data_submitted()){
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
$katest = $DB->get_record('katest', array('id' => $k), '*', MUST_EXIST);
$kaskills = $DB->get_records('katest_skills',array('katestid'=>$katest->id));

$grade = get_grade_data($results, $katest, $kaskills)
katest_update_grade($id, $k, $userid, $grade)

// In the simplest case just redirect to the view page.
redirect('results.php?user='.$USER->id.'&k='.$k.'&id='.$id);
