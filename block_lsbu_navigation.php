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

/**
 * This file contains classes used to manage the navigation structures in Moodle
 * and was introduced as part of the changes occuring in Moodle 2.0
 *
 * @since     2.0
 * @package   block_navigation
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The global navigation tree block class
 *
 * Used to produce the global navigation block new to Moodle 2.0
 *
 * @package   block_navigation
 * @category  navigation
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_lsbu_navigation extends block_base {

    /** @var string The name of the block */
    public $blockname = null;

    /** @var int Trim characters from the right */
    const TRIM_RIGHT = 1;
    /** @var int Trim characters from the left */
    const TRIM_LEFT = 2;
    /** @var int Trim characters from the center */
    const TRIM_CENTER = 3;

    /**
     * Set the initial properties for the block
     */
    function init() {
        global $CFG, $COURSE, $USER;
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', $this->blockname);
    }

    /**
     * All multiple instances of this block
     * @return bool Returns false
     */
    function instance_allow_multiple() {
        return true;
    }

    /**
     * Set the applicable formats for this block to all
     * @return array
     */
    function applicable_formats() {
        return array('all' => true);
    }

    /**
     * Allow the user to configure a block instance
     * @return bool Returns true
     */
    function instance_allow_config() {
        return true;
    }

    /**
     * The navigation block cannot be hidden by default as it is integral to
     * the navigation of Moodle.
     *
     * @return false
     */
    function  instance_can_be_hidden() {
        return false;
    }

    /**
     * Find out if an instance can be docked.
     *
     * @return bool true or false depending on whether the instance can be docked or not.
     */
    function instance_can_be_docked() {
        return (parent::instance_can_be_docked() && (empty($this->config->enabledock) || $this->config->enabledock=='yes'));
    }

    function get_personal_info() {
        global $USER;
        
        // username firstname surname
        $result = html_writer::start_tag('div', array('class'=>'username'));
        $result .= $USER->username . ' (' . $USER->firstname . ', ' . $USER->lastname . ')';
        $result .= html_writer::end_tag('div');
        
        // student id / staff number
        $result .= html_writer::start_tag('div', array('class'=>'idnumber'));
        $result .= $USER->idnumber;
        $result .= html_writer::end_tag('div');
        
        return $result;
    }
    
    /**
     * Gets the content for this block by grabbing it from $this->page
     *
     * @return object $this->content
     */
    function get_content() {
        global $USER, $COURSE, $OUTPUT;
        
        if($this->content !== NULL) {
            return $this->content;
        }
        
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        
        // Construct an array containing the different elements that make up the block's content
        $content = array();
        
       if(!empty($USER->id) && $COURSE->id==1) {
           $content[] = $this->get_personal_info();
        }
        /*
         Structure output tree for internal courses...
        
        Current Year (12/13)
        |
        --- Courses
        |
        --- Modules
        |
        --- Student support
        
        Previous Year (11/12)
        |
        --- Courses
        |
        --- Modules
        */
        
        $moodle_courses = enrol_get_all_users_courses($USER->id, true, 'id, shortname, modinfo, sectioncache');
        
        // We contruct a multidimentional array of academic years, modules and courses
        $course_tree = array();
        
        $lsbu_api = lsbu_api::getInstance();
        
        $course_tree = $lsbu_api->get_raw_course_tree($moodle_courses);
        
        $renderer = $this->page->get_renderer('block_lsbu_navigation');
        
        $renderer->initialise($course_tree);
        
        // render internal courses

        // The renderer knows how to render the course hierarchy
        $content[] = $renderer->get_rendered_hierarchy();

        $this->content->text = implode($content);

        return $this->content;
    }

    /**
     * Returns the attributes to set for this block
     *
     * This function returns an array of HTML attributes for this block including
     * the defaults.
     * {@link block_tree::html_attributes()} is used to get the default arguments
     * and then we check whether the user has enabled hover expansion and add the
     * appropriate hover class if it has.
     *
     * @return array An array of HTML attributes
     */
    public function html_attributes() {
        $attributes = parent::html_attributes();
        if (!empty($this->config->enablehoverexpansion) && $this->config->enablehoverexpansion == 'yes') {
            $attributes['class'] .= ' block_js_expansion';
        }
        return $attributes;
    }

    /**
     * Trims the text and shorttext properties of this node and optionally
     * all of its children.
     *
     * @param navigation_node $node
     * @param int $mode One of navigation_node::TRIM_*
     * @param int $long The length to trim text to
     * @param int $short The length to trim shorttext to
     * @param bool $recurse Recurse all children
     */
    public function trim(navigation_node $node, $mode=1, $long=50, $short=25, $recurse=true) {
        switch ($mode) {
            case self::TRIM_RIGHT :
                if (textlib::strlen($node->text)>($long+3)) {
                    // Truncate the text to $long characters
                    $node->text = $this->trim_right($node->text, $long);
                }
                if (is_string($node->shorttext) && textlib::strlen($node->shorttext)>($short+3)) {
                    // Truncate the shorttext
                    $node->shorttext = $this->trim_right($node->shorttext, $short);
                }
                break;
            case self::TRIM_LEFT :
                if (textlib::strlen($node->text)>($long+3)) {
                    // Truncate the text to $long characters
                    $node->text = $this->trim_left($node->text, $long);
                }
                if (is_string($node->shorttext) && textlib::strlen($node->shorttext)>($short+3)) {
                    // Truncate the shorttext
                    $node->shorttext = $this->trim_left($node->shorttext, $short);
                }
                break;
            case self::TRIM_CENTER :
                if (textlib::strlen($node->text)>($long+3)) {
                    // Truncate the text to $long characters
                    $node->text = $this->trim_center($node->text, $long);
                }
                if (is_string($node->shorttext) && textlib::strlen($node->shorttext)>($short+3)) {
                    // Truncate the shorttext
                    $node->shorttext = $this->trim_center($node->shorttext, $short);
                }
                break;
        }
        if ($recurse && $node->children->count()) {
            foreach ($node->children as &$child) {
                $this->trim($child, $mode, $long, $short, true);
            }
        }
    }
    /**
     * Truncate a string from the left
     * @param string $string The string to truncate
     * @param int $length The length to truncate to
     * @return string The truncated string
     */
    protected function trim_left($string, $length) {
        return '...'.textlib::substr($string, textlib::strlen($string)-$length, $length);
    }
    /**
     * Truncate a string from the right
     * @param string $string The string to truncate
     * @param int $length The length to truncate to
     * @return string The truncated string
     */
    protected function trim_right($string, $length) {
        return textlib::substr($string, 0, $length).'...';
    }
    /**
     * Truncate a string in the center
     * @param string $string The string to truncate
     * @param int $length The length to truncate to
     * @return string The truncated string
     */
    protected function trim_center($string, $length) {
        $trimlength = ceil($length/2);
        $start = textlib::substr($string, 0, $trimlength);
        $end = textlib::substr($string, textlib::strlen($string)-$trimlength);
        $string = $start.'...'.$end;
        return $string;
    }
}
