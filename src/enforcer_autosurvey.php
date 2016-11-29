<?php

require __DIR__.'/../bootstrap/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;

use App\Libs\Enforcer;

// initiate slack
$processId = time();
$slack = new Maknz\Slack\Client(getenv('slack_url'));
$slackTo = '@shidiq';

// get first loan only
$countApps = DB::table('application_data')->where('apli_loan_app_id', 'like', '%01')->count();

$take = 1000;
$pages = ceil($countApps / $take);
$counter = 0;

for ($i=0; $i < $pages; $i++) {

    $this->info("***********************");
    $this->info("PROCESSING PAGE " . ($i + 1) . " of " . $pages);
    $this->info("***********************");

    $slack->to($slackTo)->send("*[$processId]* PROCESSING PAGE " . ($i + 1) . " of " . $pages);

    $apps = DB::table('application_data')
            ->where('apli_loan_app_id', 'like', '%01')
            ->offset($take * $i)
            ->take($take)
            ->get();

    foreach ($apps as $app) {

        $counter++;

        $enforcer = new Enforcer($app->apli_id, true);
        $response = $enforcer->exec();

        $this->line("No. " . ($i + 1) ."-". $counter);
        $this->line("Executing apli_id: ". $app->apli_id);

        DB::table('hit_autosurvey')->insert(
            array(
                'ap_id' => $app->apli_ap_id,
                'apli_id' => $app->apli_id,
                'raw' => json_encode($response['data']['raw']),
                'sanitized' => json_encode($response['data']['sanitized']),
                'response' => $response['data']['response'],
                'created_at' => date('Y-m-d H:i:s'),
            )
        );

        if ($counter == 100) {
            sleep(2);
            $this->info("take a breath for 2 sec :)");
            $counter = 0;
        }

    }

    sleep(3);
    $this->info("take a breath for 3 sec :)");

}

$slack->to($slackTo)->send("*[$processId]* DONE");