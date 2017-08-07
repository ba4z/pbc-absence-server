<?php

    define("AUTHENTICATION_ERROR", "AUTHENTICATION_ERROR");
    define("LOCKED_OUT", "LOCKED_OUT");
    define("LOG_IN", "LOG_IN");

//    define("DB_USER", "citbib_absence");
//    define("DB_PW", "+%'d!wN&+0f1b[[");
//    define("DB_SERVER", "portlandbiblecollege.org");
//    define("DB_NAME", "citbib_absence");

    //Tmp database
    define("DB_USER", "portland_att");
    define("DB_PW", "f4r7539a6U365tt");
    define("DB_SERVER", "174.121.166.165");
    define("DB_NAME", "portland_attendance");

    //DB_COLUMNS
    define("ID", "id");
    define("PERSON_ID", "personId");
    define("FIRST_NAME", "firstname");
    define("LAST_NAME", "lastname");
    define("CLASSNAME", "class");
    define("DATE", "date");
    define("REASON", "reason");
    define("ABSENCE_TYPE", "type");
    define("PERIOD", "period");
    define("STATUS", "status");
    define("TIMESTAMP", "timestamp");
    define("EMAIL", "email");
    define("PHONE", "phone");
    define("RESUBMITTED", "resubmitted");

    //Session vars
    define("SES_PERSON_ID", "personId");
    define("SES_COURSES", "courses");

    //Statuses
    define("PENDING", "");
    define("APPROVED","APPROVED");
    define("DENIED","DENIED");
    define("RESUBMIT","RESUBMIT");

?>
