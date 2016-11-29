<?php

date_default_timezone_set("Asia/Jakarta");

// CREATE TABLE `hit_cre` (
//   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
//   `ap_id` int(11) DEFAULT NULL,
//   `apli_id` int(11) DEFAULT NULL,
//   `sent` longtext,
//   `response_applicant` longtext,
//   `response_application` longtext,
//   `created_at` datetime DEFAULT NULL,
//   PRIMARY KEY (`id`)
// ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

define("CONF_HOST", "localhost");
define("CONF_USER", "root");
define("CONF_PASS", "broklak");
define("CONF_DB_SELECT", "utprodtembak2");

define("LIMIT_NUMBER", 1);
define("OFFSET_NUMBER", 0);

$db = new PDO('mysql:host=' . CONF_HOST . ';dbname=' . CONF_DB_SELECT . ';charset=utf8mb4', CONF_USER, CONF_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);


$stmt = $db->query("SELECT
    `ap_id`,
    `ap_gender`,
    `ap_religion`,
    `ap_national_status`,
    `ap_marital_status`,
    `ap_education`,
    `ap_race_id`,
    `ap_know_ut`,
    `ap_dob`,
    `ap_home_status`,
    `ap_home_status2`,
    `ap_full_name`,
    `ap_bank_name_id`,
    `ap_mrtw_id`,
    `ap_telp_no`,
    `ap_telp_work`,
    `ap_telp_dom`,
    `ap_telp_fam1`,
    `ap_telp_fam2`,
    `ap_mobile_no`,
    `ap_mobile_no2`,
    `ap_facebook_id`,
    `ap_email_address`,
    `ap_bank_username`,
    `ap_start_created_at`,
    `ap_latitude`,
    `ap_from_ip_address`,
    `ap_repeat`,
    `ap_pareto_id`,
    `ap_par_return_val`, 
    `ap_par_api_res_date`,
    `ap_par_api_res_score`,
    `ap_submit_at`, 
    `ap_tax_id_no`,
    `ap_bank_number`,
    `ap_pob`,
    `ap_hll_work`,
    `ap_hll_dom`, 
    `ap_brw_reff`,
    `ap_mobile_prefix`,
    `ap_monthly_income`,
    `ap_par_api_res_version`,
    `ap_linkedin_id`,
    `ap_campaign`,
    `ap_utfc_api_res_date`,
    `ap_hll_ktp`,
    `ap_family_id_no`,
    -- `ap_utfc_api_return_val`, 
    `ap_amount_child`,
    `ap_age`,
    `ap_longitude`,
    `ap_banned`, 
    `ap_mobile_prefix2`,
    `ap_personal_id_no`, 
    `ap_address`,
    IF(province.name IS NULL OR province.name = '', 'unknown', province.name) as `ap_province`,
    IF(city.name IS NULL OR city.name = '', 'unknown', city.name) as `ap_kab_kot`,
    IF(district.name IS NULL OR district.name = '', 'unknown', district.name) as `ap_kecamatan`,
    IF(sub_district.name IS NULL OR sub_district.name = '', 'unknown', sub_district.name) as `ap_kelurahan`,
    IF(postal_code.postal_code IS NULL OR postal_code.postal_code = '' OR postal_code.postal_code = 0, '-1', postal_code.postal_code) as `ap_postal_code`,
    `ap_dom_address`,
    IF(dom_province.name IS NULL OR dom_province.name = '', 'unknown', dom_province.name) as `ap_dom_province`,
    IF(dom_city.name IS NULL OR dom_city.name = '', 'unknown', dom_city.name) as `ap_dom_kab_kot`,
    IF(dom_district.name IS NULL OR dom_district.name = '', 'unknown', dom_district.name) as `ap_dom_kecamatan`,
    IF(dom_sub_district.name IS NULL OR dom_sub_district.name = '', 'unknown', dom_sub_district.name) as `ap_dom_kelurahan`,
    IF(dom_postal_code.postal_code IS NULL OR dom_postal_code.postal_code = '' OR dom_postal_code.postal_code = 0, '-1', dom_postal_code.postal_code) as `ap_dom_postal_code`,
    `ap_fam1_name`,
    `ap_fam1_address`,
    IF(fam1_province.name IS NULL OR fam1_province.name = '', 'unknown', fam1_province.name) as `ap_fam1_province`,
    IF(fam1_city.name IS NULL OR fam1_city.name = '', 'unknown', fam1_city.name) as `ap_fam1_kab_kot`,
    IF(fam1_district.name IS NULL OR fam1_district.name = '', 'unknown', fam1_district.name) as `ap_fam1_kecamatan`,
    IF(fam1_sub_district.name IS NULL OR fam1_sub_district.name = '', 'unknown', fam1_sub_district.name) as `ap_fam1_kelurahan`,
    IF(fam1_postal_code.postal_code IS NULL OR fam1_postal_code.postal_code = '' OR fam1_postal_code.postal_code = 0, '-1', fam1_postal_code.postal_code) as `ap_fam1_postal_code`,
    `ap_fam2_name`,
    `ap_fam2_address`,
    IF(fam2_province.name IS NULL OR fam2_province.name = '', 'unknown', fam2_province.name) as `ap_fam2_province`,
    IF(fam2_city.name IS NULL OR fam2_city.name = '', 'unknown', fam2_city.name) as `ap_fam2_kab_kot`,
    IF(fam2_district.name IS NULL OR fam2_district.name = '', 'unknown', fam2_district.name) as `ap_fam2_kecamatan`,
    IF(fam2_sub_district.name IS NULL OR fam2_sub_district.name = '', 'unknown', fam2_sub_district.name) as `ap_fam2_kelurahan`,
    IF(fam2_postal_code.postal_code IS NULL OR fam2_postal_code.postal_code = '' OR fam2_postal_code.postal_code = 0, '-1', fam2_postal_code.postal_code) as `ap_fam2_postal_code`,
    `ap_employer_name`,
    `ap_employer_role`,
    `ap_employer_type`,
    `ap_employer_address`,
    IF(employer_province.name IS NULL OR employer_province.name = '', 'unknown', employer_province.name) as `ap_employer_province`,
    IF(employer_city.name IS NULL OR employer_city.name = '', 'unknown', employer_city.name) as `ap_employer_kab_kot`,
    IF(employer_district.name IS NULL OR employer_district.name = '', 'unknown', employer_district.name) as `ap_employer_kecamatan`,
    IF(employer_sub_district.name IS NULL OR employer_sub_district.name = '', 'unknown', employer_sub_district.name) as `ap_employer_kelurahan`,
    IF(employer_postal_code.postal_code IS NULL OR employer_postal_code.postal_code = '' OR employer_postal_code.postal_code = 0, '-1', employer_postal_code.postal_code) as `ap_employer_postal_code`,
    `apli_id`,
    `apli_ap_id`,
    `apli_loan_app_id`,
    `apli_loan_days_length`,
    -- `apli_coverage_area`, 
    `apli_loan_purpose`,
    `apli_loan_start_datetime`,
    `apli_loan_due_datetime`,
    `apli_loan_amount`,
    `apli_loan_interest_rate`,
    `apli_loan_repay_amount`,
    `apli_office_phoned`,
    `apli_visit_today`,
    `apli_status`, 
    `apli_manual_assignment`,
    `apli_sms_attemp`,
    `apli_override`, 
    `apli_approval_status`,
    `apli_adr_status`, 
    `apli_cre_manual`,
    `apli_sms_status`,
    `apli_agree_signature`,
    `apli_social_type_id`,
    `apli_cre_datetime`, 
    `apli_survey_status`,
    `apli_am_id`,
    `apli_cre_return_val`,
    `apli_channel`, 
    `apli_reject_expired`,
    `apli_attemp_datetime`,
    `apli_reject_reason`,
    `apli_loan_interest_fee`,
    `apli_sms_datetime`,
    `apli_completed_datetime`,
    `apli_agree`,
    `apli_promo_code`,
    `apli_repeat`
    FROM applicant_data
    JOIN application_data ON ap_id = apli_ap_id
    LEFT JOIN mr_province province ON province.id = ap_province
    LEFT JOIN mr_city city ON city.id = ap_kab_kot
    LEFT JOIN mr_district district ON district.id = ap_kecamatan
    LEFT JOIN mr_sub_district sub_district ON sub_district.id = ap_kelurahan
    LEFT JOIN mr_postal_code postal_code ON postal_code.id = ap_postal_code
    LEFT JOIN mr_province dom_province ON dom_province.id = ap_dom_province
    LEFT JOIN mr_city dom_city ON dom_city.id = ap_dom_kab_kot
    LEFT JOIN mr_district dom_district ON dom_district.id = ap_dom_kecamatan
    LEFT JOIN mr_sub_district dom_sub_district ON dom_sub_district.id = ap_dom_kelurahan
    LEFT JOIN mr_postal_code dom_postal_code ON dom_postal_code.id = ap_dom_postal_code
    LEFT JOIN mr_province fam1_province ON fam1_province.id = ap_fam1_province
    LEFT JOIN mr_city fam1_city ON fam1_city.id = ap_fam1_kab_kot
    LEFT JOIN mr_district fam1_district ON fam1_district.id = ap_fam1_kecamatan
    LEFT JOIN mr_sub_district fam1_sub_district ON fam1_sub_district.id = ap_fam1_kelurahan
    LEFT JOIN mr_postal_code fam1_postal_code ON fam1_postal_code.id = ap_fam1_postal_code
    LEFT JOIN mr_province fam2_province ON fam2_province.id = ap_fam2_province
    LEFT JOIN mr_city fam2_city ON fam2_city.id = ap_fam2_kab_kot
    LEFT JOIN mr_district fam2_district ON fam2_district.id = ap_fam2_kecamatan
    LEFT JOIN mr_sub_district fam2_sub_district ON fam2_sub_district.id = ap_fam2_kelurahan
    LEFT JOIN mr_postal_code fam2_postal_code ON fam2_postal_code.id = ap_fam2_postal_code
    LEFT JOIN mr_province employer_province ON employer_province.id = ap_employer_province
    LEFT JOIN mr_city employer_city ON employer_city.id = ap_employer_kab_kot
    LEFT JOIN mr_district employer_district ON employer_district.id = ap_employer_kecamatan
    LEFT JOIN mr_sub_district employer_sub_district ON employer_sub_district.id = ap_employer_kelurahan
    LEFT JOIN mr_postal_code employer_postal_code ON employer_postal_code.id = ap_employer_postal_code
    ORDER BY apli_id DESC
    LIMIT " . OFFSET_NUMBER . ", " . LIMIT_NUMBER ."    
");

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(count($result) > 0){
    foreach($result as $key => $value){
        $post_param = array(); 

        foreach($value as $key_2 => $value_2)
            $post_param[$key_2] = "{$value_2}";

        $post_param   = json_encode($post_param); 

        /* s: Check NEW CRE */
        $response_applicant       = send_curl("check_applicant_json", $post_param); 
        $response_application     = send_curl("check_application_json", $post_param); 

        $stmt_command = $db->prepare("
                            INSERT INTO hit_cre (ap_id, apli_id, sent, response_applicant, response_application, created_at) 
                            VALUES (:ap_id, :apli_id, :sent, :response_applicant, :response_application, :created_at) 
                        ");
        
        $stmt_command->execute(array(
            ":ap_id"                    => $value['ap_id'],
            ":apli_id"                  => $value['apli_id'],
            ":sent"                     => $post_param,
            ":response_applicant"       => $response_applicant,
            ":response_application"     => $response_application, 
            ":created_at"               => date("Y-m-d H:i:s")
        )); 
        /* e: Check NEW CRE */
 
    }

    die("Done !");
}


function send_curl($endpoint, $param = array()){
    $ch = curl_init(); 

    curl_setopt($ch, CURLOPT_URL, "http://103.58.100.153:8000/{$endpoint}");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $headers = [ 
        'Cache-Control: no-cache',
        'Content-Type: application/json; charset=utf-8', 
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    curl_close ($ch);

    return $response;
}