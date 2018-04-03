@block @block_clampmail
Feature: Alternate email addresses
  In order to communicate effectively
  As someone who can send email
  I need the ability to set an alternate email

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Test Course | CF101 | 0 | 1 |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Ted       | Teacher  | teacher1@example.com |
      | teacher2 | Terry     | Teacher  | teacher2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | CF101  | editingteacher |
      | teacher2 | CF101  | editingteacher |
    And I log in as "teacher1"
    And I am on "Test Course" course homepage
    And I turn editing mode on
    When I add the "Quickmail" block
    Then I should see "Alternate emails"

  @javascript
  Scenario: Add alternate email
    Given I follow "Alternate emails"
    Then I should see "No alternate emails found for Test Course"
    When I press "Continue"
    Then I should see "Email address"
    When I set the following fields to these values:
      | Email address | teacher1_alt@example.com |
    And I press "Save changes"
    Then I should see "Alternate address teacher1_alt@example.com has been saved"
    When I press "Continue"
    Then I should see "Waiting"
    And I log out
    And I log in as "teacher2"
    And I am on "Test Course" course homepage
    And I follow "Alternate emails"
    And I should see "teacher1_alt@example.com"
    And I follow "Delete"
    And I should see "Are you sure you want to delete teacher1_alt@example.com?"
    And I press "Continue"
    Then I should see "Changes saved"
