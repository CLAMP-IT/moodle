<?php // $id$
////////////////////////////////////////////////////////////////////////
//  jmol plugin filtering for viewing molecules online
// 
//  This filter will replace any links to a .MOL, .CSMOL, .PDB, 
//  .PDB.GZ .XYZ, .CML, .MOL2, .CIF file 
//  with the Javascript needed to display the molecular structure inline
//
//  To activate this filter, go to admin and enable 'jmol'.
//
//  Jmol is designed only to display files held on your own server -
//  remember that!
//
//  Filter written by Dan Stowell and updated by Geoffrey Rowland.
//
////////////////////////////////////////////////////////////////////////

/// This is the filtering function itself.  It accepts the 
/// courseid and the text to be filtered (in HTML form).

//New class and function code for Moodle 2.0 http://docs.moodle.org/en/Development:Filters_2.
class filter_jmol extends moodle_text_filter {

//function jmol_filter($courseid, $text) {
//public function filter($text) {
function filter($text, array $options = array()){
    global $CFG, $jmol_applet_has_been_initialised;
    
    // The global variable "$filter_jmol_has_initialized"
    //  is used by BOTH the Jmol filter and the Jmol resource type
    //  to ensure that the Jmol applet code is only ever once 
    //  written to any given web page.


    // Jmol requires that we convert our full URL to a relative URL.
    // Otherwise it displays a warning message and refuses to run!
    
    $u = $CFG->wwwroot;
    
    if(preg_match('|https?://.*?/|', $u)){
      $relurl = preg_replace('|https?://.*?/|', '', $u);
    }else{
      $relurl = ''; // This will typically be the case if Moodle is the web root
    }
    $numdirs = substr_count($_SERVER['PHP_SELF'], '/') - 1;
    if($numdirs==0){
      $relurl = './' . $relurl;
    }else{
      $relurl = str_repeat('../', $numdirs) . $relurl;
    }

//  $search = '/<a\\b([^>]*?)href=\"((?:\.|\\\|https?:\/\/'.$_SERVER["HTTP_HOST"].')[^\"]+\.(cif|mol|mol2|pdb\.gz|pdb|csmol|xyz|cml))\??(.*?)\"([^>]*)>(.*?)<\/a>(\s*JMOLSCRIPT\{(.*?)\})?/is';
    $host = preg_replace('~^.*://([^:/]*).*$~', '$1', $u);
    $search = '/<a\\b([^>]*?)href=\"((?:\.|\\\|https?:\/\/' . $host . ')[^\"]+\.(cif|mol|mol2|pdb\.gz|pdb|csmol|xyz|cml))\??(.*?)\"([^>]*)>(.*?)<\/a>(\s*JMOLSCRIPT\{(.*?)\})?/is';

    $callbackfunction = '    
             if(preg_match(\'/s=(\d{1,3})/\', $matches[4], $optmatch))
               $size = $optmatch[1];
             else
               $size = 350;
             if(!preg_match(\'/c=(\d{1,2})/\', $matches[4], $optmatch))
             {
               $optmatch = array(1=>1);
             }
               switch($optmatch[1])
               {
               case 0:
                 $controls = \'</script>\';
                 break;
               case 2:
                 $controls =\'jmolBr();
                  jmolHtml("Atoms ");
                  jmolRadioGroup([
                     ["spacefill off", "off"],
                     ["spacefill 20%", "20%", "checked"],
                     ["spacefill 100%", "100%"]
                  ]);
                  jmolHtml(" ");
                  jmolCheckbox("set unitcell 4;color unitcell goldenrod", "set unitcell off", "Unit Cell");
                  jmolHtml(" ");
                  jmolCheckbox("spin on", "spin off", "Spin");
                  jmolHtml(" ");
                  </script><a href="\'.$matches[2].\'" title="Download the .\'.$matches[3].\' structure data file"><img align="absmiddle" height="17" width="17" src="'.$u.'/filter/jmol/download.gif" /></a> <a target="popup" href = "'.$u.'/filter/jmol/help.php" onclick = "return openpopup(\\\'/filter/jmol/help.php\\\', \\\'popup\\\', \\\'menubar=0,location=0,statusbar=0,scrollbars=1,resizeable=1,width=550,height=550\\\', 0);" title="Help with Jmol"><img align="absmiddle" height="17" width="17" src="'.$u.'/pix/help.gif" /></a>\';
                 break;
               case 3:
                 $controls =\'jmolBr();
                  jmolHtml("Atoms ");
                  jmolRadioGroup([
                     ["backbone off;cartoons off;wireframe on;spacefill off", "off"],
                     ["backbone off;cartoons off;wireframe on;spacefill 20%", "20%", "checked"],
                     ["backbone off;cartoons off;wireframe on;spacefill 100%", "100%"],
                     ["spacefill off;cartoons off;wireframe off;backbone 0.5;color backbone group", "backbone"],
                     ["spacefill off;backbone off;wireframe off;cartoons on;color cartoons structure", "cartoon"]
                  ]);
                  jmolHtml(" ");
                  jmolCheckbox("spin on", "spin off", "Spin");
                  jmolHtml(" ");
                  </script><a href="\'.$matches[2].\'" title="Download the .\'.$matches[3].\' structure data file"><img align="absmiddle" height="17" width="17" src="'.$u.'/filter/jmol/download.gif" /></a> <a target="popup" href = "'.$u.'/filter/jmol/help.php" onclick = "return openpopup(\\\'/filter/jmol/help.php\\\', \\\'popup\\\', \\\'menubar=0,location=0,statusbar=0,scrollbars=1,resizeable=1,width=550,height=550\\\', 0);" title="Help with Jmol"><img align="absmiddle" height="17" width="17" src="'.$u.'/pix/help.gif" /></a>\';
                 break;
              default:
                $controls =\'jmolBr();
                  jmolHtml("Atoms ");
                  jmolRadioGroup([
                     ["spacefill off", "off"],
                     ["spacefill 20%", "20%", "checked"],
                     ["spacefill 100%", "100%"]
                  ]);
                  jmolHtml(" ");
                  jmolCheckbox("spin on", "spin off", "Spin");
                  jmolHtml(" ");
                  </script><a href="\'.$matches[2].\'" title="Download the .\'.$matches[3].\' structure data file"><img align="absmiddle" height="17" width="17" src="'.$u.'/filter/jmol/download.gif" /></a> <a target="popup" href = "'.$u.'/filter/jmol/help.php" onclick = "return openpopup(\\\'/filter/jmol/help.php\\\', \\\'popup\\\', \\\'menubar=no,toolbar=no,statusbar=no,location=no,scrollbars=yes,resizeable=yes,width=550,height=550\\\', 0);" title="Help with Jmol"><img align="absmiddle" height="17" width="17" src="'.$u.'/pix/help.gif" /></a>\';
               } // End of switch
             if(sizeof($matches)>8){
               //echo "Found Jmol script: $matches[8]";
               $initscript = preg_replace("@(\s|<br />)+@si", " ", str_replace(array("\\n","\\"", "<br />"), array("; ", "", ""), $matches[8]));
             }else{
               $initscript = "";
             }
             return "<script type=\\"text/javascript\\">
                  jmolApplet($size, \\"load $matches[2]; set antialiasDisplay true; $initscript\\");
                  " . $controls . "";
             //return "Parameters:<dl><dt>url:</dt><dd>".htmlspecialchars($matches[2])."</dd><dt>opts:</dt><dd>".htmlspecialchars($matches[4])."</dd><dt>Size:</dt><dd>$size</dd><dt>Controls:</dt><dd>$controls</dd></dl>";
             ';

//    echo "<pre>".htmlspecialchars($callbackfunction)."</pre>";

    $newtext = preg_replace_callback($search, 
        create_function('$matches', $callbackfunction), $text);
  
    // We also want to output the script which initialises Jmol in a page. 
    // Here's the tricky bit: we only ever want to output it once in a page!
 
    if(($newtext != $text) && !isset($jmol_applet_has_been_initialised)){
      $jmol_applet_has_been_initialised = true;
      $newtext = '<script type="text/javascript" src="' . $relurl . '/filter/jmol/jmolapplet/Jmol.js"></script>'
               . '<script type="text/javascript">
               jmolInitialize("' . $relurl . '/filter/jmol/jmolapplet/");
               </script>
               <noscript>
The 3D viewer uses JavaScript to create the Java viewing window. If you are unable 
to make use of this technology, you can use the "download" link to download the 
file. If you are still unable to make use of the information, contact your 
' . get_string('defaultcourseteacher') . '.
</noscript>
'
             . $newtext;
             
// GR Hack to use popup window for help.php in Moodle 2.0
//
$newtext = '
<script type="text/javascript">
var windowobj = null
function openpopup(url, name, options, fullscreen) {
    var fullurl = "'.$u.'" + url;
    windowobj = window.open(fullurl, name, options);
}
if (!document.all) {
document.captureEvents (Event.CLICK);
}
document.onclick = function() {
    if (windowobj != null && !windowobj.closed) {
    windowobj.focus();
    }
}
</script>
'.$newtext;          
  } 
 
return $newtext;
}
}
?>
