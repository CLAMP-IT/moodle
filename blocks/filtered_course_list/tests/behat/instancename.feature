@block @block_filtered_course_list @block_filtered_course_list_instancename
Feature: Each instance of the block can have a custom name
    In order to rename a Filtered course list block
    As an admin
    I need to add and then configure a block

    @javascript
    Scenario: Renaming a block
        Given the following "courses" exist:
          | fullname | shortname |
          | Test     | test      |
        And I log in as "admin"
        And I am on site homepage
        And I follow "Test"
        And I turn editing mode on
        And I add the "Filtered course list" block
        And I wait until ".block_filtered_course_list" "css_element" exists
        And I configure the "Filtered course list" block
        And I set the following fields to these values:
            | config_title | My custom title |
        When I press "Save changes"
        Then I should see "My custom title" in the ".block_filtered_course_list" "css_element"
        And I should not see "Where this block appears"
