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
class results implements renderable, templatable {
    /** @var $results, the results data from Khan Academy */
    var $results = null;

    /** @var $katest, katest record object */
    var $katest = null;

    /** @var $kaskills, skills object related to katest */
    var $kaskills = null;

    public function __construct($results, $katest, $kaskills, $is_admin=false){
      $this->results = $results;
      $this->katest = $katest;
      $this->kaskills = $kaskills;
      $this->is_admin = $is_admin;
    }
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $attempts = array();
        $expected_attempts = array();
        foreach($this->kaskills as $key=>$kaskill){
          $expected_attempts[explode('~',$kaskill->skillname)[1]] = 1;
        }
        foreach($this->results as $key=>$result){
            $result->skillname = explode('~',$result->skillname)[1];
            $attempts[$result->katestattempt]['results'][] = $result;
        }



        $data = new stdClass;
        $data->attempts = array();
        $data->is_admin = $this->is_admin;
        $data->katestid = $this->katest->id;
        $data->userid = array_shift($this->results)->userid;
        foreach($attempts as $k=>$attempt){
            $unattempted = $expected_attempts;
            foreach($attempt['results'] as $key=>$result){
              unset($unattempted[$result->skillname]);
            }
            $attempt['grade'] = katest_calculate_grade($attempt['results'], $this->katest, $this->kaskills);
            $attempt['number'] = $k;
            foreach($unattempted as $skillname=>$v){
                $attempt['unattempted'][] = array("skillname"=>$skillname);
            }
            $data->attempts[] = $attempt;
        }

        return $data;
    }
}
