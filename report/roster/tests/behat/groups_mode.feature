@report @report_roster
Feature: In the roster report a teacher may filter students by group
  In order to filter students by group
  As a teacher
  I need to view the roster report

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1 |
    And the following "groups" exist:
      | name | course | idnumber |
      | Group 1 | C1 | G1 |
      | Group 2 | C1 | G2 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Terry | Teacher | teacher1@example.com |
      | student1 | Sally | Student | student1@example.com |
      | student2 | Sean | Student | student2@example.com |
      | student3 | Steve | Student | student3@example.com |
      | student4 | Serena | Student | student4@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
      | student3 | C1 | student |
      | student4 | C1 | student |
    And the following "group members" exist:
      | user | group |
      | student1 | G1 |
      | student2 | G1 |
      | student3 | G2 |
      | student4 | G2 |

  @javascript
  Scenario: Filter by groups
    Given I log in as "teacher1"
    And I follow "Course 1"
    And I navigate to "Roster" node in "Course administration > Reports"
    And I should see "Sally Student"
    And I should see "Serena Student"
    And the "group" select box should contain "All users"
    And the "group" select box should contain "Group 1"
    And the "group" select box should contain "Group 2"
    And I set the field "group" to "Group 1"
    And I should see "Sean Student"
    And I should not see "Steve Student"
    And I set the field "group" to "Group 2"
    And I should see "Steve Student"
    And I should not see "Sean Student"
    And I set the field "group" to "All users"
    And I should see "Sean Student"
    And I should see "Steve Student"
