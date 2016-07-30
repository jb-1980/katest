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
 * Renderer class for KA Test.
 *
 * @package    mod_katest
 * @copyright  2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_katest\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;

/**
 * Renderer class for KA Test.
 *
 * @package    mod_katest
 * @copyright  2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Defer to template.
     *
     * @param index_page $page
     *
     * @return string html for the page
     */
    public function render_index($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_katest/index_page', $data);
    }

    /**
     * Defer to template.
     *
     * @param password $page
     *
     * @return string html for the page
     */
    public function render_password($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_katest/password', $data);
    }

    /**
     * Defer to template.
     *
     * @param khan_authenticate $page
     *
     * @return string html for the page
     */
     public function render_khan_authenticate($page) {
         $data = $page->export_for_template($this);
         return parent::render_from_template('mod_katest/khan_authenticate', $data);
     }

     /**
      * Defer to template.
      *
      * @param results $page
      *
      * @return string html for the page
      */
      public function render_results($page) {
          $data = $page->export_for_template($this);
          return parent::render_from_template('mod_katest/results', $data);
      }

}
