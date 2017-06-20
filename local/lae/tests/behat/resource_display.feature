@local_lae @course @course_settings
Feature: Set a resource display preference at the course level
  In order to set a resource display preference at the course level
  As a teacher
  I need to modify the course settings.

  Background:
    Given the following "users" exist:
      | username |
      | teacher  |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user    | course   | role           |
      | teacher | C1 | editingteacher |
    And the following config values are set as admin:
      | displayoptions | 0,1,2,3,4,5,6 | resource |
    And I log in as "teacher"
    And I am on "Course 1" course homepage

  @javascript
  Scenario Outline: Setting the default will cause future uploads to have the expected display type
    Given I navigate to "Edit settings" node in "Course administration"
    And I expand all fieldsets
    And I set the following fields to these values:
      | filedisplaydefault | <displaytype> |
    And I press "Save and display"
    And I turn editing mode on
    And I add a "File" to section "1"
    When I expand all fieldsets
    Then I should see "<displaytype>" in the "#id_display" "css_element"

    Examples:
      | displaytype    |
      | Automatic      |
      | Embed          |
      | In frame       |
      | New window     |
      | Force download |
      | Open           |
      | In pop-up      |
