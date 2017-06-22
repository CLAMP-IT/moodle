@block @block_filtered_course_list @block_filtered_course_list_sort
Feature: Courses within a rubric can be sorted by various fields
  In order to sort courses within a rubric
  As an administrator
  I need to change some value on the Filtered Course List settings page

  Background:
    Given the following "courses" exist:
      | fullname   | shortname   | idnumber   | startdate |
      | A Fullname | b_shortname | c_idnumber | 141000000 |
      | B Fullname | c_shortname | d_idnumber | 140900000 |
      | C Fullname | d_shortname | e_idnumber | 140800000 |
      | D Fullname | e_shortname | a_idnumber | 140700000 |
      | E Fullname | a_shortname | b_idnumber | 140700000 |
      | Test       | test        | test       | 141000000 |
    And the following "users" exist:
      | username |
      | testuser |
    And the following "course enrolments" exist:
      | course      | user     | role    |
      | a_shortname | testuser | student |
      | b_shortname | testuser | student |
      | c_shortname | testuser | student |
      | d_shortname | testuser | student |
      | e_shortname | testuser | student |
      | test        | testuser | student |
    And the following "blocks" exist:
      | blockname            | contextlevel | reference |
      | filtered_course_list | Course       | test      |

  @javascript
  Scenario Outline:
    Given the following config values are set as admin:
      | primarysort     | <sort1>    | block_filtered_course_list |
      | primaryvector   | <vec1>     | block_filtered_course_list |
      | secondarysort   | <sort2>    | block_filtered_course_list |
      | secondaryvector | <vec2>     | block_filtered_course_list |
    And I set the multiline "block_filtered_course_list" "filters" setting as admin to:
      """
      shortname | expanded | Courses | shortname
      """
    When I log in as "testuser"
    And I am on site homepage
    And I follow "Test"
    And I wait until ".block_filtered_course_list" "css_element" exists
    Then <first> "text" should appear before <second> "text"
    And <second> "text" should appear before <third> "text"
    And <third> "text" should appear before <fourth> "text"
    And <fourth> "text" should appear before <fifth> "text"

  Examples:
    | sort1     | vec1 | sort2    | vec2 | first    | second   | third    | fourth   | fifth    |
    | fullname  | ASC  | none     | ASC  | "A Full" | "B Full" | "C Full" | "D Full" | "E Full" |
    | fullname  | DESC | none     | ASC  | "E Full" | "D Full" | "C Full" | "B Full" | "A Full" |
    | shortname | ASC  | none     | ASC  | "E Full" | "A Full" | "B Full" | "C Full" | "D Full" |
    | idnumber  | DESC | none     | ASC  | "C Full" | "B Full" | "A Full" | "E Full" | "D Full" |
    | startdate | ASC  | idnumber | ASC  | "D Full" | "E Full" | "C Full" | "B Full" | "A Full" |
    | startdate | ASC  | idnumber | DESC | "E Full" | "D Full" | "C Full" | "B Full" | "A Full" |
    | none      | ASC  | fullname | ASC  | "A Full" | "B Full" | "C Full" | "D Full" | "E Full" |

  @javascript
  Scenario Outline:
    Given the following config values are set as admin:
      | primarysort     | sortorder  | block_filtered_course_list |
      | primaryvector   | ASC        | block_filtered_course_list |
      | secondarysort   | none       | block_filtered_course_list |
      | secondaryvector | ASC        | block_filtered_course_list |
    And I set the multiline "block_filtered_course_list" "filters" setting as admin to:
      """
      shortname | expanded | Courses | shortname
      """
    And I log in as "admin"
    And I am on site homepage
    And I navigate to "Manage courses and categories" node in "Site administration>Courses"
    And I wait until "<sort>" "link" exists
    And I click on "Sort courses" "link"
    And I wait until the page is ready
    And I click on "<sort>" "link" in the ".course-listing-actions" "css_element"
    And I log out
    When I log in as "testuser"
    And I am on site homepage
    And I follow "Test"
    And I wait until ".block_filtered_course_list" "css_element" exists
    Then <first> "text" should appear before <second> "text"
    And <second> "text" should appear before <third> "text"
    And <third> "text" should appear before <fourth> "text"
    And <fourth> "text" should appear before <fifth> "text"

  Examples:
    | sort                                 | first    | second   | third    | fourth   | fifth    |
    | Sort by Course full name ascending   | "A Full" | "B Full" | "C Full" | "D Full" | "E Full" |
    | Sort by Course full name descending  | "E Full" | "D Full" | "C Full" | "B Full" | "A Full" |
    | Sort by Course short name descending | "D Full" | "C Full" | "B Full" | "A Full" | "E Full" |
