@assignfeedback @assignfeedback_pdf @_only_local @wip
Feature: Teachers can add comments to a PDF submitted by a student
  In order to provide feedback to students
  As a teacher
  I need to be able to annotate a PDF

  @javascript
  Scenario: Student submits a PDF, the teacher can add comments to the PDF, then the teacher can view the annotate PDF
    Given the following "courses" exists:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 1 |
    And the following "users" exists:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@asd.com |
      | student1 | Student | 1 | student1@asd.com |
    And the following "course enrolments" exists:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Assignment" to section "1" and I fill the form with:
      | Assignment name               | Test assignment name |
      | Description                   | Submit your PDF |
      | assignsubmission_pdf_enabled  | 1 |
      | assignsubmission_file_enabled | 0 |
      | assignfeedback_pdf_enabled    | 1 |
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I follow "Test assignment name"
    And I press "Add submission"
    And I upload "mod/assign/feedback/pdf/tests/pdf_test1.pdf" file to "PDF submissions" filepicker
    And I press "Save changes"
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I follow "Test assignment name"
    When I follow "View/grade all submissions"
    And I click on "//img[@alt='Grade Student 1']/parent::a" "xpath_element"
    And I follow "Annotate submission"
    # Make sure there are no comments in the 'Find comments' menu.
    Then I click on "#findcommentsbutton" "css_element"
    And I should see "No comments"
    And I click on "#findcommentsbutton" "css_element"
    # Add a comment to page 1.
    And I add a comment at "10" "10" containing "This is a comment on page 1"
    And I wait "2" seconds
    And "div.comment" "css_element" should exists
    And I should see "This is a comment on page 1"
    # Add a comment to page 2.
    And I press "Next -->"
    And I should not see "This is a comment on page 1"
    And "div.comment" "css_element" should not exists
    And I add a comment at "20" "20" containing "Comment on page 2"
    And I wait "2" seconds
    And "div.comment" "css_element" should exists
    And I should see "Comment on page 2"
    # Check the comment on page 1.
    And I press "<-- Prev"
    And I should see "This is a comment on page 1"
    And I should not see "Comment on page 2"
    # Check the 'Find comments' menu.
    And I click on "#findcommentsbutton" "css_element"
    And I should see "1: This is a comment on page 1"
    And I should see "2: Comment on page 2"
    And I click on "#findcommentsbutton" "css_element"
    And I click on "#generateresponse" "css_element"
    # View the response online + check the comments are present.
    And I follow "View response online"
    And I should see "This is a comment on page 1"
    And I select "2" from "selectpage"
    And I should see "Comment on page 2"
    And I click on "#findcommentsbutton" "css_element"
    And I click on "//li[text()='1: This is a comment on page 1']" "xpath_element"
    And I should see "This is a comment on page 1"
    And I should not see "1: This is a comment on page 1"
    And I should not see "Comment on page 2"
    # Wait before moving on, as otherwise the DB can be torn-down whilst there is still an AJAX request pending
    # which results in an alert box (and a failed test).
    And I wait "3" seconds

