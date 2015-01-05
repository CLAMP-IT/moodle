## Ensemble Video Moodle Filter Plugin

__[Overview](#overview)__<br/>
__[Requirements](#req)__<br/>
__[Installing from Git](#git_install)__<br/>
__[Upgrading from Git](#git_upgrade)__<br/>
__[Installing from ZIP](#zip_install)__<br/>
__[Upgrading from ZIP](#zip_upgrade)__<br/>
__[Plugin Setup](#setup)__<br/>

### <a id="overview"></a>Overview

Along with the [Ensemble Video Moodle Repository Plugin](https://github.com/ensembleVideo/moodle-repository_ensemble), this plugin
makes it easier for Moodle users to add videos and playlists to content without
having to navigate to Ensemble Video and copy/paste complicated embed codes.  This
plugin filters content to render urls added by the repository plugin as Ensemble Video
embed codes.

In addition to this documentation, also see our [Setup the Moodle Plugin](http://support.ensemblevideo.com/setup-the-moodle-plugin/)
and [Using the Moodle Plugin](http://support.ensemblevideo.com/using-the-moodle-plugin/) support articles.

### <a id="req"></a>Requirements

* Ensemble Video version of 3.4 or higher.
* Moodle version 2.6 or higher.
* Depends on the [Ensemble Video Moodle Repository Plugin](https://github.com/ensembleVideo/moodle-repository_ensemble) for searching and choosing videos and playlists.

### <a id="git_install"></a>Installing from Git

These installation instructions are based off the strategy endorsed by Moodle
for [installing contributed extensions via Git](http://docs.moodle.org/26/en/Git_for_Administrators#Installing_a_contributed_extension_from_its_Git_repository).

    $ cd /path/to/your/moodle
    $ cd filter
    $ git clone https://github.com/ensembleVideo/moodle-filter_ensemble.git ensemble
    $ cd ensemble
    $ git checkout -b MOODLE_26_STABLE origin/MOODLE_26_STABLE

As a Moodle administrator, navigate to _Settings -> Site Administration -> Notifications_
and click _Upgrade Moodle database now_ to install the plugin.

### <a id="git_upgrade"></a>Upgrading from Git

To upgrade the plugin do the following:

    $ cd /path/to/your/moodle/filter/ensemble
    $ git pull

As a Moodle administrator, navigate to _Settings -> Site Administration -> Notifications_
and click _Upgrade Moodle database now_ to upgrade the plugin.

### <a id="zip_install"></a>Installing from ZIP

    $ wget https://github.com/ensembleVideo/moodle-filter_ensemble/archive/MOODLE_26_STABLE.zip
    $ unzip MOODLE_26_STABLE.zip
    $ mv moodle-filter_ensemble-MOODLE_26_STABLE /path/to/your/moodle/filter/ensemble

As a Moodle administrator, navigate to _Settings -> Site Administration -> Notifications_
and click _Upgrade Moodle database now_ to install the plugin.

### <a id="zip_upgrade"></a>Upgrading from ZIP

To upgrade the plugin delete the
_/path/to/your/moodle/filter/ensemble_ directory, then repeat the installation
steps above.

As a Moodle administrator, navigate to _Settings -> Site Administration -> Notifications_
and click _Upgrade Moodle database now_ to upgrade the plugin.

### <a id="setup"></a>Plugin Setup

Navigate to _Settings -> Site Administration -> Plugins -> Filters -> Manage filters_
and change the _Active?_ setting for the Ensemble Video filter from _Disabled_ to _On_.

#### Configuration Settings

##### Ensemble Urls

Optional.  By default this plugin will use the Ensemble URL configured in the corresponding
[repository plugin](https://github.com/ensembleVideo/moodle-repository_ensemble) so, typically,
nothing needs to be added here.  In order to render embed codes this plugin filters content
that contains this url.  If for some reason, however, the configured Ensemble URL has changed
after content has already been added, you can add entries here to tell the plugin which
additional urls to filter.
