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
 * Hide Inactive Courses plugin settings.
 *
 * @package    local_hide_inactive_courses
 * @copyright  2018 onwards Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $pluginname = get_string('pluginname', 'local_hide_inactive_courses');
    $settings = new admin_settingpage('local_hide_inactive_courses', $pluginname);

    $settings->add(new admin_setting_configduration('local_hide_inactive_courses/limit',
        get_string('limit_desc', 'local_hide_inactive_courses'),
        get_string('limit_subdesc', 'local_hide_inactive_courses'),
        $defaultsetting = 31536000,
        $defaultunit = 604800)
    );

    $settings->add(new admin_setting_configcheckbox('local_hide_inactive_courses/email_onoff',
        get_string('email_onoff_desc', 'local_hide_inactive_courses'),
        get_string('email_onoff_subdesc', 'local_hide_inactive_courses'),
        1)
    );

    $settings->add(new admin_setting_configtext('local_hide_inactive_courses/email_subject',
        get_string('email_subject_desc', 'local_hide_inactive_courses'),
        '',
        get_string('email_subject_default', 'local_hide_inactive_courses'))
    );

    $settings->add(new admin_setting_configtextarea('local_hide_inactive_courses/email_content',
        get_string('email_content_desc', 'local_hide_inactive_courses'),
        '',
        get_string('email_content_default', 'local_hide_inactive_courses'))
    );

    $ADMIN->add('localplugins', $settings);
}
