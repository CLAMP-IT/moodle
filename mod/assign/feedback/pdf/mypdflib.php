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
 * Library code for manipulating PDFs
 *
 * @package   mod_assign
 * @subpackage assignsubmission_pdf
 * @copyright 2012 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/pdflib.php');
if (file_exists($CFG->dirroot.'/mod/assign/feedback/editpdf/fpdi/fpdi.php')) {
    require_once($CFG->dirroot.'/mod/assign/feedback/editpdf/fpdi/fpdi.php');
} else {
    require_once($CFG->dirroot.'/mod/assign/feedback/pdf/fpdi/fpdi.php');
}

class AssignPDFLib extends FPDI {

    /** @var int the number of the current page in the PDF being processed */
    protected $currentpage = 0;
    /** @var int the total number of pages in the PDF being processed */
    protected $pagecount = 0;
    /** @var float used to scale the pixel position of annotations (in the database) to the position in the final PDF */
    protected $scale = 0.0;
    /** @var string the path in which to store generated page images */
    protected $imagefolder = null;
    /** @var string the path to the PDF currently being processed */
    protected $filename = null;

    const GSPATH_OK = 'ok';
    const GSPATH_EMPTY = 'empty';
    const GSPATH_DOESNOTEXIST = 'doesnotexist';
    const GSPATH_ISDIR = 'isdir';
    const GSPATH_NOTEXECUTABLE = 'notexecutable';
    const GSPATH_NOTESTFILE = 'notestfile';
    const GSPATH_ERROR = 'error';

    /**
     * Combine the given PDF files into a single PDF. Optionally add a coversheet and coversheet fields.
     * @param $pdflist string[] the filenames of the files to combine
     * @param $outfilename string the filename to write to
     * @param $coversheet string optional the coversheet to include
     * @param $fields stdClass[] optional the fields to write onto the coversheet
     * @return int the number of pages in the combined PDF
     */
    public function combine_pdfs($pdflist, $outfilename, $coversheet = null, $fields = null) {

        $this->setPageUnit('pt');
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $this->scale = 72.0 / 100.0;
        $this->SetFont('helvetica', '', 12.0 * $this->scale);
        $this->SetTextColor(0, 0, 0);

        $totalpagecount = 0;
        if ($coversheet) {
            $pagecount = $this->setSourceFile($coversheet);
            $totalpagecount += $pagecount;
            $this->create_page_from_source(1);
            if ($fields) {
                foreach ($fields as $c) {
                    $x = $c->xpos * $this->scale;
                    $y = $c->ypos * $this->scale;
                    $width = 0;

                    $text = '';
                    if ($c->type == 'text') {
                        $width = $c->width * $this->scale;
                        $text = $c->data;
                    } else if ($c->type == 'shorttext') {
                        $text = $c->data;
                    } else if ($c->type == 'date') {
                        $text = date($c->setting);
                    }

                    $text = str_replace('&lt;', '<', $text);
                    $text = str_replace('&gt;', '>', $text);
                    $this->MultiCell($width, 1.0, $text, 0, 'L', 0, 1, $x, $y);
                    // Params: width, height, text, border, justify, fill, ln, x, y.
                }
            }

            for ($i = 2; $i<=$pagecount; $i++) {
                $this->create_page_from_source($i);
            }
        }
        foreach ($pdflist as $file) {
            $pagecount = $this->setSourceFile($file);
            $totalpagecount += $pagecount;
            for ($i = 1; $i<=$pagecount; $i++) {
                $this->create_page_from_source($i);
            }
        }

        $this->save_pdf($outfilename);

        return $totalpagecount;
    }

    /**
     * The number of the current page in the PDF being processed
     * @return int
     */
    public function current_page() {
        return $this->currentpage;
    }

    /**
     * The total number of pages in the PDF being processed
     * @return int
     */
    public function page_count() {
        return $this->pagecount;
    }

    /**
     * Load the specified PDF and set the initial output configuration
     * Used when processing comments and outputting a new PDF
     * @param $filename string the path to the PDF to load
     * @return int the number of pages in the PDF
     */
    public function load_pdf($filename) {
        $this->setPageUnit('pt');
        $this->scale = 72.0 / 100.0;
        $this->SetFont('helvetica', '', 12.0 * $this->scale);
        $this->SetFillColor(255, 255, 176);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(1.0 * $this->scale);
        $this->SetTextColor(0, 0, 0);
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $this->pagecount = $this->setSourceFile($filename);
        $this->filename = $filename;
        return $this->pagecount;
    }

    /**
     * Sets the name of the PDF to process, but only loads the file if the
     * pagecount is zero (in order to count the number of pages)
     * Used when generating page images (but not a new PDF)
     * @param $filename string the path to the PDF to process
     * @param $pagecount int optional the number of pages in the PDF, if known
     * @return int the number of pages in the PDF
     */
    public function set_pdf($filename, $pagecount = 0) {
        if ($pagecount == 0) {
            return $this->load_pdf($filename);
        } else {
            $this->filename = $filename;
            $this->pagecount = $pagecount;
            return $pagecount;
        }
    }

    /**
     * Copy the next page from the source file and set it as the current page
     * @return bool true if successful
     */
    public function copy_page() {
        if (!$this->filename) {
            return false;
        }
        if ($this->currentpage>=$this->pagecount) {
            return false;
        }
        $this->currentpage++;
        $this->create_page_from_source($this->currentpage);
        return true;
    }

    protected function create_page_from_source($pageno) {
        // Get the size (and deduce the orientation) of the next page.
        $template = $this->importPage($pageno);
        $size = $this->getTemplateSize($template);
        $orientation = 'P';
        if ($size['w'] > $size['h']) {
            $orientation = 'L';
        }
        // Create a page of the required size / orientation.
        $this->AddPage($orientation, array($size['w'], $size['h']));
        // Prevent new page creation when comments are at the bottom of a page.
        $this->setPageOrientation($orientation, false, 0);
        // Fill in the page with the original contents from the student.
        $this->useTemplate($template);
    }

    /**
     * Copy all the remaining pages in the file
     */
    public function copy_remaining_pages() {
        while ($this->copy_page()); // Not found a way to stop codechecker complaing about this line.
    }

    /**
     * Add a comment to the current page
     * @param $text string the text of the comment
     * @param $x int the x-coordinate of the comment (in pixels)
     * @param $y int the y-coordinate of the comment (in pixels)
     * @param $width int the width of the comment (in pixels)
     * @param $colour string optional the background colour of the comment (red, yellow, green, blue, white, clear)
     * @return bool true if successful (always)
     */
    public function add_comment($text, $x, $y, $width, $colour = 'yellow') {
        if (!$this->filename) {
            return false;
        }
        switch ($colour) {
            case 'red':
                $this->SetFillColor(255, 176, 176);
                break;
            case 'green':
                $this->SetFillColor(176, 255, 176);
                break;
            case 'blue':
                $this->SetFillColor(208, 208, 255);
                break;
            case 'white':
                $this->SetFillColor(255, 255, 255);
                break;
            default: /* Yellow */
                $this->SetFillColor(255, 255, 176);
                break;
        }

        $x *= $this->scale;
        $y *= $this->scale;
        $width *= $this->scale;
        $text = str_replace('&lt;', '<', $text);
        $text = str_replace('&gt;', '>', $text);
        // Draw the text with a border, but no background colour (using a background colour would cause the fill to
        // appear behind any existing content on the page, hence the extra filled rectangle drawn below).
        $this->MultiCell($width, 1.0, $text, 0, 'L', 0, 1, $x, $y); /* width, height, text, border, justify, fill, ln, x, y */
        if ($colour != 'clear') {
            $newy = $this->GetY();
            if (($newy - $y)<(24.0 * $this->scale)) { /* Single line comment (ie less than 2*text height) */
                $width = $this->GetStringWidth($text) + 4.0; /* Resize box to the length of the text + 2 line widths */
            }
            // Now we know the final size of the comment, draw a rectangle with the background colour.
            $this->Rect($x, $y, $width, $newy - $y, 'DF');
            // Re-draw the text over the top of the background rectangle.
            $this->MultiCell($width, 1.0, $text, 0, 'L', 0, 1, $x, $y); /* width, height, text, border, justify, fill, ln, x, y */
        }
        return true;
    }

    /**
     * Add an annotation to the current page
     * @param $sx int starting x-coordinate (in pixels)
     * @param $sy int starting y-coordinate (in pixels)
     * @param $ex int ending x-coordinate (in pixels)
     * @param $ey int ending y-coordinate (in pixels)
     * @param $colour string optional the colour of the annotation (red, yellow, green, blue, white, black)
     * @param $type string optional the type of annotation (line, oval, rectangle, highlight, freehand, stamp)
     * @param $path mixed int[]|string optional for 'freehand' annotations this is an array of x and y coordinates for
     *              the line, for 'stamp' annotations it is the name of the stamp file (without the path)
     * @return bool true if successful (always)
     */
    public function add_annotation($sx, $sy, $ex, $ey, $colour = 'red', $type = 'line', $path = null) {
        global $CFG;
        if (!$this->filename) {
            return false;
        }
        switch ($colour) {
            case 'yellow':
                $this->SetDrawColor(255, 255, 0);
                break;
            case 'green':
                $this->SetDrawColor(0, 255, 0);
                break;
            case 'blue':
                $this->SetDrawColor(0, 0, 255);
                break;
            case 'white':
                $this->SetDrawColor(255, 255, 255);
                break;
            case 'black':
                $this->SetDrawColor(0, 0, 0);
                break;
            default: /* Red */
                $colour = 'red';
                $this->SetDrawColor(255, 0, 0);
                break;
        }

        $sx *= $this->scale;
        $sy *= $this->scale;
        $ex *= $this->scale;
        $ey *= $this->scale;

        $this->SetLineWidth(3.0 * $this->scale);
        switch ($type) {
            case 'oval':
                $rx = abs($sx - $ex) / 2;
                $ry = abs($sy - $ey) / 2;
                $sx = min($sx, $ex) + $rx;
                $sy = min($sy, $ey) + $ry;
                $this->Ellipse($sx, $sy, $rx, $ry);
                break;
            case 'rectangle':
                $w = abs($sx - $ex);
                $h = abs($sy - $ey);
                $sx = min($sx, $ex);
                $sy = min($sy, $ey);
                $this->Rect($sx, $sy, $w, $h);
                break;
            case 'highlight':
                $w = abs($sx - $ex);
                $h = 12.0 * $this->scale;
                $sx = min($sx, $ex);
                $sy = min($sy, $ey) - $h * 0.5;
                $imgfile = $CFG->dirroot.'/mod/assign/feedback/pdf/pix/trans'.$colour.'.png';
                $this->Image($imgfile, $sx, $sy, $w, $h);
                break;
            case 'freehand':
                if ($path) {
                    $scalepath = array();
                    foreach ($path as $point) {
                        $scalepath[] = intval($point) * $this->scale;
                    }
                    $this->PolyLine($scalepath, 'S');
                }
                break;
            case 'stamp':
                if (!$imgfile = self::get_stamp_file($path)) {
                    break;
                }
                $w = abs($sx - $ex);
                $h = abs($sy - $ey);
                $sx = min($sx, $ex);
                $sy = min($sy, $ey);
                $this->Image($imgfile, $sx, $sy, $w, $h);
                break;
            default: // Line.
                $this->Line($sx, $sy, $ex, $ey);
                break;
        }
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(1.0 * $this->scale);

        return true;
    }

    /**
     * Get a list of the available stamp images - the PNG files found within the mod/assign/feedback/pdf/pix/stamps folder
     * @return string[] 'stampname' => 'filepath'
     */
    public static function get_stamps() {
        global $CFG;
        static $stamplist = null;
        if ($stamplist == null) {
            $stamplist = array();
            $basedir = $CFG->dirroot.'/mod/assign/feedback/pdf/pix/stamps';
            if ($dir = opendir($basedir)) {
                while (false !== ($file = readdir($dir))) {
                    $pathinfo = pathinfo($file);
                    if (isset($pathinfo['extension']) && strtolower($pathinfo['extension']) == 'png') {
                        $stamplist[$pathinfo['filename']] = $basedir.'/'.$file;
                    }
                }
            }
        }
        return $stamplist;
    }

    /**
     * Get the location of the image file for a given stamp (or false, if it does not exist)
     * @param $stampname
     * @return mixed string|false the path to the image file for the stamp
     */
    public static function get_stamp_file($stampname) {
        if (!$stampname) {
            return false;
        }
        $stamps = self::get_stamps();
        if (!array_key_exists($stampname, $stamps)) {
            return false;
        }
        return $stamps[$stampname];
    }

    /**
     * Save the completed PDF to the given file
     * @param $filename string the filename for the PDF (including the full path)
     */
    public function save_pdf($filename) {
        $this->Output($filename, 'F');
    }

    /**
     * Set the path to the folder in which to generate page image files
     * @param $folder string
     */
    public function set_image_folder($folder) {
        $this->imagefolder = $folder;
    }

    /**
     * Generate an image of the specified page in the PDF
     * @param $pageno int the page to generate the image of
     * @throws moodle_exception
     * @throws coding_exception
     * @return string the filename of the generated image
     */
    public function get_image($pageno) {
        if (!$this->filename) {
            throw new coding_exception('Attempting to generate a page image without first setting the PDF filename');
        }

        if (!$this->imagefolder) {
            throw new coding_exception('Attempting to generate a page image without first specifying the image output folder');
        }

        if (!is_dir($this->imagefolder)) {
            throw new coding_exception('The specified image output folder is not a valid folder');
        }

        $imagefile = $this->imagefolder.'/image_page'.$pageno.'.png';
        $generate = true;
        if (file_exists($imagefile)) {
            if (filemtime($imagefile)>filemtime($this->filename)) {
                // Make sure the image is newer than the PDF file.
                $generate = false;
            }
        }

        if ($generate) {
            // Use ghostscript to generate an image of the specified page.
            $gsexec = get_config('assignfeedback_pdf', 'gspath');
            $imageres = 100;
            $filename = $this->filename;
            $command = "$gsexec -q -sDEVICE=png16m -dSAFER -dBATCH -dNOPAUSE -r$imageres -dFirstPage=$pageno -dLastPage=$pageno ".
                "-dGraphicsAlphaBits=4 -dTextAlphaBits=4 -sOutputFile=\"$imagefile\" \"$filename\" 2>&1";
            $result = exec($command);
            if (!file_exists($imagefile)) {
                $fullerror = 'Command:'.htmlspecialchars($command).'<br/>';
                $fullerror .= 'Result:'.htmlspecialchars($result).'<br/>';
                throw new moodle_exception('errorgenerateimage', 'assignfeedback_pdf', '', $fullerror);
            }
        }

        return 'image_page'.$pageno.'.png';
    }

    /**
     * Check to see if PDF is version 1.4 (or below); if not: use ghostscript to convert it
     * @param stored_file $file
     * @return bool false if the PDF is invalid, true if the PDF is valid (or has been converted)
     */
    public static function ensure_pdf_compatible(stored_file $file) {
        global $CFG;

        $fp = $file->get_content_file_handle();
        $ident = fread($fp, 10);
        if (substr_compare('%PDF-', $ident, 0, 5) !== 0) {
            return false; // This is not a PDF file at all.
        }
        $ident = substr($ident, 5); // Remove the '%PDF-' part.
        $ident = explode('\x0A', $ident); // Truncate to first '0a' character.
        list($major, $minor) = explode('.', $ident[0]); // Split the major / minor version.
        $major = intval($major);
        $minor = intval($minor);
        if ($major == 0 || $minor == 0) {
            return false; // Not a valid PDF version number.
        }
        if ($major = 1 && $minor<=4) {
            return true; // We can handle this version - nothing else to do.
        }

        $temparea = $CFG->dataroot.'/temp/assignsubmission_pdf';
        $hash = $file->get_contenthash(); // Use the contenthash to make sure the temp files have unique names.
        $tempsrc = $temparea."/src-$hash.pdf";
        $tempdst = $temparea."/dst-$hash.pdf";

        if (!file_exists($temparea)) {
            if (!mkdir($temparea, 0777, true)) {
                die("Unable to create temporary folder $temparea");
            }
        }

        $file->copy_content_to($tempsrc); // Copy the file.

        $gsexec = get_config('assignfeedback_pdf', 'gspath');
        $command = "$gsexec -q -sDEVICE=pdfwrite -dBATCH -dNOPAUSE -sOutputFile=\"$tempdst\" \"$tempsrc\" 2>&1";
        exec($command);
        if (!file_exists($tempdst)) {
            return false; // Something has gone wrong in the conversion.
        }

        $fileinfo = array(
            'contextid' => $file->get_contextid(),
            'component' => $file->get_component(),
            'filearea' => $file->get_filearea(),
            'itemid' => $file->get_itemid(),
            'filename' => $file->get_filename(),
            'filepath' => $file->get_filepath()
        );
        $file->delete(); // Delete the original file.
        $fs = get_file_storage();
        $fs->create_file_from_pathname($fileinfo, $tempdst); // Create replacement file.
        @unlink($tempsrc); // Delete the temporary files.
        @unlink($tempdst);

        return true;
    }

    public static function test_gs_path($generateimage = true) {
        global $CFG;

        $ret = (object)array(
            'status' => self::GSPATH_OK,
            'message' => null,
        );
        $gspath = get_config('assignfeedback_pdf', 'gspath');
        if (empty($gspath)) {
            $ret->status = self::GSPATH_EMPTY;
            return $ret;
        }
        if (!file_exists($gspath)) {
            $ret->status = self::GSPATH_DOESNOTEXIST;
            return $ret;
        }
        if (is_dir($gspath)) {
            $ret->status = self::GSPATH_ISDIR;
            return $ret;
        }
        if (!is_executable($gspath)) {
            $ret->status = self::GSPATH_NOTEXECUTABLE;
            return $ret;
        }

        $testfile = $CFG->dirroot.'/mod/assign/feedback/pdf/testgs.pdf';
        if (!file_exists($testfile)) {
            $ret->status = self::GSPATH_NOTESTFILE;
            return $ret;
        }

        if (!$generateimage) {
            return $ret;
        }

        $testimagefolder = $CFG->dataroot.'/temp/assignfeedback_pdf_test';
        @unlink($testimagefolder.'/image_page1.png'); // Delete any previous test images.
        check_dir_exists($testimagefolder, true, true);

        $pdf = new AssignPDFLib();
        $pdf->set_pdf($testfile);
        $pdf->set_image_folder($testimagefolder);
        try {
            $pdf->get_image(1);
        } catch (moodle_exception $e) {
            $ret->status = self::GSPATH_ERROR;
            $ret->message = $e->getMessage();
        }

        return $ret;
    }

    public static function send_test_image() {
        global $CFG;
        header('Content-type: image/png');
        $testimagefolder = $CFG->dataroot.'/temp/assignfeedback_pdf_test';
        $testimage = $testimagefolder.'/image_page1.png';
        @readfile($testimage);
        die();
    }
}

