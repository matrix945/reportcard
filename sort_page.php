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
session_start();


require_once(__DIR__ . '/../../config.php');
//require_once($CFG->dirroot . '/local/reportcard/action_page.php');

$PAGE->set_url(new moodle_url('/local/reportcard/sort_page.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Edit');
$context = context_system::instance();
$roles = get_user_roles($context, $USER->id, true);


echo $OUTPUT->header();
var_dump($_SESSION["insetdata"]);
print_r($_SESSION["insetdata"])


$parameter = $_GET['courses'];
print_r($parameter) ;

$orderedInsertData = $_SESSION["insetdata"];

$orderedInsertData = array_combine($parameter , $orderedInsertData );
var_dump($orderedInsertData);



//array(2) { [0]=> string(10) "coursess=1" [1]=> string(10) "coursess=0" }
//$str = str_replace("coursess=","&",$str);
//echo $str;
//echo ('<br>');
//var_dump (explode("&",$str));



echo 'hello?';

