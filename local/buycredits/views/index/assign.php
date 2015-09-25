<script src="assets/js/jquery.min.js"></script>

<script type="text/javascript">
    $(function () {
        $("#adduserform").submit(function () {
            $("#emailtaken").css(({"display": "none"}));
            $("#resulttext").html('');
            $.ajax({
                url: $("input[name='url']").val(),
                type: 'post',
                data: {
                    "addUserFunc": "1",
                    "email": $("input[name='email']").val(),
                    "firstname": $("input[name='firstname']").val(),
                    "lastname": $("input[name='lastname']").val(),
                    "uid": $("input[name='uid']").val()
                },
                success: function (response) {
                    if (response === 'email taken')
                        $("#emailtaken").css(({"display": "block"}));
                    else {
                        $("input[name='email']").val('');
                        $("input[name='firstname']").val('');
                        $("input[name='lastname']").val('');
                        $("#resulttext").html("<?php echo $this->get_string('usercreated'); ?> " + response);
                        $("#useroverview").load(location.href + " #useroverview>*", "");
                    }
                }
            });
            return false;
        });
    });

    $(function () {
        $("#editcourse").submit(function () {

            $.ajax({
                url: $("input[name='url']").val(),
                type: 'post',
                data: {
                    "addUserFunc": "1",
                    "email": $("input[name='email']").val(),
                    "firstname": $("input[name='firstname']").val(),
                    "lastname": $("input[name='lastname']").val(),
                    "uid": $("input[name='uid']").val()
                },
                success: function (response) {
                    if (response === 'email taken')
                        $("#emailtaken").css(({"display": "block"}));
                    else {
                        $("input[name='email']").val('');
                        $("input[name='firstname']").val('');
                        $("input[name='lastname']").val('');
                        $("#resulttext").html("<?php echo $this->get_string('usercreated'); ?> " + response);
                        $("#useroverview").load(location.href + " #useroverview>*", "");
                    }
                }
            });
            return false;
        });
    });
</script>

<div class="creditbody">
    <div class="overview">
        <div class="title"><p><?php echo $this->get_string('titleadduser'); ?></p></div>
        <div id="pure-g">

            <form action="" method="POST" id="adduserform">
                <fieldset>
                    <input name="url" value="<?php echo $this->geturl('assign'); ?>" type="hidden"/>
                    <input name="uid" value="<?php echo $this->get_user()->id; ?>" type="hidden"/>

                    <div class="pure-u-1-5">
                        <label for="firstname"><?php echo $this->get_string('firstname'); ?>:</label>
                    </div>
                    <div class="pure-u-1-3">
                        <input name="firstname"/>
                    </div>
                    <br/>

                    <div class="pure-u-1-5">
                        <label for="lastname"><?php echo $this->get_string('lastname'); ?>:</label>
                    </div>
                    <div class="pure-u-1-3">
                        <input name="lastname"/>
                    </div>
                    <br/>

                    <div class="pure-u-1-5">
                        <label for="email"><?php echo $this->get_string('email'); ?>:</label>
                    </div>
                    <div class="pure-u-1-3">
                        <input name="email"/>

                        <div style="display:none;color:red"
                             id="emailtaken"><?php echo $this->get_string('emailtaken'); ?></div>
                    </div>
                    <br/>
                    <input type="submit" value="<?php echo $this->get_string('submit'); ?>"/>
                </fieldset>
            </form>
            <div id="resulttext"></div>
        </div>
    </div>

    <div class="overview">
        <div class="title"><p><?php echo $this->get_string('titleuseroverview'); ?></p></div>
        <div id="useroverview">
            <br/>
            <table class="pure-table pure-table-bordered">
                <?php
                $count = 0;

                $courselist = $this->model->getcreatedusers($this->get_user()->id, 5);
                $numItems = count($courselist);
                foreach ($courselist as $value) {
                    if ($count == 0) {
                        echo '<thead><tr><th>' . $this->get_string('username') . '</th>';
                        echo '<th>' . $this->get_string('firstname') . '</th>';
                        echo '<th>' . $this->get_string('lastname') . '</th>';
                        echo '</tr></thead>';
                        echo '<tbody>';
                    }

                    $count++;
                    $user = $this->model->getuserdetails($value->user_userid);

                    if ($count % 2 == 0)
                        echo '<tr class="pure-table-odd"><td>' . $user->username . '</td>';
                    else
                        echo '<tr><td>' . $user->username . '</td>';
                    echo '<td>' . $user->firstname . '</td>';
                    echo '<td>' . $user->lastname . '</td>';
                    echo '</tr>';

                    if ($count == $numItems)
                        echo '</table>';
                }
                ?>
            </table>
        </div>
    </div>
    <div class="overview">
        <div class="title"><p><?php echo $this->get_string('titleuserassign'); ?></p></div>
        <div>
            <br/>
            <table class="pure-table pure-table-bordered">
                <?php
                $count = 0;

                $courselist = $this->model->getteachercourses($this->get_user()->id);
                $numItems = count($courselist);
                foreach ($courselist as $value) {
                    if ($count == 0) {
                        echo '<thead><th>' . $this->get_string('shortname') . '</th>';
                        echo '<th>' . $this->get_string('coursename') . '</th>';
                        echo '<th>' . $this->get_string('usecourse') . '</th>';
                        echo '</tr></thead>';
                        echo '<tbody>';
                    }

                    $count++;
                    if ($count % 2 == 0)
                        echo '<tr class="pure-table-odd"><td>' . $value->shortname . '</td>';
                    else
                        echo '<tr><td>' . $value->shortname . '</td>';

                    echo '<td>' . $value->fullname . '</td>';
                    echo '<td><form action="editcourse.php" method="post"><input type="hidden" value="' . $value->id . '" name="id" /><input type="submit" value="' . $this->get_string('usecourse') . '"/></td></form></td>';

                    echo '</tr>';


                    if ($count > $numItems)
                        echo '</table>';
                }
                ?>
            </table>
        </div>
    </div>


</div>
