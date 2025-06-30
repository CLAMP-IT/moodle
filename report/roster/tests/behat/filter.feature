@report @report_roster @report_roster_filter
Feature: In the roster report a teacher may filter by various parameters
  In order to filter users
  As a teacher
  I need to view the roster report

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "groups" exist:
      | name    | course | idnumber |
      | Group 1 | C1     | G1       |
      | Group 2 | C1     | G2       |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher0 | POV       | Teacher  | teacher0@example.com |
      | teacher1 | Terry     | Teacher  | teacher1@example.com |
      | ta1      | Tanya     | TA       | ta1@example.com      |
      | ta2      | Travis    | TA       | ta2@example.com      |
      | student1 | Sally     | Student  | student1@example.com |
      | student2 | Sean      | Student  | student2@example.com |
      | student3 | Steve     | Student  | student3@example.com |
      | student4 | Serena    | Student  | student4@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher0 | C1     | editingteacher |
      | teacher1 | C1     | editingteacher |
      | ta1      | C1     | teacher        |
      | ta2      | C1     | teacher        |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
      | student4 | C1     | student        |
    And the following "group members" exist:
      | user     | group |
      | ta1      | G1    |
      | ta2      | G1    |
      | student1 | G1    |
      | student2 | G1    |
      | teacher1 | G2    |
      | student3 | G2    |
      | student4 | G2    |

  @javascript
  Scenario: Filter by role
    Given I log in as "teacher0"
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Roster" "link"
    And I should see "Sally Student"
    And I should see "Sean Student"
    And I should see "Steve Student"
    And I should see "Serena Student"
    And I should see "Terry Teacher"
    And I should see "Tanya TA"
    And I should see "Travis TA"
    And the "role" select box should contain "All users"
    And the "role" select box should contain "Teacher"
    And the "role" select box should contain "Non-editing teacher"
    And the "role" select box should contain "Student"
    And I set the field "role" to "Teacher"
    And I should see "Terry Teacher"
    And I should not see "Steve Student"
    And I should not see "Travis TA"
    And I set the field "role" to "Non-editing teacher"
    And I should see "Tanya TA"
    And I should see "Travis TA"
    And I should not see "Terry Teacher"
    And I should not see "Sean Student"
    And I set the field "role" to "Student"
    And I should see "Sally Student"
    And I should see "Sean Student"
    And I should see "Steve Student"
    And I should see "Serena Student"
    And I should not see "Terry Teacher"
    And I should not see "Travis TA"
    And I set the field "role" to "All users"
    And I should see "Sally Student"
    And I should see "Sean Student"
    And I should see "Steve Student"
    And I should see "Serena Student"
    And I should see "Terry Teacher"
    And I should see "Tanya TA"
    And I should see "Travis TA"

  @javascript
  Scenario: Filter by groups
    Given I log in as "teacher0"
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Roster" "link"
    And I should see "Terry Teacher"
    And I should see "Tanya TA"
    And I should see "Travis TA"
    And I should see "Sally Student"
    And I should see "Sean Student"
    And I should see "Steve Student"
    And I should see "Serena Student"
    And the "group" select box should contain "All users"
    And the "group" select box should contain "Group 1"
    And the "group" select box should contain "Group 2"
    And I set the field "group" to "Group 1"
    And I should see "Tanya TA"
    And I should see "Travis TA"
    And I should see "Sally Student"
    And I should see "Sean Student"
    And I should not see "Steve Student"
    And I should not see "Serena Student"
    And I should not see "Terry Teacher"
    And I set the field "group" to "Group 2"
    And I should see "Steve Student"
    And I should see "Serena Student"
    And I should see "Terry Teacher"
    And I should not see "Tanya TA"
    And I should not see "Travis TA"
    And I should not see "Sally Student"
    And I should not see "Sean Student"
    And I set the field "group" to "All users"
    And I should see "Terry Teacher"
    And I should see "Tanya TA"
    And I should see "Travis TA"
    And I should see "Sally Student"
    And I should see "Sean Student"
    And I should see "Steve Student"
    And I should see "Serena Student"

  @javascript
  Scenario: Filter by role and group
    Given I log in as "teacher0"
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Roster" "link"
    And I should see "Terry Teacher"
    And I should see "Tanya TA"
    And I should see "Travis TA"
    And I should see "Sally Student"
    And I should see "Sean Student"
    And I should see "Steve Student"
    And I should see "Serena Student"
    And I set the field "role" to "Non-editing teacher"
    And I set the field "group" to "Group 1"
    And I should not see "Terry Teacher"
    And I should see "Tanya TA"
    And I should see "Travis TA"
    And I should not see "Sally Student"
    And I should not see "Sean Student"
    And I should not see "Steve Student"
    And I should not see "Serena Student"
    And I set the field "role" to "Student"
    And I should not see "Terry Teacher"
    And I should not see "Tanya TA"
    And I should not see "Travis TA"
    And I should see "Sally Student"
    And I should see "Sean Student"
    And I should not see "Steve Student"
    And I should not see "Serena Student"
    And I set the field "group" to "Group 2"
    And I should not see "Terry Teacher"
    And I should not see "Tanya TA"
    And I should not see "Travis TA"
    And I should not see "Sally Student"
    And I should not see "Sean Student"
    And I should see "Steve Student"
    And I should see "Serena Student"
    And I set the field "role" to "Teacher"
    And I should see "Terry Teacher"
    And I should not see "Tanya TA"
    And I should not see "Travis TA"
    And I should not see "Sally Student"
    And I should not see "Sean Student"
    And I should not see "Steve Student"
    And I should not see "Serena Student"
