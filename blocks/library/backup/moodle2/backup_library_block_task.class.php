<?php
/**
 * Specialised backup task for the library block
 * (requires encode_content_links in some configdata attrs)
 *
 */
class backup_library_block_task extends backup_block_task {

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
    }

    public function get_fileareas() {
        return array('content');
    }

    public function get_configdata_encoded_attributes() {
        return array('text'); // We need to encode some attrs in configdata
    }

    static public function encode_content_links($content) {
        return $content; // No special encoding of links
    }
}

