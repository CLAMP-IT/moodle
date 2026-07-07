@report @report_roster @report_roster_images
Feature: User images can be configured to display at different sizes
  In order to configure user image size display
  As an administrator
  I need to set some settings

  @javascript
  Scenario: Test user image configurable sizes
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                 |
      | hpotter  | Harry     | Potter   | hpotter@hogwarts.wiz  |
      | hgranger | Hermione  | Granger  | hgranger@hogwarts.wiz |
      | rweasley | Runil     | Wazlib   | rweasley@hogwarts.wiz |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | hpotter  | C1     | student |
      | hgranger | C1     | student |
      | rweasley | C1     | student |
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Roster" "link"
    Then the "size" select box should contain "Small"
    And the "size" select box should contain "Medium"
    And the "size" select box should contain "Large"
    Then the "width" attribute of "ul.report-roster img.userpicture" "css_element" should contain "100"
    And the "height" attribute of "ul.report-roster img.userpicture" "css_element" should contain "100"
    When I set the field "size" to "Medium"
    Then the "width" attribute of "ul.report-roster img.userpicture" "css_element" should contain "200"
    And the "height" attribute of "ul.report-roster img.userpicture" "css_element" should contain "200"
    When I set the field "size" to "Large"
    Then the "width" attribute of "ul.report-roster img.userpicture" "css_element" should contain "300"
    And the "height" attribute of "ul.report-roster img.userpicture" "css_element" should contain "300"

    # Different values and different default.
    Given the following config values are set as admin:
      | config       | value  | plugin        |
      | size_default | medium | report_roster |
      | size_small   | 150    | report_roster |
      | size_medium  | 250    | report_roster |
      | size_large   | 350    | report_roster |
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Roster" "link"
    Then the "width" attribute of "ul.report-roster img.userpicture" "css_element" should contain "250"
    And the "height" attribute of "ul.report-roster img.userpicture" "css_element" should contain "250"
    When I set the field "size" to "Small"
    And the "height" attribute of "ul.report-roster img.userpicture" "css_element" should contain "150"
    Then the "width" attribute of "ul.report-roster img.userpicture" "css_element" should contain "150"
    When I set the field "size" to "Large"
    Then the "width" attribute of "ul.report-roster img.userpicture" "css_element" should contain "350"
    And the "height" attribute of "ul.report-roster img.userpicture" "css_element" should contain "350"

    # One value is 0, and default is set to that one. That option should be hidden, and should fall back to next highest size.
    Given the following config values are set as admin:
      | config       | value | plugin        |
      | size_default | small | report_roster |
      | size_small   | 0     | report_roster |
      | size_medium  | 500   | report_roster |
      | size_large   | 600   | report_roster |
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Roster" "link"
    And the "size" select box should not contain "Small"
    Then the "width" attribute of "ul.report-roster img.userpicture" "css_element" should contain "500"
    And the "height" attribute of "ul.report-roster img.userpicture" "css_element" should contain "500"
    When I set the field "size" to "Large"
    And the "height" attribute of "ul.report-roster img.userpicture" "css_element" should contain "600"
    Then the "width" attribute of "ul.report-roster img.userpicture" "css_element" should contain "600"

    # Only one populated value, select should not display.
    Given the following config values are set as admin:
      | config       | value  | plugin        |
      | size_default | medium | report_roster |
      | size_small   | 0      | report_roster |
      | size_medium  | 0      | report_roster |
      | size_large   | 175    | report_roster |
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Roster" "link"
    And "select[name='size']" "css_element" should not exist
    Then the "width" attribute of "ul.report-roster img.userpicture" "css_element" should contain "175"
    And the "height" attribute of "ul.report-roster img.userpicture" "css_element" should contain "175"

    # Hard default to 100.
    Given the following config values are set as admin:
      | config       | value  | plugin        |
      | size_default | medium | report_roster |
      | size_small   | 0      | report_roster |
      | size_medium  | 0      | report_roster |
      | size_large   | 0      | report_roster |
    And I am on "Course 1" course homepage
    And I navigate to "Reports" in current page administration
    And I click on "Roster" "link"
    And "select[name='size']" "css_element" should not exist
    Then the "width" attribute of "ul.report-roster img.userpicture" "css_element" should contain "100"
    And the "height" attribute of "ul.report-roster img.userpicture" "css_element" should contain "100"
