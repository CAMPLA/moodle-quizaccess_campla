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
 * Global configuration settings for the quizaccess_campla plugin.
 *
 * @package    quizaccess_campla
 * @author     Luca Bösch <luca.boesch@bfh.ch>
 * @copyright  2024 BFH Bern University of Applied Sciences
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $ADMIN;

if ($hassiteconfig) {
    $settings->add(new admin_setting_heading(
        'quizaccess_campla/settingnotification',
        '',
        $OUTPUT->notification(get_string('settingnotification', 'quizaccess_campla'), 'warning')));


    $settings->add(new admin_setting_configtext('quizaccess_campla/camplabasisurl',
        get_string('camplabasisurl', 'quizaccess_campla'),
        get_string('camplabasisurl_desc', 'quizaccess_campla'),
        '',
        PARAM_URL));

    $settings->add(new admin_setting_configtext('quizaccess_campla/lmsid',
        get_string('camplalmsid', 'quizaccess_campla'),
        get_string('camplalmsid_desc', 'quizaccess_campla'),
        '',
        PARAM_RAW));

    $settings->add(new admin_setting_configtext('quizaccess_campla/secret',
        get_string('camplasecret', 'quizaccess_campla'),
        get_string('camplasecret_desc', 'quizaccess_campla'),
        '',
        PARAM_RAW));
}
