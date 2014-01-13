<?php
// This file is part of the submit PDF plugin for Moodle - http://moodle.org/
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

require_once(dirname(__FILE__).'/../../../../config.php');
global $CFG, $DB, $PAGE;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/assign/feedback/pdf/mypdflib.php');

class edit_templates {

    public $courseid;
    public $templateid;
    public $imagetime;
    public $itemid;
    public $url;

    public $extrajs = '';

    public function __construct($courseid, $templateid = 0, $imagetime = false, $itemid = 0) {
        $this->courseid = $courseid;
        $this->templateid = $templateid;
        $this->imagetime = $imagetime;
        $this->itemid = $itemid;

        $this->url = new moodle_url('/mod/assign/submission/pdf/edittemplate.php', array('courseid'=>$courseid) );
        if ($templateid) {
            $this->url->param('templateid', $templateid);
        }
        if ($itemid) {
            $this->url->param('itemid', $itemid);
        }
        if ($imagetime) {
            $this->url->param('imagetime', $imagetime);
        }
    }

    public function view() {
        global $PAGE;

        $PAGE->set_url($this->url);
        $PAGE->set_title('Edit Templates');
        $PAGE->set_pagelayout('popup');

        $this->process_actions();

        $this->view_header();

        echo $this->extrajs;
        $this->view_select_template();
        $this->view_template_edit_form();
        $this->view_item_form();
        $this->view_image();

        $this->view_footer();
    }

    public function caneditsite() {
        return has_capability('moodle/site:config', context_system::instance());
    }

    public function view_header() {
        global $OUTPUT;

        echo $OUTPUT->header();
    }

    public function view_footer() {
        global $OUTPUT;

        echo $OUTPUT->footer();
    }

    public function view_select_template() {
        global $DB, $OUTPUT;

        echo $OUTPUT->box_start();
        echo '<form name="selecttemplate" enctype="multipart/form-data" method="get" action="edittemplates.php">';
        echo '<fieldset>';
        // Output the hidden params, apart from those listed.
        echo html_writer::input_hidden_params($this->url, array('templateid', 'itemid'));
        echo '<label for="templateid">'.get_string('choosetemplate', 'assignsubmission_pdf').': </label>';
        echo '<select name="templateid" onchange="document.selecttemplate.submit();">';
        if ($this->templateid == -1) {
            echo '<option value="-1" selected="selected">'.get_string('newtemplate', 'assignsubmission_pdf').'</option>';
        } else {
            echo '<option value="-1">'.get_string('newtemplate', 'assignsubmission_pdf').'</option>';
        }
        $templates_data = $DB->get_records_select('assignsubmission_pdf_tmpl', 'courseid = 0 OR courseid = ?',
                                                  array($this->courseid) );
        foreach ($templates_data as $td) {
            $selected = '';
            if ($td->id == $this->templateid) {
                $selected = ' selected="selected" ';
            }
            echo '<option value="'.$td->id.'"'.$selected.'>'.s($td->name).'</option>';
        }
        echo '</select>';
        echo '<input type="submit" name="selecttemplate" value="'.get_string('select', 'assignsubmission_pdf').'" />';
        echo '</fieldset>';
        echo '</form>';
        echo $OUTPUT->box_end();
    }

    public function view_template_edit_form() {
        global $DB, $OUTPUT;

        if ($this->templateid == 0) {
            return;
        }

        echo $OUTPUT->box_start();

        echo '<form name="edittemplate" enctype="multipart/form-data" method="post" action="edittemplates.php">';
        echo '<fieldset>';
        if ($uses = $this->count_uses($this->templateid)) {
            echo '<p>'.get_string('templateusecount', 'assignsubmission_pdf', $uses);
            echo ' <input type="submit" name="showwhereused" value="'.get_string('showwhereused', 'assignsubmission_pdf').'" />';
        } else {
            echo '<p>'.get_string('templatenotused', 'assignsubmission_pdf').'';
        }
        if (optional_param('showwhereused', false, PARAM_TEXT)) {
            echo '<br/>';
            echo get_string('showused', 'assignsubmission_pdf').':<br />';
            echo '<ul>';
            $sql = "SELECT a.id, a.name
                      FROM {assign_plugin_config} ac
                      JOIN {assign} a ON a.id = ac.assignment
                     WHERE ac.subtype = 'assignsubmission' AND ac.plugin = 'pdf' AND ac.name = 'templateid'
                       AND ac.value = ?";
            $usestemplate = $DB->get_records_sql($sql, array($this->templateid));
            foreach ($usestemplate as $ut) {
                echo '<li>'.s($ut->name).'</li>';
            }
            echo '</ul>';
        }
        echo '</p>';
        echo html_writer::input_hidden_params($this->url, array('itemid')); // Output the hidden params, apart from those listed.
        $caneditsite = $this->caneditsite();
        $templatename = '';
        $sitetemplate = '';
        $editdisabled = '';
        $template = $DB->get_record('assignsubmission_pdf_tmpl', array('id' => $this->templateid) );
        if ($template) {
            $templatename = $template->name;
            if ($template->courseid == 0) {
                $sitetemplate = ' checked="checked" ';
                if (!$caneditsite) {
                    $editdisabled = ' disabled="disabled" ';
                    echo '<p>'.get_string('cannotedit', 'assignsubmission_pdf').'</p>';
                }
            }
        }
        if (!$caneditsite) {
            $sitetemplate .= ' disabled="disabled" ';
        }
        echo '<label for="templatename">'.get_string('templatename', 'assignsubmission_pdf').': </label>';
        echo '<input type="text" name="templatename" value="'.$templatename.'"'.$editdisabled.' /><br />';
        echo '<input type="checkbox" name="sitetemplate"'.$sitetemplate.' id="sitetemplate" />'.
            '<label for="sitetemplate">'.get_string('sitetemplate', 'assignsubmission_pdf').' </label>';
        echo get_string('sitetemplatehelp', 'assignsubmission_pdf');
        echo '<br /><br />';
        echo '<input type="submit" name="savetemplate" value="'.
            get_string('savetemplate', 'assignsubmission_pdf').'"'.$editdisabled.' />&nbsp;';
        if (($this->templateid > 0) && ($uses == 0)) {
            $deletedisabled = $editdisabled;
        } else {
            $deletedisabled = ' disabled="disabled" ';
        }
        echo '<input type="submit" name="deletetemplate" value="'.
            get_string('deletetemplate', 'assignsubmission_pdf').'"'.$deletedisabled.'/>';
        echo '<br />';
        echo '<input type="submit" name="duplicatetemplate" value="'.
            get_string('duplicatetemplate', 'assignsubmission_pdf').'"/>';
        if ($this->templateid > 0) {
            echo '<br />';
            echo '<label for="itemid">'.get_string('chooseitem', 'assignsubmission_pdf').': </label>';
            echo '<select name="itemid" id="itemid" onchange="document.edittemplate.submit();">';
            if ($this->itemid == -1) {
                echo '<option value="-1" selected="selected">'.get_string('newitem', 'assignsubmission_pdf').'</option>';
            } else {
                echo '<option value="-1">'.get_string('newitem', 'assignsubmission_pdf').'</option>';
            }
            $items_data = $DB->get_records('assignsubmission_pdf_tmplit', array('templateid' => $this->templateid) );
            if (!empty($items_data)) {
                $datenum = 1;
                if ($this->itemid == 0) {
                    $this->itemid = reset($items_data)->id;
                    if ($this->itemid) {
                        $this->url->param('itemid', $this->itemid);
                    }
                }
                foreach ($items_data as $item) {
                    $selected = '';
                    if ($item->id == $this->itemid) {
                        $selected = ' selected="selected" ';
                    }
                    $itemtext = 'Unknown';
                    if ($item->type == 'date') {
                        $itemtext = get_string('itemdate', 'assignsubmission_pdf').$datenum;
                        $datenum++;
                    } else if (($item->type == 'text') || ($item->type == 'shorttext')) {
                        $itemtext = $item->setting;
                        if (strlen($itemtext) > 22) {
                            $itemtext = substr($itemtext, 0, 20).'...';
                        }
                    }

                    echo '<option value="'.$item->id.'"'.$selected.'>'.s($itemtext).'</option>';
                }
            }
            echo '</select>';
            echo '<input type="submit" name="selectitem" id="selectitem" value="'.
                get_string('select', 'assignsubmission_pdf').'" />';
        }

        echo '</fieldset>';
        echo '</form>';

        echo $OUTPUT->box_end();
    }

    public function view_item_form() {
        global $DB, $OUTPUT;

        if ($this->itemid == 0) {
            return;
        }

        $canedit = false;
        if ($this->caneditsite()) {
            $canedit = true;
        } else {
            $template = $DB->get_record('assignsubmission_pdf_tmpl', array('id' => $this->templateid) );
            if ($template && $template->courseid > 0) {
                $canedit = true;
            }
        }

        $disabled = '';
        if (!$canedit) {
            $disabled = ' disabled="disabled" ';
        }
        $item = false;
        if ($this->itemid > 0) {
            $item = $DB->get_record('assignsubmission_pdf_tmplit', array('id' => $this->itemid) );
        }
        $deletedisabled = '';
        if (!$item || !$canedit) {
            $deletedisabled = ' disabled="disabled" ';
        }
        if (!$item) {
            $item = new stdClass();
            $item->type = 'shorttext';
            $item->xpos = 0;
            $item->ypos = 0;
            $item->width = 0;
            $item->setting = get_string('enterformtext', 'assignsubmission_pdf');
        }

        // Javascript to allow more interactive editing of the items (by clicking on the image).
        echo '<script type="text/javascript">';
        echo 'var oldtype = "'.$item->type.'";';
        // Enable save/cancel buttons, disable selection.
        echo 'function EnableSave() {';
        if ($canedit) {
            echo 'document.getElementById("saveitem").removeAttribute("disabled");';
            echo 'document.getElementById("cancelitem").removeAttribute("disabled");';
            echo 'document.getElementById("itemid").setAttribute("disabled", "disabled");';
            echo 'document.getElementById("selectitem").setAttribute("disabled", "disabled");';
        }
        echo '}';
        // Highlight the titles of items that are unsaved.
        echo 'function Highlight(name) {';
        echo 'document.getElementById(name+"_label").style.fontWeight = "bold";';
        echo 'UpdatePreview();';
        echo 'EnableSave();';
        echo '}';
        // Move the preview item on the page.
        echo 'function UpdatePreview() {';
        echo 'currel = document.getElementById("current_template_item"); if (!currel) return;';
        echo 'currel.style.left = document.getElementById("itemx").value + "px";';
        echo 'currel.style.top = document.getElementById("itemy").value + "px";';
        echo 'type = document.getElementById("itemtype").value;';
        echo 'if ((type != "date") || (oldtype != "date"))';
        echo '{ currel.innerHTML = document.getElementById("itemsetting").value; }';
        echo 'if (type == "text") { currel.style.width = document.getElementById("itemwidth").value + "px"; }';
        echo 'else { currel.style.width = ""; }';
        echo '}';
        echo 'function Left(obj) {
                 var curleft = 0;
                 if (obj.offsetParent) {
                     while (1) {
                         curleft += obj.offsetLeft;
                         if (!obj.offsetParent) {
                             break;
                         }
                         obj = obj.offsetParent;
                     }
                 } else if (obj.x) {
                      curleft += obj.x;
                 }
                 return curleft;
             }';
        echo 'function Top(obj) {
                  var curtop = 0;
                  if (obj.offsetParent) {
                      while (1) {
                          curtop += obj.offsetTop;
                          if (!obj.offsetParent) {
                              break;
                          }
                          obj = obj.offsetParent;
                      }
                  } else if (obj.y) {
                      curtop += obj.y;
                  }
                  return curtop;
              }';
        // Update the 'item' position when the image is clicked on.
        echo 'function clicked_on_image(e) {';
        echo 'var targ;
              if (!e) {
                  var e = e.target;
              }
              if (e.target) {
                  targ = e.target;
              } else if (e.srcElement) {
                  targ = e.srcElement;
              }';
        echo 'pos_x = e.offsetX?(e.offsetX):e.pageX-Left(targ);';
        echo 'pos_y = e.offsetY?(e.offsetY):e.pageY-Top(targ);';
        echo 'document.getElementById("itemx").value = pos_x; document.getElementById("itemy").value = pos_y;';
        echo 'Highlight("itemx"); Highlight("itemy");';
        echo '}';
        // Fill in a default value when different types selected.
        echo 'function type_changed() {';
        echo 'Highlight("itemtype");';
        echo 'var newtype = document.getElementById("itemtype").value;
              var settingel = document.getElementById("itemsetting");';
        echo 'if (newtype == "date") {
                  settingel.value = "d/m/Y"; Highlight("itemsetting");
              }  else if (oldtype == "date") {
                  settingel.value = "'.get_string('enterformtext', 'assignsubmission_pdf').'";
                  Highlight("itemsetting");
              } ';
        echo 'UpdatePreview();';
        echo 'oldtype = newtype;';
        echo '}';
        echo '</script>';

        echo $OUTPUT->box_start();

        echo '<form enctype="multipart/form-data" method="post" action="edittemplates.php">';
        echo html_writer::input_hidden_params($this->url); // Output the hidden params, apart from those listed.
        echo '<fieldset>';

        echo '<table>';
        echo '<tr><td width="150pt"><label id="itemtype_label" for="itemtype">'.
            get_string('itemtype', 'assignsubmission_pdf').': </label></td>';
        echo '<td><select id="itemtype" onchange = "type_changed();" name="itemtype"'.$disabled.'>';
        echo '<option value="shorttext"'.(($item->type == 'shorttext') ? ' selected="selected" ' : '').'>'.
            get_string('itemshorttext', 'assignsubmission_pdf').'</option>';
        echo '<option value="text"'.(($item->type == 'text') ? ' selected="selected" ' : '').'>'.
            get_string('itemtext', 'assignsubmission_pdf').'</option>';
        echo '<option value="date"'.(($item->type == 'date') ? ' selected="selected" ' : '').'>'.
            get_string('itemdate', 'assignsubmission_pdf').'</option>';
        echo '</select></td></tr>';

        echo '<tr><td><label for="itemx" id="itemx_label">'.get_string('itemx', 'assignsubmission_pdf').': </label></td>';
        echo '<td><input type="text" id="itemx" name="itemx" onChange="Highlight(\'itemx\');" value="'.$item->xpos.'"'.
            $disabled.' /> '.get_string('clicktosetposition', 'assignsubmission_pdf').'</td></tr>';
        echo '<tr><td><label for="itemy" id="itemy_label">'.get_string('itemy', 'assignsubmission_pdf').': </label></td>';
        echo '<td><input type="text" id="itemy" name="itemy" onChange="Highlight(\'itemy\');" value="'.$item->ypos.'"'.
            $disabled.' /></td></tr>';
        echo '<tr><td><label for="itemsetting" id="itemsetting_label">'.
            get_string('itemsetting', 'assignsubmission_pdf').': </label></td>';
        echo '<td><input type="text" id="itemsetting" name="itemsetting" onChange="Highlight(\'itemsetting\');"
             value="'.$item->setting.'"'.$disabled.' /> '.get_string('itemsettingmore', 'assignsubmission_pdf');
        echo ' <a href="http://www.php.net/date" target="_blank">'.
            get_string('dateformatlink', 'assignsubmission_pdf').'</td></tr>';
        echo '<tr><td><label for="itemwidth" id="itemwidth_label" >'.
            get_string('itemwidth', 'assignsubmission_pdf').': </label></td>';
        echo '<td><input type="text" id="itemwidth" name="itemwidth" onChange="Highlight(\'itemwidth\');"
             value="'.$item->width.'"'.$disabled.' /> '.get_string('textonly', 'assignsubmission_pdf').'</td></tr>';
        echo '</table>';
        echo '<input type="submit" name="saveitem" id="saveitem" value="'.
            get_string('saveitem', 'assignsubmission_pdf').'"'.$disabled.'/>&nbsp;';
        echo '<input type="submit" name="cancelitem" id="cancelitem" value="'.get_string('cancel').'"'.$disabled.'/>&nbsp;&nbsp;';
        echo '<input type="submit" name="deleteitem" value="'.
            get_string('deleteitem', 'assignsubmission_pdf').'"'.$deletedisabled.'/>';
        echo '</fieldset>';
        echo '</form>';

        // Disable the 'Save Item' button until something to save.
        echo '<script type="text/javascript">';
        echo 'document.getElementById("saveitem").setAttribute("disabled", "disabled");';
        echo 'document.getElementById("cancelitem").setAttribute("disabled", "disabled");';
        echo '</script>';

        echo $OUTPUT->box_end();
    }

    public function view_image() {
        global $DB;

        if ($this->imagetime) {
            $context = context_course::instance($this->courseid);
            $fs = get_file_storage();
            if ($image = $fs->get_file($context->id, 'assignsubmission_pdf', 'previewimage', 0, '/', 'preview.png')) {
                $imginfo = $image->get_imageinfo();

                echo "<div style='width: {$imginfo['width']}px; height: {$imginfo['height']}px; border: solid 1px black;'>";
                echo '<div style="position: relative;">';
                $imageurl = new moodle_url('/mod/assign/submission/pdf/previewimage.php',
                                           array('context'=>$context->id, 'time'=>$this->imagetime));

                echo '<img src="'.$imageurl.'" alt="Preview Template" style="position:absolute;top:0px;left:0px;"
                    onclick="clicked_on_image(event);" />';
                if ($this->templateid > 0) {
                    $templateitems = $DB->get_records('assignsubmission_pdf_tmplit',
                                                      array('templateid' => $this->templateid) );
                    if ($templateitems) {
                        foreach ($templateitems as $ti) {
                            $tiwidth = '';
                            if ($ti->type == 'text') {
                                $tiwidth = ' width: '.$ti->width.'px; ';
                            }
                            $cssid = '';
                            $border = '';
                            if ($this->itemid == $ti->id) {
                                $border = 'border: dashed 1px red;';
                                $cssid = ' id = "current_template_item" ';
                            }
                            echo "<div style=\"position: absolute;{$border}font-family:helvetica, arial, sans;
                             font-size: 12px; top:{$ti->ypos}px;left:{$ti->xpos}px;$tiwidth\" $cssid >";
                            if (($ti->type == 'text') || ($ti->type == 'shorttext')) {
                                echo s($ti->setting);
                            } else if ($ti->type == 'date') {
                                echo date($ti->setting);
                            }
                            echo '</div>';
                        }
                    }
                    if ($this->itemid == -1) {
                        echo '<div style="position:absolute;border:dashed 1px red;
                              font-family:helvetica, arial, sans;font-size: 12px;
                              top: 0px; left: 0px;" id = "current_template_item">';
                        echo get_string('enterformtext', 'assignsubmission_pdf');
                        echo '</div>';
                    }
                }
                echo '</div>';
                echo '&nbsp;';
                echo '</div>';
            }
        }

        $mform = new edit_templates_form();
        $mform->addhidden($this->url->params());

        $mform->display();
    }

    public function process_actions() {

        $savetemplate = optional_param('savetemplate', false, PARAM_TEXT);
        $deletetemplate = optional_param('deletetemplate', false, PARAM_TEXT);
        $duplicatetemplate = optional_param('duplicatetemplate', false, PARAM_TEXT);
        $saveitem = optional_param('saveitem', false, PARAM_TEXT);
        $deleteitem = optional_param('deleteitem', false, PARAM_TEXT);
        $uploadpreview = optional_param('uploadpreview', false, PARAM_TEXT);

        if ($savetemplate) {
            $templatename = required_param('templatename', PARAM_TEXT);
            $sitewide = optional_param('sitetemplate', false, PARAM_BOOL);

            $this->templateid = $this->save_template($this->templateid, $templatename, $sitewide);

        } else if ($deletetemplate) {
            $this->templateid = $this->delete_template($this->templateid);

        } else if ($saveitem) {
            $item = new stdClass;
            $item->type = required_param('itemtype', PARAM_TEXT);
            $item->xpos = required_param('itemx', PARAM_INT);
            $item->ypos = required_param('itemy', PARAM_INT);
            $item->width = required_param('itemwidth', PARAM_INT);
            $item->setting = required_param('itemsetting', PARAM_TEXT);
            $item->templateid = $this->templateid;

            $this->itemid = $this->save_item($this->itemid, $item);

        } else if ($deleteitem) {
            $this->itemid = $this->delete_item($this->itemid);

        } else if ($uploadpreview) {
            $this->upload_preview();

        } else if ($duplicatetemplate) {
            $this->templateid = $this->duplicate_template($this->templateid);
        }

        if ($this->templateid) {
            $this->url->param('templateid', $this->templateid);
        }
        if ($this->itemid) {
            $this->url->param('itemid', $this->itemid);
        }
        if ($this->imagetime) {
            $this->url->param('imagetime', $this->imagetime);
        }
    }

    public function save_template($id, $name, $sitewide = false) {
        global $DB;

        if ($id == 0) {
            return 0;
        }

        $caneditsite = $this->caneditsite();

        $oldname = null;
        if ($id == -1) {
            // New template.
            $template = new stdClass();
        } else {
            $template = $DB->get_record('assignsubmission_pdf_tmpl', array('id' => $id), '*', MUST_EXIST);
            if ($template->courseid == 0) {
                if (!$caneditsite) {
                    print_error("No permission to edit site templates");
                }
            } else if ($template->courseid != $this->courseid) {
                print_error("Attempting to edit template from a different course");
            }
            $oldname = $template->name;
        }
        $template->name = $name;

        if ($sitewide && $caneditsite) {
            $template->courseid = 0;
        } else {
            $template->courseid = $this->courseid;
        }

        if ($id == -1) {
            $template->id = $DB->insert_record('assignsubmission_pdf_tmpl', $template);
            $this->extrajs .= '<script type="text/javascript">';
            $this->extrajs .= 'var el=window.opener.document.getElementById("id_assignsubmission_pdf_templateid");';
            $this->extrajs .= 'if (el) {';
            $this->extrajs .= 'var newtemp = window.opener.document.createElement("option");
                newtemp.value = "'.$template->id.'"; newtemp.innerHTML = "'.s($template->name).'";';
            $this->extrajs .= 'el.appendChild(newtemp); ';
            $this->extrajs .= '}';
            $this->extrajs .= '</script>';
            $this->itemid = -1;
        } else {
            $DB->update_record('assignsubmission_pdf_tmpl', $template);
            if ($oldname != $template->name) {
                $this->extrajs .= '<script type="text/javascript">';
                $this->extrajs .= 'var el=window.opener.document.getElementById("id_assignsubmission_pdf_templateid");';
                $this->extrajs .= 'if (el) {';
                $this->extrajs .= 'var opts = el.getElementsByTagName("option"); var i=0;';
                $this->extrajs .= 'for (i=0; i<opts.length; i++) {';
                $this->extrajs .= 'if (opts[i].value == "'.$template->id.'") {';
                $this->extrajs .= 'opts[i].innerHTML = "'. s($template->name) .'";';
                $this->extrajs .= '}}}';
                $this->extrajs .= '</script>';
            }
        }

        return $id;
    }

    public function delete_template($id) {
        global $DB;

        if ($id == 0) {
            return 0;
        }

        if ($this->count_uses($this->templateid) > 0) {
            return $id;
        }

        $template = $DB->get_record('assignsubmission_pdf_tmpl', array('id' => $id) );
        if (!$template) {
            return 0; // Already deleted?
        }

        if ($template->courseid == 0) {
            if (!$this->caneditsite()) {
                print_error("No permission to edit site templates");
            }
        } else if ($template->courseid != $this->courseid) {
            print_error('Attempting to delete a template from a different course');
        }

        $DB->delete_records('assignsubmission_pdf_tmplit', array('templateid' => $id) );
        $DB->delete_records('assignsubmission_pdf_tmpl', array('id' => $id) );

        $this->extrajs .= '<script type="text/javascript">';
        $this->extrajs .= 'var el=window.opener.document.getElementById("id_assignsubmission_pdf_templateid");';
        $this->extrajs .= 'if (el) {';
        $this->extrajs .= 'var opts = el.getElementsByTagName("option"); var i=0;';
        $this->extrajs .= 'for (i=0; i<opts.length; i++) {';
        $this->extrajs .= 'if (opts[i].value == "'.$id.'") {';
        $this->extrajs .= 'el.removeChild(opts[i]);';
        $this->extrajs .= '}}}';
        $this->extrajs .= '</script>';

        return 0;
    }

    public function save_item($id, $details) {
        global $DB;

        if ($id == 0) {
            return 0;
        }
        if ($details->templateid == 0) {
            return 0;
        }

        if ($id != -1) {
            $item = $DB->get_record('assignsubmission_pdf_tmplit', array('id' => $id), '*', MUST_EXIST);
            $template = $DB->get_record('assignsubmission_pdf_tmpl', array('id' => $item->templateid), '*', MUST_EXIST);
            if ($template->courseid == 0) {
                if (!$this->caneditsite()) {
                    print_error("No permission to edit site templates");
                }
            } else if ($template->courseid != $this->courseid) {
                print_error('Attempting to edit a template from a different course');
            }

            $details->id = $id;
        }

        if ($id == -1) {
            $id = $DB->insert_record('assignsubmission_pdf_tmplit', $details);
        } else {
            $DB->update_record('assignsubmission_pdf_tmplit', $details);
        }

        return $id;
    }

    public function delete_item($id) {
        global $DB;

        if (!$id) {
            return 0;
        }

        $item = $DB->get_record('assignsubmission_pdf_tmplit', array('id' => $id) );
        if ($item) {
            $template = $DB->get_record('assignsubmission_pdf_tmpl', array('id' => $item->templateid) );
            if ($template) {
                if ($template->courseid == 0) {
                    if (!$this->caneditsite()) {
                        print_error("No permission to edit site templates");
                    }
                } else if ($template->courseid != $this->courseid) {
                    print_error('Attempting to delete an item from a template belonging to a different course');
                }
            }
            $DB->delete_records('assignsubmission_pdf_tmplit', array('id' => $item->id) );
        }

        return 0;
    }

    public function upload_preview() {
        global $CFG;

        $imagefolder = $CFG->dataroot.'/temp/assignsubmission_pdf/img';
        if (!check_dir_exists($imagefolder, true, true)) {
            echo "Unable to create temporary image folder";
            die();
        }

        $mform = new edit_templates_form();
        $fname = $mform->save_temp_file('preview');
        if (!$fname) {
            return;
        }

        $pdf = new AssignPDFLib();
        $pdf->load_pdf($fname);
        $pdf->set_image_folder($imagefolder);
        $imgname = $pdf->get_image(1);

        $context = context_course::instance($this->courseid);

        $fs = get_file_storage();
        if ($oldfile = $fs->get_file($context->id, 'assignsubmission_pdf', 'previewimage', 0, '/', 'preview.png')) {
            $oldfile->delete();
        }

        $imginfo = array(
            'contextid' => $context->id,
            'component' => 'assignsubmission_pdf',
            'filearea' => 'previewimage',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'preview.png'
        );

        $fs->create_file_from_pathname($imginfo, $imagefolder.'/'.$imgname); // Copy the image into the file storage.

        // Delete the temporary files.
        unlink($fname);
        unlink($imagefolder.'/'.$imgname);

        $this->imagetime = time();
    }

    public function duplicate_template($srcid) {
        global $DB;

        // Should not have access to the 'duplicate' button unless a template is selected
        // but, just in case, we check here (but just do nothing if that is not the case).
        if ($srcid < 1) {
            return $srcid;
        }

        $template = $DB->get_record('assignsubmission_pdf_tmpl', array('id' => $srcid), '*', MUST_EXIST);
        $template->courseid = $this->courseid;
        $template->name = $template->name . get_string('templatecopy', 'assignsubmission_pdf');
        unset($template->id);
        $dstid = $DB->insert_record('assignsubmission_pdf_tmpl', $template);

        // Update the list on the main page.
        $this->extrajs .= '<script type="text/javascript">';
        $this->extrajs .= 'var el=window.opener.document.getElementById("id_assignsubmission_pdf_templateid");';
        $this->extrajs .= 'if (el) {';
        $this->extrajs .= 'var newtemp = window.opener.document.createElement("option"); newtemp.value = "'.$dstid.'";
            newtemp.innerHTML = "'.s($template->name).'";';
        $this->extrajs .= 'el.appendChild(newtemp); ';
        $this->extrajs .= '}';
        $this->extrajs .= '</script>';
        $this->itemid = -1;

        $items_data = $DB->get_records('assignsubmission_pdf_tmplit', array('templateid' => $srcid) );
        foreach ($items_data as $item) {
            unset($item->id);
            $item->templateid = $dstid;
            $DB->insert_record('assignsubmission_pdf_tmplit', $item);
        }

        return $dstid;
    }

    protected function count_uses($templateid) {
        global $DB;

        $sql = "subtype = 'assignsubmission' AND plugin = 'pdf' AND name = 'templateid'
                AND ".$DB->sql_compare_text('value')." = ?";
        return $DB->count_records_select('assign_plugin_config', $sql, array('value' => $templateid));
    }
}

class edit_templates_form extends moodleform {
    public function definition() {

        $mform =& $this->_form;
        $mform->addElement('filepicker', 'preview', get_string('previewinstructions', 'assignsubmission_pdf'), null,
                           array('accepted_types'=>array('*.pdf')));

        $mform->addElement('submit', 'uploadpreview', get_string('uploadpreview', 'assignsubmission_pdf'));
    }

    public function addhidden($params) {
        foreach ($params as $name => $val) {
            $this->_form->addElement('hidden', $name, $val);
        }
    }
}
