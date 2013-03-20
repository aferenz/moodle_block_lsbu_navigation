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
 * Outputs the navigation tree.
 *
 * @since     2.0
 * @package   block_navigation
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Renderer for block navigation
 *
 * @package   block_navigation
 * @category  navigation
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_lsbu_navigation_renderer extends plugin_renderer_base {
    /**
     * Returns the content of the navigation tree.
     *
     * @param global_navigation $navigation
     * @param int $expansionlimit
     * @param array $options
     * @return string $content
     */
    public function navigation_tree(global_navigation $navigation, $expansionlimit, array $options = array()) {
        global $COURSE, $USER;
        
        $content='';
        
        if(!empty($USER->id) && $COURSE->id==1) {
            // username firstname surname
            $content = html_writer::start_tag('div', array('class'=>'username'));
            $content .= $USER->username . ' (' . $USER->firstname . ', ' . $USER->lastname . ')';
            $content .= html_writer::end_tag('div');
            
            // student id / staff number
            $content .= html_writer::start_tag('div', array('class'=>'idnumber'));
            $content .= $USER->idnumber;
            $content .= html_writer::end_tag('div');
            
            if($this->isStudent($USER->username)==true) {
        
                // custom links
                switch($USER->institution) {
                    case "AHS" :
                            $content .= html_writer::start_tag('p');
                            $content .= html_writer::link('https://my.lsbu.ac.uk/page/faculty-offices-ahs', get_string('missingamodule', 'block_lsbu_navigation'),array('class' => 'external_link', 'target' => '_blank'));
                            $content .= html_writer::end_tag('p');
                            break;
                    case "BUS" :
                            $content .= html_writer::start_tag('p');
                            $content .= html_writer::link('https://my.lsbu.ac.uk/page/faculty-offices-bus', get_string('missingamodule', 'block_lsbu_navigation'),array('class' => 'external_link', 'target' => '_blank'));
                            $content .= html_writer::end_tag('p');
                            break;
                    case "ESBE" :
                            $content .= html_writer::start_tag('p');
                            $content .= html_writer::link('https://my.lsbu.ac.uk/page/faculty-offices-esbe', get_string('missingamodule', 'block_lsbu_navigation'),array('class' => 'external_link', 'target' => '_blank'));                            
                            $content .= html_writer::end_tag('p');
                            break;
                    case "HSC" :
                            $content .= html_writer::start_tag('p');
                            $content .= html_writer::link('https://my.lsbu.ac.uk/page/faculty-offices-hsc', get_string('missingamodule', 'block_lsbu_navigation'),array('class' => 'external_link', 'target' => '_blank'));                            
                            $content .= html_writer::end_tag('p');
                            break;
                    case "LDC" :
                            $content .= html_writer::start_tag('p');
                            $content .= html_writer::link('https://my.lsbu.ac.uk/page/communication-skills-development-contact-us', get_string('missingamodule', 'block_lsbu_navigation'),array('class' => 'external_link', 'target' => '_blank'));                            
                            $content .= html_writer::end_tag('p');
                            break;
                }
                
                // Messaging announcements– a Moodle link
                $content .= html_writer::start_tag('p');
                $content .= html_writer::link('/message/index.php?viewing=recentnotifications', get_string('message_announcements', 'block_lsbu_navigation'),array('class' => 'announcements'));
                $content .= html_writer::end_tag('p');
            } 
        }
        
        $navigation->add_class('navigation_node');
        $content .= $this->navigation_node(array($navigation), array('class'=>'block_tree list'), $expansionlimit, $options);
        if (isset($navigation->id) && !is_numeric($navigation->id) && !empty($content)) {
            $content = $this->output->box($content, 'block_tree_box', $navigation->id);
        }
        return $content;
    }
    
    /**
     *
     * function to check if the logged in user is a student
     *
     */
    private function isStudent($username)
    {
        global $DB;
        
        // TODO get database name from db extended config plugins setting
        $sql="SELECT role FROM mis_lsbu.moodle_users where username='$username'";
        
        $roles = array();
        
        $roles = $DB->get_records_sql($sql ,null);
        
        foreach($roles as $role)
        {
            if(!empty($role->role))
            {
                return true;    
            }
        }
        
        return false;    
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
                // djsomers - use full name for course
                // for students do not create a link from hidden items but still display the item
                // e.g. hidden courses are not navigable
                if($item->hidden && $this->isStudent($USER->username)==true) {
                    $content = $title;
                } else {
                    $content = html_writer::link($item->action, $title, $attributes);
                }
            }

            // djsomers - for students do not show children of hidden items (e.g. hidden courses)
            if($item->hidden && $this->isStudent($USER->username)==true) {
                
            } else {
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
            }
            
            $content = html_writer::tag('li', $content, $liattr);
           
            // djsomers - if in the context of a course - only show the active course
            global $COURSE;
            if($item->type === navigation_node::TYPE_COURSE && $COURSE->id!=1 && $COURSE->id!=$item->key) {
               
            } else {
                $lis[] = $content;
            }
        }
       
        if (count($lis)) {
            return html_writer::tag('ul', implode("\n", $lis), $attrs);
        } else {
            return '';
        }
    }

}


