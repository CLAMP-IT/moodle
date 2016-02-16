Turnitin block for Moodle
=========================

Please be aware that the **Develop** branch should not be considered production ready, although it contains the latest fixes and features it may contain bugs. It should be avoided in favour of the **Master** branch which is the latest available branch that has been through the QA process. Please make any pull requests you would like to make to the develop branch.

To see what has changed in recent versions of this block, see the [CHANGELOG](https://github.com/turnitin/moodle-block_turnitin/blob/master/CHANGELOG.md).

Installation
------------

Before installing this plugin firstly make sure you are logged in as an Administrator and that you are using Moodle 2.3 or higher.

Note: the [TURNITINTOOLTWO](https://github.com/turnitin/moodle-mod_turnitintooltwo) module must be installed before you can use the block.

This block plugin enables non-administrator moodle users to migrate courses from Turnitin to Moodle.

To install, you need to do is copy all the files to the /blocks/turnitin directory in your Moodle installation. You should then go to `"Site Administration" > "Notifications"` and follow the on screen instructions.

Once the block is installed, it will need to be added to one or more of your Moodle screens. To do this you should switch Moodle to editing mode, in Moodle 2.5 this is done by clicking the "Turn editing on" button. Once the "Add a block" box is showing on screen select "Turnitin" and the block will be added to the screen. The block can be dragged around and positioned wherever you require.

If you have added a block and the logged-in user is enrolled on a class within your Turnitin account then a link will be displayed detailing how many classes a user has available to migrate. If the user has no courses available to migrate then the block will be hidden.

Note that this plugin inherits it's Turnitin connection from the turnitintooltwo module.