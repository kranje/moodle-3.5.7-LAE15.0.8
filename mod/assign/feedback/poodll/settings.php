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
 * This file defines the admin settings for this plugin
 *
 * @package   assignfeedback_poodll
 * @copyright 2013 Justin Hunt {@link http://www.poodll.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use assignfeedback_poodll\constants;

	//enable by default
	$settings->add(new admin_setting_configcheckbox(constants::M_COMPONENT . '/default',
                   new lang_string('default', constants::M_COMPONENT),
                   new lang_string('default_help', constants::M_COMPONENT), 1));
                   

	//Recorders
    $rec_options = array( constants::M_REPLYMP3VOICE => get_string("replymp3voice", constants::M_COMPONENT),
				constants::M_REPLYVIDEO => get_string("replyvideo", constants::M_COMPONENT),
				constants::M_REPLYWHITEBOARD => get_string("replywhiteboard", constants::M_COMPONENT),
				constants::M_REPLYSNAPSHOT => get_string("replysnapshot", constants::M_COMPONENT));
	$rec_defaults = array(constants::M_REPLYMP3VOICE  => 1);
	$settings->add(new admin_setting_configmulticheckbox(constants::M_COMPONENT . '/allowedrecorders',
						   get_string('allowedrecorders', constants::M_COMPONENT),
						   get_string('allowedrecordersdetails', constants::M_COMPONENT), $rec_defaults,$rec_options));
						   
	//show current feedback on feedback form
	$yesno_options = array( 0 => get_string("no", constants::M_COMPONENT),
				1 => get_string("yes", constants::M_COMPONENT));
	$settings->add(new admin_setting_configselect(constants::M_COMPONENT . '/showcurrentfeedback',
					new lang_string('showcurrentfeedback', constants::M_COMPONENT),
					new lang_string('showcurrentfeedbackdetails', constants::M_COMPONENT), 1, $yesno_options));

