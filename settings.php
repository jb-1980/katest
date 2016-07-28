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
 * Khan Academy Test settings.
 *
 * @package    mod_katest
 * @copyright  2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_heading('katest_header',
                                         get_string('headerconfig', 'mod_katest'),
                                         get_string('descconfig', 'mod_katest')));

$settings->add(new admin_setting_configtext('katest/consumer_key',
                                                get_string('consumerkey', 'mod_katest'),
                                                get_string('descconsumerkey', 'mod_katest'),
                                                null));
$settings->add(new admin_setting_configtext('katest/consumer_secret',
                                                get_string('consumersecret', 'mod_katest'),
                                                get_string('descconsumersecret', 'mod_katest'),
                                                null));
