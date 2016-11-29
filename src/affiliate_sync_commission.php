<?php

require __DIR__.'/../bootstrap/autoload.php';

use Illuminate\Database\Capsule\Manager as DB; 
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

$q = DB::table('aff_step as t1')
    ->join('application_data as t2', 't2.apli_ap_id', '=', 't1.ap_id')
    ->join('customer_loan_data as t3', 't3.cld_ap_id', '=', 't1.ap_id')
    ->join('customer_loan_repayment as t4', 't4.clr_ap_id', '=', 't1.ap_id')
    ->join('aff_links as t5', 't5.id', '=', 't1.aff_links_id')
    ->join('aff_users as t6', 't6.id', '=', 't5.aff_users_id')
    ->where('t2.apli_status', 'B')
    ->where(
        DB::raw('convert(right(t2.apli_loan_app_id, 2), unsigned integer)'),
        1
    )
    ->select([
        't1.id',
        't1.aff_links_id',
        't1.ap_id',
        't2.apli_loan_app_id',
        DB::raw("if(t3.cld_status = 'Y' AND t4.clr_status = 'Y', 'paid', 'unpaid') AS payment_status"),
        't3.cld_loan_start_datetime AS disbursement_date',
        DB::raw("if(t3.cld_status = 'Y' AND t4.clr_status = 'Y', t4.clr_repayment_date, NULL) AS repayment_date"),
        DB::raw("t6.id AS aff_users_id"),
        DB::raw("t6.email AS aff_users_email"),
        DB::raw("t6.full_name AS aff_users_full_name"),
        DB::raw("(SELECT count(1) FROM aff_commission WHERE ap_id IS NULL AND category = 'B' AND aff_users_id = t6.id) AS commision_bonus"),
        DB::raw("(SELECT count(1) FROM aff_commission WHERE ap_id IS NOT NULL AND category = 'D' AND aff_users_id = t6.id AND ap_id = t1.ap_id) AS commision_disbursement"),
        DB::raw("if(t3.cld_status = 'Y' AND t4.clr_status = 'Y', (SELECT count(1) FROM aff_commission WHERE ap_id IS NOT NULL AND category = 'R' AND aff_users_id = t6.id AND ap_id = t1.ap_id), NULL) AS commision_repayment"),
    ])
    ->groupBy(['t1.ap_id'])
    ->orderBy('t1.ap_id', 'asc')
    ->get();

echo "Data: \n";
print_r($q);