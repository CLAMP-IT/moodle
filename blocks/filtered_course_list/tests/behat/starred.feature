@block @block_filtered_course_list @block_filtered_course_list_starred
Feature: The starred courses filter displays a user's starred courses
  In order to add a starred courses filter
  As a Moodle administrator
  I need to add a line to the filter configuration field

  @javascript
  Scenario: Viewing a starred courses rubric
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | course1   |
      | Course 2 | course2   |
      | Course 3 | course3   |
    And the following "users" exist:
      | username |
      | student1 |
      | student2 |
    And the following "course enrolments" exist:
      | user     | course   | role    |
      | student1 | course1  | student |
      | student1 | course2  | student |
      | student2 | course2  | student |
    And the following "blocks" exist:
      | blockname            | contextlevel | reference            | pagetypepattern | defaultregion |
      | filtered_course_list | Course       | Acceptance test site | site-index      | site-pre      |
    And I set the multiline FCL "filters" setting as admin to:
    """
    starred | expanded | My starred courses
    """
    Given I am on the "My courses" page logged in as "student2"
    And I hover "//div[@class='card dashboard-card' and contains(.,'Course 2')]" "xpath_element"
    And I click on ".coursemenubtn" "css_element" in the "//div[@class='card dashboard-card' and contains(.,'Course 2')]" "xpath_element"
    And I click on "Star this course" "link" in the "//div[@class='card dashboard-card' and contains(.,'Course 2')]" "xpath_element"
    And I log out
    Given I am on the "My courses" page logged in as "student1"
    And I hover "//div[@class='card dashboard-card' and contains(.,'Course 1')]" "xpath_element"
    And I click on ".coursemenubtn" "css_element" in the "//div[@class='card dashboard-card' and contains(.,'Course 1')]" "xpath_element"
    And I click on "Star this course" "link" in the "//div[@class='card dashboard-card' and contains(.,'Course 1')]" "xpath_element"
    And I am on site homepage
    Then I should see "Filtered course list"
    And I should see "My starred courses" in the ".block_filtered_course_list" "css_element"
    And I should see "Course 1" in the ".block_filtered_course_list" "css_element"
    And I should not see "Course 2" in the ".block_filtered_course_list" "css_element"
    And I should not see "Course 3" in the ".block_filtered_course_list" "css_element"
    And the "class" attribute of ".tabpanel1 .fcl-icon" "css_element" should contain "fa-star"
    And I should see "Starred course" in the ".tabpanel1 .fcl-sr-text" "css_element"
    And the "class" attribute of ".tabpanel2 .fcl-icon" "css_element" should contain "fa-graduation-cap"
    When I follow "Other courses"
    Then I should see "Course" in the ".tabpanel2 .fcl-sr-text" "css_element"
    And I should not see "Starred" in the ".tabpanel2 .fcl-sr-text" "css_element"
    When I log out
    And I am on site homepage
    Then I should see "Filtered course list"
    And the "class" attribute of ".tabpanel1 .fcl-icon" "css_element" should contain "fa-graduation-cap"
    And I should see "Course" in the ".tabpanel1 .fcl-sr-text" "css_element"
    And I should not see "Starred" in the ".tabpanel1 .fcl-sr-text" "css_element"
    When the following config values are set as admin:
      | maxallcourse | 1 | block_filtered_course_list |
    And I am on site homepage
    Then the "class" attribute of ".tabpanel1 .fcl-icon" "css_element" should contain "fa-folder"
    And I should see "Category" in the ".tabpanel1 .fcl-sr-text" "css_element"
