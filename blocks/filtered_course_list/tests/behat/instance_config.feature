@block @block_filtered_course_list @block_filtered_course_list_instance_config
Feature: Each instance of the block can have custom settings
  In order to configure a Filtered course list block instance
  As an admin
  I need to add and then configure a block

  @javascript
  Scenario: Renaming a block
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Test  | 0        | test     |
      | Cat 1 | test     | cat1     |
      | Cat 2 | test     | cat2     |
    And the following "courses" exist:
      | fullname  | shortname | category |
      | Course 11 | course11  | cat1     |
      | Course 12 | course12  | cat1     |
      | Course 13 | course13  | cat1     |
      | Course 21 | course21  | cat2     |
      | Course 22 | course22  | cat2     |
      | Course 23 | course23  | cat2     |
      | Test      | test      | test     |
    And the following "course enrolments" exist:
      | user  | course   | role    |
      | admin | course11 | student |
      | admin | course12 | student |
      | admin | course13 | student |
      | admin | course21 | student |
      | admin | course22 | student |
      | admin | course23 | student |
      | admin | test     | student |
    And I set the multiline "block_filtered_course_list" "filters" setting as admin to:
    """
    category | collapsed | 0
    """
    And the following config values are set as admin:
      | managerview | own | block_filtered_course_list |
    And the following "blocks" exist:
      | blockname            | contextlevel | reference |
      | filtered_course_list | Course       | test      |
    When I log in as "admin"
    And I am on site homepage
    And I follow "Test"
    Then I should see "Filtered course list"
    And I should see "Test"
    And I should see "Cat 1"
    And I should see "Cat 2"
    And I should not see "Course 12"
    When I turn editing mode on
    And I configure the "Filtered course list" block
    And I set the following fields to these values:
      | config_title | My custom title |
    And I set the field "config_filters" to multiline:
    """
    """
    And I press "Save changes"
    Then I should see "My custom title"
    And I should see "Test"
    And I should see "Cat 1"
    And I should see "Cat 2"
    And I should not see "Course 12"
    When I configure the "My custom title" block
    And I set the following fields to these values:
      | config_title | My custom title |
    And I set the field "config_filters" to multiline:
    """
    regex | exp | Doubles | (\d)\1$
    """
    And I press "Save changes"
    Then I should see "My custom title" in the ".block_filtered_course_list" "css_element"
    And I should see "Doubles"
    And I should see "Course 11"
    And I should see "Course 22"
    And I should not see "Course 12"
    And I should not see "Where this block appears"
    And I should not see "Cat 1"
    When I add the "Filtered course list" block
    And I wait until ".block_filtered_course_list" "css_element" exists
    Then I should see "Filtered course list"
    And I should see "Test"
    And I should see "Cat 1"
    And I should see "Cat 2"
    And I should not see "Course 12"
    When I follow "Doubles"
    Then I should not see "Course 11"
    When I follow "Cat 1"
    Then I should see "Course 12"
