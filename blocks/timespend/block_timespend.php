<?php

class block_timespend extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_timespend');
    }

    function specialization() {
        // Previous block versions didn't have config settings
        if ($this->config === null) {
            $this->config = new stdClass();
        }
        // Set always show_timespend config settings to avoid errors
        if (!isset($this->config->show_timespend)) {
            $this->config->show_timespend = 1;
        }
    }

    function get_content() {
        global $OUTPUT, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if ($this->config->show_timespend == 1) {
            require_once 'timespend_lib.php';
            $mintime = $this->page->course->startdate;
            $maxtime = time();
            $dm = new block_timespend_manager($this->page->course, $mintime, $maxtime, $this->config->limit);
            $timespend_time = $dm->get_user_timespend($USER, true);
            $this->content->text .= html_writer::tag('p', get_string('timespend_estimation', 'block_timespend'));
            $this->content->text .= html_writer::tag('p', block_timespend_utils::format_timespend($timespend_time));
        }

        if (has_capability('block/timespend:use', context_block::instance($this->instance->id))) {
            $this->content->footer .= html_writer::tag('hr', null);
            $this->content->footer .= html_writer::tag('p', get_string('access_info', 'block_timespend'));
            $url = new moodle_url('/blocks/timespend/timespend.php', array(
                'courseid' => $this->page->course->id,
                'instanceid' => $this->instance->id,
            ));
            $this->content->footer .= $OUTPUT->single_button($url, get_string('access_button', 'block_timespend'), 'get');
        }

        return $this->content;
    }

    function applicable_formats() {
        return array('course' => true);
    }

}
