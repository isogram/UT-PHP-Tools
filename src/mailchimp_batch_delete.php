<?php

require __DIR__.'/../bootstrap/autoload.php';

use Illuminate\Database\Capsule\Manager as DB; 
use Drewm\MailChimp;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

$filename = basename(__FILE__, '.php');

// Create the logger
$logger = new Logger('mailchimp');

// Now add some handlers
$logger->pushHandler(new RotatingFileHandler( LOGS_DIR . $filename, 0, Logger::DEBUG ));

// Create new function for loggin
$addlog = function ($message, array $context = array(), $newline_count = 1) use ($logger) {
    $logger->addDebug($message, $context);
    $n = '';
    if ((bool)$newline_count) {
        for ($i=0; $i < (int)$newline_count; $i++) { 
            $n .= "\n";
        }
    }

    echo $message . $n;
};

// Set mailchimp params
$deleteMember = true;
$sendGoodbye = false;
$sendNotify = false;

// Set key and list id from configuration
$key = getenv('mailchimp_key');
$listId = getenv('mailchimp_listid');

// set MailChimp table
// this table has field: id, email, status 
$mailChimpTable = 'tmp_mailchimp';

// Initailize MailChimp libaray
$mailChimp = new MailChimp($key);

// Get data
$total_data = DB::table($mailChimpTable)->where('status', 0)->count();
$datas = DB::table($mailChimpTable)->where('status', 0)->get();
$batch = [];

$addlog("=== Started ===");
$addlog("Total Data: $total_data", ['total_data' => $total_data]);

foreach($datas as $ds){
    $batch[] = [
        'email' => $ds->email,
        'euid' => null,
        'leid' => null
    ];
}

$take   = 5;
$items  = count($batch);
$pages  = ceil($items/$take);

$addlog("Total Page: $pages", ['total_pages' => $pages]);

if ($pages > 0) {

    $addlog("Processing Data");

    for ($i=1; $i <= $pages; $i++) {

        $tmp = [];
        $emls = [];

        $addlog("Processing page $i", ['page' => $i]);

        for($j = (($i - 1 ) * $take); $j < ($i * $take); $j++){

            if(isset($batch[$j])) {
                $emls[] = $batch[$j]['email'];
                $tmp[] = $batch[$j];
            }

        }

        $rest = $mailChimp->call('lists/batch-unsubscribe',
                [
                    'id' => $listId,
                    'batch' => $tmp,
                    'delete_member' => $deleteMember,
                    'send_goodbye' => $sendGoodbye,
                    'send_notify' => $sendNotify
                ]);

        $jsonRest = json_encode($rest);
        $addlog("Response: " . $jsonRest);

        if ($rest) {
            $addlog("Updating records");
            $update = DB::table($mailChimpTable)->whereIn('email', $emls)->update(['status' => 1]);
            $addlog("Records updated", ['rows' => json_encode($update)], 2);
        }
    }

}

$addlog("=== Finished ===");
