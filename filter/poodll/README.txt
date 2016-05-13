PoodLL Filter
========================================
Thanks for downloading PoodLL.

Installation instructions and a video can be found at http://www.poodll.com .

There should be only one folder "poodll" expanded after you unzip the zip file.
Place this folder into your moodle installation under the [site_root]/filter folder.

Then login to your site as admin and go to your Moodle site's top page. Moodle should then guide you through the installation or upgrade of the PoodLL filter. 
You can probably accept the defaults, scroll to the bottom of the page and press save.
(When Moodle shows you the PoodLL Filter Settings page, the second property from the top of the page is the PoodLL Server Port Number property. 
This defaults to 80, which allows users to make recordings through firewalls. 
If you experience instability when recording, come back here try setting the PoodLL Server Port Number to 1935.)

You will then have to enable the filter. Go to:
"Site Administration->Plugins->Filters->Manage Filters"
And set the PoodLL Filter to "On."

Also set the "Multimedia Plugins" filter to "On." The PoodLL filter should be higher in the list.

All of the PoodLL mods require the PoodLL filter to be installed. If you are only interesting in audio and video recording, then you don't need to know much more. 


PoodLL Widgets
==============
If however you are interested in the stopwatch,flashcards etc widgets, consult the PoodLL website for the more information about using them.
For now, here are some sample filter strings you can use. It is probably easy enough to cut and paste from the strings below, and modify them as needed.

*In this release the HTML5 widgets have been excluded. They will be back in the next release we hope*

Stopwatch
{POODLL:type=stopwatch,fontheight=48,permitfullscreen=false,mode=normal,uniquename=ignorethis,runtime=swf,width=400,height=265}

Whiteboard
{POODLL:type=whiteboard,slave=false,standalone=true,mode=normal,boardname=,backimage=,rooms=,runtime=swf,width=600,height=350}

Countdown Timer
{POODLL:type=countdown,usepresets=false,initseconds=30,fontheight=48,permitfullscreen=false,mode=normal,uniquename=ignorethis,runtime=swf,width=400,height=265}

Calculator
{POODLL:type=calculator,runtime=swf,width=300,height=400}

Video Player(MP4/FLV)
{POODLL:type=video,path=http://path.to.video.mp4,protocol=http,embed=false,embedstring=Play,permitfullscreen=false,runtime=swf,width=320,height=240}

Audio Player(FLV only)
{POODLL:type=audio,path=http://path.to.audio.flv,protocol=http,embed=false,embedstring=Play,runtime=swf,width=320,height=25}

Audio List Player(FLV only)
{POODLL:type=audiolist,filearea=content,path=/,protocol=http,sequentialplay=true,player=pd,width=400,height=250}
Note: In Moodle 2.x managing multiple files within a single activity is harder. But if  one or more flv files have been added to this activity via the filepicker or the PoodLL File explorer, they will be displayed in the list player. Currently it will not work in a question.

Flashcards
{POODLL:type=flashcards,cardset=id_number_of_matching_question,qname=justtohelpyouremember,cardwidth=300,cardheight=150,randomize=yes,runtime=swf,width=400,height=252}
Note: In Moodle 2 we recommend you set up a standard matching question as the data for each set of flashcards. You will need to set the id no. of that question as the cardset parameter. Later it will be hard to know which question that was when you want to edit. So put the question name with no spaces in the qname parameter. (We are going to make this easier!) 

Dice
{POODLL:type=dice,dicecount=2,dicesize=200,runtime=swf,width=300,height=300}

Whiteboard
{POODLL:type=whiteboard,slave=false,standalone=true,mode=normal,boardname=,backimage=,rooms=,runtime=swf,width=600,height=350

Good luck.

Justin Hunt