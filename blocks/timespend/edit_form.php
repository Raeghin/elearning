<?php

class block_timespend_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        require_once 'timespend_lib.php';

        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('selectyesno', 'config_show_timespend', get_string('show_timespend', 'block_timespend'));
        $mform->addHelpButton('config_show_timespend', 'show_timespend', 'block_timespend');
        $mform->setDefault('config_text', 0);

        $limit_opts = array();
        for ($i = 1; $i <= 150; $i++) {
            $limit_opts[$i * 60] = $i;
        }
        $mform->addElement('select', 'config_limit', get_string('limit', 'block_timespend'), $limit_opts);
        $mform->addHelpButton('config_limit', 'limit', 'block_timespend');
        $mform->setDefault('config_limit', BLOCK_TIMESPEND_DEFAULT_REGEN_TIME);
    }
}