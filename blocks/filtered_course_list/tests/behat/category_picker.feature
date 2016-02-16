@block @block_filtered_course_list @settings @block_filterd_course_list_category_picker
Feature: Select category to filter by
    In order to select a category to filter by
    As admin
    I need to select an option from the "Categories" dropdown

    @javascript
    Scenario: Selecting a category to filter by from the settings page
        Given the following "categories" exist:
            | name  | category | idnumber |
            | Cat 1 | 0        | cat1     |
            | Cat 2 | 0        | cat2     |
        And the following "courses" exist:
            | fullname  | shortname | category |
            | Course 11 | course11  | cat1     |
            | Course 21 | course21  | cat2     |
        And the following "course enrolments" exist:
            | user  | course   | role    |
            | admin | course11 | student |
            | admin | course21 | student |
        And I log in as "admin"
        And the following config values are set as admin:
            | filtertype | categories | block_filtered_course_list |
            | adminview  | own        | block_filtered_course_list |
        And I navigate to "Filtered course list" node in "Site administration>Plugins>Blocks"
        And I press "Blocks editing on"
        And I add the "filtered_course_list" block
        When I navigate to "Filtered course list" node in "Site administration>Plugins>Blocks"
        Then I should see "Top" in the "#id_s_block_filtered_course_list_categories" "css_element"
        And "Cat 1" "link" in the ".block_filtered_course_list" "css_element" should be visible
        And "Cat 2" "link" in the ".block_filtered_course_list" "css_element" should be visible
        When I set the field "s_block_filtered_course_list_categories" to "Cat 1"
        And I click on "Save changes" "button"
        Then "Cat 1" "link" in the ".block_filtered_course_list" "css_element" should be visible
        And I should not see "Cat 2" in the ".block_filtered_course_list" "css_element"
