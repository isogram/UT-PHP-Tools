<?php

namespace App\Libs;

use Illuminate\Database\Capsule\Manager as DB;

class Enforcer
{

    protected $forceToHit;
    protected $data;
    protected $response = [
        'message' => '',
        'data' => [],
        'error' => false,
    ];
    protected $rules;
    protected $rulesToRemove = [];

    const API_HOST = "http://54.255.187.15:6000";
    const EP_CHECK_APPLICANT = "check_applicant_json";
    const EP_CHECK_APPLICATION = "check_application_json";

    function __construct($applicationId, $forceToHit = null)
    {
        $this->forceToHit = $forceToHit;
        $this->data = $this->getData($applicationId);
        $this->rules = array_merge($this->rulesApplication(), $this->rulesApplicant());

    }

    public function exec()
    {

        if (!$this->data)
        {
            $this->response['error'] = true;
            $this->response['message'] = 'Application data not found!';

            return $this->response;
        }

        // get keys requested by peter
        $keys = array_keys($this->rules);

        // filtering data: only keys that peter wanted
        // $data = array_where($this->data, function($i, $v) use ($keys) {
        //     return in_array($i, $keys);
        // });

        $data = $this->data;
        $noNullData = $this->nullToString($data);

        $this->response['data']['raw'] = $data;
        // set default sanitized data
        $this->response['data']['sanitized'] = $noNullData;

        $this->validateData($noNullData);

        if (!$this->response['error'] || $this->forceToHit === true) {
            $this->response['data']['response'] = $this->sendCurl(null, json_encode($this->response['data']['sanitized']));
        }

        return $this->response;

    }

    protected function getData($applicationId)
    {

        DB::connection()->setFetchMode(\PDO::FETCH_ASSOC);

        $q = DB::table('applicant_data')->join('application_data', 'apli_ap_id', '=', 'ap_id')
            ->leftjoin('mr_province as province', 'province.id', '=', 'ap_province')
            ->leftjoin('mr_city as city', 'city.id', '=', 'ap_kab_kot')
            ->leftjoin('mr_district as district', 'district.id', '=', 'ap_kecamatan')
            ->leftjoin('mr_sub_district as sub_district', 'sub_district.id', '=', 'ap_kelurahan')
            ->leftjoin('mr_postal_code as postal_code', 'postal_code.id', '=', 'ap_postal_code')
            ->leftjoin('mr_province as dom_province', 'dom_province.id', '=', 'ap_dom_province')
            ->leftjoin('mr_city as dom_city', 'dom_city.id', '=', 'ap_dom_kab_kot')
            ->leftjoin('mr_district as dom_district', 'dom_district.id', '=', 'ap_dom_kecamatan')
            ->leftjoin('mr_sub_district as dom_sub_district', 'dom_sub_district.id', '=', 'ap_dom_kelurahan')
            ->leftjoin('mr_postal_code as dom_postal_code', 'dom_postal_code.id', '=', 'ap_dom_postal_code')
            ->leftjoin('mr_province as fam1_province', 'fam1_province.id', '=', 'ap_fam1_province')
            ->leftjoin('mr_city as fam1_city', 'fam1_city.id', '=', 'ap_fam1_kab_kot')
            ->leftjoin('mr_district as fam1_district', 'fam1_district.id', '=', 'ap_fam1_kecamatan')
            ->leftjoin('mr_sub_district as fam1_sub_district', 'fam1_sub_district.id', '=', 'ap_fam1_kelurahan')
            ->leftjoin('mr_postal_code as fam1_postal_code', 'fam1_postal_code.id', '=', 'ap_fam1_postal_code')
            ->leftjoin('mr_province as fam2_province', 'fam2_province.id', '=', 'ap_fam2_province')
            ->leftjoin('mr_city as fam2_city', 'fam2_city.id', '=', 'ap_fam2_kab_kot')
            ->leftjoin('mr_district as fam2_district', 'fam2_district.id', '=', 'ap_fam2_kecamatan')
            ->leftjoin('mr_sub_district as fam2_sub_district', 'fam2_sub_district.id', '=', 'ap_fam2_kelurahan')
            ->leftjoin('mr_postal_code as fam2_postal_code', 'fam2_postal_code.id', '=', 'ap_fam2_postal_code')
            ->leftjoin('mr_province as employer_province', 'employer_province.id', '=', 'ap_employer_province')
            ->leftjoin('mr_city as employer_city', 'employer_city.id', '=', 'ap_employer_kab_kot')
            ->leftjoin('mr_district as employer_district', 'employer_district.id', '=', 'ap_employer_kecamatan')
            ->leftjoin('mr_sub_district as employer_sub_district', 'employer_sub_district.id', '=', 'ap_employer_kelurahan')
            ->leftjoin('mr_postal_code as employer_postal_code', 'employer_postal_code.id', '=', 'ap_employer_postal_code')
            ->select([
                'ap_id',
                'ap_gender',
                'ap_religion',
                'ap_national_status',
                'ap_marital_status',
                'ap_education',
                'ap_race_id',
                'ap_know_ut',
                'ap_dob',
                'ap_home_status',
                'ap_home_status2',
                'ap_full_name',
                'ap_bank_name_id',
                'ap_mrtw_id',
                'ap_telp_no',
                'ap_telp_work',
                'ap_telp_dom',
                'ap_telp_fam1',
                'ap_telp_fam2',
                'ap_mobile_no',
                'ap_mobile_no2',
                'ap_facebook_id',
                'ap_email_address',
                'ap_bank_username',
                'ap_start_created_at',
                'ap_latitude',
                'ap_from_ip_address',
                'ap_repeat',
                'ap_pareto_id',
                'ap_par_return_val',
                'ap_par_api_res_date',
                'ap_par_api_res_score',
                'ap_submit_at',
                'ap_tax_id_no',
                'ap_bank_number',
                'ap_pob',
                'ap_hll_work',
                'ap_hll_dom', 
                'ap_brw_reff',
                'ap_mobile_prefix',
                'ap_monthly_income',
                'ap_par_api_res_version',
                'ap_linkedin_id',
                'ap_campaign',
                'ap_utfc_api_res_date',
                'ap_hll_ktp',
                'ap_family_id_no',
                'ap_amount_child',
                'ap_age',
                'ap_longitude',
                'ap_banned',
                'ap_mobile_prefix2',
                'ap_personal_id_no',
                'ap_address',
                DB::raw('IF(province.name IS NULL OR province.name = "", "unknown", province.name) as "ap_province"'),
                DB::raw('IF(city.name IS NULL OR city.name = "", "unknown", city.name) as "ap_kab_kot"'),
                DB::raw('IF(district.name IS NULL OR district.name = "", "unknown", district.name) as "ap_kecamatan"'),
                DB::raw('IF(sub_district.name IS NULL OR sub_district.name = "", "unknown", sub_district.name) as "ap_kelurahan"'),
                DB::raw('IF(postal_code.postal_code IS NULL OR postal_code.postal_code = "" OR postal_code.postal_code = 0, "-1", postal_code.postal_code) as "ap_postal_code"'),
                DB::raw('ap_dom_address'),
                DB::raw('IF(dom_province.name IS NULL OR dom_province.name = "", "unknown", dom_province.name) as "ap_dom_province"'),
                DB::raw('IF(dom_city.name IS NULL OR dom_city.name = "", "unknown", dom_city.name) as "ap_dom_kab_kot"'),
                DB::raw('IF(dom_district.name IS NULL OR dom_district.name = "", "unknown", dom_district.name) as "ap_dom_kecamatan"'),
                DB::raw('IF(dom_sub_district.name IS NULL OR dom_sub_district.name = "", "unknown", dom_sub_district.name) as "ap_dom_kelurahan"'),
                DB::raw('IF(dom_postal_code.postal_code IS NULL OR dom_postal_code.postal_code = "" OR dom_postal_code.postal_code = 0, "-1", dom_postal_code.postal_code) as "ap_dom_postal_code"'),
                DB::raw('ap_fam1_name'),
                DB::raw('ap_fam1_address'),
                DB::raw('IF(fam1_province.name IS NULL OR fam1_province.name = "", "unknown", fam1_province.name) as "ap_fam1_province"'),
                DB::raw('IF(fam1_city.name IS NULL OR fam1_city.name = "", "unknown", fam1_city.name) as "ap_fam1_kab_kot"'),
                DB::raw('IF(fam1_district.name IS NULL OR fam1_district.name = "", "unknown", fam1_district.name) as "ap_fam1_kecamatan"'),
                DB::raw('IF(fam1_sub_district.name IS NULL OR fam1_sub_district.name = "", "unknown", fam1_sub_district.name) as "ap_fam1_kelurahan"'),
                DB::raw('IF(fam1_postal_code.postal_code IS NULL OR fam1_postal_code.postal_code = "" OR fam1_postal_code.postal_code = 0, "-1", fam1_postal_code.postal_code) as "ap_fam1_postal_code"'),
                DB::raw('ap_fam2_name'),
                DB::raw('ap_fam2_address'),
                DB::raw('IF(fam2_province.name IS NULL OR fam2_province.name = "", "unknown", fam2_province.name) as "ap_fam2_province"'),
                DB::raw('IF(fam2_city.name IS NULL OR fam2_city.name = "", "unknown", fam2_city.name) as "ap_fam2_kab_kot"'),
                DB::raw('IF(fam2_district.name IS NULL OR fam2_district.name = "", "unknown", fam2_district.name) as "ap_fam2_kecamatan"'),
                DB::raw('IF(fam2_sub_district.name IS NULL OR fam2_sub_district.name = "", "unknown", fam2_sub_district.name) as "ap_fam2_kelurahan"'),
                DB::raw('IF(fam2_postal_code.postal_code IS NULL OR fam2_postal_code.postal_code = "" OR fam2_postal_code.postal_code = 0, "-1", fam2_postal_code.postal_code) as "ap_fam2_postal_code"'),
                DB::raw('ap_employer_name'),
                DB::raw('ap_employer_role'),
                DB::raw('ap_employer_type'),
                DB::raw('ap_employer_address'),
                DB::raw('IF(employer_province.name IS NULL OR employer_province.name = "", "unknown", employer_province.name) as "ap_employer_province"'),
                DB::raw('IF(employer_city.name IS NULL OR employer_city.name = "", "unknown", employer_city.name) as "ap_employer_kab_kot"'),
                DB::raw('IF(employer_district.name IS NULL OR employer_district.name = "", "unknown", employer_district.name) as "ap_employer_kecamatan"'),
                DB::raw('IF(employer_sub_district.name IS NULL OR employer_sub_district.name = "", "unknown", employer_sub_district.name) as "ap_employer_kelurahan"'),
                DB::raw('IF(employer_postal_code.postal_code IS NULL OR employer_postal_code.postal_code = "" OR employer_postal_code.postal_code = 0, "-1", employer_postal_code.postal_code) as "ap_employer_postal_code"'),
                'apli_id',
                'apli_ap_id',
                'apli_loan_app_id',
                'apli_loan_days_length',
                'apli_loan_purpose',
                'apli_loan_start_datetime',
                'apli_loan_due_datetime',
                'apli_loan_amount',
                'apli_loan_interest_rate',
                'apli_loan_repay_amount',
                'apli_office_phoned',
                'apli_visit_today',
                'apli_status',
                DB::raw('IF(apli_manual_assignment IS NULL, "N", apli_manual_assignment) as apli_manual_assignment'),
                'apli_sms_attemp',
                'apli_override', 
                'apli_approval_status',
                'apli_adr_status', 
                DB::raw('IF(apli_cre_manual IS NULL, "D", apli_cre_manual) as apli_cre_manual'),
                'apli_sms_status',
                DB::raw('IF(apli_agree_signature IS NULL, "D", apli_agree_signature) as apli_agree_signature'),
                'apli_social_type_id',
                'apli_cre_datetime', 
                'apli_survey_status',
                'apli_am_id',
                'apli_cre_return_val as ap_cre_return_val',
                'apli_channel',
                'apli_reject_expired',
                'apli_attemp_datetime',
                'apli_reject_reason',
                'apli_loan_interest_fee',
                'apli_sms_datetime',
                'apli_completed_datetime',
                'apli_agree',
                'apli_promo_code',
                'apli_repeat',
            ])
            ->where('application_data.apli_id', $applicationId)
            ->first();

        DB::connection()->setFetchMode(\PDO::FETCH_CLASS);

        return $q;

    }

    protected function rulesApplication()
    {

        $rules = [

            'apli_id' => [
                'type' => 'int',
                'range' => '0,999999999',
                'mandatory' => true
            ],

            'apli_ap_id' => [
                'type' => 'int',
                'range' => '0,999999999',
                'mandatory' => true
            ],

            'apli_am_id' => [
                'type' => 'int',
                'range' => '0,999999999',
                'mandatory' => true
            ],

            'apli_promo_code' => [
                'type' => 'string',
                'charset' => 'alnum',
                'len' => '0,125',
                'mandatory' => true,
                'can_be_empty' => true, # added by shidiq in this script
            ],

            'apli_loan_app_id' => [
                'type' => 'string',
                'charset' => 'extended1',
                'len' => '5,125',
                'mandatory' => true
            ],

            'apli_loan_days_length' => [
                'type' => 'int', 
                'range' => '0,300',
                'mandatory' => true
            ],

            'apli_loan_start_datetime' => [
                'type' => 'datetime', #FIXME
                'mandatory' => true
            ],

            'apli_loan_due_datetime' => [
                'type' => 'datetime', #FIXME
                'mandatory' => true
            ],

            'apli_loan_interest_rate' => [
                'type' => 'float', 
                'range' => '0,0.5',
                'mandatory' => true
            ],

            'apli_loan_interest_fee' => [
                'type' => 'float', 
                'range' => '0,100000000.0',
                'mandatory' => true
            ],

            'apli_loan_repay_amount' => [
                'type' => 'float', 
                'range' => '0,100000000.0',
                'mandatory' => true
            ],

            'apli_loan_purpose' => [
                'type' => 'int', 
                'range' => '-1,100',
                'mandatory' => true
            ],

            'ap_cre_datetime' => [
                'type' => 'datetime', #FIXME
            ],

            'ap_cre_return_val' => [
                'type' => 'string', 
                'valset' => ['accepted', 'rejected', 'maybe'],
            ],

            'apli_cre_manual' => [
                'type' => 'string', 
                'valset' => ['n', 'y', 'd'],
            ],      

            'apli_sms_datetime' => [
                'type' => 'datetime', #FIXME
            ],

            'apli_sms_status' => [
                'type' => 'string', 
                'valset' => ['y', 'n'],
            ],


            'apli_agree' => [
                'type' => 'string', 
                'valset' => ['y', 'n'],
                'mandatory' => true
            ],

            'apli_agree_signature' => [
                'type' => 'string', 
                'valset' => ['y', 'n', 'd'],
                'mandatory' => true
            ],

            'apli_manual_assignment' => [
                'type' => 'string', 
                'valset' => ['y', 'n'],
            ],

            'apli_adr_status' => [
                'type' => 'string', 
                'valset' => ['y', 'n', 'p', 'f'],
            ],

            'apli_status' => [
                'type' => 'string', 
                'valset' => ['d', 'fc', 'cs', 'st', 'a', 'r', 'b', 't', 'o', 'f'],
            ],

            'apli_reject_reason' => [
                'type' => 'int', 
                'range' => '0,5000',
            ],

            'apli_reject_expired' => [
                'type' => 'datetime', #FIXME
            ],

            'apli_survey_status' => [
                'type' => 'string', 
                'valset' => ['', 'surveyed'],
            ],

            'apli_approval_status' => [
                'type' => 'string', 
                'valset' => ['y', 'n'],
            ],

            'apli_override' => [
                'type' => 'string', 
                'valset' => ['y', 'n'],
            ],

            'apli_channel' => [
                'type' => 'string', 
                'valset' => ['m', 'p', 'd'],
            ],

        ];

        return $rules;

    }

    protected function rulesApplicant()
    {

        $rules = [

            'ap_id' => [
                'type' => 'int',
                'range' => '0,999999999',
                'mandatory' => true
            ],

            'ap_gender' => [
                'type' => 'string', 
                'len' => '0,1',
                'valset' => ['M', 'F'], 
                'mandatory' => true
            ],

            'ap_email_address' => [
                'type' => 'string', 
                'charset' => 'email', 
                'mandatory' => true
            ],

            'ap_linkedin_id' => [
                'type' => 'string', 
                'len' => '0,16',
                'can_be_null' => true,
                'charset' => 'numeric', 
                'mandatory' => true,
                'can_be_empty' => true, # added by shidiq in this script
            ],

            'ap_facebook_id' => [
                'type' => 'string', 
                'len' => '0,30',
                'charset' => 'extended1',
                'mandatory' => true,
                'can_be_empty' => true, # added by shidiq in this script
            ],

            'ap_pob' => [
                'type' => 'string',
                'len' => '0,40',
                'charset' => 'extended3',
                'mandatory' => true
            ],

            'ap_dob' => [
                'type' => 'date',
                'mandatory' => true
            ],

            'ap_age' => [
                'type' => 'int',
                'range' => '-1,150',
                'mandatory' => true
            ],

            'ap_religion' => [
                'type' => 'int',
                'range' => '-1, 30',
                'mandatory' => true
            ],

            'ap_education' => [
                'type' => 'int',
                'can_be_null' => true,
                'range' => '-1,9',
                'mandatory' => true
            ],

            'ap_national_status' => [
                'type' => 'string',
                'valset' => ['', 'wni'],
                'mandatory' => true
            ],

            'ap_race_id' => [
                'type' => 'int',
                'can_be_null' => true,
                'range' => '-1,15',
                'mandatory' => true
            ],

            'ap_tax_id_no' => [
                'type' => 'string', 
                'len' => '0,18',
                'can_be_null' => true,
                'charset' => 'numeric',
                'mandatory' => true,
                'can_be_empty' => true, # added by shidiq in this script
            ],

            'ap_personal_id_no' => [
                'type' => 'string', 
                'len' => '0,18',
                'charset' => 'numeric', 
                'mandatory' => true
            ],

            'ap_family_id_no' => [
                'type' => 'string', 
                'len' => '0,18',
                'charset' => 'numeric', 
                'mandatory' => true,
                'can_be_empty' => true, # added by shidiq in this script
            ],

            'ap_mobile_prefix' => [
                'type' => 'string', 
                'valset' => ['', '0', '1'],
                'mandatory' => true
            ],

            'ap_mobile_prefix2' => [
                'type' => 'string', 
                'valset' => ['', '0', '1'],
                'mandatory' => true
            ],

            'ap_mobile_no' => [
                'type' => 'string', 
                'len' => '0,22',
                'charset' => 'phone_number', 
                'mandatory' => true
            ],

            'ap_mobile_no2' => [
                'type' => 'string', 
                'len' => '0,22',
                'charset' => 'phone_number', 
                'mandatory' => true
            ],

            'ap_home_status' => [
                'type' => 'int',
                'range' => '-1,25',
                'mandatory' => true
            ],

            'ap_home_status2' => [
                'type' => 'int',
                'range' => '-1,25',
                'mandatory' => true
            ],

            'ap_telp_no' => [
                'type' => 'string', 
                'len' => '0,22',
                'charset' => 'phone_number', 
                'mandatory' => true
            ],

            'ap_kelurahan' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_kecamatan' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_kab_kot' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_province' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_postal_code' => [
                'type' => 'int',
                'range' => '-1,999999',
                'mandatory' => true
            ],

            'ap_telp_dom' => [
                'type' => 'string', 
                'len' => '0,22',
                'charset' => 'phone_number', 
                'mandatory' => true
            ],

            'ap_dom_kelurahan' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_dom_kecamatan' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_dom_kab_kot' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_dom_province' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_dom_postal_code' => [
                'type' => 'int',
                'can_be_empty' => true,
                'range' => '-1,999999',
                'mandatory' => true
            ],

            'ap_fam1_name' => [
                'type' => 'string', 
                'len' => '0,125',
                'can_be_null' => true,
                'charset' => 'extended2',
                'mandatory' => true
            ],

            'ap_telp_fam1' => [
                'type' => 'string', 
                'len' => '0,22',
                'charset' => 'phone_number', 
                'mandatory' => true
            ],

            'ap_fam1_kelurahan' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_fam1_kecamatan' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_fam1_kab_kot' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_fam1_province' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_fam1_postal_code' => [
                'type' => 'int',
                'can_be_empty' => true,
                'range' => '-1,999999',
                'mandatory' => true
            ],

            'ap_fam2_name' => [
                'type' => 'string', 
                'len' => '0,125',
                'charset' => 'extended2',
                'mandatory' => true,
                'can_be_empty' => true, # added by shidiq in this script
            ],

            'ap_telp_fam2' => [
                'type' => 'string', 
                'len' => '0,22',
                'charset' => 'phone_number', 
                'mandatory' => true
            ],

            'ap_fam2_kelurahan' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_fam2_kecamatan' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_fam2_kab_kot' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_fam2_province' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_fam2_postal_code' => [
                'type' => 'int',
                'can_be_empty' => true,
                'range' => '-1,999999',
                'mandatory' => true
            ],

            'ap_marital_status' => [
                'type' => 'int',
                'range' => '0,16',
                'mandatory' => true
            ],

            'ap_amount_child' => [
                'type' => 'int',
                'range' => '-1,30',
                'mandatory' => true
            ],

            'ap_monthly_income' => [
                'type' => 'float',
                'can_be_null' => true,
                'range' => '0,99999999999',
                'mandatory' => true
            ],

            'ap_bank_name_id' => [
                'type' => 'int',
                'range' => '0,1000',
                'mandatory' => true
            ],

            'ap_bank_username' => [
                'type' => 'string',
                'len' => '0,125',
                'charset' => 'extended2',
                'mandatory' => true
            ],

            'ap_bank_number' => [
                'type' => 'string',
                'charset' => 'numeric',
                'len' => '0,32',
                'mandatory' => true
            ],

            'ap_mrtw_id' => [
                'type' => 'int',
                'range' => '0,20',
                'mandatory' => true,
            ],

            'ap_employer_name' => [
                'type' => 'string',
                'charset' => 'extended3',
                'can_be_null' => true,
                'len' => '0,125',
                'mandatory' => true
            ],

            'ap_employer_role' => [
                'type' => 'string',
                'charset' => 'extended3',
                'can_be_null' => true,
                'len' => '0,120',
                'mandatory' => true
            ],

            'ap_telp_work' => [
                'type' => 'string', 
                'len' => '0,22',
                'can_be_null' => true,
                'charset' => 'phone_number', 
                'mandatory' => true
            ],

            'ap_employer_kelurahan' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_employer_kecamatan' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_employer_kab_kot' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_employer_province' => [
                'type' => 'string',
                'charset' => 'province',
                'len' => '3,50',
                'mandatory' => true
            ],

            'ap_employer_postal_code' => [
                'type' => 'int',
                'can_be_empty' => true,
                'range' => '-1,999999',
                'mandatory' => true
            ],

            'ap_from_ip_address' => [
                'type' => 'ip',
                'mandatory' => true
            ], #FIXME NOT IMPLEMENTED => 'ip'

            
            'ap_start_created_at' => [
                'type' => 'timestamp',
                'mandatory' => true
            ], #FIXME NOT IMPLEMENTED => 'timestamp'

            'ap_submit_at' => [
                'type' => 'timestamp',
                'mandatory' => true
            ], #FIXME NOT IMPLEMENTED => 'timestamp'


            'ap_pareto_id' => [
                'type' => 'int',
                'can_be_null' => true,
                'range' => '0,999999999',
                'mandatory' => true
            ],

            'ap_repeat' => [
                'type' => 'string', 
                'len' => '0,1',
                'valset' => ['', 'y', 'n'], 
                'mandatory' => true
            ],

            'ap_latitude' => [
                'type' => 'float',
                'can_be_null' => true,
                'mandatory' => true
            ],

            'ap_longitude' => [
                'type' => 'float',
                'can_be_null' => true,
                'mandatory' => true
            ],

            'ap_know_ut' => [
                'type' => 'int',
                'can_be_null' => true,
                'range' => '0,32',
                'mandatory' => true
            ],

            'ap_campaign' => [
                'type' => 'string', 
                'len' => '0,1',
                'valset' => ['', 'y', 'n'], 
                'mandatory' => true
            ],

            'ap_banned' => [
                'type' => 'string', 
                'len' => '0,1',
                'valset' => ['u', 'b'], 
                'mandatory' => true
            ],

        ];

        return $rules;

    }

    protected function validateData($data)
    {

        foreach ($this->rules as $key => $value)
        {

            $dataKey = isset($data[$key]) ? $data[$key] : null;

            foreach ($value as $krule => $vrule) {

            // dd($key, $value, $krule, $vrule, $dataKey);
            // string 'apli_id' (length=7)
            // array (size=3)
            //   'type' => string 'int' (length=3)
            //   'range' => string '0,999999999' (length=11)
            //   'mandatory' => boolean true
            // string 'type' (length=4)
            // string 'int' (length=3)
            // string '63598' (length=5)

                if ($krule == 'type' && $vrule == 'int' && $dataKey == '') {

                    if (array_key_exists('range', $value)) {

                        $minMax = explode(',', $value['range']);
                        $min = $minMax[0]; $max = $minMax[1];

                        $dataKey = $min;

                    } else {

                        $dataKey = '0';

                    }

                }

                $validate = $this->validateRule($krule, $vrule, $dataKey, $key);

                $this->response['data']['sanitized']["{$key}"] = $dataKey;

                // if validation not ok
                if ($validate) {
                    $this->response['error']["{$key}"]["{$krule}"] = [
                        'expected' => $vrule,
                        'given'    => $dataKey
                    ];
                }

                // super validation
                if ($krule == 'can_be_empty' && $dataKey == '') {

                    $this->rulesToRemove[] = $key;

                }

                // super validation
                if ($krule == 'can_be_null' && is_null($dataKey)) {

                    $this->rulesToRemove[] = $key;

                }

            }

        }

        if ($this->rulesToRemove) {
            $this->response['error'] = array_diff_key($this->response['error'], array_flip($this->rulesToRemove));
        }

        if ($this->response['error']) {
            $this->response['message'] = 'validation fails';
        } else {
            $this->response['message'] = 'success';
        }

    }

    protected function validateRule($ruleKey, $rules, $data, $key = null)
    {

        switch ($ruleKey) {
            case 'mandatory':
                return $this->validateRuleMandatory($rules, $key);
                break;

            case 'type':
                return $this->validateRuleType($rules, $data);
                break;

            case 'range':
                return $this->validateRuleRange($rules, $data);
                break;

            case 'charset':
                return $this->validateRuleCharset($rules, $data);
                break;

            case 'len':
                return $this->validateRuleLen($rules, $data);
                break;

            case 'valset':
                return $this->validateRuleValset($rules, $data);
                break;

            case 'can_be_empty':
                return $this->validateRuleCanBeNull($rules, $data);
                break;

            case 'can_be_null':
                return $this->validateRuleCanBeNull($rules, $data);
                break;
        }

    }

    protected function validateRuleMandatory($mandatory, $key)
    {

        if ($mandatory === true && !isset($this->response['data']['sanitized']["{$key}"]))
            return true;

        return false;
    }

    protected function validateRuleType($type, $value)
    {

        if ($type == 'int') {

            return is_numeric($value) == false;

        } elseif ($type == 'string') {

            return is_string($value) == false;

        } elseif ($type == 'float') {

            if (is_numeric($value)) {

                $value = floatval($value);
                return is_float($value) == false;

            }

        }

    }

    protected function validateRuleRange($ranges, $value)
    {

        $num = explode(',', $ranges);
        $min = $num[0];
        $max = $num[1];

        return (($min <= $value) && ($value <= $max)) == false;

    }

    protected function validateRuleCharset($charset, $value)
    {

        // alnum √
        // email √
        // phone_number: '0123456789()-+ ' (space included)
        // extended1 : alphanumeric and [' ', '_', '-', '#', '.']
        // extended2 : alphanumeric and space and ,.
        // extended3 : alphanumeric and ,./()&
        // province  : extended2 and /()- and the character '
        // numeric   : it's simply a number, can be float or integer.


        if ($charset == 'alnum') {

            return preg_match('/^[\pL\pM\pN]+$/u', $value) == false;

        } elseif ($charset == 'email') {

            return filter_var($value, FILTER_VALIDATE_EMAIL) == false;

        } elseif ($charset == 'numeric') {

            return is_numeric($value) == false;

        } elseif ($charset == 'phone_number') {

            return preg_match('/^([0-9\(\)\+ \-]*)$/', $value) == false;

        } elseif ($charset == 'extended1') {

            return preg_match('/^[\pL\pM\pN\_\-\# \.]+$/u', $value) == false;

        } elseif ($charset == 'extended2') {

            return preg_match('/^[\pL\pM\pN\. \,]+$/u', $value) == false;

        } elseif ($charset == 'extended3') {

            return preg_match('/^[\pL\pM\pN\. \,\/\(\)\&]+$/u', $value) == false;

        } elseif ($charset == 'province') {

            return preg_match("/^[\pL\pM\pN\. \,\/\(\)\-\']+$/u", $value) == false;

        }

    }

    protected function validateRuleLen($length, $value)
    {

        $num = explode(',', $length);
        $min = $num[0];
        $max = $num[1];

        $value = trim($value);

        $chars = strlen($value);

        return (($min <= $chars) && ($chars <= $max)) == false;

    }

    protected function validateRuleValset($array, $value)
    {

        $value = strtolower($value);

        return in_array($value, array_map('strtolower', $array)) == false;

    }

    protected function validateRuleCanBeNull($canBeNull, $value)
    {

        if (!$canBeNull) {

            if (is_null($value)) return true;

        }

        return false;

    }

    protected function validateRuleCanBeEmpty($canBeEmpty, $value)
    {

        if (!$canBeEmpty) {

            if (is_null($value) || empty($value)) {
                return true;
            }

        }

        return false;

    }

    protected function nullToString($value)
    {

        if (is_array($value)) {

            $result = [];
            foreach ($value as $k => $v) {
                if (is_null($v)){
                    $result[$k] = '';
                } else {
                    $result[$k] = $v;
                }
            }

            return $result;

        } else {

            if (is_null($value))
                return '';

            return $value;

        }

    }

    private function sendCurl($endpoint, $param = array())
    {

        $ch = curl_init(); 

        if ($endpoint === null) {
            curl_setopt($ch, CURLOPT_URL, self::API_HOST);
        } else {
            curl_setopt($ch, CURLOPT_URL, self::API_HOST . "/" . $endpoint);
        }
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

}