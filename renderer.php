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

require_once ( $CFG->dirroot . '/local/lsbu_api/lsbu_course.class.php' );
require_once ( $CFG->dirroot . '/local/lsbu_api/lib.php' );

use lsbu_course\lsbu_course as lsbu_course;	// Course class

/**
 * Outputs an LSBU-specifc navigation tree.
 *
 * @package   block_lsbu_navigation
 * @copyright 2013 University of London Computer Centre
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Renderer for block navigation
 *
 * @package   block_lsbu_navigation
 * @category  navigation
 * @copyright 2013 University of London Computer Centre
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_lsbu_navigation_renderer extends plugin_renderer_base {
    /**
     * This function assumes the 'raw_structure' contains a multi-dimensional array, indexed (basically) by academic year.
     *
     * @param $raw_structure
     */
    public function initialise($raw_structure)
    {
        // What is the current academic year?
        $time = time();
        $year = date('y', $time);
    
        $academicyearstart = intval(get_config('block_lsbu_course_overview', 'academicyearstart'));
    
        if(date('n', $time) < $academicyearstart){
            $this->currentacademicyear = ($year - 1).'/'.$year;
            $this->previousacademicyear = ($year - 2).'/'.($year-1);
            $this->nextacademicyear = ($year).'/'.$year+1;
        }else{
            $this->currentacademicyear = ($year).'/'.($year + 1);
            $this->previousacademicyear = ($year-1).'/'.$year;
            $this->nextacademicyear = ($year+1).'/'.($year);
        }
    
        // Current academic year goes first
        if(isset($raw_structure[$this->currentacademicyear][lsbu_course::COURSETYPE_MODULE])) {
            $this->hierarchy[$this->currentacademicyear][lsbu_course::COURSETYPE_MODULE] = $raw_structure[$this->currentacademicyear][lsbu_course::COURSETYPE_MODULE];
        }
    
        if(isset($raw_structure[$this->currentacademicyear][lsbu_course::COURSETYPE_COURSE])) {
            $this->hierarchy[$this->currentacademicyear][lsbu_course::COURSETYPE_COURSE] = $raw_structure[$this->currentacademicyear][lsbu_course::COURSETYPE_COURSE];
        }
    
        if(isset($raw_structure['n/a'][lsbu_course::COURSETYPE_STUDENTSUPPORT])) {
            $this->hierarchy[$this->currentacademicyear][lsbu_course::COURSETYPE_STUDENTSUPPORT] = $raw_structure['n/a'][lsbu_course::COURSETYPE_STUDENTSUPPORT];
        }
        // Then previous academic year
        if(isset($raw_structure[$this->previousacademicyear][lsbu_course::COURSETYPE_MODULE])) {
            $this->hierarchy[$this->previousacademicyear][lsbu_course::COURSETYPE_MODULE] = $raw_structure[$this->previousacademicyear][lsbu_course::COURSETYPE_MODULE];
        }
    
        if(isset($raw_structure[$this->previousacademicyear][lsbu_course::COURSETYPE_COURSE])) {
            $this->hierarchy[$this->previousacademicyear][lsbu_course::COURSETYPE_COURSE] = $raw_structure[$this->previousacademicyear][lsbu_course::COURSETYPE_COURSE];
        }
    
        // Finally next academic year
        if(isset($raw_structure[$this->nextacademicyear][lsbu_course::COURSETYPE_COURSE])) {
            $this->hierarchy[$this->nextacademicyear][lsbu_course::COURSETYPE_COURSE] = $raw_structure[$this->nextacademicyear][lsbu_course::COURSETYPE_COURSE];
        }
    
        if(isset($raw_structure[$this->nextacademicyear][lsbu_course::COURSETYPE_MODULE])) {
            $this->hierarchy[$this->nextacademicyear][lsbu_course::COURSETYPE_MODULE] = $raw_structure[$this->nextacademicyear][lsbu_course::COURSETYPE_MODULE];
        }
    }
    
    public function get_hierarchy() {
        return $this->hierarchy;
    }
    
    /*
     * Function to check whether the tree should expanded.
    * The tree should be expanded if its elements belong to current year.
    *
    * @param $year
    */
    public function isexpandable($year){
    
        if($year == $this->currentacademicyear){
            $result = html_writer::start_tag('li', array('class'=>'expanded'));
        }else{
            $result = html_writer::start_tag('li');
        }
    
        return $result;
    }
    
    
    public function get_header($year){
    
        if($year == $this->currentacademicyear){
            $header = html_writer::tag('h3', get_string('currentyear','block_lsbu_course_overview',$year));
        } elseif ($year == $this->previousacademicyear){
            $header = html_writer::tag('h3', get_string('previousyear','block_lsbu_course_overview',$year));
        } elseif($year == $this->nextacademicyear){
            $header = html_writer::tag('h3', get_string('nextyear','block_lsbu_course_overview',$year));
        }
        return $header;
    }
    
    
    public function get_rendered_hierarchy() {
        $result = '';
    
        if(!empty($this->hierarchy)) {
    
            $yuiconfig = array();
            $yuiconfig['type'] = 'html';
    
            $result .= html_writer::start_tag('ul');
    
            foreach ($this->hierarchy as $year=>$courses) {
    
                $result .= $this->isexpandable($year);
    
                // Heading for the year
                $result .= $this->get_header($year);
                // display courses and modules if there are any
                if(!empty($courses)) {
                    // Display courses
                    $result .= html_writer::start_tag('ul');
    
                    $lsbu_api = lsbu_api::getInstance();
                    	
                    // Display modules
                    if(isset($courses[lsbu_course::COURSETYPE_MODULE])) {
    
    
                        $modules_html = $this->isexpandable($year);
                        $modules_html .= html_writer::tag('h3', get_string('modules', 'block_lsbu_course_overview', $year));
                        $modules_html .= html_writer::start_tag('ul');
    
                        foreach ($courses[lsbu_course::COURSETYPE_MODULE] as $module) {
                            $modules_html .= html_writer::tag('li', $lsbu_api->get_lsbu_course_instance_html($module));
                        }
    
                        $modules_html .= html_writer::end_tag('ul');
                        $modules_html .= html_writer::end_tag('li');
                        $result .= $modules_html;
                    }
    
                    // Display courses
                    if(isset($courses[lsbu_course::COURSETYPE_COURSE])) {
    
                        $courses_html = $this->isexpandable($year);
                        $courses_html .= html_writer::tag('h3', get_string('courses', 'block_lsbu_course_overview', $year));
                        $courses_html .= html_writer::start_tag('ul');
                        foreach ($courses[lsbu_course::COURSETYPE_COURSE] as $course) {
                            $courses_html .= html_writer::tag('li', $lsbu_api->get_lsbu_course_instance_html($course));
                        }
    
                        $courses_html .= html_writer::end_tag('ul');
                        $courses_html .= html_writer::end_tag('li');
                        $result .= $courses_html;
                    }
    
                    // Display student support
                    if(isset($courses[lsbu_course::COURSETYPE_STUDENTSUPPORT])) {
    
                        $studentsupport_html = $this->isexpandable($year);
                        $studentsupport_html .= html_writer::tag('h3', get_string('studentsupport', 'block_lsbu_course_overview'));
                        $studentsupport_html .= html_writer::start_tag('ul');
    
                        foreach ($courses[lsbu_course::COURSETYPE_STUDENTSUPPORT] as $studentsupport) {
                            $studentsupport_html .= html_writer::tag('li', $lsbu_api->get_lsbu_course_instance_html($studentsupport));
                        }
    
                        $studentsupport_html .= html_writer::end_tag('ul');
                        $studentsupport_html .= html_writer::end_tag('li');
                        $result .= $studentsupport_html;
                    }
                    // Display support
                    if(isset($courses[lsbu_course::COURSETYPE_SUPPORT])) {
    
                        $support_html = $this->isexpandable($year);
                        $support_html .= html_writer::tag('h3', get_string('support', 'block_lsbu_course_overview'));
                        $support_html .= html_writer::start_tag('ul');
    
                        foreach ($courses[lsbu_course::COURSETYPE_SUPPORT] as $support) {
                            $support_html .= html_writer::tag('li', $lsbu_api->get_lsbu_course_instance_html($support));
                        }
    
                        $support_html .= html_writer::end_tag('ul');
                        $support_html .= html_writer::end_tag('li');
                        $result .= $support_html;
                    }//if(isset($courses[lsbu_course::COURSETYPE_SUPPORT])) {
    
                    $result .= html_writer::end_tag('ul');
    
                }//if(!empty($courses))
            }//foreach ($this->hierarchy as $year=>$courses)
    
            $result .= html_writer::end_tag('ul');
    
            // Now wrap this in a <div> for YUI to pick up
            $htmlid = 'lsbu_course_overview_tree_'.uniqid();
            $this->page->requires->js_init_call('M.block_lsbu_course_overview.init_tree', array(false, $htmlid));
            $html = '<div id="'.$htmlid.'">'.$result.'</div>';
    
            $result = $html;
    
        }//if(!empty($this->hierarchy))
    
        return $result;
    
    }
    
    
    

}


