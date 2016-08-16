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
 * Class containing data for index page
 *
 * @copyright  2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class index implements renderable, templatable {
    /** @var $katest, the katest module */
    var $katest = null;
    /** @var $attempt, the current attempt number */
    var $attempt = null;
    /** @var $cmid, the course module id for the katest */
    var $cmid = null;

    public function __construct($katest,$attempt,$cmid){
      $this->katest = $katest;
      $this->attempt= $attempt;
      $this->cmid   = $cmid;
    }
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $DB, $CFG, $USER;
        $katest = $this->katest;

        $kaskills = $DB->get_records('katest_skills',array('katestid'=>$katest->id),'position');

        $data = new stdClass();
        $data->questions = array();
        foreach($kaskills as $k=>$v){
            $v->position = $v->position + 1;
            $v->skillname = explode('~',$v->skillname)[0];
            $data->questions[] = $v;
        }
        $data->timestarted = time();
        $data->cmid = $this->cmid;
        $data->katestid = $katest->id;
        $data->katestattempt = $this->attempt + 1;
        if($katest->attempts){
          $data->exceededattempts = $data->katestattempt > $katest->attempts ? true : false;
        }


        return $data;
    }
}
