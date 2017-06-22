@block @block_filtered_course_list @block_filtered_course_list_completion
Feature: We can filter courses by completion status
    In order to filter courses by completion status
    As a moodle administrator
    I need to add completion filters to the block configuration

    @javascript
    Scenario: filtering by completion status
        Given the following "courses" exist:
            | fullname | shortname | enablecompletion |
            | Course 1 | course1   | 1                |
            | Course 2 | course2   | 1                |
            | Test     | test      | 0                |
        And the following "users" exist:
            | username |
            | testuser |
            | teacher  |
        And the following "course enrolments" exist:
            | user     | course  | role           |
            | teacher  | course1 | editingteacher |
            | testuser | course1 | student        |
            | testuser | course2 | student        |
            | testuser | test    | student        |
        And the following "blocks" exist:
            | blockname            | contextlevel | reference |
            | filtered_course_list | Course       | test      |
        And I set the multiline "block_filtered_course_list" "filters" setting as admin to:
          """
          completion | collapsed | Completed courses  | complete
          completion | collapsed | Incomplete courses | incomplete
          """
        And the following config values are set as admin:
          | enablecompletion | 0 |
        When I log in as "testuser"
        And I am on site homepage
        And I follow "Test"
        Then I should see "Filtered course list"
        And I should not see "Completed courses"
        And I should not see "Incomplete courses"
        Given the following config values are set as admin:
          | enablecompletion | 1 |
        When I reload the page
        Then I should see "Filtered course list"
        And I should see "Incomplete courses"
        When I follow "Incomplete courses"
        Then "Course 1" "link" in the ".block_filtered_course_list" "css_element" should be visible
        And "Course 2" "link" in the ".block_filtered_course_list" "css_element" should be visible
        And I should not see "Completed courses"
        And "Test" "link" in the ".block_filtered_course_list" "css_element" should not be visible
        Given I log out
        And I log in as "teacher"
        And I am on site homepage
        And I follow "Course 1"
        And I navigate to "Course completion" node in "Course administration"
        And I expand all fieldsets
        And I set the field "Teacher" to "1"
        And I press "Save changes"
        And I turn editing mode on
        And I add the "Course completion status" block
        And I follow "View course report"
        And I follow "Click to mark user complete"
        And I wait "1" seconds
        And I run the scheduled task "core\task\completion_regular_task"
        And I am on site homepage
        And I log out
        When I log in as "testuser"
        And I am on site homepage
        And I follow "Test"
        Then I should see "Completed courses"
        When I follow "Completed courses"
        Then "Course 1" "link" in the ".block_filtered_course_list" "css_element" should be visible
        And "Course 2" "link" in the ".block_filtered_course_list" "css_element" should not be visible
        When I follow "Completed courses"
        And I follow "Incomplete courses"
        Then "Course 1" "link" in the ".block_filtered_course_list" "css_element" should not be visible
        And "Course 2" "link" in the ".block_filtered_course_list" "css_element" should be visible
