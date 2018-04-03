@block @block_clampmail
Feature: Email signatures
  In order to communicate effectively
  As someone who can send email
  I need the ability to manage signatures

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
    Then I should see "Manage signatures"

  @javascript
  Scenario: Add and remove signatures
    Given I follow "Manage signatures"
    And I set the following fields to these values:
      | Signature | Doom At 11 |
      | default_flag | 1 |
    And I press "Save changes"
    And I set the following fields to these values:
      | title | Primary signature |
    When I press "Save changes"
    Then I should see "Changes saved"
    When I set the following fields to these values:
      | id | New signature |
    Then I should see "New signature"
    When I set the following fields to these values:
      | title | Secondary signature |
      | Signature | Doom At 12 |
    When I press "Save changes"
    Then I should see "Changes saved"
    When I set the following fields to these values:
      | id | Primary signature (Default) |
    Then I should see "Doom At 11"
    When I set the following fields to these values:
      | id | Secondary signature |
    Then I should see "Doom At 12"
    When I set the following fields to these values:
      | default_flag | 1 |
    And I press "Save changes"
    Then I should see "Changes saved"
    When I set the following fields to these values:
      | id | Secondary signature (Default) |
    Then I should see "Doom At 12"
    When I press "Delete"
    Then I should see "Are you sure you want to delete Secondary signature?"
    When I press "Cancel"
    Then I should see "Doom At 12"
    When I press "delete"
    Then I should see "Are you sure you want to delete Secondary signature?"
    When I press "Continue"
    Then I should see "Changes saved"
