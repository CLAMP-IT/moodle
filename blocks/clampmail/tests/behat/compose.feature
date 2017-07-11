@block @block_clampmail
Feature: Send email
  In order to communicate effectively
  As someone who can send email
  I need the ability to send email

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Test Course | CF101 | 0 | 1 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
      | student2 | Student | 2 | student2@example.com |
      | student3 | Student | 3 | student3@example.com |
      | student4 | Student | 4 | student4@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | CF101 | editingteacher |
      | student1 | CF101 | student |
      | student2 | CF101 | student |
      | student4 | CF101 | student |
      | student3 | CF101 | student |
    And the following "groups" exist:
      | name | description | course | idnumber |
      | Group A | Group A | CF101 | GROUPA |
      | Group B | Group B | CF101 | GROUPB |
    And the following "group members" exist:
      | user | group |
      | student1 | GROUPA |
      | student2 | GROUPA |
      | student3 | GROUPB |
      | student4 | GROUPB |
    And I log in as "teacher1"
    And I am on "Test Course" course homepage
    And I turn editing mode on
    When I add the "Quickmail" block
    Then I should see "Compose new email"

  @javascript
  Scenario: Internal navigation
    Given I follow "Compose new email"
    And I follow "View drafts"
    Then I should see "You have no email drafts"
    When I press "Continue"
    And I follow "View history"
    Then I should see "You have no email history yet"
    When I press "Continue"
    Then I should see "Selected recipients"

  @javascript
  Scenario: Compose
    Given I follow "Compose new email"
    And I set the following fields to these values:
      | groups | Group B |
    And I press "Add"
    And I set the following fields to these values:
      | Subject | Doom At 11 |
      | Message | Salvation At Noon |
    And I upload "blocks/clampmail/tests/fixtures/uploadtext.txt" file to "Attachment(s)" filemanager
    When I press "Send email"
    Then I should see "View history"
    And I should see "uploadtext.txt"
    And I follow "Open email"
    Then I should see "Student 3" in the "#mail_users" "css_element"
    And I should see "Student 4" in the "#mail_users" "css_element"
