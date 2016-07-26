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
 * The main katest configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_katest
 * @copyright  2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 *
 * @package    mod_katest
 * @copyright  2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_katest_mod_form extends moodleform_mod {
    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $DB, $PAGE;

        $PAGE->requires->jquery();
        $PAGE->requires->js('/mod/katest/scripts/chosen.jquery.min.js');
        $PAGE->requires->js('/mod/katest/script.js');

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('katestname', 'katest'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'katestname', 'katest');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding the rest of katest settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        $mform->addElement('header', 'katestfieldset', get_string('katestfieldset', 'katest'));

        // fetch an parse the data from Khan Academy. This url is fast, but is
        // limited to math skills. Also, as an internal api it is not documented
        // and could be changed without notice. User beware!!
        // TODO: Make an admin setting to use this url or the given url of
        // https://www.khanacademy.org/api/v1/exercises as the expense of speed
        $skills_url = 'https://www.khanacademy.org/api/internal/exercises/math_topics_and_exercises';
        $json = file_get_contents($skills_url);
        $skill_data = json_decode($json)->exercises;
        $skill_select[] = '';
        foreach($skill_data as $skill=>$data){
              $skill_select[$data->name.'~'.$data->display_name]=$data->display_name;
        }
        asort($skill_select);

        $repeatarray = array();
        $repeatarray[] = $mform->createElement('select',
            'skillname',
            get_string('question', 'katest').' {no}',
            $skill_select,
            array('height'=>'64px','overflow'=>'hidden','width'=>'240px',
            'class'=>'katest-chosen-select')
        );
        $repeatarray[] = $mform->createElement('hidden', 'skillid',null);

        if ($this->_instance){
            $repeatno = $DB->count_records('katest_skills', array('katestid'=>$this->_instance));
            $repeatno += 2;
        } else {
            $repeatno = 5;
        }

        $repeateloptions = array();
        $repeateloptions['skillname']['default'] = '';
        // $repeateloptions['limit']['disabledif'] = array('limitanswers', 'eq', 0);
        // $repeateloptions['limit']['rule'] = 'numeric';
        // $repeateloptions['limit']['type'] = PARAM_INT;

        // $repeateloptions['option']['helpbutton'] = array('choiceoptions', 'choice');
        // $mform->setType('option', PARAM_CLEANHTML);
        //
        $mform->setType('skillid', PARAM_INT);

        $this->repeat_elements($repeatarray, $repeatno,
                    $repeateloptions, 'option_repeats', 'option_add_fields', 3, null, true);

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values){
        global $DB;
        if(!empty($this->_instance) && ($skills = $DB->get_records('katest_skills',
              array('katestid'=>$this->_instance), 'position'))){

            foreach($skills as $key => $value){
                $default_values['skillname['.$value->position.']'] = $value->skillname;
                $default_values['skillid['.$value->position.']'] = $value->id;
            }

        }
    }
}
