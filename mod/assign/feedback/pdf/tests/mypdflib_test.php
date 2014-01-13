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
 * Test the functionality of mypdflib
 *
 * @package   assignfeedback_pdf
 * @copyright 2013 Davo Smith, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/mod/assign/feedback/pdf/mypdflib.php');

/**
 * Class assignfeedback_mypdflib_testcase
 * @group assignfeedback_pdf
 */
class assignfeedback_mypdflib_testcase extends advanced_testcase {

    protected $tempdir = null;

    public function setUp() {
        parent::setUp();
        $this->resetAfterTest(true);
        if (!$this->tempdir = make_temp_directory('assignfeedback_pdf')) {
            throw new coding_exception("Unable to create temporary directory");
        }
    }

    /**
     * Get the path to one of the test PDFs. Available files are:
     * 1: 17 pages long
     * 2: 2 pages long
     * cover: 1 page coversheet
     * convert: 3 page document that is PDFv1.5
     *
     * @param $name
     * @return string
     * @throws coding_exception
     */
    protected static function pdf_path($name) {
        global $CFG;

        $filename = $CFG->dirroot."/mod/assign/feedback/pdf/tests/pdf_test{$name}.pdf";
        if (!is_readable($filename)) {
            throw new coding_exception("No PDF test file found with ID {$name}");
        }
        return $filename;
    }

    public function test_load_pdf() {
        $pdf = new AssignPDFLib();
        $size = $pdf->load_pdf(self::pdf_path('1'));
        $this->assertEquals(17, $size);
    }

    public function test_combine_pdfs_no_coversheet() {
        $pdflist = array(self::pdf_path('1'), self::pdf_path('2'));
        $outfile = $this->tempdir.'/testout.pdf';
        $this->assertFileNotExists($outfile);

        $pdf = new AssignPDFLib();
        $size = $pdf->combine_pdfs($pdflist, $outfile);
        $this->assertEquals(19, $size);
        $this->assertFileExists($outfile);
    }

    public function test_combine_pdfs_coversheet() {
        $pdflist = array(self::pdf_path('1'), self::pdf_path('2'));
        $coversheet = self::pdf_path('cover');
        $outfile = $this->tempdir.'/testout.pdf';
        $this->assertFileNotExists($outfile);

        $pdf = new AssignPDFLib();
        $size = $pdf->combine_pdfs($pdflist, $outfile, $coversheet);
        $this->assertEquals(20, $size);
        $this->assertFileExists($outfile);
    }

    public function test_combine_pdfs_coversheet_template() {
        $pdflist = array(self::pdf_path('1'), self::pdf_path('2'));
        $coversheet = self::pdf_path('cover');
        $outfile = $this->tempdir.'/testout.pdf';
        $templatefields = array(
            (object)array('xpos' => 100, 'ypos' => 100, 'type' => 'text', 'width' => 150, 'data' => 'Testing text'),
            (object)array('xpos' => 100, 'ypos' => 120, 'type' => 'shorttext', 'data' => 'Testing some shorttext'),
            (object)array('xpos' => 100, 'ypos' => 140, 'type' => 'date', 'setting' => 'd/m/Y'),
        );
        $this->assertFileNotExists($outfile);

        $pdf = new AssignPDFLib();
        $size = $pdf->combine_pdfs($pdflist, $outfile, $coversheet, $templatefields);
        $this->assertEquals(20, $size);
        $this->assertFileExists($outfile);
    }

    public function test_create_image() {
        $pdf = new AssignPDFLib();
        $pdf->set_pdf(self::pdf_path('1'));
        $pdf->set_image_folder($this->tempdir);

        $imagefile = $pdf->get_image(1); // First page.
        $this->assertFileExists($this->tempdir.'/'.$imagefile);

        $imagefile = $pdf->get_image(10); // Somewhere in the middle page.
        $this->assertFileExists($this->tempdir.'/'.$imagefile);

        $imagefile = $pdf->get_image(17); // Last page.
        $this->assertFileExists($this->tempdir.'/'.$imagefile);
    }

    public function test_ensure_pdf_compatible() {
        $fs = get_file_storage();

        // Test a file that is already PDF version 1.4.
        $pdf = new AssignPDFLib();
        $filerecord = array(
            'contextid' => context_system::instance()->id,
            'component' => 'assignfeedback_pdf',
            'filearea' => 'phpunit',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'testfileok.pdf',
        );
        $fileok = $fs->create_file_from_pathname($filerecord, self::pdf_path('1'));
        $result = $pdf->ensure_pdf_compatible($fileok);
        $this->assertEquals(true, $result); // Checking has gone OK.
        $outfile = $this->tempdir.'/pdfout.pdf';
        $fileok->copy_content_to($outfile);
        $pagecount = $pdf->load_pdf($outfile);
        $this->assertEquals(17, $pagecount); // File can be loaded by the pdf library.

        // Test a file that is PDF version 1.5.
        $pdf = new AssignPDFLib();
        $filerecord = array(
            'contextid' => context_system::instance()->id,
            'component' => 'assignfeedback_pdf',
            'filearea' => 'phpunit',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'testfileconvert.pdf',
        );
        $fileconvert = $fs->create_file_from_pathname($filerecord, self::pdf_path('convert'));
        $result = $pdf->ensure_pdf_compatible($fileconvert);
        $this->assertEquals(true, $result); // Checking has gone OK.
        $outfile = $this->tempdir.'/pdfout.pdf';
        $fileconvert->copy_content_to($outfile);
        $pagecount = $pdf->load_pdf($outfile);
        $this->assertEquals(3, $pagecount); // File can be loaded by the pdf library.
    }

    public function test_annotating_pdf() {
        $pdf = new AssignPDFLib();
        $pdf->load_pdf(self::pdf_path('1'));

        // Add comments + annotations to page 1.
        $pdf->copy_page();
        $pdf->add_comment('Test comment', 100, 100, 150, 'yellow');
        $pdf->add_comment('Test comment2', 100, 150, 100, 'green');
        $pdf->add_annotation(90, 90, 110, 110, 'red', 'line');
        $pdf->add_annotation(140, 140, 230, 230, 'yellow', 'oval');
        $pdf->add_annotation(150, 150, 110, 90, 'green', 'rectangle');
        $pdf->add_annotation(160, 160, 170, 170, 'black', 'stamp', 'smile');
        $pdf->add_annotation(200, 200, 250, 250, 'blue', 'highlight');
        $pdf->add_annotation(0, 0, 0, 0, 'white', 'freehand', array(10, 10, 20, 20, 40, 40, 35, 40));

        // Add comments + annotations to page 2.
        $pdf->copy_page();
        $pdf->add_comment('Comment on page 2', 140, 200, 100, 'clear');
        $pdf->add_annotation(40, 40, 250, 250, 'yellow', 'line');

        // Copy the rest of the pages.
        $pdf->copy_remaining_pages();

        // Save the PDF and check it exists.
        $outfile = $this->tempdir.'/outfile.pdf';
        $pdf->save_pdf($outfile);
        $this->assertFileExists($outfile);

        // Load the PDF and check it has the right number of pages.
        $pdf = new AssignPDFLib();
        $size = $pdf->load_pdf($outfile);
        $this->assertEquals(17, $size);
    }
}
