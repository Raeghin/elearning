<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/ccv/lib.php');

class block_ccv extends block_base {

    /**
     * Sets the block title
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_ccv');
    }

    /**
     *  we have global config/settings data
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Controls whether multiple instances of the block are allowed on a page
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Controls whether the block is configurable
     *
     * @return bool
     */
    public function instance_allow_config() {
        return false;
    }

    /**
     * Defines where the block can be added
     *
     * @return array
     */
    public function applicable_formats() {
        return array(
            'course-view'    => true,
            'site'           => true,
            'mod'            => true,
            'my'             => true
        );
    }

    /**
     * Creates the blocks main content
     *
     * @return string
     */
    public function get_content() {
        global $USER, $COURSE, $CFG, $OUTPUT, $DB;

        // If content has already been generated, don't waste time generating it again.
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        $blockinstancesonpage = array();

        // Guests do not have any progress. Don't show them the block.
        if (!isloggedin() or isguestuser()) {
            return $this->content;
        }

        if (has_capability('block/ccv:overview', $this->context)) {
           	$parameters = array();
           	$url = new moodle_url('/blocks/ccv/overview.php', $parameters);
           	$label = get_string('overview', 'block_ccv');
           	$options = array('class' => 'overviewButton');
          	$this->content->text .= $OUTPUT->single_button($url, $label, 'post', $options);
        }
        
        return $this->content;
    }
}
