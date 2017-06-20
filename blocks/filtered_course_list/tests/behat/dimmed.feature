@block @block_filtered_course_list @block_filtered_course_list_dimmed
Feature: Hidden courses and categories are dimmed for those who can see them
    In order to see dimmed courses and categories
    As an admin
    I need to have the ability to see hidden courses

    @javascript
    Scenario: Viewing the courses under a rubric
        Given the following "categories" exist:
            | name             | category | idnumber | visible |
            | Visible category | 0        | visible  | 1       |
            | Hidden category  | 0        | hidden   | 0       |
        And the following "courses" exist:
            | fullname  | shortname | category |
            | Visible 1 | visible1  | visible  |
            | Hidden  1 | hidden1   | visible  |
            | Visible 2 | visible2  | hidden   |
            | Hidden  2 | hidden2   | hidden   |
            | Test      | test      |          |
        And the following "course enrolments" exist:
            | user  | course   | role    |
            | admin | visible1 | student |
            | admin | hidden1  | student |
            | admin | visible2 | student |
            | admin | hidden2  | student |
        And the following "blocks" exist:
            | blockname            | contextlevel | reference |
            | filtered_course_list | Course       | test      |
        And the following config values are set as admin:
            | maxallcourse | 1 | block_filtered_course_list |
        And I set the multiline "block_filtered_course_list" "filters" setting as admin to:
          """
          category | expanded | 0
          """
        When I log in as "admin"
        And I am on site homepage
        And I follow "Test"
        Then the "class" attribute of "Visible category" "link" should not contain "dimmed"
        And the "class" attribute of "Visible category" "list_item" should contain "fcl-category-link"
        And the "class" attribute of "Hidden category" "link" should contain "dimmed"
        And the "class" attribute of "Hidden category" "list_item" should not contain "fcl-course-link"
