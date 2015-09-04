<script src="assets/js/jquery.min.js"></script>

<?php
    $courseid = $_POST['id'];
    $this->model->setcourseid($courseid);
?>

<div class="creditbody">
    <div class="overview">
        <div class="title"><p><?php echo $this->get_string('titleuseroverview'); ?></p></div>
        <div id="useroverview">
            <br/>
            <table class="pure-table pure-table-bordered" style="width:90%;">
                <?php
                $course = $this->model->getcourse();

                echo '<thead><tr><th colspan="2" style="text-align: center">' . $this->get_string('course') . '</th></tr></thead>';
                echo '<tr class="pure-table-odd"><td>'.$this->get_string('coursename') . '</td>';
                echo '<td>' . $course->fullname . '</td></tr>';

                echo '<tr><td>'.$this->get_string('shortname') . '</td>';
                echo '<td>' . $course->shortname . '</td></tr>';

                echo '<tr class="pure-table-odd"><td>'.$this->get_string('summary') . '</td>';
                echo '<td>' . $course->summary . '</td></tr>';
                ?>
            </table>
            <table class="pure-table pure-table-bordered" style="margin-top: 20px;width:90%;">
                <?php
                echo '<thead><tr><th colspan="5" style="text-align: center">' . $this->get_string('assignedusers') . '</th></tr></thead>';
                echo '<tr><th>' . $this->get_string('username') . '</th><th>' . $this->get_string('firstname') . '</th><th>' . $this->get_string('lastname') . '</th><th>' . $this->get_string('timestart') . '</th><th>' . $this->get_string('timeend') . '</th></tr>';

                $count = 0;
                foreach($this->model->getcourseparticipants($this->get_user()->id) as $user) {
                    $count++;

                    $dt = new DateTime();
                    $dt->setTimestamp($user->timestart);
                    $timestart = $dt->format('d-m-Y H:i:s');

                    if($user->timeend = 0) {
                        $dt->setTimestamp($user->timeend);
                        $timeend = $dt->format('d-m-Y H:i:s');
                    } else{
                        $timeend = '-';
                    }

                    if ($count % 2 <> 0)
                        echo '<tr class="pure-table-odd">';
                    else
                        echo '<tr>';
                    echo '<td>' . $user->username . '</td><td>' . $user->firstname . '</td><td>' . $user->lastname . '</td><td>' . $timestart . '</td><td>' . $timeend . '</td></tr>';
                }
                ?>
                <table class="pure-table pure-table-bordered" style="margin-top: 20px;width:90%;">
                    <?php
                    echo '<thead><tr><th colspan="5" style="text-align: center">' . $this->get_string('titleuserassign') . '</th></tr></thead>';
                    echo '<tr><th>' . $this->get_string('username') . ' (' . $this->get_string('email') . ')</th><th>' . $this->get_string('timeend') . '</th></tr>';

                    $dt = new DateTime();
                    $dt->setTimestamp($dt->getTimeStamp());
                    $now = $dt->format('d-m-Y');

                    echo '<form action="" method="POST" id="assignuserform"><tr><td><select name="username">';

                    foreach($this->model->getcreatedusers($this->get_user()->id) as $user) {
                        echo '<option value="' . $user->id . '">' . $user->username . '</option>';
                    }

                    echo '</select></td><td><input name="enddate" value="' . $now . '"></td></tr></form>';

                    ?>
            </table>
        </div>
    </div>
</div>