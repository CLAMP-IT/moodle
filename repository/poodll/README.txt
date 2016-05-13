PoodLL Repository
========================================
Thanks for downloading PoodLL.

Installation instructions and a video can be found at http://www.poodll.com .

There should be only one folder "poodll" in the poodllrepository after you unzip the zip file.
Place this folder into your moodle installation under the [site_root]/repository folder.

Then login to your site as admin and go to your Moodle site's top page. Moodle should then guide you through the installation or upgrade of the PoodLL repository. 
Before you can use the repository you will have to set it up. Go to: 
"Site Administration->Plugins->Repositories->Manage Repositories" 
and set the PoodLL repository to "enabled and visible". 
Then a "PoodLL" link will appear beneath "Manage Repositories" in the repositories menu. 
From that link create one or more instances of it, probably one for audio recording and one for video recording.
Then it will show in the file picker and you can use it in your courses.

Default recorded audio displays in the default video player. If you are using tokyo.poodll.com server for recording, you also have the option of setting "automatic audio transcoding to mp3" in the PoodLL filter settings page.
This will save your recorded audio as MP3, and it will display in an audio player.

*Please be aware that the repository relies on the PoodLL Filter being installed, and won't work properly otherwise*

Good luck.

Justin Hunt
NB Audio recording on Flash versions 11.2.202.228 - 11.2.202.235 (at the time of writing the most current releases) won't playback. It is an Adobe issue and it is fixed in Adobe Flash Player 11.3 Beta.
You can also avoid this problem if you set PoodLL Server Port (RTMP) to 1935 on the PoodLL filter settings page, though this may be blocked by a school's firewall. Setting "automatic transcoding to MP3" will also solve this problem,
because it only affects .flv files.
