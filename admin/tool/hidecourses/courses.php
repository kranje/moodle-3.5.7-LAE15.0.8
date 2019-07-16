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
 * Show and hide courses.
 *
 * @package   tool_hidecourses
 * @copyright 2017 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/admin/tool/hidecourses/locallib.php');
require_once($CFG->dirroot.'/lib/coursecatlib.php');

$categoryid = required_param('category', PARAM_INT);
$action = required_param('action', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$category = \coursecat::get($categoryid);
$context = \context_coursecat::instance($categoryid);

// Ensure the user can be here.
require_login(0, false);
require_capability('tool/hidecourses:hidecourses', $context);
$returnurl = new \moodle_url('/course/management.php', array('categoryid' => $categoryid));

if ($confirm && isloggedin() && confirm_sesskey()) {
    $task = new \tool_hidecourses\task\hide_courses_task();
    $task->set_custom_data(
        array(
            'category' => $categoryid,
            'action' => $action
        )
    );
    \core\task\manager::queue_adhoc_task($task);
    redirect($returnurl, get_string('updatequeued', 'tool_hidecourses', $category->name));
}

// Current location.
$url = new \moodle_url('/admin/tool/hidecourses/courses.php',
    array(
        'category' => $categoryid,
        'action' => $action
    )
);

$customtext = new stdClass();
$customtext->category = $category->name;

switch ($action) {
    case TOOL_HIDECOURSES_ACTION_HIDE:
        $actionstring = new lang_string('hideallcourses', 'tool_hidecourses');
        $customtext->state = new lang_string('hidden', 'tool_hidecourses');
        break;
    case TOOL_HIDECOURSES_ACTION_SHOW:
        $actionstring = new lang_string('showallcourses', 'tool_hidecourses');
        $customtext->state = new lang_string('visible', 'tool_hidecourses');
        break;
    default:
        print_error('invalidactionid', 'tool_hidecourses', $returnurl);
        break;
}

$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_title(new lang_string('coursecatmanagement') . ': '. $actionstring);
$PAGE->set_heading($SITE->fullname);

// Confirmation URL.
$confirmurl = new \moodle_url('/admin/tool/hidecourses/courses.php',
    array(
        'category' => $categoryid,
        'action' => $action,
        'confirm' => 1,
        'sesskey' => sesskey()
    )
);

// Print page.
echo $OUTPUT->header();
echo $OUTPUT->heading($actionstring);
echo $OUTPUT->box_start('generalbox', 'notice');
echo html_writer::tag('p', get_string('changevisibilityconfirm', 'tool_hidecourses', $customtext));
echo $OUTPUT->single_button($confirmurl, get_string('confirm'), 'post');
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
