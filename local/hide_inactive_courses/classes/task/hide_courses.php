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
 * Scheduled task to hide inactive courses.
 *
 * @package    local_hide_inactive_courses
 * @copyright  2018 onwards Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hide_inactive_courses\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/course/lib.php');

use stdClass;

/**
 * Scheduled task (cron task) that checks all courses, and if they haven't been accessed within the time limit, hides them.
 *
 * @copyright  2018 onwards Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hide_courses extends \core\task\scheduled_task {

    /**
     * Returns name of scheduled task.
     *
     * @return string
     */
    public function get_name() : string {
        return get_string('hide_courses_task', 'local_hide_inactive_courses');
    }

    /**
     * Find all courses with no activity inside time limit, hide them,
     * and send out email alerts (if enabled).
     *
     * @return string
     */
    public function execute() {
        global $DB;

        // Get all courses.
        $courses = $DB->get_records_select(
          'course',
          'id > 1'
        );

        // Loop through them and do stuff.
        foreach ($courses as $course) {
            $limit = get_config('local_hide_inactive_courses', 'limit');
            $t = time() - $limit;

            // Get all accesses to this course from users enrolled in the course more recent than $t.
            $sql = "SELECT ac.*";
            $sql .= " FROM {user_lastaccess} ac";
            $sql .= " JOIN {course} c ON ac.courseid=c.id";
            $sql .= " JOIN {enrol} e ON c.id=e.courseid";
            $sql .= " JOIN {user_enrolments} ue ON e.id=ue.enrolid";
            $sql .= " JOIN {user} u ON u.id=ue.userid";
            $sql .= " WHERE c.id=$course->id";
            $sql .= " AND ac.timeaccess > $t";
            $sql .= " AND ac.userid=u.id";
            $accesses = $DB->get_records_sql($sql);

            if (count($accesses) == 0) {
                // Hide course.
                course_change_visibility($course->id, false);

                // Trigger custom event.
                $context = $DB->get_record('context', ['instanceid' => $course->id, 'contextlevel' => 50]);
                $event = \local_hide_inactive_courses\event\course_auto_hidden::create([
                    'contextid' => $context->id,
                    'other' => [
                        'coursename' => $course->fullname
                    ]
                ]);
                $event->trigger();

                // If email is turned off, abort now.
                if (! get_config('local_hide_inactive_courses', 'email_onoff')) {
                    return;
                }

                // Find users with Teacher role.
                $roleassignments = $DB->get_records(
                    'role_assignments',
                    [
                        'contextid' => $context->id,
                        'roleid' => 3
                    ]
                );

                // If there are teachers, build an email and send it to each of them.
                if (count($roleassignments) > 0) {
                    $noreplyuser = \core_user::get_noreply_user();
                    $from = new stdClass();
                    $from->customheaders = 'Auto-Submitted: auto-generated';
                    $from->maildisplay = true; // Required to prevent Notice.
                    $from->email = $noreplyuser->email; // Required to prevent Notice.

                    // Get email content and subject line.
                    $message = get_config('local_hide_inactive_courses', 'email_content');
                    $subject = get_config('local_hide_inactive_courses', 'email_subject');

                    // For each instructor, customize and send the email.
                    foreach ($roleassignments as $roleassignment) {
                        // Establish patterns and replaces.
                        $recipient = $DB->get_record('user', ['id' => $roleassignment->userid]);
                        $replace = [
                            '/\{RECIPIENT\}/' => fullname($recipient),
                            '/\{COURSE\}/' => $course->fullname,
                        ];

                        // Replace patterns in both subject and content.
                        $subject = preg_replace(array_keys($replace), array_values($replace), $subject);
                        $message = preg_replace(array_keys($replace), array_values($replace), $message);

                        // Send.
                        email_to_user($recipient, $from, $subject, $message);
                    }
                }
            }
        }
    }
}
