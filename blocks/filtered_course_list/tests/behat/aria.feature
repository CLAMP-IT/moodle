@block @block_filtered_course_list @block_filtered_course_list_aria
Feature: The block includes ARIA support
    In order to enjoy expanded accessibility
    As a user
    I need to take advantage of ARIA roles, states, and properties

    @javascript
    Scenario: Checking ARIA support
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
        And the following "users" exist:
            | username |
            | testuser |
        And the following "course enrolments" exist:
            | user     | course   | role    |
            | testuser | course11 | student |
            | testuser | course12 | student |
            | testuser | course13 | student |
            | testuser | course21 | student |
            | testuser | course22 | student |
            | testuser | course23 | student |
            | testuser | test     | student |
        And I log in as "admin"
        And I am on site homepage
        And I follow "Test"
        And I turn editing mode on
        And I add the "Filtered course list" block
        And I set the multiline "block_filtered_course_list" "filters" setting as admin to:
          """
          shortname | collapsed | Current courses | 3
          shortname | expanded  | Future courses  | 2
          """
        And I log out
        When I log in as "testuser"
        And I am on site homepage
        And I follow "Test"
        And I wait until ".block_filtered_course_list" "css_element" exists
        Then I should see "Filtered course list"
        And the "role" attribute of ".block_filtered_course_list.block" "css_element" should contain "navigation"
        And the "role" attribute of ".block_filtered_course_list .tablist" "css_element" should contain "tablist"
        And the "aria-multiselectable" attribute of ".block_filtered_course_list .tablist" "css_element" should contain "true"
        And the "id" attribute of ".block_filtered_course_list .course-section.tab1" "css_element" should contain "tab1"
        And the "aria-controls" attribute of ".block_filtered_course_list .course-section.tab1" "css_element" should contain "panel1"
        And the "aria-expanded" attribute of ".block_filtered_course_list .course-section.tab1" "css_element" should contain "false"
        And the "aria-selected" attribute of ".block_filtered_course_list .course-section.tab1" "css_element" should contain "false"
        And the "role" attribute of ".block_filtered_course_list .course-section.tab1" "css_element" should contain "tab"
        And the "aria-labelledby" attribute of ".block_filtered_course_list .tabpanel1" "css_element" should contain "tab1"
        And the "aria-hidden" attribute of ".block_filtered_course_list .tabpanel1" "css_element" should contain "true"
        And the "role" attribute of ".block_filtered_course_list .tabpanel1" "css_element" should contain "tabpanel"
        And the "aria-controls" attribute of ".block_filtered_course_list .course-section.tab2" "css_element" should contain "panel2"
        And the "aria-expanded" attribute of ".block_filtered_course_list .course-section.tab2" "css_element" should contain "true"
        And the "aria-selected" attribute of ".block_filtered_course_list .course-section.tab2" "css_element" should contain "false"
        And the "aria-labelledby" attribute of ".block_filtered_course_list .tabpanel2" "css_element" should contain "tab2"
        And the "aria-hidden" attribute of ".block_filtered_course_list .tabpanel2" "css_element" should contain "false"
        When I follow "Current courses"
        Then the "aria-expanded" attribute of ".block_filtered_course_list .course-section.tab1" "css_element" should contain "true"
        And the "aria-selected" attribute of ".block_filtered_course_list .course-section.tab1" "css_element" should contain "true"
        And the "aria-hidden" attribute of ".block_filtered_course_list .tabpanel1" "css_element" should contain "false"
        And the "aria-selected" attribute of ".block_filtered_course_list .course-section.tab2" "css_element" should contain "false"
        When I follow "Future courses"
        Then the "aria-expanded" attribute of ".block_filtered_course_list .course-section.tab2" "css_element" should contain "false"
        And the "aria-selected" attribute of ".block_filtered_course_list .course-section.tab2" "css_element" should contain "true"
        And the "aria-hidden" attribute of ".block_filtered_course_list .tabpanel2" "css_element" should contain "true"
        And the "aria-selected" attribute of ".block_filtered_course_list .course-section.tab1" "css_element" should contain "false"
