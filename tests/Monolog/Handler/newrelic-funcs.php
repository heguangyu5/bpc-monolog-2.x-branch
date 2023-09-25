<?php

use Monolog\Handler\NewRelicHandlerTest;

function newrelic_notice_error()
{
    return true;
}

function newrelic_set_appname($appname)
{
    return NewRelicHandlerTest::$appname = $appname;
}

function newrelic_name_transaction($transactionName)
{
    return NewRelicHandlerTest::$transactionName = $transactionName;
}

function newrelic_add_custom_parameter($key, $value)
{
    NewRelicHandlerTest::$customParameters[$key] = $value;

    return true;
}
