@local @local_hide_inactive_courses
Feature: Hide Inactive Courses

  @javascript
  Scenario: Has been visited by enrolled user (no auto hide)
    Given the following "courses" exist:
      | fullname        | shortname      | numsections | id |
      | Inactive Course | inactivecourse | 44          | 23 |
      | Active Course   | activecourse   | 44          | 24 |
    Given the following "users" exist:
      | username     | firstname | lastname |
      | testadmin    | Test      | Admin    |
      | testteacher  | Test      | Teacher  |
      | teststudent  | Test      | Student  |
    When I log in as "admin"
    And I am on site homepage
    And I follow "Active Course"
    And I follow "Participants"
    And I enrol "testteacher" user as "Teacher"
    And I enrol "teststudent" user as "Student"
    And I am on site homepage
    And I follow "Inactive Course"
    And I follow "Participants"
    And I enrol "testteacher" user as "Teacher"
    And I enrol "teststudent" user as "Student"
    And I log out

    When I log in as "testadmin"
    And I am on site homepage
    And I follow "Active Course"
    And I am on site homepage
    And I follow "Inactive Course"
    And I log out
    When I log in as "testteacher"
    And I am on site homepage
    And I follow "Active Course"
    And I am on site homepage
    And I follow "Inactive Course"
    And I log out
    When I log in as "teststudent"
    And I am on site homepage
    And I follow "Active Course"
    And I am on site homepage
    And I follow "Inactive Course"
    And I log out

    When I log in as "admin"
    And I run the scheduled task "\local_hide_inactive_courses\task\hide_courses"
    And I am on site homepage
    And I log out

    When I log in as "testteacher"
    And I am on site homepage
    Then I should see "Inactive Course"
    And I should see "Active Course"
    And I log out
    When I log in as "teststudent"
    And I am on site homepage
    Then I should see "Inactive Course"
    And I should see "Active Course"

  @javascript
  Scenario: Has not been visited by enrolled user (auto hide)
    Given the following "courses" exist:
      | fullname        | shortname      | numsections | id |
      | Inactive Course | inactivecourse | 44          | 23 |
      | Active Course   | activecourse   | 44          | 24 |
    Given the following "users" exist:
      | username     | firstname | lastname |
      | testadmin    | Test      | Admin    |
      | testteacher  | Test      | Teacher  |
      | teststudent  | Test      | Student  |
    When I log in as "admin"
    And I am on site homepage
    And I follow "Active Course"
    And I follow "Participants"
    And I enrol "testteacher" user as "Teacher"
    And I enrol "teststudent" user as "Student"
    And I am on site homepage
    And I follow "Inactive Course"
    And I follow "Participants"
    And I enrol "testteacher" user as "Teacher"
    And I enrol "teststudent" user as "Student"
    And I log out

    When I log in as "testadmin"
    And I am on site homepage
    And I follow "Active Course"
    And I am on site homepage
    And I follow "Inactive Course"
    And I log out
    When I log in as "testteacher"
    And I am on site homepage
    And I follow "Active Course"
    And I am on site homepage
    And I should see "Inactive Course"
    And I log out
    When I log in as "teststudent"
    And I am on site homepage
    And I follow "Active Course"
    And I am on site homepage
    And I should see "Inactive Course"
    And I log out

    When I log in as "admin"
    And I run the scheduled task "\local_hide_inactive_courses\task\hide_courses"
    And I am on site homepage
    When I navigate to "Reports > Events list" in site administration
    Then I should see "Course auto hidden"
    And I navigate to "Reports > Logs" in site administration
    And I set the field "menuedulevel" to "Other"
    And I set the field "menumodaction" to "Update"
    When I press "Get these logs"
    Then I should see "The course with id '"
    And I should see "' has been automatically hidden by the Hide Inactive Courses plugin."
    And I log out

    When I log in as "testteacher"
    And I am on site homepage
    Then I should see "Inactive Course"
    And I should see "Active Course"
    And I log out
    When I log in as "teststudent"
    And I am on site homepage
    Then I should not see "Inactive Course"
    And I should see "Active Course"
