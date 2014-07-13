<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Jmol filter.
 *
 * @package    filter
 * @subpackage jmol
 * @copyright  2006 Dan Stowell
 * @copyright  2007-2008 Szymon Kalasz Internationalisation strings added as part of GHOP
 * @url        http://moodle.org/mod/forum/discuss.php?d=88201
 * @copyright  20011 Geoffrey Rowland <growland at strode-college dot ac dot uk> Updated for Moodle 2
 * @copyright  20013 Geoffrey Rowland <growland at strode-college dot ac dot uk> Updated to use JSmol
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Jmol/JSmol plugin filtering for viewing molecules online
//
// This filter will replace any links to a .mol, .sdf, .csmol, .pdb,
// .pdb.gz .xyz, .cml, .mol2, or .cif file
// with the JavaScript (and associated technologies) needed to display the molecular structure inline
//
// If required, allows customisation of the Jmol applet size (default 350 px)
//
// Similarly, allows selection of a few different Jmol control sets depending on the chemical context
// e.g. small molecule, biological macromolecule, crystal
//
// Also, customisation of the initial display though Jmol scripting
//
// To activate this filter, go to admin and enable 'jmol'.
//
// Latest JSmol version is available from http://chemapps.stolaf.edu/jmol/jsmol.zip
// Unzipped jsmol folder (and contents) can be used to replace/update the jsmol folder in this bundle
// Jmol project site: http://jmol.sourceforge.net/
// Jmol interactive scripting documentation(Use with JMOLSCRIPT{ }): http://chemapps.stolaf.edu/jmol/docs/
// Jmol Wiki: http//wiki.jmol.org.

class filter_jmol extends moodle_text_filter {
    
    public function setup($page, $context) {
        global $CFG;
        
        // This only requires execution once per request.
        static $jsinitialised = false;

        if (empty($jsinitialised)) {
	    $url= '/filter/jmol/yui/jsmol/JSmol.min.js';
            $url = new moodle_url($url);
            $moduleconfig = array(
                'name' => 'jsmol',
                'fullpath' => $url
            );
        $page->requires->js_module($moduleconfig);
        $jsinitialised = true;
        }
    }

    public function filter($text, array $options = array()) {
        
        global $CFG;
        $wwwroot = $CFG->wwwroot;
        $host = preg_replace('~^.*://([^:/]*).*$~', '$1', $wwwroot);

        // Edit $jmolfiletypes to add/remove chemical structure file types that can be displayed.
        // For more detail see: http://wiki.jmol.org/index.php/File_formats.
        $jmolfiletypes ='cif|cml|csmol|mol|mol2|pdb\.gz|pdb|pse|sdf|xyz';

        $search = '/<a\\b([^>]*?)href=\"((?:\.|\\\|https?:\/\/' . $host . ')[^\"]+\.('.$jmolfiletypes.'))\??(.*?)\"([^>]*)>(.*?)<\/a>(\s*JMOLSCRIPT\{(.*?)\})?/is';

        $newtext = preg_replace_callback($search, 'filter_jmol_replace_callback', $text);

        return $newtext;
    }
}

function filter_jmol_replace_callback($matches) {
    global $CFG;
    $wwwroot = $CFG->wwwroot;
    static $count = 0;
    $count++;
    $id = time() . $count;

    // JSmol size (width = height) in pixels defined by parameter appended to structure file URL e.g. ?s=200, ?s=300 (default) etc.
    if (preg_match('/s=(\d{1,3})/', $matches[4], $optmatch)) {
        $size = $optmatch[1];
    } else {
        $size = 300;
    }
    if (!preg_match('/c=(\d{1,2})/', $matches[4], $optmatch)) {
        $optmatch = array(1 => 1);
    }
    // Get language strings.
    $style = get_string('style', 'filter_jmol');
    $wireframe = get_string('wireframe', 'filter_jmol');
    $stick = get_string('stick', 'filter_jmol');
    $ballandstick = get_string('ballandstick', 'filter_jmol');
    $spacefill = get_string('spacefill', 'filter_jmol');
    $unitcell = get_string('unitcell', 'filter_jmol');
    $polyhedra = get_string('polyhedra', 'filter_jmol');
    $hydrogens = get_string('hydrogens', 'filter_jmol');
    $backbone = get_string('backbone', 'filter_jmol');
    $cartoon = get_string('cartoon', 'filter_jmol');
    $spin = get_string('spin', 'filter_jmol');
    $jmolhelp = get_string('jmolhelp', 'filter_jmol');
    $jsdisabled = get_string('jsdisabled', 'filter_jmol');
    $downloadstructurefile = get_string('downloadstructurefile', 'filter_jmol');
    
    // File path = $matches[2]
    // File extension = $matches[3]
    // Controls defined by parameter appended to structure file URL ?c=0, ?c=1 (default), ?c=2 ,?c=3 or ?c=4.
    switch($optmatch[1]) {
        // No controls at all.
        case 0:
            $control = '';
            break;
        // Controls for crystallography e.g. cif files): Atom display, Unit cell and Spin.
        case 2:
            $control = 'Jmol.jmolMenu(jmol'.$id.', [
            ["#optgroup", "'.$style.'"],
            ["wireframe only", "'.$wireframe.'"],
            ["spacefill off; wireframe 0.15", "'.$stick.'"], ["wireframe 0.15; spacefill 23%", "'.$ballandstick.'", "selected"],
            ["spacefill on", "'.$spacefill.'"], ["#optgroupEnd"]
            ])+
            Jmol.jmolCheckbox(jmol'.$id.', "set unitcell on; set axes on", "set unitcell off; set axes off", "'.$unitcell.'", "")+
            Jmol.jmolCheckbox(jmol'.$id.', "spin on", "spin off", "'.$spin.'", "")';
            break;
        // Default controls for biological macromolecules e.g. pdb files: Display and Spin.
        case 3:
            $control = 'Jmol.jmolMenu(jmol'.$id.', [
            ["#optgroup", "'.$style.'"],
            ["cartoons off; backbone off; wireframe only", "'.$wireframe.'"],
            ["cartoons off; backbone off; spacefill off; wireframe 0.15", "'.$stick.'"],
            ["cartoons off; backbone off; wireframe 0.15; spacefill 23%", "'.$ballandstick.'", "selected"],
            ["cartoons off; backbone off; spacefill on", "'.$spacefill.'"],
            ["spacefill off; cartoons off; wireframe off; backbone 0.5; color backbone group", "'.$backbone.'"],
            ["spacefill off; backbone off; wireframe off; cartoons on; color cartoons structure", "'.$cartoon.'"],
            ["#optgroupEnd"]
            ])+
            Jmol.jmolCheckbox(jmol'.$id.', "spin on", "spin off", "'.$spin.'", "")';
             break;
        // Default controls for small molecules e.g. mol files: Atom display and Spin.
        default:
            $control = 'Jmol.jmolMenu(jmol'.$id.', [
            ["#optgroup", "'.$style.'"],
            ["wireframe only", "'.$wireframe.'"],
            ["spacefill off; wireframe 0.15","'.$stick.'"],
            ["wireframe 0.15; spacefill 23%", "'.$ballandstick.'", "selected"],
            ["spacefill on", "'.$spacefill.'"], ["#optgroupEnd"]
            ])+
            Jmol.jmolCheckbox(jmol'.$id.', "spin on", "spin off", "Spin", "")';
    } // End of switch
    //
    // Set up appropriate controls and scripts for different file types
    if ($matches[3] == "cif") {
        $loadscript = 'load \"'.$matches[2].'\" {1 1 1} PACKED; set antialiasDisplay on;';
    } else if ($matches[3] == "pdb" || $matches[3] == "pdb.gz") {
        $loadscript =  'set pdbAddHydrogens true; load \"'.$matches[2].'\"; set antialiasDisplay on;';
    } else {
        $loadscript = 'load \"'.$matches[2].'\"; set antialiasDisplay on;';
    }
    if (count($matches) > 8) {
        // Uncomment the following line to debug JMOLSCRIPT{}
        // echo "Found Jmol script: $matches[8]";
        // End of comment.
        $initscript = preg_replace("@(\s|<br />)+@si", " ",
        str_replace(array("\n", '"', '<br />'), array("; ", "", ""), $matches[8]));
    } else {
        $initscript = '';
    }
    // Force Java applet for binary files (.pdb.gz or .pse) with some browsers (IE, Chrome or Safari)
    $browser = strtolower($_SERVER['HTTP_USER_AGENT']);
    if ($matches[3] == "pdb.gz" || $matches[3] == "pse") { 
        // Internet Explorer 11
        if (strpos($browser,'trident')) {
            $technol = 'JAVA';
        // Internet Explorer, older versions
        } else if (strpos($browser,'msie')) {
            $technol = 'JAVA';
        } else if (strpos($browser,'chrome')) {
            $technol = 'JAVA';
        } else if (strpos($browser,'safari')) {
            $technol = 'JAVA';
        } else if (strpos($browser,'opera')) {
            $technol = 'HTML5';
        } else {
            $technol = 'HTML5';
        }
    } else {
        $technol = 'HTML5';	
    } 
    // Prepare divs for Jmol/JSmol and controls.
    // Each Jmol/JSmol instance, in a page, has a unique ID.
    // Load JSmol JavaScript as a YUI module.
    // The Y.on('load', function () {}) is important in ensuring that JSmol does not interfere with Moodle YUI functions.
    // The YUI resize function allows Jmol/Jmol instance to be proportionately resized - drag handle at bottom-right corner.
    return "
    <div id='resize".$id."' class='yui3-resize-knob' style='position: relative; border: 1px solid lightgray; width: ".$size."px; height: ".$size."px;'>
    <div id='jmoldiv".$id."' style='display: block; position: absolute; z-index: 0; width: 100%; height: 100%;'>
    <noscript>".$jsdisabled."</noscript>
    </div>
    </div>
    <div style='width: ".$size."px'>
    <div id='control".$id."' style='float: left'></div>
    <div id='download".$id."' style='float: left; margin: 6px 1em'>
    <a href='".$matches[2]."' title='".$downloadstructurefile."'>
    <img src='".$wwwroot."/filter/jmol/pix/download.svg' />
    </a> <a href='".$wwwroot."/filter/jmol/help.php' title='".$jmolhelp."' target='_blank'>
    <img src='".$wwwroot."/filter/jmol/pix/help.svg' />
    </a>
    </div>
    </div>
    <script type='text/javascript'>
    // Resize Jmol from autohiding handle at bottom-right corner
    YUI().use('resize', 'jsmol', 'node-base', function (Y) {
        var resize = new Y.Resize({
            node: '#resize".$id."',
            wrap: true,
            autoHide: true,
            handles: 'br'
        });
        // Fix Jmol aspect ratio and set min max size
        resize.plug(Y.Plugin.ResizeConstrained, {
            preserveRatio: true,
            minWidth: 100,
            minHeight: 100,
            maxWidth: 1000,
            maxHeight: 1000
        });
        // Reset jmol resolution to adjusted size
        resize.on('resize:end', function(event) {
            $('#jmoldiv".$id."').html(Jmol.resizeApplet(jmol".$id.",'100%'));
        });
        var Info = {
            width: '100%',
            height: '100%',
            debug: false,
            color: 'white',
            addSelectionOptions: false,
            serverURL: '".$wwwroot."/filter/jmol/yui/jsmol/php/jsmol.php',
            use: '".$technol."',
            deferApplet: false,
            deferUncover: false,
            jarPath: '".$wwwroot."/filter/jmol/yui/jsmol/java',
            j2sPath: '".$wwwroot."/filter/jmol/yui/jsmol/j2s',
            jarFile: 'JmolAppletSigned0.jar',
            isSigned: true,
            disableInitialConsole: true,
            disableJ2SLoadMonitor: true,
            readyFunction: null,
            script: 'frank off;".$loadscript.$initscript."',
            defaultModel: null,
            debug: false
        }

        Y.on('load', function () {
            //Uncomment following if MathJax is installed
            //MathJax.Hub.Queue(function () {
                Jmol.setDocument(0);
                Jmol._alertNoBinary = false;
        	    Jmol.getApplet('jmol".$id."', Info);
                $('#jmoldiv".$id."').html(Jmol.getAppletHtml(jmol".$id."));
                $('#control".$id."').html(".$control.");
            //});
        });
    });
    </script>";
}
