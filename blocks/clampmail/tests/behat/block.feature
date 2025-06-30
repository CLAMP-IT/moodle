@block @block_clampmail
Feature: Navigate with block
  In order to use CLAMPMail
  In a course with blocks
  I need the ability to navigate from the block

  Background:
    Given the following "courses" exist:
      | fullname    | shortname | category | groupmode |
      | Test Course | CF101     | 0        | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email | emailstop |
      | teacher1 | Teacher | 1 | teacher1@example.com | 0 |
      | student1 | Student | 1 | student1@example.com | 0 |
      | student2 | Student | 2 | student2@example.com | 0 |
      | student3 | Student | 3 | student3@example.com | 0 |
      | student4 | Student | 4 | student4@example.com | 0 |
      | student5 | Student | 5 | student5@example.com | 1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | CF101 | editingteacher |
      | student1 | CF101 | student |
      | student2 | CF101 | student |
      | student3 | CF101 | student |
      | student4 | CF101 | student |

  @javascript
  Scenario: Add the block
    Given I log in as "teacher1"
    And I am on "Test Course" course homepage with editing mode on
    And I add the "CLAMPMail" block
    Then I should see "Compose new email"
    And I should see "View history"
    And I should see "View drafts"
    And I should see "Manage signatures"
    And I should see "Alternate emails"
    And I should see "Configuration"
    And I log out
    And I log in as "student1"
    And I am on "Test Course" course homepage
    Then I should not see "Compose new email"
    And I should not see "View history"
    And I should not see "View drafts"
    And I should not see "Manage signatures"
    And I should not see "Alternate emails"
    And I should not see "Configuration"
