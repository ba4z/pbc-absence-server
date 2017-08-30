<?php
/**
 * Created by Bas van Klaarbergen
 * Date: 11/26/16
 */
header('Access-Control-Allow-Origin: *');
session_start();
require_once "constants.php";
require_once "Populi.php";

function checkDatePattern($date)
{
    //Match: yyyy-mm-dd
    return preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $date);
}

header('Content-Type: application/json');
if(isset($_POST["function"])) {
    $populiService = new Populi();
    $function = $_POST["function"];
    switch ($function) {
        case "login":
            if($_POST["username"] && $_POST["password"]){
                $user = $_POST["username"];
                $pw = $_POST["password"];
                try {
                    $token = $populiService->login($user, $pw);
                    $personId = $populiService->getPersonId();
                    echo json_encode(array('personId' => $personId, 'token' => $token));
                } catch (PopuliException $e) {
                    echo json_encode(array('error' => "Incorrect Username/password combination"));
                } catch (Exception $e) {
                    echo json_encode(array('error' => "Could connect to Populi. Please try again in a couple minutes"));
                }
            }
            break;
        case "getCurrentAcademicTerm":
            if($_POST["token"]){
                $token = $_POST["token"];
                echo $populiService->getCurrentTerm($token);
            } else {
                echo http_response_code(400);
            }
            break;
        case "getPerson":
            if($_POST["token"] && $_POST["personId"]){
                $token = $_POST["token"];
                $personId = $_POST["personId"];
                echo $populiService->getPerson($token, $personId);
            } else {
                echo http_response_code(400);
            }
            break;
        case "getStudentCourses":
            if($_POST["personId"] && $_POST["token"] && $_POST["termId"]){
                $personId = $_POST["personId"];
                $token = $_POST["token"];
                $termId = $_POST["termId"];
                echo $populiService->getMyCourses($token, $termId, $personId);
            } else {
                echo http_response_code(400);
            }
            break;
        case "getCourseInstance":
            if($_POST["token"] && $_POST["instanceId"]){
                $token = $_POST["token"];
                $instanceId = $_POST["instanceId"];
                echo $populiService->getCourseInstance($token, $instanceId);
            } else {
                echo http_response_code(400);
            }
            break;
        case "getCourseInstanceMeetings":
            if($_POST["token"] && $_POST["instanceId"]){
                $token = $_POST["token"];
                $instanceId = $_POST["instanceId"];
                echo $populiService->getCourseInstanceMeetings($token, $instanceId);
            } else {
                echo http_response_code(400);
            }
            break;
        case "getCourseInstanceStudentAttendance":
            if($_POST["token"] && $_POST["instanceId"] && $_POST["personId"]){
                $token = $_POST["token"];
                $instanceId = $_POST["instanceId"];
                $personId = $_POST["personId"];
                echo $populiService->getCourseInstanceStudentAttendance($token, $instanceId, $personId);
            } else {
                echo http_response_code(400);
            }
            break;
        case "getCourseInstanceMeetingAttendance":
            if($_POST["token"] && $_POST["instanceId"] && $_POST["meetingId"]) {
                $token = $_POST["token"];
                $instanceId = $_POST["instanceId"];
                $meetingId = $_POST["meetingId"];
                echo $populiService->getCourseInstanceMeetings($token, $instanceId, $meetingId);
            } else {
                echo http_response_code(400);
            }
            break;
        case "submitExcuse":
            if($_POST["personId"] && $_POST["instanceId"] && $_POST["meetingId"] && $_POST["reason"] && $_POST["firstName"] && $_POST["lastName"] && $_POST["className"] && $_POST["period"] && $_POST["date"] && $_POST["absenceType"]) {
                require_once "db.php";  
                $db = new DB(DB_NAME, DB_SERVER, DB_USER, DB_PW);
                $db->insertAbsence($_POST["personId"], $_POST["instanceId"],$_POST["meetingId"], $_POST["firstName"], $_POST["lastName"], $_POST["className"], $_POST["period"], $_POST["date"], $_POST["reason"], $_POST["absenceType"], $_POST["email"], $_POST["phone"]);
                echo http_response_code(200);
            } else {
                echo http_response_code(400);
            }
            break;
        case "getPersonSubmissions":
            if($_POST["personId"]) {
                require_once "db.php";  
                $db = new DB(DB_NAME, DB_SERVER, DB_USER, DB_PW);
                $absences = $db->getPersonSubmissions($_POST["personId"]);
                
                $submissions = array();
                $index = 0;
                while($row = mysql_fetch_array($absences)) {
                    $submission = array();
                    $submission["studentId"] =  $row["personId"];
                    $submission["meetingId"] =  $row["meetingId"];
                    $submission["instanceId"] =  $row["instanceId"];
                    $submission["reason"] =  $row["reason"];
                    $submission["timestamp"] =  $row["timestamp"];
                    $submission["timestamp"] =  $row["timestamp"];
                    $submission["status"] =  $row["status"];
                    $submissions[$index] = $submission;
                    $index++;
                }

                echo json_encode($submissions);
            } else {
                echo http_response_code(400);
            }
            break;
        case "error": 
            if($_POST["message"]) {
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From: PBC Attendance<attendance@portlandbiblecollege.org>' . "\r\n";
                mail("bas.clancy@gmail.com","PBC Attendance Error", $_POST["message"], $headers);
            }
    }
    return;
} else {
    echo json_encode(array('status' => 'ok'));
    return;
}