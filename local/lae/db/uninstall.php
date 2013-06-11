<?php
    function xmldb_local_lae_uninstall() {
        global $DB;
        $dbman = $DB->get_manager();
        
        // By design the anonymous user remains
        // Restore normal forum schema
        $table = new xmldb_table('forum');
        $field = new xmldb_field('anonymous');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
        // Restore normal forum post schema
        $table = new xmldb_table('forum_posts');
        $field = new xmldb_field('hiddenuserid');
        if ($dbman->field_exists($table, $field)) $dbman->drop_field($table, $field);
        return true;
    }
?>
