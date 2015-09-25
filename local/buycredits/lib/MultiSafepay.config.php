<?php

define('MSP_TEST_API',     true); // seperate testaccount needed
define('MSP_ACCOUNT_ID',   '90092848');
define('MSP_SITE_ID',      '11393');
define('MSP_SITE_CODE',    '976087');

define('BASE_URL', ($_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . dirname($_SERVER['SCRIPT_NAME']) . "/");

?>