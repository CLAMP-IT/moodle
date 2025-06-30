@report @report_roster @report_roster_fields
Feature: An administrator may configure the displayed profile fields
  In order to display different profile fields
  As an administrator
  I need to set some settings

  @javascript
  Scenario: Test configurable profile field display
    Given I create a dummy custom profile field
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                 |
      | hpotter  | Harry     | Potter   | hpotter@hogwarts.wiz  |
      | hgranger | Hermione  | Granger  | hgranger@hogwarts.wiz |
      | rweasley | Runil     | Wazlib   | rweasley@hogwarts.wiz |
    And I set the custom profile field "test_custom_field" to "pottertest" for user "hpotter"
    And I set the custom profile field "test_custom_field" to "grangertest" for user "hgranger"
    And I set the custom profile field "test_custom_field" to "weasleytest" for user "rweasley"
    And the following "course enrolments" exist:
      | user     | course | role    |
      | hpotter  | C1     | student |
      | hgranger | C1     | student |
      | rweasley | C1     | student |
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Roster" "link"
    Then I should see "Harry Potter"
    Then I should see "Hermione Granger"
    Then I should see "Runil Wazlib"

    Given I set report_roster/fields to:
    """
    email

    profile_field_test_custom_field

    """
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Roster" "link"
    Then I should not see "Harry Potter"
    Then I should not see "Hermione Granger"
    Then I should not see "Runil Wazlib"
    Then I should see "hpotter@hogwarts.wiz"
    Then I should see "hgranger@hogwarts.wiz"
    Then I should see "rweasley@hogwarts.wiz"
    Then I should see "pottertest"
    Then I should see "grangertest"
    Then I should see "weasleytest"
