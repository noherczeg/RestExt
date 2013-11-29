<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

/*
|--------------------------------------------------------------------------
| Database Logging
|--------------------------------------------------------------------------
| 1) Add "require app_path().'/errors.php';" to the end of app/start/global.php
| 2) Uncomment the "Application Error Logger" section in app/start/global.php
| 3) Migrate "create_log_table", or create the table manualy
*/
Log::listen(function($level, $message, $context) {

    // Save the php sapi and date, because the closure needs to be serialized
    $apiName = php_sapi_name();
    $date = new \DateTime;

    Queue::push(function() use ($level, $message, $context, $apiName, $date) {
        DB::insert("INSERT INTO log (php_sapi_name, level, message, context, created_at) VALUES (?, ?, ?, ?, ?)", array(
            $apiName,
            $level,
            $message,
            json_encode($context),
            $date
        ));
    });

});