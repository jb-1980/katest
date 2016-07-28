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
 * Khan Academy Test external services.
 *
 * @package    mod_katest
 * @copyright  2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(

    'mod_katest_post_grade' => array(
        'classname'   => 'mod_katest\external',
        'methodname'  => 'post_grade',
        'classpath'   => '',
        'description' => 'User Khan API to set a grade for user',
        'type'        => 'write',
        'capabilities'=> '',
    ),

    'mod_katest_check_password' => array(
        'classname'   => 'mod_katest\external',
        'methodname'  => 'check_password',
        'classpath'   => '',
        'description' => 'Authenticates for a test that has an assigned password',
        'type'        => 'read',
        'capabilities'=> '',
    )
);