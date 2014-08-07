<?php
/**
 * Form for editing library (similar to HTML) block instances.
 *
 */

class block_library extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_library');
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function instance_allow_multiple() {
        return false;
    }

    function get_content() {
        global $CFG, $DB, $COURSE, $OUTPUT;

        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== NULL) {
            return $this->content;
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;
        if ($this->content_is_trusted()) {
            // fancy html allowed only on course, category and system blocks.
            $filteropt->noclean = true;
        }

        $this->content = new stdClass;
        $this->content->footer = '';

        $this->librarian = isset($this->config->librarian) ? $this->config->librarian : '';
        if($this->librarian) {
                $user = $DB->get_record('user', array('id' => $this->librarian));
                if($user) {
                        $this->content->text .= "<p><strong>Librarian</strong><br>";
                        $this->content->text .= "<a href=\"$CFG->wwwroot/user/view.php?id=$user->id&course=$COURSE->id\">$user->firstname $user->lastname</a>";
                        $this->content->text .= "<br><a href=\"mailto:$user->email\">$user->email</a>";
                        if($user->phone1) {
                                $this->content->text .= "<br>Office Extension: x$user->phone1";
                        }
                        $this->content->text .= '</p>';
                }

        }

        $this->content->text .= '<p><img src="'.$OUTPUT->pix_url('i/libicon').'" height="16" width="16" alt="Library Homepage" />';
        $this->content->text .= ' <a href="http://library.hampshire.edu" target="_blank">Library Homepage</a></p>';



        if (isset($this->config->text)) {
            // rewrite url
            $this->config->text = file_rewrite_pluginfile_urls($this->config->text, 'pluginfile.php', $this->context->id, 'block_library', 'content', NULL);
            // Default to FORMAT_HTML which is what will have been used before the
            // editor was properly implemented for the block.
            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config
            if (isset($this->config->format)) {
                $format = $this->config->format;
            }
            $this->content->text .= format_text($this->config->text, $format, $filteropt);
        } else {
            $this->content->text .= '';
        }

        unset($filteropt); // memory footprint

        return $this->content;
    }


    /**
     * Serialize and store config data
     */
    function instance_config_save($data, $nolongerused = false) {
        global $DB;

        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links to match
        $config->text = file_save_draft_area_files($data->text['itemid'], $this->context->id, 'block_library', 'content', 0, array('subdirs'=>true), $data->text['text']);
        $config->format = $data->text['format'];

        parent::instance_config_save($config, $nolongerused);
    }

    function instance_delete() {
        global $DB;
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_library');
        return true;
    }

    function content_is_trusted() {
        global $SCRIPT;

        if (!$context = get_context_instance_by_id($this->instance->parentcontextid)) {
            return false;
        }
        //find out if this block is on the profile page
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // this is exception - page is completely private, nobody else may see content there
                // that is why we allow JS here
                return true;
            } else {
                // no JS on public personal pages, it would be a big security issue
                return false;
            }
        }

        return true;
    }
}
