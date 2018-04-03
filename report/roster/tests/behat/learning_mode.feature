@report @report_roster
Feature: In the roster report a teacher may toggle the display of names
  In order to toggle the display of names
  As a teacher
  I need to view the roster report

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Terry | Teacher | teacher1@example.com |
      | student1 | Sally | Student | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |

  @javascript
  Scenario: Toggle learning mode off and on
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Roster" node in "Course administration > Reports"
    Then I should see "Sally Student"
    And I should see "Learning mode off"
    When I press "Learning mode off"
    Then I should not see "Sally Student"
    And I should see "Learning mode on"
    When I press "Learning mode on"
    Then I should see "Sally Student"
    And I should see "Learning mode off"
