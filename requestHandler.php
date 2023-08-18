<?php

$module = new \UIOWA\QuickDeleter\QuickDeleter();

if(SUPER_USER == "1") {
    if(isset($_POST['type']) && $_POST['type'] == 'changeStatus') {
        call_user_func(array($module, 'changeProjectStatus'), $_POST);
    } 
    elseif (isset($_POST['type']) && $_POST["type"] == "custom") {
        call_user_func(array($module, 'getReportData'), $_POST);
    }
    elseif (isset($_POST['report-id'])) {
        call_user_func(array($module, 'getReportData'), $_POST);
    }
} else {
    echo "Something went wrong";
}



