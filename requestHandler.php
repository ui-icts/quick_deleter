<?php

$module = new \UIOWA\QuickDeleter\QuickDeleter();

if(isset($_POST['type']) && $_POST['type'] == 'changeStatus') {
    call_user_func(array($module, 'changeProjectStatus'), $_POST);
} 
elseif (isset($_POST['type']) && $_POST["type"] == "custom") {
    call_user_func(array($module, 'getReportData'), $_POST);
}
elseif (isset($_POST['report-id'])) {
    call_user_func(array($module, 'getReportData'), $_POST);
}

