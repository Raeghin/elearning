<script src="assets/js/jquery.min.js"></script>
<script type="text/javascript">
    $(function () {

        $("#assignuserform").submit(function () {
            var courseid = $("input[name='courseid']").val();
            $(".insufficientfunds").css(({"display": "none"}));
            $.ajax({
                url: $("input[name='url']").val(),
                type: 'post',
                data: {
                    "assignUserFunc": "1",
                    "userid": $("select[name='username'] option:selected").val(),
                    "enddate": $("input[name='enddate']").val(),
                    "courseid": $("input[name='courseid']").val(),
                    "creatorid": $("input[name='creatorid']").val()
                },
                success: function (response) {
                    if (response === 'useralreadyenrolled')
                        alert("User already enrolled");
                    else if(response === 'notenoughcredits')
                        $(".insufficientfunds").css(({"display": "block"}));
                    else {
                        alert(response);

                        var form= document.createElement('form');
                        form.method= 'post';
                        form.action= window.location.href;
                        var input= document.createElement('input');

                        input.type= 'hidden';
                        input.name= 'id';
                        input.value= courseid;
                        form.appendChild(input);
                        document.body.appendChild(form);
                        form.submit();
                        return false;
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(xhr.status);
                    alert(thrownError);

                }
            });
            return false;
        });
    });
</script>


<?php
    if(isset($_POST['id']))
    {
        $courseid = $_POST['id'];
        $this->model->setcourseid($courseid);
    } elseif (isset($_POST['courseid']))
    {
        $courseid = $_POST['courseid'];
        $this->model->setcourseid($courseid);
    }
?>

<div class="creditbody">
    <div class="error" id="insufficientfunds" style="display:none"><p><?php echo $this->get_string('insufficientcredits'); ?></p></div>
    <div class="overview">
        <div>
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
        </div>
    </div>


    <div class="overview">
        <div class="title"><p><?php echo $this->get_string('titleuseroverview'); ?></p></div>
        <div id="useroverview">
            <table id="tbl_participants" class="pure-table pure-table-bordered" style="margin-top: 20px;width:90%;">
                <?php
                echo '<thead><tr><th colspan="5" style="text-align: center">' . $this->get_string('assignedusers') . '</th></tr></thead>';
                echo '<tr><th>' . $this->get_string('username') . '</th><th>' . $this->get_string('firstname') . '</th><th>' . $this->get_string('lastname') . '</th><th>' . $this->get_string('timestart') . '</th><th>' . $this->get_string('timeend') . '</th></tr>';

                $count = 0;

                $activeparticipants = $this->model->getactivecourseparticipants($this->get_user()->id);
                foreach($activeparticipants as $user) {
                    $count++;

                    $dt = new DateTime();
                    $dt->setTimestamp($user->timestart);
                    $timestart = $dt->format('d-m-Y');

                    if ($user->timeend > 0) {
                        $dt->setTimestamp($user->timeend);
                        $dt->modify('-1 days');
                        $timeend = $dt->format('d-m-Y');
                    } else {
                        $timeend = '-';
                    }

                    if ($count % 2 <> 0)
                        echo '<tr class="pure-table-odd">';
                    else
                        echo '<tr>';
                    echo '<td>' . $user->username . '</td><td>' . $user->firstname . '</td><td>' . $user->lastname . '</td><td>' . $timestart . '</td><td>' . $timeend . '</td></tr>';

                }
                ?>
                </table>
            </div>
        </div>
        <div class="overview">
            <div class="useroverview">
                <table class="pure-table pure-table-bordered" style="margin-top: 20px;width:90%;">
                    <?php
                    echo '<thead><tr><th colspan="5" style="text-align: center">' . $this->get_string('titleuserassign') . '</th></tr></thead>';
                    echo '<tr><th>' . $this->get_string('username') . ' (' . $this->get_string('email') . ')</th><th>' . $this->get_string('timeend') . '</th></tr>';

                    $dt = new DateTime();
                    $dt->setTimestamp($dt->getTimeStamp());
                    $now = $dt->format('d-m-Y');

                    echo '<form action="" method="POST" id="assignuserform"><input name="courseid" type="hidden" value="' . $course->id . '" />';
                    echo '<input name="creatorid" type="hidden" value="' . $this->get_user()->id . '" />';
                    echo '<input name="url" value="' .  $this->geturl('editcourse') . '" type="hidden"/>';
                    echo '<tr><td><select name="username">';

                    foreach($this->model->getcreatedusers($this->get_user()->id) as $user) {
                        echo 'User ' . $user->firstname . ' id: ' . $user->id . ' ' . time() . ' \'' . $this->model->checkifuseralreadyenrolled($user) . '\'<br/>';
                        if(!$this->model->checkifuseralreadyenrolled($user, $activeparticipants))
                            echo '<option value="' . $user->id . '">' . $user->username . '</option>';
                    }

                    echo '</select></td><td><input name="enddate" value="' . $now . '"></td><td><input type="submit" value="Toevoegen"></td></tr></form>';

                    ?>
            </table>
        </div>
    </div>
</div>