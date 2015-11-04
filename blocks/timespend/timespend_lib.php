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

// Default session time limit in seconds
define('BLOCK_TIMESPEND_DEFAULT_SESSION_LIMIT', 20 * 60);
// Ignore sessions with a duration less than defined value in seconds
define('BLOCK_TIMESPEND_IGNORE_SESSION_TIME', 59);
// Default regeneration time in seconds
define('BLOCK_TIMESPEND_DEFAULT_REGEN_TIME', 60 * 15);

// Generate TIMESPEND reports based in passed params
class block_TIMESPEND_manager {

    protected $course;
    protected $mintime;
    protected $maxtime;
    protected $limit;

    function __construct($course, $mintime, $maxtime, $limit) {
        $this->course = $course;
        $this->mintime = $mintime;
        $this->maxtime = $maxtime;
        $this->limit = $limit;
    }

    public function get_students_timespend($students) {
        global $DB;

        $rows = array();

        $where = 'courseid = :courseid AND userid = :userid AND timecreated >= :mintime AND timecreated <= :maxtime';
        $params = array(
            'courseid' => $this->course->id,
            'userid' => 0,
            'mintime' => $this->mintime,
            'maxtime' => $this->maxtime
        );

        $perioddays = ($this->maxtime - $this->mintime) / DAYSECS;

        foreach ($students as $user) {
            $daysconnected = array();
            $params['userid'] = $user->id;
            $logs = block_timespend_utils::get_events_select($where, $params);

            if ($logs) {
                $previouslog = array_shift($logs);
                $previouslogtime = $previouslog->time;
                $sessionstart = $previouslog->time;
                $TIMESPEND = 0;
                $daysconnected[date('Y-m-d', $previouslog->time)] = 1;

                foreach ($logs as $log) {
                    if (($log->time - $previouslogtime) > $this->limit) {
                        $timespend += $previouslogtime - $sessionstart;
                        $sessionstart = $log->time;
                    }
                    $previouslogtime = $log->time;
                    $daysconnected[date('Y-m-d', $log->time)] = 1;
                }
                $timespend += $previouslogtime - $sessionstart;
            } else {
                $timespend = 0;
            }
            $groups = groups_get_user_groups($this->course->id, $user->id);
            $group = !empty($groups) && !empty($groups[0]) ? $groups[0][0] : 0;
            $rows[] = (object) array(
                'user' => $user,
                'groupid' => $group,
                'timespendtime' => $timespend,
                'connectionratio' => round(count($daysconnected) / $perioddays, 2),
            );
        }

        return $rows;
    }

    public function download_students_timespend($rows) {
        $groups = groups_get_all_groups($this->course->id);

        $headers = array(
            array(
                get_string('sincerow', 'block_timespend'),
                userdate($this->mintime),
                get_string('torow', 'block_timespend'),
                userdate($this->maxtime),
                get_string('perioddiffrow', 'block_timespend'),
                format_time($this->maxtime - $this->mintime),
            ),
            array(''),
            array(
                get_string('firstname'),
                get_string('lastname'),
                get_string('group'),
                get_string('timespendrow', 'block_timespend') . ' (' . get_string('mins') . ')',
                get_string('timespendrow', 'block_timespend'),
                get_string('connectionratiorow', 'block_timespend'),
            ),
        );

        foreach ($rows as $index => $row) {
            $rows[$index] = array(
                $row->user->firstname,
                $row->user->lastname,
                isset($groups[$row->groupid]) ? $groups[$row->groupid]->name : '',
                round($row->timespendtime / MINSECS),
                block_timespend_utils::format_timespend($row->timespendtime),
                $row->connectionratio,
            );
        }

        $rows = array_merge($headers, $rows);

        return block_timespend_utils::generate_download("{$this->course->shortname}_timespend", $rows);
    }

    public function get_user_timespend($user, $simple = false) {
        $where = 'courseid = :courseid AND userid = :userid AND timecreated >= :mintime AND timecreated <= :maxtime';
        $params = array(
            'courseid' => $this->course->id,
            'userid' => $user->id,
            'mintime' => $this->mintime,
            'maxtime' => $this->maxtime
        );
        $logs = block_timespend_utils::get_events_select($where, $params);

        if ($simple) {
            // Return total timespend time in seconds
            $total = 0;

            if ($logs) {
                $previouslog = array_shift($logs);
                $previouslogtime = $previouslog->time;
                $sessionstart = $previouslogtime;

                foreach ($logs as $log) {
                    if (($log->time - $previouslogtime) > $this->limit) {
                        $timespend = $previouslogtime - $sessionstart;
                        $total += $timespend;
                        $sessionstart = $log->time;
                    }
                    $previouslogtime = $log->time;
                }
                $timespend = $previouslogtime - $sessionstart;
                $total += $timespend;
            }

            return $total;

        } else {
            // Return user sessions with details
            $rows = array();

            if ($logs) {
                $previouslog = array_shift($logs);
                $previouslogtime = $previouslog->time;
                $sessionstart = $previouslogtime;
                $ips = array($previouslog->ip => true);

                foreach ($logs as $log) {
                    if (($log->time - $previouslogtime) > $this->limit) {
                        $timespend = $previouslogtime - $sessionstart;

                        // Ignore sessions with a really short duration
                        if ($timespend > BLOCK_TIMESPEND_IGNORE_SESSION_TIME) {
                            $rows[] = (object) array('start_date' => $sessionstart, 'timespendtime' => $timespend, 'ips' => array_keys($ips));
                            $ips = array();
                        }
                        $sessionstart = $log->time;
                    }
                    $previouslogtime = $log->time;
                    $ips[$log->ip] = true;
                }

                $timespend = $previouslogtime - $sessionstart;

                // Ignore sessions with a really short duration
                if ($timespend > BLOCK_TIMESPEND_IGNORE_SESSION_TIME) {
                    $rows[] = (object) array('start_date' => $sessionstart, 'timespendtime' => $timespend, 'ips' => array_keys($ips));
                }
            }

            return $rows;
        }
    }

    // Downloads user timespend with passed data
    public function download_user_timespend($user) {
        $headers = array(
            array(
                get_string('sincerow', 'block_timespend'),
                userdate($this->mintime),
                get_string('torow', 'block_timespend'),
                userdate($this->maxtime),
                get_string('perioddiffrow', 'block_timespend'),
                format_time($this->maxtime - $this->mintime),
            ),
            array(''),
            array(
                get_string('firstname'),
                get_string('lastname'),
                get_string('sessionstart', 'block_timespend'),
                get_string('timespendrow', 'block_timespend') . ' ' . get_string('secs'),
                get_string('sessionduration', 'block_timespend'),
                'IP',
            )
        );

        $rows = $this->get_user_timespend($user);
        foreach ($rows as $index => $row) {
            $rows[$index] = array(
                $user->firstname,
                $user->lastname,
                userdate($row->start_date),
                $row->timespendtime,
                block_timespend_utils::format_timespend($row->timespendtime),
                implode(', ', $row->ips),
            );
        }

        $rows = array_merge($headers, $rows);

        return block_timespend_utils::generate_download("{$this->course->shortname}_timespend", $rows);
    }

}

// Utils functions used by block timespend
class block_timespend_utils {

    public static $LOGSTORES = array('logstore_standard', 'logstore_legacy');

    // Return formatted events from logstores
    public static function get_events_select($selectwhere, array $params) {
        $return = array();

        static $allreaders = NULL;

        if (is_null($allreaders)) {
            $allreaders = get_log_manager()->get_readers();
        }

        $processed_readers = 0;

        foreach (self::$LOGSTORES as $name) {
            if (isset($allreaders[$name])) {
                $reader = $allreaders[$name];
                $events = $reader->get_events_select($selectwhere, $params, 'timecreated ASC', 0, 0);
                foreach ($events as $event) {
                    // Note: see \core\event\base to view base class of event
                    $obj = new stdClass();
                    $obj->time = $event->timecreated;
                    $obj->ip = $event->get_logextra()['ip'];
                    $return[] = $obj;
                }
                if (!empty($events)) {
                    $processed_readers++;
                }
            }
        }

        // Sort mixed array by time ascending again only when more of a reader has added events to return array
        if ($processed_readers > 1) {
            usort($return, function($a, $b) { return $a->time > $b->time; });
        }

        return $return;
    }

    // Formats time based in Moodle function format_time($totalsecs)
    public static function format_timespend($totalsecs) {
        $totalsecs = abs($totalsecs);

        $str = new stdClass();
        $str->hour = get_string('hour');
        $str->hours = get_string('hours');
        $str->min = get_string('min');
        $str->mins = get_string('mins');
        $str->sec = get_string('sec');
        $str->secs = get_string('secs');

        $hours = floor($totalsecs / HOURSECS);
        $remainder = $totalsecs - ($hours * HOURSECS);
        $mins = floor($remainder / MINSECS);
        $secs = $remainder - ($mins * MINSECS);

        $ss = ($secs == 1) ? $str->sec : $str->secs;
        $sm = ($mins == 1) ? $str->min : $str->mins;
        $sh = ($hours == 1) ? $str->hour : $str->hours;

        $ohours = '';
        $omins = '';
        $osecs = '';

        if ($hours)
            $ohours = $hours . ' ' . $sh;
        if ($mins)
            $omins = $mins . ' ' . $sm;
        if ($secs)
            $osecs = $secs . ' ' . $ss;

        if ($hours)
            return trim($ohours . ' ' . $omins);
        if ($mins)
            return trim($omins . ' ' . $osecs);
        if ($secs)
            return $osecs;
        return get_string('none');
    }

    // Formats ips
    public static function format_ips($ips) {
        return implode(', ', array_map('block_timespend_utils::link_ip', $ips));
    }

    // Generates an linkable ip
    public static function link_ip($ip) {
        return html_writer::link("http://en.utrace.de/?query=$ip", $ip, array('target' => '_blank'));
    }

    // Table styles
    public static function get_table_styles() {
        global $PAGE;

        // Twitter Bootstrap styling
        if (in_array('bootstrapbase', $PAGE->theme->parents)) {
            $styles = array(
                'table_class' => 'table table-striped table-bordered table-hover table-condensed table-timespend',
                'header_style' => 'background-color: #333; color: #fff;'
            );
        } else {
            $styles = array(
                'table_class' => 'table-timespend',
                'header_style' => ''
            );
        }

        return $styles;
    }

    // Generates generic Excel file for download
    public static function generate_download($download_name, $rows) {
        global $CFG;

        require_once($CFG->libdir . '/excellib.class.php');

        $workbook = new MoodleExcelWorkbook('-', 'excel5');
        $workbook->send(clean_filename($download_name));

        $myxls = $workbook->add_worksheet(get_string('pluginname', 'block_timespend'));

        $row_count = 0;
        foreach ($rows as $row) {
            foreach ($row as $index => $content) {
                $myxls->write($row_count, $index, $content);
            }
            $row_count++;
        }

        $workbook->close();

        return $workbook;
    }

}
