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
    
    
    /**
     * Returns the content of the navigation tree.
     *
     * @param global_navigation $navigation
     * @param int $expansionlimit
     * @param array $options
     * @return string $content
     */
    public function navigation_tree(lsbu_navigation $navigation, $expansionlimit, array $options = array()) {
        $navigation->add_class('navigation_node');
        $content = $this->navigation_node(array($navigation), array('class'=>'block_tree list'), $expansionlimit, $options);
        if (isset($navigation->id) && !is_numeric($navigation->id) && !empty($content)) {
            $content = $this->output->box($content, 'block_tree_box', $navigation->id);
        }
        return $content;
    }
    /**
     * Produces a navigation node for the navigation tree
     *
     * @param array $items
     * @param array $attrs
     * @param int $expansionlimit
     * @param array $options
     * @param int $depth
     * @return string
     */
    protected function navigation_node($items, $attrs=array(), $expansionlimit=null, array $options = array(), $depth=1) {
    
        // exit if empty, we don't want an empty ul element
        if (count($items)==0) {
            return '';
        }
    
        // array of nested li elements
        $lis = array();
        foreach ($items as $item) {
            if (!$item->display && !$item->contains_active_node()) {
                continue;
            }
            $content = $item->get_content();
            $title = $item->get_title();
    
            $isexpandable = (empty($expansionlimit) || ($item->type > navigation_node::TYPE_ACTIVITY || $item->type < $expansionlimit) || ($item->contains_active_node() && $item->children->count() > 0));
            $isbranch = $isexpandable && ($item->children->count() > 0 || ($item->has_children() && (isloggedin() || $item->type <= navigation_node::TYPE_CATEGORY)));
    
            // Skip elements which have no content and no action - no point in showing them
            if (!$isexpandable && empty($item->action)) {
                continue;
            }
    
            $hasicon = ((!$isbranch || $item->type == navigation_node::TYPE_ACTIVITY || $item->type == navigation_node::TYPE_RESOURCE) && $item->icon instanceof renderable);
    
            if ($hasicon) {
                $icon = $this->output->render($item->icon);
            } else {
                $icon = '';
            }
            $content = $icon.$content; // use CSS for spacing of icons
            if ($item->helpbutton !== null) {
                $content = trim($item->helpbutton).html_writer::tag('span', $content, array('class'=>'clearhelpbutton'));
            }
    
            if ($content === '') {
                continue;
            }
    
            $attributes = array();
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            if (is_string($item->action) || empty($item->action) || ($item->type === navigation_node::TYPE_CATEGORY && empty($options['linkcategories']))) {
                $attributes['tabindex'] = '0'; //add tab support to span but still maintain character stream sequence.
                $content = html_writer::tag('span', $content, $attributes);
            } else if ($item->action instanceof action_link) {
                //TODO: to be replaced with something else
                $link = $item->action;
                $link->text = $icon.$link->text;
                $link->attributes = array_merge($link->attributes, $attributes);
                $content = $this->output->render($link);
                $linkrendered = true;
            } else if ($item->action instanceof moodle_url) {
                $content = html_writer::link($item->action, $content, $attributes);
            }
    
            // this applies to the li item which contains all child lists too
            $liclasses = array($item->get_css_type(), 'depth_'.$depth);
            $liexpandable = array();
            if ($item->has_children() && (!$item->forceopen || $item->collapse)) {
                $liclasses[] = 'collapsed';
            }
            if ($isbranch) {
                $liclasses[] = 'contains_branch';
                $liexpandable = array('aria-expanded' => in_array('collapsed', $liclasses) ? "false" : "true");
            } else if ($hasicon) {
                $liclasses[] = 'item_with_icon';
            }
            if ($item->isactive === true) {
                $liclasses[] = 'current_branch';
            }
            $liattr = array('class' => join(' ',$liclasses)) + $liexpandable;
            // class attribute on the div item which only contains the item content
            $divclasses = array('tree_item');
            if ($isbranch) {
                $divclasses[] = 'branch';
            } else {
                $divclasses[] = 'leaf';
            }
            if ($hasicon) {
                $divclasses[] = 'hasicon';
            }
            if (!empty($item->classes) && count($item->classes)>0) {
                $divclasses[] = join(' ', $item->classes);
            }
            $divattr = array('class'=>join(' ', $divclasses));
            if (!empty($item->id)) {
                $divattr['id'] = $item->id;
            }
            $content = html_writer::tag('p', $content, $divattr);
            if ($isexpandable) {
                $content .= $this->navigation_node($item->children, array(), $expansionlimit, $options, $depth+1);
            }
            if (!empty($item->preceedwithhr) && $item->preceedwithhr===true) {
                $content = html_writer::empty_tag('hr') . $content;
            }
            $content = html_writer::tag('li', $content, $liattr);
            $lis[] = $content;
        }
    
        if (count($lis)) {
            return html_writer::tag('ul', implode("\n", $lis), $attrs);
        } else {
            return '';
        }
    }

}


