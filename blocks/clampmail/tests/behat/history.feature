@plugintype @block_clampmail
Feature: Email history
  In order to communicate effectively
  As someone who can send email
  I need the ability to review my history

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Test Course | CF101 | 0 | 1 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
      | student2 | Student | 2 | student2@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | CF101 | editingteacher |
      | student1 | CF101 | student |
      | student2 | CF101 | student |
    And I log in as "teacher1"
    And I am on "Test Course" course homepage
    And I turn editing mode on
    When I add the "Quickmail" block
    Then I should see "View history"

  @javascript
  Scenario: View and delete history
    Given I follow "View history"
    Then I should see "You have no email history yet"
    And I press "Continue"
    And I should see "Selected recipients"
    And I set the following fields to these values:
      | from_users | Student 1, Student 2 |
      | Subject | Hello World |
      | Message | Doom at 11 |
    And I press "Add"
    When I press "Send email"
    Then I should see "View history"
    And I should see "Hello World"
    When I follow "Open email"
    Then I should see "Selected recipients"
    And I should see "Doom at 11"
    When I set the following fields to these values:
      | Subject | Hello World Redux |
    And I press "Send email"
    Then I should see "View history"
    And I should see "Hello World Redux"
    And I log out
    And I log in as "admin"
    And I navigate to "Manage courses and categories" node in "Site administration > Courses"
    And I follow "Test Course"
    And I follow "View"
    And I follow "View history"
    And I set the field "userid" to "Teacher 1"
    Then I should see "Hello World Redux"
