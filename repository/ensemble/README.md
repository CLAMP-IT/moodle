## ![Ensemble Video logo](ext_chooser/css/images/logo.png) Ensemble Video Moodle Repository Plugin

__[Overview](#overview)__<br/>
__[Requirements](#req)__<br/>
__[Installing from Git](#git_install)__<br/>
__[Upgrading from Git](#git_upgrade)__<br/>
__[Installing from ZIP](#zip_install)__<br/>
__[Upgrading from ZIP](#zip_upgrade)__<br/>
__[Plugin Setup](#setup)__<br/>

### <a id="overview"></a>Overview

Along with the [Ensemble Video Moodle Filter Plugin](https://github.com/ensembleVideo/moodle-filter_ensemble),
this plugin makes it easier for Moodle users to add videos and playlists to
content without having to navigate to Ensemble Video and copy/paste complicated
embed codes.  Once setup, you should see two additional repositories under
_Insert Moodle media_ in the Moodle content editor, one for choosing videos and
another for choosing playlists from the configured Ensemble Video installation.

In addition to this documentation, also see our [Setup the Moodle Plugin](http://support.ensemblevideo.com/setup-the-moodle-plugin/)
and [Using the Moodle Plugin](http://support.ensemblevideo.com/using-the-moodle-plugin/) support articles.

### <a id="req"></a>Requirements

* Ensemble Video version of 3.4 or higher.
* Moodle version 2.6 or higher.
* Internet Explorer 9 or higher.  No known issues with other browsers.
* Depends on the [Ensemble Video Moodle Filter Plugin](https://github.com/ensembleVideo/moodle-filter_ensemble) for embed code rendering.

### <a id="git_install"></a>Installing from Git

These installation instructions are based off the strategy endorsed by Moodle
for [installing contributed extensions via Git](http://docs.moodle.org/26/en/Git_for_Administrators#Installing_a_contributed_extension_from_its_Git_repository).

    $ cd /path/to/your/moodle
    $ cd repository
    $ git clone https://github.com/ensembleVideo/moodle-repository_ensemble.git ensemble
    $ cd ensemble
    $ git checkout -b MOODLE_26_STABLE origin/MOODLE_26_STABLE

As a Moodle administrator, navigate to _Settings -> Site Administration -> Notifications_
and click _Upgrade Moodle database now_ to install the plugin.

### <a id="git_upgrade"></a>Upgrading from Git

To upgrade the plugin do the following:

    $ cd /path/to/your/moodle/repository/ensemble
    $ git pull

As a Moodle administrator, navigate to _Settings -> Site Administration -> Notifications_
and click _Upgrade Moodle database now_ to upgrade the plugin.

### <a id="zip_install"></a>Installing from ZIP

    $ wget https://github.com/ensembleVideo/moodle-repository_ensemble/archive/MOODLE_26_STABLE.zip
    $ unzip MOODLE_26_STABLE.zip
    $ mv moodle-repository_ensemble-MOODLE_26_STABLE /path/to/your/moodle/repository/ensemble

As a Moodle administrator, navigate to _Settings -> Site Administration -> Notifications_
and click _Upgrade Moodle database now_ to install the plugin.

### <a id="zip_upgrade"></a>Upgrading from ZIP

To upgrade the plugin delete the
_/path/to/your/moodle/repository/ensemble_ directory, then repeat the installation
steps above.

As a Moodle administrator, navigate to _Settings -> Site Administration -> Notifications_
and click _Upgrade Moodle database now_ to upgrade the plugin.

### <a id="setup"></a>Plugin Setup

Navigate to _Settings -> Site Administration -> Plugins -> Repositories -> Manage repositories_
and set the Ensemble Video repository to _Enabled and visible_.

#### Configuration Settings

##### Ensemble URL
Required setting.  Must point to the application root of your Ensemble Video
installation.  If, for example, the url for your Ensemble install is
_https://cloud.ensemblevideo.com/app/library.aspx_, you would use
_https://cloud.ensemblevideo.com_.  In the case of a url like
_https://server.myschool.edu/ensemble/app/library.aspx_ you would use
_https://server.myschool.edu/ensemble_.

##### Service Account Username (optional)

Optional.  If left empty, users of the repository will be prompted to
authenticate with their Ensemble Video credentials. Otherwise, this can be set
to a "service account" (an Ensemble Video account with a "System Administrator"
role) that has access to all content for your Moodle user population within
Ensemble Video.  The plugin will use this account to query the Ensemble Video
API, but will filter results by the username of the currently logged in Moodle
user.  With this approach users won't have to authenticate to Ensemble Video,
but it does imply that Moodle and Ensemble Video usernames match.

##### Service Account Password (optional)

Optional.  Used along with the Service Account Username as the credentials used
to query the Ensemble Video API.  See above.

##### Ensemble Account Domain (optional)

Optional.  Used to specify an Ensemble Video authentication domain to be used
when filtering results by Moodle username.  This is only used when the Service
Account Username is set and is appended to the currently authenticated Moodle
username.
