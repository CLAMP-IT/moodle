# [Moodle Liberal Arts Edition v3.3.0-LAE13.0.0]

Welcome to the Moodle Liberal Arts Edition. The goal of the LAE is to provide a coherent package for modules, patches, and code developed (or improved) by the Collaborative Liberal Arts Moodle Project (CLAMP).

This package consists of the code that the developers and instructional technologists at CLAMP schools have deemed essential to their operation of Moodle. A number of other recommend add-ons for Moodle are available through CLAMP web site (<http://www.clamp-it.org>). These recommended add-ons,  however,  have certain caveats that you should be aware of, and it is imperative that you read their respective readme files before installing them.

## Legal

The LAE is offered "as is", with no warranty. The institutions that comprise CLAMP have done their best to test this code, but we offer it strictly as a convenience to our members.

## Contact

Questions about the LAE can be sent to Ken Newquist at <newquisk@lafayette.edu> or 610-330-5759. CLAMP members may participate in the development of the LAE by joining the Development Project in Redmine (our collaboration web site) at:

<http://redmine.clamp-it.org/projects/development>

## Contents

Moodle 3.3.0+LAE13.0.0 consists of the core Moodle 3.3.0 release plus a number of CLAMP-developed features and bug fixes.

The following core features are included:

* Anonymous Forums
* Per-course resource display options

### Anonymous Forums

A completely new version of the Anonymous Forums option in Moodle. This version introduces a new "Anonymous User" who is attached to forum posts, allowing faculty to back up and restore a forum without losing anonymity. This feature is disabled by default.

### Per-course resource display options

You may choose a default resource display option at the course level now instead of at the site-level.

## Contributed modules

CLAMP recommends the following contributed modules. If you have downloaded the "Package" version of the LAE then these modules are already available.

### Filtered Course List

This block allows you to list a current term and a future term's courses first, based on whatever term-based naming convention you use in your Moodle courses' shortname field (e.g. FA11, SP12). It also allows you to specify a course category instead."

### OU Dates Report

This course report, developed by Tim Hunt at the Open University, allows teachers to quickly edit date-aware items in course modules such as quizzes and assignments.

### Quickmail

A block used to quickly send emails to members of a class, replicating similar functionality found in other learning management systems. This version is forked from the Quickmail currently maintained by Louisiana State University. This functionality is now delivered by "CLAMPMail" (block_clampmail). For full details please see <http://www.clamp-it.org/blog/2014/01/20/the-state-of-quickmail/>.

### Roster Report

A course report which displays the user pictures for everyone enrolled in a course.

### Ad-hoc database queries (customsql)

This report plugin, developed by Tim Hunt at the Open University,  allows Administrators to set up arbitrary database queries to act as ad-hoc reports.

## Downloading the LAE

You can get the LAE in two ways:

* Download the tar and zip packages from the CLAMP web site: <http://www.clamp-it.org/code/>
* Download the current release branch from the CLAMP code repository:

    git clone https://github.com/CLAMP-IT/moodle v3.3.0-LAE13.0.0
    git checkout -b v3.3.0-LAE13.0.0

By default this is the "Package" version which includes the contributed modules. If you just want the core version of the LAE checkout the v3.3.0-LAE13.0.0-base tag instead.

CLAMP maintains two branches for each major version:

* LAE\_33\_STABLE contains the core code only
* LAE\_33\_PACKAGE includes the contributed modules

## Installing the LAE

If you are installing Moodle for the first time, you can follow the standard Moodle installation instructions (substituting the LAE Moodle package for the regular Moodle one)

<http://docs.moodle.org/en/Installing_Moodle>

## Upgrading to the LAE

If you are upgrading an existing installation, you can follow your normal procedure for doing an "in-place" upgrade (replacing your old Moodle files with the new LAE ones, then copying over any additional modules or blocks you might have from the old install into the new one)

A few notes:

1. Always backup your original Moodle files and database before doing an upgrade.

2. We *strongly* recommend doing a test upgrade on a development Moodle instance before upgrading your production instance.
