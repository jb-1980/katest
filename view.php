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

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$k  = optional_param('k', 0, PARAM_INT);  // katest instance ID

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

require_login($course, true, $cm);

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


$page = new \mod_katest\output\index_page($katest);
echo $output->render($page);

// $kaskill = $DB->get_records('katest_skills',array('katestid'=>$katest->id),'position');
//
// $html = "";
// foreach($kaskill as $key=>$skill){
//     $position = $skill->position + 1;
//     $slug = explode('~',$skill->skillname)[0];
//     $html.= "<div>\n<a href='http://www.khanacademy.org/exercise/{$slug}' target='_blank' class='katest-skill-button'>\n";
//     $html.= "Question {$position}\n</a>\n</div>";
// }
//
// $html.= "<div><div style='text-align:center;'><button class='katest-submit-button'>Submit test for grading</button></div></div>";
// echo $html;

// Finish the page.
echo $output->footer();
