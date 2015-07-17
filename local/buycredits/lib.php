<?php

function local_buycredits_extend_settings_navigation($navigation, $context) {
	
	if (!has_capability('local/buycredits:view', context_system::instance())) {
        return;
    }
	
	$nodeCredits = $navigation->add('Credits');
	$buycredits = get_string('buycredits', 'local_buycredits');
	$nodeBuy = $nodeCredits->add($buycredits, new moodle_url('/local/buycredits/index.php'));
	$nodeAssign = $nodeCredits->add(get_string('assigncredits', 'local_buycredits'), new moodle_url('/local/buycredits/assigncredits.php'));
	$nodeOverview = $nodeCredits->add(get_string('creditsoverview', 'local_buycredits'), new moodle_url('/local/buycredits/creditoverview.php'));
}