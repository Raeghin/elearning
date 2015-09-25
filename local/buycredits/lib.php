<?php

function local_buycredits_extend_settings_navigation($navigation, $context) {
	global $USER;

	if($USER->id <> 84)
		return;

	if (!has_capability('local/buycredits:view', context_system::instance())) {
        return;
    }
	
	$nodeCredits = $navigation->add('Credits');

	$nodeBuy = $nodeCredits->add(get_string('buycredits', 'local_buycredits'), new moodle_url('/local/buycredits/index.php'));
	$nodeAssign = $nodeCredits->add(get_string('assigncredits', 'local_buycredits'), new moodle_url('/local/buycredits/assigncredits.php'));


}