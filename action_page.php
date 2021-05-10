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
require_once($CFG->dirroot . '/local/reportcard/action_page.php');

$PAGE->set_url(new moodle_url('/local/reportcard/action_page.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Edit');
$context = context_system::instance();
$roles = get_user_roles($context, $USER->id, true);

global $SESSION;

echo $OUTPUT->header();
$email = $_POST["email"];

echo "query email: " . $_POST["email"];


/**
 *
 *  Pre-define constant
 *
 */

define('TOKEN' , "40dfe254a5767e45bd5f2a7973837f92");
define('URL' , "https://test.cia-online.cn/webservice/rest/server.php?moodlewsrestformat=json");
define('FDFLOCATION' , $CFG->dirroot.'/local/reportcard/data.fdf' );  // fdf file location
define('GENERATEFDFLOCATION' , $CFG->dirroot.'/local/reportcard/repo/' );  // fdf file location
define('SHELL_COMMAND' , '');   // pdftk shell command for future use
define('DEBUG' , true); //debug mode


/**
 * Main apiCall cuntion
 * @param $url: api url
 * @param $post_data: data input for restfull call
 * detail see moodle document.
 */


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

function validateUserEmail($url , $email) {
//    echo $_POST[TOKEN];
    $post_data = array ("field" => "email","wsfunction" => "core_user_get_users_by_field","wstoken"=>TOKEN,"values[0]"=>$email);
    $result = apiCall($url , $post_data);
    return $result;
}

function checkErrorOrEmpty($result) {

    if(sizeof($result) < 3){
        return false;
    }
    return true;

}

function getEnrolledCourseByStuId($url , $id) {
    $post_data = array ("wsfunction" => "core_enrol_get_users_courses","wstoken"=>TOKEN,"userid"=>$id);
    $result = apiCall($url , $post_data);
    return $result;
}

/*

Array ( [0] => Array ( [id] => 8 [code] => MHF4U [idnumber] => Advanced Functions ) [1] => Array ( [id] => 7 [code] => MCV4U [idnumber] => MCV4U ) [2] => Array ( [id] => 12 [code] => ENG4U [idnumber] => ) [3] => Array ( [id] => 19 [code] => SCH4U [idnumber] => SCH4U ) [4] => Array ( [id] => 14 [code] => SPH4U [idnumber] => ) [5] => Array ( [id] => 67 [code] => BBB4MO [idnumber] => International Business ) [6] => Array ( [id] => 27 [code] => BBB4M [idnumber] => ) )

*/

function parseGetEnrolledCourseByStuId($data) {
    $courseList = array();
    $course = array();
    $data = getEnrolledCourseByStuId(URL,39);
    $obj = json_decode($data);


    if (checkErrorOrEmpty($obj)){

        foreach ( $obj as $item ){
            $course["id"] = $item->{"id"};
            $course["code"] = $item->{"shortname"}; //MHF4U
            $course["idnumber"] = $item->{"idnumber"}; //idnumber
            $courseList[] = $course;
            $course = array();
        }

    }

    if(DEBUG){echo "student enrolled courses list: \n";}
    if(DEBUG){var_dump($courseList);}

    return $courseList;
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

//        echo $item->{'itemname'};
//        echo $item->{'itemname'} == 'Responsibility ';
//
//        echo strpos($item->{'itemname'} ,'Responsibility');


            if( strpos($item->{'itemname'} ,'Responsibility') !== false or
                strpos($item->{'itemname'} ,'Organization' )   !== false or
                strpos($item->{'itemname'} ,'Independent Work') !== false  or
                strpos($item->{'itemname'} ,'Collaboration' )  !== false or
                strpos($item->{'itemname'} ,'Self-Regulation') !== false ){
                array_push($gradeList,$item->{'gradeformatted'} );
            }

        }

    }

//    var_dump($gradeList);
//echo $gradeList;

    return $gradeList;
}

/**
 * @function: produce the checkbox html
 * @param $userid: student moodle id
 * @param $data: student course list
 */

function htmlCheckBoxMaker($userid , $data){

    $courseIDWithGardesList=array();
    $singleCourseData= array();

    $CourseNumber =  count($data);
    $checkBoxHtml = '<form action="/local/reportcard/action_page.php" method="post"> Student Course List:<br />';
    $htmlid= 0;
    for ($x = 0; $x < $CourseNumber;$x++) {
        $fullCourseGradeList = fetchCourseGradeBasedOnCourseId($userid,$data[$x]['id']);
        $courseGradeList = parseStudentGrade($fullCourseGradeList);

//
//        if(count($courseGradeList) != 14){
//            $courseGradeList = [100, "midterm comments ", "E", "E", "E", "E", "99","finial comment", "E", "E", "E", "E", "E", "E"];
//        }
//
//        $courseIDWithGardesList[($data[$x]['id'])] = $courseGradeList[0] . ' '. ' '.$courseGradeList[1] .' '.$courseGradeList[2] .' '.$courseGradeList[3].' '
//            .$courseGradeList[4] .' '.$courseGradeList[5] .' '.$courseGradeList[6] . $courseGradeList[7] . ' '. ' '.$courseGradeList[8] .' '.$courseGradeList[9] .' '.$courseGradeList[10].' '
//            .$courseGradeList[11] .' '.$courseGradeList[12] .' '.$courseGradeList[13];
//
//
//        if ($courseGradeList[7] != '-'){
//            $checkBoxHtml = $checkBoxHtml . '<input type="checkbox" name="formDoor[]" value=" ' .  $data[$x]['id'] . ' ' .$data[$x]['code'].  ' midterm '. $courseGradeList[0] . ' '. ' '.$courseGradeList[1] .' '.$courseGradeList[2] .' '.$courseGradeList[3].' '
//                .$courseGradeList[4] .' '.$courseGradeList[5] .' '.$courseGradeList[6] . ';' .
//                '" />' . 'midterm'. $courseGradeList[0] . ' '. ' '.$courseGradeList[1] .' '.$courseGradeList[2] .' '.$courseGradeList[3].' '
//                .$courseGradeList[4] .' '.$courseGradeList[5] .' '.$courseGradeList[6] . '<br />';
//
//
//            $checkBoxHtml = $checkBoxHtml . '<input type="checkbox" name="formDoor[]" value=" '.  $data[$x]['id'] . ' ' .$data[$x]['code'].  ' final '. $courseGradeList[7] . ' '. ' '.$courseGradeList[8] .' '.$courseGradeList[9] .' '.$courseGradeList[10].' '
//                .$courseGradeList[11] .' '.$courseGradeList[12] .' '.$courseGradeList[13] . ';' .
//                '" />' . 'final'. $courseGradeList[7] . ' '. ' '.$courseGradeList[8] .' '.$courseGradeList[9] .' '.$courseGradeList[10].' '
//                .$courseGradeList[11] .' '.$courseGradeList[12] .' '.$courseGradeList[13] . '<br />';
//        }
//        else {
//            $checkBoxHtml = $checkBoxHtml . '<input type="checkbox" name="formDoor[]" value=" '.  $data[$x]['id']  . ' ' .$data[$x]['code']. ' midterm '. $courseGradeList[0] . ' '. ' '.$courseGradeList[1] .' '.$courseGradeList[2] .' '.$courseGradeList[3].' '
//                .$courseGradeList[4] .' '.$courseGradeList[5] .' '.$courseGradeList[6] . ';' .
//                '" />' . 'midterm'. $courseGradeList[0] . ' '. ' '.$courseGradeList[1] .' '.$courseGradeList[2] .' '.$courseGradeList[3].' '
//                .$courseGradeList[4] .' '.$courseGradeList[5] .' '.$courseGradeList[6] . '<br />';
//
//        }

//      TODO: HOTFIX why? release this block if all moodle courses contain 14 fields
        if(count($courseGradeList) != 14){
            $courseGradeList = [100, "midterm comments ", "E", "E", "E", "E", "99","finial comment", "E", "E", "E", "E", "E", "E"];
        }

//        $courseIDWithGardesList[($data[$x]['id'])] = $courseGradeList[0] . ' '. ' '.$courseGradeList[1] .' '.$courseGradeList[2] .' '.$courseGradeList[3].' '
//            .$courseGradeList[4] .' '.$courseGradeList[5] .' '.$courseGradeList[6] . $courseGradeList[7] . ' '. ' '.$courseGradeList[8] .' '.$courseGradeList[9] .' '.$courseGradeList[10].' '
//            .$courseGradeList[11] .' '.$courseGradeList[12] .' '.$courseGradeList[13];


//      Mid-term
        $singleCourseData= array();
        array_push($singleCourseData,$data[$x]['code']);
        array_push($singleCourseData,$courseGradeList[0]);
        array_push($singleCourseData,$courseGradeList[1]);
        array_push($singleCourseData,$courseGradeList[2]);
        array_push($singleCourseData,$courseGradeList[3]);
        array_push($singleCourseData,$courseGradeList[4]);
        array_push($singleCourseData,$courseGradeList[5]);
        array_push($singleCourseData,$courseGradeList[6]);
        array_push($courseIDWithGardesList ,$singleCourseData );


//      Final-term
        $singleCourseData = array();
        array_push($singleCourseData,$data[$x]['code']);
        array_push($singleCourseData,$courseGradeList[7]);
        array_push($singleCourseData,$courseGradeList[8]);
        array_push($singleCourseData,$courseGradeList[9]);
        array_push($singleCourseData,$courseGradeList[10]);
        array_push($singleCourseData,$courseGradeList[11]);
        array_push($singleCourseData,$courseGradeList[12]);
        array_push($singleCourseData,$courseGradeList[13]);
        array_push($courseIDWithGardesList ,$singleCourseData );

//      Full year
        $singleCourseData= array();
        array_push($singleCourseData,$data[$x]['code']);
        array_push($singleCourseData,$courseGradeList[0]);
        array_push($singleCourseData,$courseGradeList[1]);
        array_push($singleCourseData,$courseGradeList[2]);
        array_push($singleCourseData,$courseGradeList[3]);
        array_push($singleCourseData,$courseGradeList[4]);
        array_push($singleCourseData,$courseGradeList[5]);
        array_push($singleCourseData,$courseGradeList[6]);
        array_push($singleCourseData,$courseGradeList[7]);
        array_push($singleCourseData,$courseGradeList[8]);
        array_push($singleCourseData,$courseGradeList[9]);
        array_push($singleCourseData,$courseGradeList[10]);
        array_push($singleCourseData,$courseGradeList[11]);
        array_push($singleCourseData,$courseGradeList[12]);
        array_push($singleCourseData,$courseGradeList[13]);
        array_push($courseIDWithGardesList ,$singleCourseData );

//      This student has final grades
        if ($courseGradeList[7] != '-'){

            $checkBoxHtml = $checkBoxHtml . "<br>";
            $checkBoxHtml = $checkBoxHtml . '<p>' . '(' .$data[$x]['id'] .')' .'&nbsp;'.'&nbsp;'.'&nbsp;'.'&nbsp;'
                .$data[$x]['code'].'&nbsp;'.'&nbsp;'.'&nbsp;'.'&nbsp;'. $data[$x]['idnumber']. '</p>';

            $checkBoxHtml = $checkBoxHtml . '<input type="checkbox" name="formDoor[]" value="' .  $htmlid.
                '" />' . 'midterm'. $courseGradeList[0] . ' '. ' '.$courseGradeList[1] .' '.$courseGradeList[2] .' '.$courseGradeList[3].' '
                .$courseGradeList[4] .' '.$courseGradeList[5] .' '.$courseGradeList[6] . '<br />';

            $htmlid = $htmlid + 1;

            $checkBoxHtml = $checkBoxHtml . '<input type="checkbox" name="formDoor[]" value="'.  $htmlid .
                '" />' . 'final'. $courseGradeList[7] . ' '. ' '.$courseGradeList[8] .' '.$courseGradeList[9] .' '.$courseGradeList[10].' '
                .$courseGradeList[11] .' '.$courseGradeList[12] .' '.$courseGradeList[13] . '<br />';
            $htmlid = $htmlid + 1;


            $checkBoxHtml = $checkBoxHtml . '<input type="checkbox" name="formDoor[]" value="'.  $htmlid .
                '" />' .  $courseGradeList[0] . ' '. ' '.$courseGradeList[1] .' '.$courseGradeList[2] .' '.$courseGradeList[3].' '
                .$courseGradeList[4] .' '.$courseGradeList[5] .' '.$courseGradeList[6] . $courseGradeList[7] . ' '. ' '.$courseGradeList[8] .' '.$courseGradeList[9] .' '.$courseGradeList[10].' '
                .$courseGradeList[11] .' '.$courseGradeList[12] .' '.$courseGradeList[13] . '<br />';
            $htmlid = $htmlid + 1;

        }

//
//        else {
//            $checkBoxHtml = $checkBoxHtml . '<input type="checkbox" name="formDoor[]" value="'.  'mid'.$data[$x]['id']  .
//                '" />' . 'midterm'. $courseGradeList[0] . ' '. ' '.$courseGradeList[1] .' '.$courseGradeList[2] .' '.$courseGradeList[3].' '
//                .$courseGradeList[4] .' '.$courseGradeList[5] .' '.$courseGradeList[6] . '<br />';
//
//        }

    }

    $checkBoxHtml = $checkBoxHtml . '<input type="submit" name="formSubmit" value="Submit" />

</form>';

    echo $checkBoxHtml;

//    var_dump($courseIDWithGardesList);
    return $courseIDWithGardesList;

}

?>

<?php


/**
 * MAIN Function
 */


$studentId = -1;
$data =  validateUserEmail(URL,"2121373869@qq.com");
//echo $data;
$obj = json_decode($data);
if(DEBUG){var_dump($obj);}
//echo $obj[0]->{"id"};
if (checkErrorOrEmpty($obj)){
    $studentId = $obj[0]-> {'id'};
    $_SESSION['reportCard_studentId'] = $obj[0]-> {'id'};
    $_SESSION['reportCard_studentEmail'] = "2121373869@qq.com";
    echo ("<br>");
    var_dump($_SESSION['reportCard_studentEmail']);
    echo ("<br>");
}


$courseList = parseGetEnrolledCourseByStuId($studentId);


//parseCourseArrayIntoCheckBoxForm($courseList);


$courseGradeList = fetchCourseGradeBasedOnCourseId(39,8);
parseStudentGrade($courseGradeList);
$courseIDWithGardesList =  htmlCheckBoxMaker(39,$courseList);
$finalInsertData = array();

/**
 * MAIN Function
 */

// If the checkbox is selected and submitted
if(isset($_POST['formDoor'])) {
    $aDoor = $_POST['formDoor'];
    if(empty($aDoor))
    {
        echo("You didn't select any courses.");
    }
    else
    {
        $filename = FDFLOCATION;
        $file = fopen( $filename, "r" );

        if( $file == false ) {
            echo ( "Error in opening file" );
            exit();
        }

        $filesize = filesize( $filename );
        $filetext = fread( $file, $filesize );
//            echo ( "File size : $filesize bytes" );
//            echo ( "<pre>$filetext</pre>" );
        if(DEBUG){var_dump($filetext);}
        fclose( $file );
        echo ("</br>");

        $N = count($aDoor);

        if(DEBUG){echo("You selected $N choice (s): ");}
        echo('<br>');
        $finalInsertData2 = array();
        $htmlSort =  '<h1>The select element</h1>
<p>Please rank your item.</p>
<form action="/local/reportcard/sort_page.php">';
        for($i=0; $i < $N; $i++) {

            array_push($finalInsertData2, $courseIDWithGardesList[intval($aDoor[$i])]);
            echo "<br>";
            echo "<br>";
            var_dump($courseIDWithGardesList[intval($aDoor[$i])]) ;
            echo "<br>";

//            echo $htmlSort;

            $singleBlock = ' <label for="'. $courseIDWithGardesList[intval($aDoor[$i])][0].'">'. $courseIDWithGardesList[intval($aDoor[$i])][0] .'</label>
  <select name="'. 'courses[]' .'"id="courses">';

            for($k=0; $k < $N; $k++) {
                $singleBlock = $singleBlock . '<option value="' . $k .'">'.$k.'</option>';
            }
            $singleBlock = $singleBlock. '  </select>
  <br><br>';

            $htmlSort = $htmlSort . $singleBlock;

        }
        $_SESSION['reportCard_studentEmail'] = "2121373869@qq.com";
        $_SESSION['insertData'] = $finalInsertData2;
//        $SESSION->{'insertData'} = $finalInsertData2;

//        var_dump($SESSION);

        $htmlSort = $htmlSort . '<input type="submit" value="Submit">
</form>';



        echo $htmlSort;


//        print_r($aDoor);

//        var_dump($courseIDWithGardesList);
//        for($i=0; $i < $N; $i++)
//        {
//
//            array_push($finalInsertData,$courseIDWithGardesList[intval($aDoor[$i])]);
////           echo($aDoor[$i] . " ");
//            if(DEBUG){echo ("finalInsertData");}
//            echo('<br>');
//            if(DEBUG){var_dump($finalInsertData);}
//            echo('<br>');
//
//            $_SESSION["insetData"] = $finalInsertData;
//
//            /**
//             *     array (size=15)
//            0 => string 'MHF4U' (length=5)
//            1 => int 88
//            2 => string 'woqu' (length=4)
//            3 => string 'G' (length=1)
//            4 => string 'N' (length=1)
//            5 => string 'S' (length=1)
//            6 => string 'G' (length=1)
//            7 => string 'G' (length=1)
//            8 => int 40
//            9 => string 'paodekuaipaodekuai' (length=18)
//            10 => string 'G' (length=1)
//            11 => string 'G' (length=1)
//            12 => string 'S' (length=1)
//            13 => string 'N' (length=1)
//            14 => string '-' (length=1)
//             *
//             */
//
//            if(count($finalInsertData[$i]) == 8){
//                if(DEBUG){echo "Replace fdf file now!";}
//                $filetext = str_replace( ("/V ()". chr(10)."/T (CourseCode" . ($i+1) . ")") ,(("/V (".$finalInsertData[$i][0].")". chr(10)."/T (CourseCode" . ($i+1) . ")")),$filetext);
//                $filetext = str_replace( ("/V ()". chr(10)."/T (MidMarkMed" . ($i+1) . ")") ,(("/V (".$finalInsertData[$i][1].")". chr(10)."/T (MidMarkMed" . ($i+1) . ")")),$filetext);
//                $filetext = str_replace( ("/V ()". chr(10)."/T (MidRes" . ($i+1) . ")") ,(("/V (".$finalInsertData[$i][3].")". chr(10)."/T (MidRes" . ($i+1) . ")")),$filetext);
//                $filetext = str_replace( ("/V ()". chr(10)."/T (MidOrg" . ($i+1) . ")") ,(("/V (".$finalInsertData[$i][4].")". chr(10)."/T (MidOrg" . ($i+1) . ")")),$filetext);
//                $filetext = str_replace( ("/V ()". chr(10)."/T (MidInd" . ($i+1) . ")") ,(("/V (".$finalInsertData[$i][5].")". chr(10)."/T (MidInd" . ($i+1) . ")")),$filetext);
//                $filetext = str_replace( ("/V ()". chr(10)."/T (MidCol" . ($i+1) . ")") ,(("/V (".$finalInsertData[$i][6].")". chr(10)."/T (MidCol" . ($i+1) . ")")),$filetext);
//                $filetext = str_replace( ("/V ()". chr(10)."/T (MidIni" . ($i+1) . ")") ,(("/V (".$finalInsertData[$i][7].")". chr(10)."/T (MidIni" . ($i+1) . ")")),$filetext);
//
//            }
//
//
//
//        }
//        echo('<br>');
//        if(DEBUG){var_dump($filetext);}
//        echo('<br>');
//
//
////      WARNING: You can;t use fullname here because fullname may contain space! It causes issue when you pass it to shell
//        $filename = GENERATEFDFLOCATION .$obj[0]->{'email'} . '.data.fdf' ;
//        echo('<br>');
//        if(DEBUG){var_dump($filename);}
//        echo('<br>');
//        $myfile = fopen($filename, "w") or die("Unable to open file!");
//        fwrite($myfile, $filetext);
//        fclose($myfile);
//
//        $command = 'pdftk /var/www/html/moodle/local/reportcard/repo/report_card_template.pdf fill_form ' . $filename . ' output /var/www/html/moodle/local/reportcard/repo/form_with_data.pdf';
//        if(DEBUG){echo $command;}
//        $msg = shell_exec($command);
//        print_r($msg);

    }
} else {



}


//windoes ! windoes shell need 2>&1 at the end
//$msg = shell_exec('pdftk C:\Users\Matrix\Desktop\kj\xa-php7.4\htdocs\moodle\local\report_card_template.pdf fill_form ' . $filename .' output C:\Users\Matrix\Desktop\kj\xa-php7.4\htdocs\moodle\local\form_with_data.pdf 2>&1');
//$msg = shell_exec('pdftk C:\Users\Matrix\Desktop\kj\xa-php7.4\htdocs\moodle\local\report_card_template.pdf fill_form C:\Users\Matrix\Desktop\kj\xa-php7.4\htdocs\moodle\local\data.fdf output C:\Users\Matrix\Desktop\kj\xa-php7.4\htdocs\moodle\local\form_with_data.pdf 2>&1');
//print_r($msg);


?>








