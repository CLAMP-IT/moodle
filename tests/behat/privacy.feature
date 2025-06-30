@block @block_filtered_course_list @block_filtered_course_list_privacy
Feature: The Filtered course list does not store personal data
  In order to view privacy details for the Filtered course list
  As an admin
  I need to visit admin/tool/dataprivacy/pluginregistry.php

  @javascript
  Scenario: Checking the privacy policy for the FCL block
    Given I log in as "admin"
    When I navigate to "Users>Privacy and policies" in site administration
    And I click on "Plugin privacy registry" "link"
    And I click on "#block" "css_element"
    And I click on "#block_filtered_course_list" "css_element"
    Then I should see "managed by other Moodle systems"
