moodle-assignsubmission_mahara
============================

Mahara assignment submission plugin for Moodle 2.7 through 3.0.
- https://github.com/MaharaProject/moodle-assignsubmission_mahara

This plugin adds Mahara page submission functionality to assignments in
Moodle.  The plugin works with the new "mod/assign" type introduced in 2.3.
It requires at least one Mahara site linked to Moodle via MNet.

This plugin allows a teacher to add a "Mahara" item to the submission
options for a Moodle assignment. Students can then select one of the pages
or collections from their Mahara portfolio as part of their assignment
submission.

The submitted Mahara page or collection can be locked from editing in
Mahara if you wish, the same as if it had been submitted to a Mahara group.
Depending on settings, it may be left permanently locked, or get unlocked
when submission has been graded (or if grading workflow is used, its state
changed to "Released").

The plugin also allows migrating old Mahara "mod/assignment" assignments to
the new type. The plugin does not include featues to communicate with the
outcomes artefact plugin.


Installation
------------
1. Make sure that your Moodle and Mahara versions are up to date.
2. If you are using Mahara 1.9 or earlier, apply the patch "mahara-patch.txt" to your Mahara site.
3. Copy the contents of this project to mod/assign/submission/mahara in your Moodle site.
4. Proceed with plugin installation in Moodle.
5. On the Moodle page "Site Admin" -> "Networking" -> "Peers", choose the Mahara site.
      Open the "Services" tab and enable "Assign Submission Mahara" services.
6. Open "Site admin" -> "Plugins" -> "Activity modules" -> "Assignment" -> "Submission plugins" -> "Mahara portfolio" and configure default locking behaviour.
6. Now you may create your first Mahara assignment.

Upgrading
---------

This plugin is designed to allow you to upgrade from either the University
of Portland version, or the Lancaster University version. It will
automatically detect which version of the plugin you have installed, and
migrate it accordingly. So all you need to do is:

1. Remove the current contents of your mod/assign/submission/mahara directory.
2. Follow the steps under "Installation" above. (This will trigger the database upgrade script.)
3. If you have installed the Mahara assignment feedback plugin (mod/assign/feedback/mahara), the upgrade will prompt you to uninstall it. You will then need to remove its directory.
4. If you have also installed the Mahara local plugin (local/mahara), you should now uninstall it and remove its directory.

NOTE: If you were using the Mahara assignment feedback plugin before, you need
to upgrade this assignment submission plugin BEFORE uninstalling the assignment
feedback plugin. This is to allow the per-assignment locking settings from
the feedback plugin to be migrated into the replacement system in the 
submission plugin.

About that patch
----------------

As you may have noticed in the installation instructions, this plugin may
require you to apply a patch to your Mahara site. The patch provides
support for the Mahara web services to handle collections. If you are using
Mahara 1.10.0 or later, you DO NOT need to apply this patch, as your Mahara
site will already include this functionality.

For information about how to apply a patch file, try Google. If you are
using Linux, the process will look something like this:

```Shell
cd /var/www/path/to/mahara
patch -p0 < /path/to/mahara-patch.txt
```

A little info about what it does:
---------------------------------

This plugin adds a "Mahara" submission method to "assignment" activities in Moodle.
When a teacher creates an "assignment" activity, they'll see "Mahara" as one of the
submission method options. This submission method will ask students to select one
of their pages or collections from Mahara, to include as part of their assignment
submission. (Therefore, this plugin requires your Moodle site to be connected to a
Mahara site via MNet.)

* Individual pages that are part of collections cannot be picked on their own (the entire collection must be picked instead).
* Pages and collections that are already locked due to being submitted to a Mahara group or another Moodle assignment, are also not available.

Optionally, the assignment may lock the submitted pages and collections
from being edited in Mahara. This is recommended, because otherwise
students will be able to continue editing part of their assignment
submission even after the assignment deadline. The teacher may choose whether
submitted pages and collection will be unlocked after grading, or when the 
grading workflow state changes to "Released".

If you choose to use locking, note that:
* Pages & collections that are part of a draft submission will be not be locked until the draft is submitted.
* The Mahara page will be locked if the submission is submitted OR the submission is "locked" via the Moodle gradebook.
* If a submission is "reopened" via the Moodle gradebook, the page will become unlocked.

If the locking setting permits unlock after grading:
* If grading workflow is disabled, then grading the work will result in page unlocking.
* If grading workflow is enabled, then page unlocking will happen at the point when workflow state changes to "Released", whether the submission has been graded or not prior to that.

If you need help, try the Moodle-Mahara Integration forum on mahara.org: https://mahara.org/interaction/forum/view.php?id=30

Bugs and Improvements?
----------------------

If you've found a bug or if you've made an improvement to this plugin and want to share your code, please
open an issue in our Github project:
* https://github.com/MaharaProject/moodle-assignsubmission_mahara/issues

Credits
-------

The original Moodle 1.9 version of this plugin was funded through a grant from the New Hampshire Department of Education to a collaborative group of the following New Hampshire school districts:

 - Exeter Region Cooperative
 - Windham
 - Oyster River
 - Farmington
 - Newmarket
 - Timberlane School District

The upgrade to Moodle 2.0 and 2.1 was written by Aaron Wells at Catalyst IT, and supported by:

 - NetSpot
 - Pukunui Technology

The upgrade to the Moodle 2.3 mod/assign module was developed by:

 - University of Portland by Philip Cali and Tony Box (box@up.edu)
 - Lancaster University

Subsequent updates to the plugin were implemented by Aaron Wells at Catalyst IT, with funding from:

 - University of Brighton
 - Canberra University

The upgrade to use events for unlocking behaviour and supporting grading
workflow was developed by Lancaster University.

License
-------

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, version 3 or later of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
