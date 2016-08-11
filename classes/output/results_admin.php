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
 * Class containing data for index page
 *
 * @package    mod_katest
 * @copyright  2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_katest\output;

//require_once("$CFG->dirroot/webservice/externallib.php");

use renderable;
use templatable;
use renderer_base;
use stdClass;

/**
 * Class containing data for results page
 *
 * @copyright  2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class results_admin implements renderable, templatable {
    /** @var $grades, the grades for the Khan Academy test */
    var $grades = null;

    var $courseid = null;
    var $modid = null;
    var $cmid = null;

    public function __construct($grades, $courseid, $modid, $cmid){
      $this->grades = $grades;
      $this->courseid = $courseid;
      $this->modid = $modid;
      $this->cmid = $cmid;
    }
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = array(
          "grades"   =>array(),
          "courseid" =>$this->courseid,
          "modid"    =>$this->modid,
          "cmid"     =>$this->cmid
        );
        foreach($this->grades as $userid=>$user){

          $user->id = $userid;
          $data["grades"][] = $user;
        }
        return $data;
    }
}
