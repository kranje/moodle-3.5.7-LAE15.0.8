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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot. '/enrol/flatfile/lib.php');

use local_hide_inactive_courses\task\hide_courses;

/**
 * Tests for the Hide Inactive Courses plugin.
 *
 * @package    local_hide_inactive_courses
 * @copyright  2018 onwards Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_hide_inactive_courses_testcase extends advanced_testcase {
    /**
     * Check email contents
     *
     * @param object $email Object containing the email data to check
     * @param array $body Target strings in email body
     * @param string $subject Target email subject
     * @param string $to Target 'to' address
     *
     * @return void
     */
    public function emailhas($email, $body, $subject, $to) {
        $this->assertContains('Auto-Submitted: auto-generated', $email->header);
        $this->assertContains('noreply@', $email->from);
        foreach ($body as $s) {
            $this->assertContains($s, preg_replace('/\s*\n\s*/', ' ', $email->body));
        }
        $this->assertContains($subject, $email->subject);
        $this->assertContains($to, $email->to);
    }

    /**
     * Do email alerts send to instructors on course auto-hide?
     *
     * @return void
     */
    public function test_email_alerts() {
        $this->resetAfterTest();
        global $DB;

        // Test teacher user.
        $teacher = $this->getDataGenerator()->create_user([
            'username'  => 'teacher',
            'email'     => 'teacher@example.com',
            'firstname' => 'Teacher',
            'lastname'  => 'Smith',
        ]);

        // Test course.
        $course = $this->getDataGenerator()->create_course([
            'shortname' => 'testcourse',
            'fullname' => 'Elementary Paste Eating'
        ]);

        // Manual enrolment entry.
        $manualenrol = $DB->get_record('enrol', ['enrol' => 'manual', 'courseid' => $course->id]);

        // Manual enrolment plugin (will use manual enrolment entry to enrol users in course context).
        $enrolplugin = new enrol_manual_plugin();

        // Course context for use in enrolments.
        $coursecontext = \context_course::instance($course->id);

        // Enrol teacher.
        $enrolplugin->enrol_user($manualenrol, $teacher->id, 3);

        $task = new hide_courses();

        $sink = $this->redirectEmails();
        $task->execute();
        $sink->close();
        $results = $sink->get_messages();

        $this->assertEquals(1, count($results));

        $body = [
            'Dear Teacher',
            "Your course 'Elementary Paste Eating' has been set to hidden because no users have accessed it for a long time."
        ];
        $subject = 'Elementary Paste Eating has been automatically hidden due to inactivity';
        $this->emailHas($results[0], $body, $subject, 'teacher@');

        set_config('email_onoff', 0, 'local_hide_inactive_courses');

        $sink = $this->redirectEmails();
        $task->execute();
        $sink->close();
        $results = $sink->get_messages();

        $this->assertEquals(0, count($results));
    }
}
