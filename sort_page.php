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

// Start the session
//session_start();


require_once(__DIR__ . '/../../config.php');
//require_once($CFG->dirroot . '/local/reportcard/action_page.php');

$PAGE->set_url(new moodle_url('/local/reportcard/sort_page.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Edit');
$context = context_system::instance();
$roles = get_user_roles($context, $USER->id, true);


echo $OUTPUT->header();

var_dump($_SESSION['insertData']);
echo ("<br>");
echo ("<br>");
echo ("<br>");
//var_dump($SESSION);



$parameter = $_GET['courses'];
print_r($parameter) ;

$orderedInsertData = $_SESSION["insertData"];

$orderedInsertData = array_combine($parameter , $orderedInsertData );
var_dump($orderedInsertData);

if( ($orderedInsertData !==null) && ($parameter !== null) ){
            for($i=0; $i < count($orderedInsertData); $i++)
        {

            /**
             *     array (size=15)
            0 => string 'MHF4U' (length=5)
            1 => int 88
            2 => string 'woqu' (length=4)
            3 => string 'G' (length=1)
            4 => string 'N' (length=1)
            5 => string 'S' (length=1)
            6 => string 'G' (length=1)
            7 => string 'G' (length=1)
            8 => int 40
            9 => string 'paodekuaipaodekuai' (length=18)
            10 => string 'G' (length=1)
            11 => string 'G' (length=1)
            12 => string 'S' (length=1)
            13 => string 'N' (length=1)
            14 => string '-' (length=1)
             *
             */

            if(count($finalInsertData[$i]) == 8){
                if(DEBUG){echo "Replace fdf file now!";}
                $filetext = str_replace( ("/V ()". chr(10)."/T (CourseCode" . ($i+1) . ")") ,(("/V (".$finalInsertData[$i][0].")". chr(10)."/T (CourseCode" . ($i+1) . ")")),$filetext);
                $filetext = str_replace( ("/V ()". chr(10)."/T (MidMarkMed" . ($i+1) . ")") ,(("/V (".$finalInsertData[$i][1].")". chr(10)."/T (MidMarkMed" . ($i+1) . ")")),$filetext);
                $filetext = str_replace( ("/V ()". chr(10)."/T (MidRes" . ($i+1) . ")") ,(("/V (".$finalInsertData[$i][3].")". chr(10)."/T (MidRes" . ($i+1) . ")")),$filetext);
                $filetext = str_replace( ("/V ()". chr(10)."/T (MidOrg" . ($i+1) . ")") ,(("/V (".$finalInsertData[$i][4].")". chr(10)."/T (MidOrg" . ($i+1) . ")")),$filetext);
                $filetext = str_replace( ("/V ()". chr(10)."/T (MidInd" . ($i+1) . ")") ,(("/V (".$finalInsertData[$i][5].")". chr(10)."/T (MidInd" . ($i+1) . ")")),$filetext);
                $filetext = str_replace( ("/V ()". chr(10)."/T (MidCol" . ($i+1) . ")") ,(("/V (".$finalInsertData[$i][6].")". chr(10)."/T (MidCol" . ($i+1) . ")")),$filetext);
                $filetext = str_replace( ("/V ()". chr(10)."/T (MidIni" . ($i+1) . ")") ,(("/V (".$finalInsertData[$i][7].")". chr(10)."/T (MidIni" . ($i+1) . ")")),$filetext);

            }



        }
        echo('<br>');
        if(DEBUG){var_dump($filetext);}
        if(DEBUG){var_dump($_SESSION['reportCard_studentEmail']);}
        echo('<br>');


//      WARNING: You can;t use fullname here because fullname may contain space! It causes issue when you pass it to shell
        $filename = GENERATEFDFLOCATION .$_SESSION['reportCard_studentEmail'] . '.data.fdf' ;
        echo('<br>');
        if(DEBUG){var_dump($filename);}
        echo('<br>');
        $myfile = fopen($filename, "w") or die("Unable to open file!");
        fwrite($myfile, $filetext);
        fclose($myfile);

        $command = 'pdftk /var/www/html/moodle/local/reportcard/repo/report_card_template.pdf fill_form ' . $filename . ' output /var/www/html/moodle/local/reportcard/repo/form_with_data.pdf';
        if(DEBUG){echo $command;}
        $msg = shell_exec($command);
        print_r($msg);

        }

echo 'hello?';

