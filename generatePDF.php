<?php

// This file is part of Moodle Course Rollover Plugin
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package     local_reportcard
 * @author      Frank
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/reportcard/generatePDF.php');

$PAGE->set_url(new moodle_url('/local/reportcard/generatePDF.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Edit');


echo $OUTPUT->header();


echo 'hello?';

define('TOKEN' , "40dfe254a5767e45bd5f2a7973837f92");
define('URL' , "https://test.cia-online.cn/webservice/rest/server.php?moodlewsrestformat=json");

function apiCall($url , $post_data) {
//    $url = "https://test.cia-online.cn/webservice/rest/server.php?moodlewsrestformat=json";
//    $post_data = array ("userid" => 56,"wsfunction" => "gradereport_overview_get_course_grades","wstoken"=>"41e8c6e5dfef059325a25c1d275b166b");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $output = curl_exec($ch);

    curl_close($ch);
    return $output;
}

function fetchCourseGradeBasedOnCourseId($userid,$courseId){
    $post_data = array ("wsfunction" => "gradereport_user_get_grade_items","wstoken"=>TOKEN,"userid"=>$userid,"courseid"=>$courseId);

    $result = apiCall(URL,$post_data);
    return $result;
}


function parseStudentGrade($data){

    $gradeList = array();

    $obj = json_decode($data);

    if(sizeof($obj->{'warnings'}) == 0){

        $gradeItems = ($obj->{'usergrades'}[0]) -> {'gradeitems'};

        foreach ($gradeItems as $item){
            if( $item->{'itemname'} == 'GRADE & Comments'){
                array_push($gradeList,$item->{'graderaw'} );
                array_push($gradeList,$item->{'feedback'} );
            }

//        TODO:fix Responsibility moodle bug(in moodle Responsibility contains a space!)

            if( strpos($item->{'itemname'} ,'Responsibility') !== false or
                strpos($item->{'itemname'} ,'Organization' )   !== false or
                strpos($item->{'itemname'} ,'Independent Work') !== false  or
                strpos($item->{'itemname'} ,'Collaboration' )  !== false or
                strpos($item->{'itemname'} ,'Self-Regulation') !== false ){
                array_push($gradeList,$item->{'gradeformatted'} );
            }

        }

    }

    return $gradeList;
}










$aDoor = $_POST['formDoor'];
$courseList = array();
if(empty($aDoor))
{
    echo("You didn't select any courses.");
}
else
{
    $N = count($aDoor);

    echo("You selected $N door(s): ");
    for($i=0; $i < $N; $i++)
    {
//        $courseList .= $aDoor[$i];
        array_push($courseList , $aDoor[$i]);
        echo($aDoor[$i] . " ");
    }
}

$filename = "C:\\Users\\Matrix\\Desktop\\kj\\xa-php7.4\\htdocs\\moodle\\local\\data.fdf";
$file = fopen( $filename, "r" );

if( $file == false ) {
    echo ( "Error in opening file" );
    exit();
}

print_r($courseList);
$courseList = array_unique($courseList);



if (isset($_GET['id'])) {
    echo $_GET['id'];


    $filesize = filesize( $filename );
    $filetext = fread( $file, $filesize );
    echo "?????????????";
    echo ( "File size : $filesize bytes" );
    echo ( "<pre>$filetext</pre>" );
    fclose( $file );

    for ($x = 0; $x <= count($courseList); $x++) {

//      Final
        if(strpos($courseList[$x] , 'mid') == false ){
            $tempData = fetchCourseGradeBasedOnCourseId(intval($_GET['id']),intval($courseList[$x])  );
            $finalInsertData = parseStudentGrade($tempData);
            var_dump($finalInsertData);
            str_replace("/V ()/T (CourseCode". ($x+1) . ")" ,"/V (".  .")/T","Hello world!");

















        }
















    }

} else {
}










