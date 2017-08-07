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
            }
            break;
        case "getPerson":
            if($_POST["token"] && $_POST["personId"]){
                $token = $_POST["token"];
                $personId = $_POST["personId"];
                echo $populiService->getPerson($token, $personId);
            }
            break;
        case "getStudentCourses":
            if($_POST["personId"] && $_POST["token"] && $_POST["termId"]){
                $personId = $_POST["personId"];
                $token = $_POST["token"];
                $termId = $_POST["termId"];
                echo $populiService->getMyCourses($token, $termId, $personId);
            }
            break;
        case "getCourseInstanceMeetings":
            if($_POST["token"] && $_POST["instanceId"]){
                $token = $_POST["token"];
                $instanceId = $_POST["instanceId"];
                echo $populiService->getCourseInstanceMeetings($token, $instanceId);
            }
            break;
        case "getCourseInstanceMeetingAttendance":
            if($_POST["token"] && $_POST["instanceId"] && $_POST["meetingId"]) {
                $token = $_POST["token"];
                $instanceId = $_POST["instanceId"];
                $meetingId = $_POST["meetingId"];
                echo $populiService->getCourseInstanceMeetings($token, $instanceId, $meetingId);
            }
            break;
        case "submitExcuse":
            //$db->insertAbsence($personId, $firstName, $lastName, $className, $period, $date, $reason, $absenceType, $email, $phone, $mobile);
            if($_POST["token"] && $_POST["instanceId"] && $_POST["meetingId"] && $_POST["reason"] && $_POST["firstName"] && $_POST["lastName"] && $_POST["className"] && $_POST["period"] && $_POST["date"] && $_POST["absenceType"] && $_POST["email"] && $_POST["phone"] && $_POST["mobile"]) {

            }
            break;
        case "getAbsences":
            // require_once "db.php";  
            // $db = new DB(DB_NAME, DB_SERVER, DB_USER, DB_PW);
            // $absences = $db->getAbsences();
            // $submission = array();
            // while ($row = mysql_fetch_array($absence)) {
            //     $submission[REASON] = $row[REASON];
            //     $submission[CLASSNAME] = $row[CLASSNAME];
            //     $submission[PERIOD] = $row[PERIOD];
            //     $submission[PERSON_ID] = $row[PERSON_ID];
            //     $submission[DATE] = $row[DATE];
            //     $submission[ABSENCE_TYPE] = $row[ABSENCE_TYPE];
            //     $submission[ID] = $row[ID];
            // }

            // echo json_encode($submission);
            break;
    }

    return;
} else {
    echo json_encode(array('status' => 'ok'));
    return;
}





if(isset($_POST["logout"]) && $_POST["logout"] == true){
    //logout
    session_unset();
    session_destroy();
    exit;

} else if (isset($_SESSION[SES_PERSON_ID]) && !empty ($_SESSION[SES_PERSON_ID]) && isset($_POST["type"]) && isset($_POST["class"]) && isset($_POST["date"])) {
    require_once "php/db.php";

    //Enter form into database, but escape strings first
    $personId = $_SESSION[PERSON_ID];
    $firstName = htmlspecialchars($_SESSION[FIRST_NAME]);
    $lastName = htmlspecialchars($_SESSION[LAST_NAME]);
    $email = htmlspecialchars($_SESSION[EMAIL]);
    $phone = htmlspecialchars($_SESSION[PHONE]);

    $className = htmlspecialchars($_POST[CLASSNAME]);
    $period = htmlspecialchars($_POST[PERIOD]);
    $date = $_POST[DATE];

    //Make sure date is correct
    if (empty($date) || checkDatePattern($date) == false || $date == '0000-00-00') {
        header("Location: form.php?submit=fail&reason=Date incorrect");
        exit;
    }

    //Escape weird characters and prevent incorrect xml display for the overview
    $reason = htmlspecialchars($_POST[REASON]);
    $absenceType = $_POST[ABSENCE_TYPE];

    $db = new DB(DB_NAME, DB_SERVER, DB_USER, DB_PW);

    $resubmitId = $_POST[ID];
    if(!empty($resubmitId)){
        $db->resubmitAbsence($resubmitId, $className, $period, $date, $absenceType, $mobile);
    }else {
        $db->insertAbsence($personId, $firstName, $lastName, $className, $period, $date, $reason, $absenceType, $email, $phone, $mobile);
    }

    $db->close();

    exit;

} else if (isset($_SESSION[SES_PERSON_ID]) && !empty ($_SESSION[SES_PERSON_ID]) && isset($_POST["id"])) {
    require_once "php/db.php";
    //Get submission from DB and return it to the form to be automatically filled in
    $db = new DB(DB_NAME, DB_SERVER, DB_USER, DB_PW);
    $absence = $db->getAbsence($_POST["id"]);

    $submission = array();
    while ($row = mysql_fetch_array($absence)) {
        $submission[REASON] = $row[REASON];
        $submission[CLASSNAME] = $row[CLASSNAME];
        $submission[PERIOD] = $row[PERIOD];
        $submission[PERSON_ID] = $row[PERSON_ID];
        $submission[DATE] = $row[DATE];
        $submission[ABSENCE_TYPE] = $row[ABSENCE_TYPE];
        $submission[ID] = $row[ID];
    }

    print json_encode($submission);
    exit;

}
