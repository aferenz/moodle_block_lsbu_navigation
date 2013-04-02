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

namespace block_lsbu_navigation\locallib;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/local/lsbu_api/lib.php';

function getUsername() {
    global $USER;
    
    $result = \html_writer::tag('div', s($USER->username).'('.s($USER->firstname).','.s($USER->lastname).')', array('id' => 'username'));
    
    return $result;
}

function getStudentNumber() {
    global $USER;
    
    if(strlen($USER->idnumber) > 0) {
        $result = \html_writer::tag('div', s($USER->idnumber), array('id' => 'student_number'));
    } else {
        $result = \html_writer::tag('div', get_string('student_number_not_found', 'block_lsbu_navigation'), array('id' => 'student_number'));
    }
    
    return $result;
}

function getStaffNumber() {
    global $USER;

    if(strlen($USER->idnumber) > 0) {
        $result = \html_writer::tag('div', s($USER->idnumber), array('id' => 'staff_number'));
    } else {
        $result = \html_writer::tag('div', get_string('staff_number_not_found', 'block_lsbu_navigation'), array('id' => 'staff_number'));
    }

    return $result;
}

function getMissingModuleLink() {
    $result = '';
    
    // custom links - return all links for now but we can obtain faculty from API???
    
    $result .= \html_writer::link('https://my.lsbu.ac.uk/page/faculty-offices-ahs', get_string('faculty-offices-ahs', 'block_lsbu_navigation'),array('class' => 'external_link', 'target' => '_blank'));
    $result .= \html_writer::empty_tag('br');
    $result .= \html_writer::link('https://my.lsbu.ac.uk/page/faculty-offices-bus', get_string('faculty-offices-bus', 'block_lsbu_navigation'),array('class' => 'external_link', 'target' => '_blank'));
    $result .= \html_writer::empty_tag('br');
    $result .= \html_writer::link('https://my.lsbu.ac.uk/page/faculty-offices-esbe', get_string('faculty-offices-esbe', 'block_lsbu_navigation'),array('class' => 'external_link', 'target' => '_blank'));
    $result .= \html_writer::empty_tag('br');
    $result .= \html_writer::link('https://my.lsbu.ac.uk/page/faculty-offices-hsc', get_string('faculty-offices-hsc', 'block_lsbu_navigation'),array('class' => 'external_link', 'target' => '_blank'));
    
    $result = \html_writer::tag('div', $result, array('id' => 'faculty_links'));
    
    return $result;
}

function getMessagingAnnouncements() {
    global $CFG;
    
    $result = \html_writer::link($CFG->wwwroot.'/message/index.php?viewing=recentnotifications', get_string('message_announcements', 'block_lsbu_navigation'), array('class' => 'announcements', 'target' => '_blank'));
    $result = \html_writer::tag('div', $result, array('id' => 'messaging_announcements'));
    
    return $result;
}
?>