@block @block_filtered_course_list @block_filtered_course_list_external
Feature: External filters can be scanned and used
  In order to test external filter pluggability
  As an admin
  I need to set up the external filter

  @javascript
  Scenario: Testing the external filter
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | course1   |
      | Course 2 | course2   |
    Given the following "users" exist:
      | username |
      | testuser |
    Given the following "blocks" exist:
      | blockname            | contextlevel | reference |
      | filtered_course_list | Course       | course1   |
    Given the following "course enrolments" exist:
      | user     | course  | role           |
      | testuser | course1 | editingteacher |
    And I log in as "admin"
    And I am on site homepage
    And I navigate to "Plugins > Blocks > Filtered course list" in site administration
    Then "input[type='checkbox'][id*='id_s_block_filtered_course_list_externalfilters_test|block_filtered_course_list|/blocks/filtered_course_list/tests/behat/data']" "css_element" should exist
    And I click on "input[type='checkbox'][id*='id_s_block_filtered_course_list_externalfilters_test|block_filtered_course_list|/blocks/filtered_course_list/tests/behat/data']" "css_element"
    And I press "Save changes"
    And I set the multiline FCL "filters" setting as admin to:
    """
    test | expanded | Test Filter
    """
    And I log out

    Given I log in as "testuser"
    And I am on site homepage
    And I follow "Course 1"
    Then I should see "Filtered course list"
    And I should see "Test Filter" in the ".block_filtered_course_list" "css_element"
    And I should see "Course 1" in the ".block_filtered_course_list" "css_element"
    And I should not see "Course 2" in the ".block_filtered_course_list" "css_element"
    And I log out

    Given I log in as "admin"
    And I am on site homepage
    And I navigate to "Plugins > Blocks > Filtered course list" in site administration
    And I click on "input[type='checkbox'][id*='id_s_block_filtered_course_list_externalfilters_test|block_filtered_course_list|/blocks/filtered_course_list/tests/behat/data']" "css_element"
    And I press "Save changes"
    And I log out

    Given I log in as "testuser"
    And I am on site homepage
    And I follow "Course 1"
    Then I should see "Filtered course list"
    And I should not see "Test Filter" in the ".block_filtered_course_list" "css_element"
