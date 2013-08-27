<?php
 
class block_annotate_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
 
        // Section header title according to language file.
        $mform->addElement('header', 'configheader', "Document sharing");
        
        
        $mform->addElement('select', 'config_access', 'Access', array('Individual', 'Group')); 
		$mform->setDefault('access', 'Individual');
		
		$attributes='size="30"';
        $mform->addElement('text', 'config_shareuser', 'Master user', $attributes);
        $mform->setDefault('config_shareuser', '');
     

        
        $mform->addHelpButton('config_access', 'group', 'block_annotate');
        $mform->addHelpButton('config_shareuser', 'master', 'block_annotate');
           
    }
}