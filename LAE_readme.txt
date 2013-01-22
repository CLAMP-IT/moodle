===Moodle 2.3.3+Liberal Arts Edition v3.0.4 Release Notes===

Welcome to the Moodle 2.3.3+Liberal Arts Edition v3.0.4. The goal of LAE is to provide a coherent package for modules, patches, and code developed (or improved) by the Collaborative Liberal Arts Moodle Project. 

This package consists of the code that the developers and instructional technologists at CLAMP schools have deemed essential to their operation of Moodle. A number of other recommend add-ons for Moodle are available through CLAMP web site (http://www.clamp-it.org). These recommended add-ons,  however,  have certain caveats that you should be aware of, and it's imperative that you read their respective lae_readme.txt files before installing them.

===LEGAL===
The LAE is offered "as is", with no warranty. The institutions that comprise CLAMP have done their best to test this code, but we're offering it strictly as a connivence to our members. 

===CONTACT===
Questions about the LAE can be sent to Ken Newquist at newquisk@lafayette.edu or 610-330-5759. Member organizations can participate in the development o
CLAMP members can participate in the development of the LAE by joining the Development Project in Redmine (our collaboration web site) at:

http://redmine.clamp-it.org/projects/development

===CONTENTS===
Moodle 2.3.3+LAEv3.0.4 consists of Moodle 2.3.3 (20121112) as well as a number of CLAMP-developed features and bug fixes. 

The following features are included:

* Anonymous Forums
* Auto-creation of groupings for groups
* LAE Grader Report
* Filtered Course List
* OU Dates Report
* Quickmail

The following bug fixes (with their CLAMP tracking number) were included in v3.0.3:

* CLAMP-461: compatibility fixes for Anonymous Forums

The following core bug fix was backported in v3.0.4:

* MDL-36668: Performance Issue with mod/data/view.php

====Anonymous Forums====
A completely new version of the Anonymous Forums option in Moodle. This version introduces a new "anonymous user" who is attached to forum posts, allowing faculty to back up and restore a forum without losing anonymity. Note: this feature is disabled by default.

====Auto-creation of groupings for groups====
This feature creates a grouping for each group in a course. This is off by default; you must enable the Experimental "Group Members Only" setting to use this feature.

====LAE Grader Report====
We've created new, easier-to-use Grader and User Reports for Moodle. These are now the default reports for the Gradebook; they allow faculty to scroll through grades vertically and horizontally. We need this tested with existing gradebooks to verify that everything displays properly.

====Filtered Course List Block====
This block addresses a problem many campuses that are into their second or third year of Moodle encounter: filtering the current term's courses from those of previous terms. 

This block allows you to specify a current term and a future term based on whatever term-based naming convention you use in your Moodle courses' shortname field (e.g. FA11, SP12). It also allows you to specify a course category instead.

====OU Dates Report====
This course report, developed by Tim Hunt at the Open University, allows teachers to quickly edit date-aware items in course modules such as quizzes and assignments.

====Quickmail====
A block used to quickly send emails to members of a class, replicating similar functionality found in other learning management systems.

===Tweaks and Enhancements===

* Assignment Max Grade increased to 250 (from 100): Moodle defaults its max grade value to 100; LAE changes that default to 250. 

http://redmine.clamp-it.org/issues/114

===DOWNLOADING THE LAE===
You can get the LAE in two ways:

* Download the tar and zip packages from the CLAMP web site:
http://www.clamp-it.org/code/

* Download the current release branch from the CLAMP code repository:

git clone ssh://<username>@mitre.clamp-it.org/home/git/moodle v2.3.3-LAE3.0.4
git checkout -b v2.3.3-LAE3.0.4

===INSTALLING THE LAE===
If you are installing Moodle for the first time, you can follow the standard Moodle installation instructions (substituting the LAE Moodle package for the regular Moodle one)

http://docs.moodle.org/en/Installing_Moodle

===UPGRADING TO THE LAE===
If you are upgrading an existing installation, you can follow your normal procedure for doing an "in-place" upgrade (replacing your old Moodle files with the new LAE ones, then copying over any additional modules or blocks you might have from the old install into the new one)

A few notes:

1) Always backup your original Moodle files and database before doing an upgrade.

2) We *strongly* recommend doing a test upgrade on a development Moodle instance before upgrading your production instance.

3) If you have a more current version of Moodle installed (one later than 2.3.3 [20121112]), do not attempt to install LAE v3.0.4, as it will cause a conflict with your newer database, and the installation will fail. You can find your current version by logging into Moodle as an administrator and then going to Administration > Notifications and looking at the bottom of the page for the Moodle version.
