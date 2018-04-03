@block @block_clampmail
Feature: Send email
  In order to communicate effectively
  As someone who can send email
  I need the ability to send email

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
    And I log in as "teacher1"
    And I am on "Test Course" course homepage
    And I turn editing mode on
    And I add the "Quickmail" block
    And I log out

  @javascript
  Scenario: Internal navigation
    Given I log in as "teacher1"
    And I am on "Test Course" course homepage
    And I follow "Compose new email"
    And I follow "View drafts"
    Then I should see "You have no email drafts"
    When I press "Continue"
    And I follow "View history"
    Then I should see "You have no email history yet"
    When I press "Continue"
    Then I should see "Selected recipients"

  @javascript
  Scenario: Teacher sends an attachment to everyone
    Given I log in as "teacher1"
    And I am on "Test Course" course homepage
    And I follow "Compose new email"
    And I press "Add all"
    And I set the following fields to these values:
      | Subject | Doom At 11 |
      | Message | Salvation At Noon |
    And I upload "blocks/clampmail/tests/fixtures/uploadtext.txt" file to "Attachment(s)" filemanager
    When I press "Send email"
    Then I should see "View history"
    And I should see "uploadtext.txt"
    And I follow "Open email"
    And I should see "Student 3" in the "#mail_users" "css_element"
    And I should see "Student 4" in the "#mail_users" "css_element"
    And I should not see "Student 5" in the "#mail_users" "css_element"
