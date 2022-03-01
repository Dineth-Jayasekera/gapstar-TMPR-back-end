<?php

namespace App\Http\Controllers;

class MemberDataController extends Controller
{

    public function onBoardingData()
    {

        /**Read CSV File*/
        $csv = array_map('str_getcsv', file('export.csv'));

        /**Remove the name columns from array*/
        array_shift($csv);

        /**Create Object Array Using CSV data*/
        foreach ($csv as $row) {
            $row_data = explode(';', $row[0]);

            $csv_data[] = array(
                "user_id" => $row_data[0],
                "created_at" => $row_data[1],
                "onboarding_perentage" => $row_data[2],
                "count_applications" => $row_data[3],
                "count_accepted_applications" => $row_data[4],
                "year" => date('Y', strtotime($row_data[1])),
                "week" => date('W', strtotime($row_data[1])),
                "week_name" => date('Y', strtotime($row_data[1])) . '/' . date('W', strtotime($row_data[1]))

            );

        }

        /**Group the data by week*/
        $week_groups = $this->group_by("week_name", $csv_data);

        /**Create Weekly retention array*/
        $weekly_retention = array();

        /**Populate weekly retention array */
        foreach ($week_groups as $group) {

            $week_start = "";
            $week_name = "";

            $step1 = 0;
            $step2 = 0;
            $step3 = 0;
            $step4 = 0;
            $step5 = 0;
            $step6 = 0;
            $step7 = 0;
            $step8 = 0;

            foreach ($group as $row) {

                $week_start = date("Y-m-d", strtotime('monday this week', strtotime($row['created_at'])));;
                $week_name = $row['week_name'];

                if ($row['onboarding_perentage'] <= 100) {
                    $step1 = $step1 + 1;
                }
                if ($row['onboarding_perentage'] > 0 && $row['onboarding_perentage'] <= 100) {
                    $step2 = $step2 + 1;
                }
                if ($row['onboarding_perentage'] > 20 && $row['onboarding_perentage'] <= 100) {
                    $step3 = $step3 + 1;
                }
                if ($row['onboarding_perentage'] > 40 && $row['onboarding_perentage'] <= 100) {
                    $step4 = $step4 + 1;
                }
                if ($row['onboarding_perentage'] > 50 && $row['onboarding_perentage'] <= 100) {
                    $step5 = $step5 + 1;
                }
                if ($row['onboarding_perentage'] > 70 && $row['onboarding_perentage'] <= 100) {
                    $step6 = $step6 + 1;
                }
                if ($row['onboarding_perentage'] > 90 && $row['onboarding_perentage'] <= 100) {
                    $step7 = $step7 + 1;
                }
                if ($row['onboarding_perentage'] == 100) {
                    $step8 = $step8 + 1;
                }

            }

            /**Add data to weekly retention array*/
            $weekly_retention[] = array(
                "week_start" => $week_start,
                "week_name" => $week_name,
                "step1" => $step1,
                "step2" => $step2,
                "step3" => $step3,
                "step4" => $step4,
                "step5" => $step5,
                "step6" => $step6,
                "step7" => $step7,
                "step8" => $step8,
            );

        }


        /**Create Chart data array for response*/

        $chartArray ["chart"] = array(
            "type" => "line"
        );
        $chartArray ["title"] = array(
            "text" => "Weekly Retention Curve"
        );
        $chartArray ["credits"] = array(
            "enabled" => false
        );
        $chartArray ["xAxis"] = array(
            "categories" => array()
        );
        $chartArray ["tooltip"] = array(
            "valueSuffix" => "%"
        );

        $categoryArray = array(
            '0',
            '20',
            '40',
            '50',
            '70',
            '90',
            '99',
            '100'
        );

        $chartArray ["xAxis"] = array(
            "categories" => $categoryArray
        );
        $chartArray ["yAxis"] = array(
            "title" => array(
                "text" => "Total Onboarded"
            ),
            'labels' => array(
                'format' => '{value}%'
            ),
            'min' => '0',
            'max' => '100'
        );

        /**Create data set for chart*/
        foreach ($weekly_retention as $week) {
            $week = (object)$week;

            $dataArray = array();

            for ($i = 1; $i <= 8; $i++) {

                if ($i == 1) {
                    $dataArray[] = 100;
                } else {
                    $dataArray[] = round(($week->{"step" . $i} / $week->step1) * 100);
                }

            }


            $chartArray ["series"] [] = array(
                "name" => $week->week_start,
                "data" => $dataArray
            );
        }

        /**Send cart response*/
        return response()->json($chartArray)->setEncodingOptions(JSON_NUMERIC_CHECK);


    }

    /**Function For Group Array*/

    function group_by($key, $data)
    {
        $result = array();

        foreach ($data as $val) {
            if (array_key_exists($key, $val)) {
                $result[$val[$key]][] = $val;
            } else {
                $result[""][] = $val;
            }
        }

        return $result;
    }

}
