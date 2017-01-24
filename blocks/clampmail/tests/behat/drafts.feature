@block @block_clampmail
Feature: Email drafts
  In order to communicate effectively
  As someone who can send email
  I need the ability to save drafts

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
    And I follow "Test Course"
    And I turn editing mode on
    When I add the "Quickmail" block
    Then I should see "View drafts"

  @javascript
  Scenario: View and delete drafts
    Given I follow "View drafts"
    Then I should see "You have no email drafts"
    When I press "Continue"
    Then I should see "Selected recipients"
    And I set the following fields to these values:
      | from_users | Student 1, Student 2 |
    And I press "Add"
    And I set the following fields to these values:
      | Subject | Hello World |
      | Message | Salvation at noon |
    When I press "Save draft"
    Then I should see "Changes saved"
    When I follow "View drafts"
    Then I should see "Hello World"
    When I follow "Open email"
    Then I should see "Selected recipients"
    And I should see "Salvation at noon"
    When I set the following fields to these values:
      | Subject | Goodbye World |
    And I press "Save draft"
    Then I should see "Changes saved"
    And the field "subject" matches value "Goodbye World"
    When I follow "View drafts"
    Then I should see "Goodbye World"
    When I follow "Delete email"
    Then I should see "Goodbye World"
    When I press "Cancel"
    Then I should see "Goodbye World"
    When I follow "Delete email"
    And I press "Continue"
    Then I should see "You have no email drafts"
    When I press "Continue"
    Then I should see "Selected recipients"
    And the field "subject" matches value ""
