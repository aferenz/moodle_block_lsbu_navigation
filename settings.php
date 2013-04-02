<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    
    // Display options
    $settings->add(new admin_setting_heading('block_lsbu_navigation_displayheader', get_string('displayheader', 'block_lsbu_navigation'), ''));
     
    $options = array(
            '0' => get_string('fullname','block_lsbu_navigation'),
            '1' => get_string('fullname_shortname','block_lsbu_navigation'),
            '2' => get_string('fullname_idnumber','block_lsbu_navigation'),
            '3' => get_string('shortname','block_lsbu_navigation'),
            '4' => get_string('idnumber','block_lsbu_navigation')
    );
    
    $dbtype = new admin_setting_configselect('block_lsbu_navigation/displaytype', get_string('displaytype','block_lsbu_navigation'), '', '', $options);
    $settings->add($dbtype);
}
