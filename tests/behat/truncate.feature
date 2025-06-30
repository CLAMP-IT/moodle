@block @block_filtered_course_list @block_filtered_course_list_truncate
Feature: Course names and category names can be truncated with their respective templates
  In order to truncate course names
  As an administrator
  I need to set various template values

  Background:
    Given the following "categories" exist:
      | name                   | category | idnumber | visible |
      | A Category With A Name | 0        | visible  | 1       |
    And the following "courses" exist:
      | fullname             | shortname | category |
      | A Course With A Name | course    | visible  |
    And the following "users" exist:
      | username |
      | testuser |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | testuser | course | editingteacher |
    And the following "blocks" exist:
      | blockname            | contextlevel | reference |
      | filtered_course_list | Course       | course    |
    And I set the multiline FCL "filters" setting as admin to:
      """
      category | expanded | visible | 0
      """
    Given I log in as "testuser"
    And I am on site homepage
    When I follow "A Course With A Name"
    Then I should see "Filtered course list"

  @javascript
  Scenario Outline: Truncation
    Given the following config values are set as admin:
      | coursenametpl | FULLNAME{<limit>} | block_filtered_course_list |
      | catrubrictpl  | NAME{<limit>}     | block_filtered_course_list |

    When I reload the page
    Then I should see "<crsname>" in the ".block_filtered_course_list" "css_element"
    And I should see "<catname>" in the ".block_filtered_course_list" "css_element"
    And I should not see "<notsee>" in the ".block_filtered_course_list" "css_element"

    Examples:
      | limit | crsname              | catname                 | notsee |
      | 0     | A Course With A Name | A Category With A Name  | Name…  |
      | 10    | A Course W…          | A Category…             | n/a    |
      | 11    | A Course Wi…         | A Category…             | n/a    |
      | 20    | A Course With A Name | A Category With A Na…   | Name…  |
      | 22    | A Course With A Name | A Category With A Name  | Name…  |
      | 30    | A Course With A Name | A Category With A Name  | Name…  |
