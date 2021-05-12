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
define('GENERATEFDFLOCATION', $CFG->dirroot . '/local/reportcard/repo/');  // fdf file location
define('FDFLOCATION', $CFG->dirroot . '/local/reportcard/data.fdf');  // fdf file location
define('DEBUG', true); //debug mode

$PAGE->set_url(new moodle_url('/local/reportcard/sort_page.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Edit');
$context = context_system::instance();
$roles = get_user_roles($context, $USER->id, true);


echo $OUTPUT->header();

// return current Toronto time format 2021-04-04T13:35:48
function getCurrentTime(): string
{

    $dtz = new DateTimeZone("America/Toronto");
    $dt = new DateTime("now", $dtz);
    //Stores time as "2021-04-04T13:35:48":
    $currentTime = $dt->format("Y-m-d") . "T" . $dt->format("H:i:s");

    if (DEBUG) {
        echo("<br>");
        var_dump($currentTime);
        echo("<br>");
    }
    return $currentTime;
}

var_dump($_SESSION['insertData']);
echo("<br>");

$parameter = $_GET['courses'];
print_r($parameter);

$orderedInsertData = $_SESSION["insertData"];

$orderedInsertData = array_combine($parameter, $orderedInsertData);
var_dump($orderedInsertData);

if (($orderedInsertData !== null) && ($parameter !== null)) {

    $filename = FDFLOCATION;
    $file = fopen($filename, "r");

    if ($file == false) {
        echo("Error in opening file");
        exit();
    }

    $filesize = filesize($filename);
    $filetext = fread($file, $filesize);
//            echo ( "File size : $filesize bytes" );
//            echo ( "<pre>$filetext</pre>" );
    if (DEBUG) {
        var_dump($filetext);
    }
    fclose($file);

    for ($i = 0; $i < count($orderedInsertData); $i++) {

        /**
         *     array (size=15)
         * 0 => string 'MHF4U' (length=5)
         * 1 => int 88
         * 2 => string 'woqu' (length=4)
         * 3 => string 'G' (length=1)
         * 4 => string 'N' (length=1)
         * 5 => string 'S' (length=1)
         * 6 => string 'G' (length=1)
         * 7 => string 'G' (length=1)
         * 8 => int 40
         * 9 => string 'paodekuaipaodekuai' (length=18)
         * 10 => string 'G' (length=1)
         * 11 => string 'G' (length=1)
         * 12 => string 'S' (length=1)
         * 13 => string 'N' (length=1)
         * 14 => string '-' (length=1)
         *
         */

        if (count($orderedInsertData[$i]) == 8) {
            if (DEBUG) {
                echo "Replace fdf file now! midterm section";
            }
            $filetext = str_replace(("/V ()" . chr(10) . "/T (CourseCode" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][0] . ")" . chr(10) . "/T (CourseCode" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (MidMarkMed" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][1] . ")" . chr(10) . "/T (MidMarkMed" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (MidRes" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][3] . ")" . chr(10) . "/T (MidRes" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (MidOrg" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][4] . ")" . chr(10) . "/T (MidOrg" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (MidInd" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][5] . ")" . chr(10) . "/T (MidInd" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (MidCol" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][6] . ")" . chr(10) . "/T (MidCol" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (MidIni" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][7] . ")" . chr(10) . "/T (MidIni" . ($i + 1) . ")")), $filetext);

        }
        if (count($orderedInsertData[$i]) == 15) {
            if (DEBUG) {
                echo "Replace fdf file now! full year section";
            }
            $filetext = str_replace(("/V ()" . chr(10) . "/T (CourseCode" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][0] . ")" . chr(10) . "/T (CourseCode" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (MidMarkMed" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][1] . ")" . chr(10) . "/T (MidMarkMed" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (MidRes" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][3] . ")" . chr(10) . "/T (MidRes" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (MidOrg" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][4] . ")" . chr(10) . "/T (MidOrg" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (MidInd" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][5] . ")" . chr(10) . "/T (MidInd" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (MidCol" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][6] . ")" . chr(10) . "/T (MidCol" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (MidIni" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][7] . ")" . chr(10) . "/T (MidIni" . ($i + 1) . ")")), $filetext);

            $filetext = str_replace(("/V ()" . chr(10) . "/T (FinMarkMed" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][8] . ")" . chr(10) . "/T (MidMarkMed" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (FinRes" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][10] . ")" . chr(10) . "/T (MidRes" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (FinOrg" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][11] . ")" . chr(10) . "/T (MidOrg" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (FinInd" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][12] . ")" . chr(10) . "/T (MidInd" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (FinCol" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][13] . ")" . chr(10) . "/T (MidCol" . ($i + 1) . ")")), $filetext);
            $filetext = str_replace(("/V ()" . chr(10) . "/T (FinIni" . ($i + 1) . ")"), (("/V (" . $orderedInsertData[$i][14] . ")" . chr(10) . "/T (MidIni" . ($i + 1) . ")")), $filetext);

        }


    }
    echo('<br>');
    if (DEBUG) {
        var_dump($filetext);
    }
    if (DEBUG) {
        var_dump($_SESSION['reportCard_studentEmail']);
    }
    echo('<br>');

    $current_time = getCurrentTime();

//      WARNING: You can;t use fullname here because fullname may contain space! It causes issue when you pass it to shell
    $filename = GENERATEFDFLOCATION . $_SESSION['reportCard_studentEmail'] . $current_time . '.data.fdf';
    echo('<br>');
    if (DEBUG) {
        var_dump($filename);
    }
    echo('<br>');
    $myfile = fopen($filename, "w") or die("Unable to open file!");
    fwrite($myfile, $filetext);
    fclose($myfile);

    $command = 'pdftk /var/www/html/moodle/local/reportcard/repo/report_card_template.pdf fill_form ' . $filename .
        ' output /var/www/html/moodle/local/reportcard/repo/' . $_SESSION['reportCard_studentEmail'] . $current_time . 'form_with_data.pdf';
    if (DEBUG) {
        echo $command;
    }
    $msg = shell_exec($command);
    print_r($msg);

}

echo 'hello?';

