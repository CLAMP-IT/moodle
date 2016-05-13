PoodLL Database Activity Field
========================================
Thanks for downloading PoodLL.

Installation instructions and a video can be found at http://www.poodll.com .

There should be only one folder "poodll" after you unzip the zip file.
Place this folder into your moodle installation under the [site_root]/mod/data/field folder.

At the time of writing, Moodle doesn't completely handle language strings for 3rd party database fields well. So you should add these stings to the bottom of [site_root]/mod/data/lang/en/data.php
$string['poodll'] = 'PoodLL';
$string['namepoodll'] = 'PoodLL';
If you skip this step however, the sky won't fall in. It will just look a bit odd when Moodle tries to display the name of the database field.

After you placed the PoodLL files in teh correct locationa, login to your site as admin and go to your Moodle site's top page. Moodle should then guide you through the installation or upgrade of the PoodLL database activity field. 

When you make a database activity in Moodle, amongst the standard list of fields that you can choose from, you will see PoodLL Multimedia field. If you select this field you will have the option of choosing video, audio (via REd5 Server), audio mp3 (standalone), whiteboard or snapshot.


*Please be aware that the PoodLL database activity field relies on the PoodLL Filter being installed, and won't work properly otherwise*

Good luck.

Justin Hunt
PoodLL Guy
poodllsupport@gmail.com