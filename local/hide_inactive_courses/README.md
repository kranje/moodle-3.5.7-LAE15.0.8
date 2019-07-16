# Moodle Local Hide Inactive Courses

This plugin adds a scheduled task which automatically hides any courses which have not been accessed for a configurable time period. It also sends an email alert to any Teachers in a course when that course is auto-hidden.

## Installation

Simply add the plugin to your Moodle install. Remember to enable the scheduled task to turn on plugin functionality.

## Settings

You can configure the following aspects of the plugin:
- The time limit for course inactivity, i.e., how long ago the last course access must have been for the course to count as inactive (default 1 year)
- Whether or not to send email alerts to teachers in auto-hidden courses
- The content of email alerts, including dynamic replacement flags (see default email content for example)

## Details

- The plugin only counts course accesses from users who are enrolled in the course
- The cron task is set to run once every day by default
- The time limit config represents a minimum 'time ago' value for course accesses. For example, with the default time limit of 1 year, a course will be considered inactive if the most recent course access is from a year ago or more; or to put it another way, if there are no course accesses within the last year.
