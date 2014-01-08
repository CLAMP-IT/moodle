# [Moodle Liberal Arts Edition v2.4.5-LAE4.0.2]

Welcome to the Moodle Liberal Arts Edition. The goal of the LAE is to provide a coherent package for modules, patches, and code developed (or improved) by the Collaborative Liberal Arts Moodle Project (CLAMP).

This package consists of the code that the developers and instructional technologists at CLAMP schools have deemed essential to their operation of Moodle. A number of other recommend add-ons for Moodle are available through CLAMP web site (<http://www.clamp-it.org>). These recommended add-ons,  however,  have certain caveats that you should be aware of, and it is imperative that you read their respective readme files before installing them.

## Legal

The LAE is offered "as is", with no warranty. The institutions that comprise CLAMP have done their best to test this code, but we offer it strictly as a convenience to our members.

## Contact

Questions about the LAE can be sent to Ken Newquist at <newquisk@lafayette.edu> or 610-330-5759. CLAMP members may participate in the development of the LAE by joining the Development Project in Redmine (our collaboration web site) at:

<http://redmine.clamp-it.org/projects/development>

## Contents

Moodle 2.4.5+LAE4.0.2 consists of the core Moodle 2.4.5 release plus a number of CLAMP-developed features and bug fixes.

The following core features are included:

* Anonymous Forums
* Auto-creation of groupings for groups
* Configurable grade recovery during enrollment
* Per-course resource display options

### Anonymous Forums

A completely new version of the Anonymous Forums option in Moodle. This version introduces a new "Anonymous User" who is attached to forum posts, allowing faculty to back up and restore a forum without losing anonymity. This feature is disabled by default.

### Auto-creation of groupings for groups

This feature creates a grouping for each group in a course. This feature is disabled by default; you must enable the Experimental "Group Members Only" setting to use this feature.

### Configurable grade recovery during enrollment

This is a backport of the core patch in <https://tracker.moodle.org/browse/MDL-36024>; you may choose to have a user's grades (if any) automatically recovered when re-enrolling in a course.

### Per-course resource display options

You may choose a default resource display option at the course level now instead of at the site-level.

## Contributed modules

CLAMP recommends the following contributed modules. If you have downloaded the "Package" version of the LAE then these modules are already available.

### Filtered Course List

This block allows you to list a current term and a future term's courses first, based on whatever term-based naming convention you use in your Moodle courses' shortname field (e.g. FA11, SP12). It also allows you to specify a course category instead."

### LAE Grader Report

This is an alternative Grader Report for Moodle. It includes fixed scrolling on the vertical and horizontal axes and a new optional total display.

### OU Dates Report

This course report, developed by Tim Hunt at the Open University, allows teachers to quickly edit date-aware items in course modules such as quizzes and assignments.

### Quickmail

A block used to quickly send emails to members of a class, replicating similar functionality found in other learning management systems. This version is forked from the Quickmail currently maintained by Louisiana State University.

### Roster Report

A course report which displays the user pictures for everyone enrolled in a course.

## Downloading the LAE

You can get the LAE in two ways:

* Download the tar and zip packages from the CLAMP web site: <http://www.clamp-it.org/code/>
* Download the current release branch from the CLAMP code repository:

    git clone https://github.com/CLAMP-IT/moodle v2.4.5-LAE4.0.2
    git checkout -b v2.4.5-LAE4.0.2

By default this is the "Package" version which includes the contributed modules. If you just want the core version of the LAE checkout the v2.4.5-LAE4.0.2-base tag instead.

CLAMP maintains two branches for each major version:

* LAE_24_STABLE contains the core code only
* LAE_24_PACKAGE includes the contributed modules

## Installing the LAE

If you are installing Moodle for the first time, you can follow the standard Moodle installation instructions (substituting the LAE Moodle package for the regular Moodle one)

<http://docs.moodle.org/en/Installing_Moodle>

## Upgrading to the LAE

If you are upgrading an existing installation, you can follow your normal procedure for doing an "in-place" upgrade (replacing your old Moodle files with the new LAE ones, then copying over any additional modules or blocks you might have from the old install into the new one)

A few notes:

1. Always backup your original Moodle files and database before doing an upgrade.

2. We *strongly* recommend doing a test upgrade on a development Moodle instance before upgrading your production instance.