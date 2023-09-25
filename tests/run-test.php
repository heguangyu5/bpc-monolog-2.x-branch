<?php
define('RUN_ROOT_DIR', __DIR__);
define('TESTCASE_LIST', include __DIR__ . '/TESTCASE_LIST.php');
include 'phpunit/loader.php';
PHPUnit_TextUI_Command::main();
