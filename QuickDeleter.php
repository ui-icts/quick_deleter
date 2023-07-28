<?php

namespace UIOWA\QuickDeleter;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;



class QuickDeleter extends AbstractExternalModule {


 

    public function generateJavascriptObject() {
        return htmlspecialchars(json_encode([
            'urlLookup' => array(
                'redcapBase' => (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . SERVER_NAME . APP_PATH_WEBROOT,
                'post' => $this->getUrl("requestHandler.php")
            ),
            'reportId' => $_GET['report-id'],
            'redcap_csrf_token' => $this->getCSRFToken()
            // 'pids' => 
        ]), ENT_QUOTES);
     
    }



    public function generateNavLinks() {
        ?> <div style='display: flex; gap: 20px; align-items: center; justify-content: center;'>
        <a href="<?= $this->getUrl("index.php?report-id=1") ?>">My Projects</a>
        <a href="<?= $this->getUrl("index.php?report-id=2") ?>">All Projects</a>
        <input id="qdCustomPids" placeholder="Enter json of projects or pid csv" style="width: 200px;"></input>
        <!-- <button type="button" class="buttonFormatting">?</button> -->
        <!-- <a href="<?= $this->getUrl("index.php?report-id=3") ?>">Search Custom</a> -->
        <button type="button" id="qdSubmitCustom" style="visibility: hidden;" class="buttonFormatting bigButtonFormatting">Search Custom</button>
      </div> <?php
    }

    public function getQuery($reportId, $pids) {
        $query = "";
        // error_log($pids);

        $columns =  [['data' => "Check All", 'title' => "Check All" ],['data' => 'Project ID', 'title' => 'Project ID'], ['data' => 'Project Title', 'title' => 'Project Title'], ['data' => 'Date Deleted', 'title' => 'Date Deleted'], ['data' => 'Purpose', 'title' => 'Purpose'], ['data' => 'Statuses', 'title' => 'Statuses'], ['data' => 'Records', 'title' => 'Records'], ['data' => 'Users', 'title' => 'Users'], ['data' => 'Last Event', 'title' => 'Last Event'], ['data' => 'Days Since Last Event', 'title' => 'Days Since Last Event'], ['data' => 'Creation Time', 'title' => 'Creation Time'], ['data' => 'Completed Date', 'title' => 'Completed Date'], ['data' => 'New Final Delete Date', 'title' => 'New Final Delete Date']];

        if($reportId == 1) {
            $query = "SELECT a.project_id, app_title, a.date_deleted, a.purpose, a.status, record_count, username, last_logged_event, creation_time, completed_time,
            CAST(CASE a.status
                WHEN 0 THEN 'Development'
                WHEN 1 THEN 'Production'
                WHEN 2 THEN 'Inactive'
                WHEN 3 THEN 'Archived'
                ELSE a.status
                END AS CHAR(50)) AS 'Statuses',
            CAST(CASE a.purpose
                WHEN 0 THEN 'Practice / Just for fun'
                WHEN 4 THEN 'Operational Support'
                WHEN 2 THEN 'Research'
                WHEN 3 THEN 'Quality Improvement'
                WHEN 1 THEN 'Other'
                ELSE a.purpose
                END AS CHAR(50)) AS 'Purpose',
            CAST(a.project_id AS char) AS 'Project ID', 
            CAST(app_title AS char) AS 'Project Title', 
            CAST(record_count AS char) AS 'Records', 
            CAST(creation_time AS date) AS 'Creation Time', 
            CAST(completed_time AS date) AS 'Completed Date', 
            CAST(a.date_deleted AS date) AS 'Date Deleted', 
            CAST(last_logged_event AS date) AS 'Last Event', 
            DATEDIFF(now(), last_logged_event) AS 'Days Since Last Event',
            CAST(DATE_ADD(a.date_deleted, INTERVAL 30 DAY) AS date) AS 'New Final Delete Date',
            CAST(CASE WHEN a.date_deleted IS NULL THEN 0 ELSE 1 END AS CHAR(50)) AS 'Flagged',
            GROUP_CONCAT((b.username) SEPARATOR ', ') AS 'Users'
            FROM redcap_projects as a
            LEFT JOIN redcap_user_rights AS b
            ON a.project_id=b.project_id
            LEFT JOIN redcap_record_counts AS c
            ON a.project_id=c.project_id
            GROUP BY a.project_id
            HAVING (GROUP_CONCAT((b.username) SEPARATOR ', ') LIKE '%".USERID."%')
            ORDER BY a.project_id ASC";

            // $columns =  [['data' => "Check All", 'title' => "Check All" ],['data' => 'project_id', 'title' => 'project_id'], ['data' => 'app_title', 'title' => 'app_title'], ['data' => 'date_deleted', 'title' => 'date_deleted'], ['data' => 'purpose', 'title' => 'purpose'], ['data' => 'status', 'title' => 'status'], ['data' => 'record_count', 'title' => 'record_count'], ['data' => 'last_logged_event', 'title' => 'last_logged_event'], ['data' => 'creation_time', 'title' => 'creation_time']];
        } else if($reportId == 2) {
            $query = "SELECT a.project_id, app_title, a.date_deleted, a.purpose, a.status, record_count, username, last_logged_event, creation_time, completed_time,
            CAST(CASE a.status
                WHEN 0 THEN 'Development'
                WHEN 1 THEN 'Production'
                WHEN 2 THEN 'Inactive'
                WHEN 3 THEN 'Archived'
                ELSE a.status
                END AS CHAR(50)) AS 'Statuses',
            CAST(CASE a.purpose
                WHEN 0 THEN 'Practice / Just for fun'
                WHEN 4 THEN 'Operational Support'
                WHEN 2 THEN 'Research'
                WHEN 3 THEN 'Quality Improvement'
                WHEN 1 THEN 'Other'
                ELSE a.purpose
                END AS CHAR(50)) AS 'Purpose',
                CAST(a.project_id AS char) AS 'Project ID', 
                CAST(a.project_id AS char) AS 'Project ID', 
            CAST(app_title AS char) AS 'Project Title', 
            CAST(record_count AS char) AS 'Records', 
            CAST(creation_time AS date) AS 'Creation Time', 
            CAST(completed_time AS date) AS 'Completed Date', 
            CAST(a.date_deleted AS date) AS 'Date Deleted', 
            CAST(last_logged_event AS date) AS 'Last Event', 
       
            DATEDIFF(now(), last_logged_event) AS 'Days Since Last Event',
            CAST(DATE_ADD(a.date_deleted, INTERVAL 30 DAY) AS date) AS 'New Final Delete Date',
            CAST(CASE WHEN a.date_deleted IS NULL THEN 0 ELSE 1 END AS CHAR(50)) AS 'Flagged',
            GROUP_CONCAT((b.username) SEPARATOR ', ') AS 'Users'
            FROM redcap_projects as a
            LEFT JOIN redcap_user_rights AS b
            ON a.project_id=b.project_id
            LEFT JOIN redcap_record_counts AS c
            ON a.project_id=c.project_id
            GROUP BY a.project_id
            ORDER BY a.project_id ASC";

            
        } else if($reportId == 3) {

      
            // $pidsLength = count(explode(",", $pids));

            foreach($pids AS $i => $pid) {
                $pids[$i] = "?";
            }
            error_log("implode");
        
      $qMarks = implode(',', $pids);
      error_log($qMarks);

            $query = "SELECT a.project_id, app_title, a.date_deleted, a.purpose, a.status, record_count, username, last_logged_event, creation_time, completed_time,
            CAST(CASE a.status
                WHEN 0 THEN 'Development'
                WHEN 1 THEN 'Production'
                WHEN 2 THEN 'Inactive'
                WHEN 3 THEN 'Archived'
                ELSE a.status
                END AS CHAR(50)) AS 'Statuses',
            CAST(CASE a.purpose
                WHEN 0 THEN 'Practice / Just for fun'
                WHEN 4 THEN 'Operational Support'
                WHEN 2 THEN 'Research'
                WHEN 3 THEN 'Quality Improvement'
                WHEN 1 THEN 'Other'
                ELSE a.purpose
                END AS CHAR(50)) AS 'Purpose',
                CAST(a.project_id AS char) AS 'Project ID', 
            CAST(app_title AS char) AS 'Project Title', 
            CAST(record_count AS char) AS 'Records', 
            CAST(creation_time AS date) AS 'Creation Time', 
            CAST(completed_time AS date) AS 'Completed Date', 
            CAST(a.date_deleted AS date) AS 'Date Deleted', 
            CAST(last_logged_event AS date) AS 'Last Event', 
            DATEDIFF(now(), last_logged_event) AS 'Days Since Last Event',
            CAST(DATE_ADD(a.date_deleted, INTERVAL 30 DAY) AS date) AS 'New Final Delete Date',
            CAST(CASE WHEN a.date_deleted IS NULL THEN 0 ELSE 1 END AS CHAR(50)) AS 'Flagged',
            GROUP_CONCAT((b.username) SEPARATOR ', ') AS 'Users'
            FROM redcap_projects as a
            LEFT JOIN redcap_user_rights AS b
            ON a.project_id=b.project_id
            LEFT JOIN redcap_record_counts AS c
            ON a.project_id=c.project_id
            WHERE a.project_id IN (". $qMarks .")  
            GROUP BY a.project_id
            ORDER BY a.project_id ASC";


        }



        return ['query' => $query, 'columns' => $columns];

        // return [
        //     1 => [
        //         'query' => "SELECT a.project_id, app_title, a.date_deleted, a.purpose, a.status, record_count, last_logged_event, creation_time, username,
        //             CAST(CASE a.status
        //                 WHEN 0 THEN 'Development'
        //                 WHEN 1 THEN 'Production'
        //                 WHEN 2 THEN 'Inactive'
        //                 WHEN 3 THEN 'Archived'
        //                 ELSE a.status
        //                 END AS CHAR(50)) AS 'Statuses',
        //             CAST(CASE a.purpose
        //                 WHEN 0 THEN 'Practice / Just for fun'
        //                 WHEN 4 THEN 'Operational Support'
        //                 WHEN 2 THEN 'Research'
        //                 WHEN 3 THEN 'Quality Improvement'
        //                 WHEN 1 THEN 'Other'
        //                 ELSE a.purpose
        //                 END AS CHAR(50)) AS 'Purpose',
        //             CAST(creation_time AS date) AS 'New Creation Time', 
        //             CAST(a.date_deleted AS date) AS 'New Date Deleted', 
        //             CAST(last_logged_event AS date) AS 'New Last Event', 
        //             DATEDIFF(now(), last_logged_event) AS 'Days Since Last Event',
        //             CAST(DATE_ADD(a.date_deleted, INTERVAL 30 DAY) AS date) AS 'New Final Delete Date',
        //             CAST(CASE WHEN a.date_deleted IS NULL THEN 0 ELSE 1 END AS CHAR(50)) AS 'Flagged',
        //             GROUP_CONCAT((b.username) SEPARATOR ', ') AS 'Users'
        //             FROM redcap_projects as a
        //             LEFT JOIN redcap_user_rights AS b
        //             ON a.project_id=b.project_id
        //             LEFT JOIN redcap_record_counts AS c
        //             ON a.project_id=c.project_id
        //             GROUP BY a.project_id
        //             HAVING (GROUP_CONCAT((b.username) SEPARATOR ', ') LIKE '%".USERID."%')
        //             ORDER BY a.project_id ASC",
        //         'columns' => [['data' => "", 'title' => "" ],['data' => 'project_id', 'title' => 'project_id'], ['data' => 'app_title', 'title' => 'app_title'], ['data' => 'date_deleted', 'title' => 'date_deleted'], ['data' => 'purpose', 'title' => 'purpose'], ['data' => 'status', 'title' => 'status'], ['data' => 'record_count', 'title' => 'record_count'], ['data' => 'last_logged_event', 'title' => 'last_logged_event'], ['data' => 'creation_time', 'title' => 'creation_time'], ['data' => 'username', 'title' => 'username']]
        //     ],
        //     2 => [
        //         'query' => "SELECT a.project_id, app_title, a.date_deleted, a.purpose, a.status, record_count, last_logged_event, creation_time, username,
        //             CAST(CASE a.status
        //                 WHEN 0 THEN 'Development'
        //                 WHEN 1 THEN 'Production'
        //                 WHEN 2 THEN 'Inactive'
        //                 WHEN 3 THEN 'Archived'
        //                 ELSE a.status
        //                 END AS CHAR(50)) AS 'Statuses',
        //             CAST(CASE a.purpose
        //                 WHEN 0 THEN 'Practice / Just for fun'
        //                 WHEN 4 THEN 'Operational Support'
        //                 WHEN 2 THEN 'Research'
        //                 WHEN 3 THEN 'Quality Improvement'
        //                 WHEN 1 THEN 'Other'
        //                 ELSE a.purpose
        //                 END AS CHAR(50)) AS 'Purpose',
        //             CAST(creation_time AS date) AS 'New Creation Time', 
        //             CAST(a.date_deleted AS date) AS 'New Date Deleted', 
        //             CAST(last_logged_event AS date) AS 'New Last Event', 
        //             DATEDIFF(now(), last_logged_event) AS 'Days Since Last Event',
        //             CAST(DATE_ADD(a.date_deleted, INTERVAL 30 DAY) AS date) AS 'New Final Delete Date',
        //             CAST(CASE WHEN a.date_deleted IS NULL THEN 0 ELSE 1 END AS CHAR(50)) AS 'Flagged',
        //             GROUP_CONCAT((b.username) SEPARATOR ', ') AS 'Users'
        //             FROM redcap_projects as a
        //             LEFT JOIN redcap_user_rights AS b
        //             ON a.project_id=b.project_id
        //             LEFT JOIN redcap_record_counts AS c
        //             ON a.project_id=c.project_id
        //             GROUP BY a.project_id
        //             ORDER BY a.project_id ASC",
        //         'columns' => [['data' => "", 'title' => "" ],['data' => 'project_id', 'title' => 'project_id'], ['data' => 'app_title', 'title' => 'app_title'], ['data' => 'date_deleted', 'title' => 'date_deleted'], ['data' => 'purpose', 'title' => 'purpose'], ['data' => 'status', 'title' => 'status'], ['data' => 'record_count', 'title' => 'record_count'], ['data' => 'last_logged_event', 'title' => 'last_logged_event'], ['data' => 'creation_time', 'title' => 'creation_time'], ['data' => 'username', 'title' => 'username']]
        //     ],
        //     3 => [
        //         'query' => "SELECT a.project_id, app_title, a.date_deleted, a.purpose, a.status, record_count, last_logged_event, creation_time, username,
        //             CAST(CASE a.status
        //                 WHEN 0 THEN 'Development'
        //                 WHEN 1 THEN 'Production'
        //                 WHEN 2 THEN 'Inactive'
        //                 WHEN 3 THEN 'Archived'
        //                 ELSE a.status
        //                 END AS CHAR(50)) AS 'Statuses',
        //             CAST(CASE a.purpose
        //                 WHEN 0 THEN 'Practice / Just for fun'
        //                 WHEN 4 THEN 'Operational Support'
        //                 WHEN 2 THEN 'Research'
        //                 WHEN 3 THEN 'Quality Improvement'
        //                 WHEN 1 THEN 'Other'
        //                 ELSE a.purpose
        //                 END AS CHAR(50)) AS 'Purpose',
        //             CAST(creation_time AS date) AS 'New Creation Time', 
        //             CAST(a.date_deleted AS date) AS 'New Date Deleted', 
        //             CAST(last_logged_event AS date) AS 'New Last Event', 
        //             DATEDIFF(now(), last_logged_event) AS 'Days Since Last Event',
        //             CAST(DATE_ADD(a.date_deleted, INTERVAL 30 DAY) AS date) AS 'New Final Delete Date',
        //             CAST(CASE WHEN a.date_deleted IS NULL THEN 0 ELSE 1 END AS CHAR(50)) AS 'Flagged',
        //             GROUP_CONCAT((b.username) SEPARATOR ', ') AS 'Users'
        //             FROM redcap_projects as a
        //             LEFT JOIN redcap_user_rights AS b
        //             ON a.project_id=b.project_id
        //             LEFT JOIN redcap_record_counts AS c
        //             ON a.project_id=c.project_id
        //             WHERE a.project_id IN (" . $pids . ")  
        //             GROUP BY a.project_id
        //             ORDER BY a.project_id ASC",
        //         'columns' => [['data' => "", 'title' => "" ],['data' => 'project_id', 'title' => 'project_id'], ['data' => 'app_title', 'title' => 'app_title'], ['data' => 'date_deleted', 'title' => 'date_deleted'], ['data' => 'purpose', 'title' => 'purpose'], ['data' => 'status', 'title' => 'status'], ['data' => 'record_count', 'title' => 'record_count'], ['data' => 'last_logged_event', 'title' => 'last_logged_event'], ['data' => 'creation_time', 'title' => 'creation_time'], ['data' => 'username', 'title' => 'username']]
        //     ]
        // ];
    }
   

    public function getReportData($params) {
      
        $reportId = $params['report-id'];


        
        $validReportIds = [1,2,3];
        
        if(SUPER_USER == "1" && in_array($reportId, $validReportIds)) {
            $customPids = "";
         
            if($reportId == 3) {
             
                // $allInts = null;
                // foreach($pids as $pid) {
                //     if(filter_var($pid, FILTER_VALIDATE_INT) === false) {
                //         $allInts = false;
                //         break;
                //     }
                // }

                $customPids = $this->parsePids($params["pids"]);
                $arrayizedPids = explode(",", $customPids);

                error_log(json_encode($arrayizedPids));
            
                $getQueryInfo = $this->getQuery($reportId, $arrayizedPids);
                $sql = $getQueryInfo['query'];
    
                
                $result = $this->query($sql, $arrayizedPids);
            } else {
                $getQueryInfo = $this->getQuery($reportId, $customPids);
                $sql = $getQueryInfo['query'];
    
                
                $result = $this->query($sql, []);
            }
  

            $returnedData = [];
            while ($row = db_fetch_assoc($result)) {
                $returnedData[] = $row;
            }

            $finalArray = ['data' => $returnedData, 'columns' => $getQueryInfo['columns'], 'customPids' => $customPids];
            echo htmlentities(strip_tags(json_encode($finalArray)), ENT_QUOTES, 'UTF-8');
            // echo json_encode($finalArray,true);
        } else {
            echo "something went wrong";
        }

    }

    public function changeProjectStatus($params) {
        error_log("change proj status");

        error_log($params['pids']);
        // $pids = json_decode($params['pids']);
        
        // $parsedPids = $this->parsePids($params['pids']);

        $parsedPids = [];
        foreach(json_decode($params['pids']) AS $pid) {
            error_log($pid);
            if(filter_var((int)$pid, FILTER_VALIDATE_INT) !== false) {
                array_push($parsedPids, $pid);
          
            } else {
                error_log("false: " . $pid);
            }
        }

        $qMarks = str_repeat("?,",count($parsedPids)-1) . "?";
        error_log($qMarks);

        $pidArray = [];
        $mergedPids = array_merge($pidArray, $parsedPids);
        $returnData = array();

        $sqlGetCurrentProjectStatuses = "SELECT project_id, date_deleted FROM redcap_projects WHERE project_id IN (" . $qMarks . ")";
        $result = $this->query($sqlGetCurrentProjectStatuses, $mergedPids);
        while ($row = db_fetch_assoc($result)) {
            $returnData[] = $row;
        }
        error_log(json_encode($returnData));
        // $preFinalValues = [];

        // foreach($returnData AS $project) {
        //     $preFinalValues[$project['project_id']] = $project['date_deleted'];
        // }

        // error_log(json_encode($preFinalValues));



        $timeParams = [NOW, NULL];
        $unpackedQueryParams = array_merge($timeParams, $parsedPids);


        error_log(json_encode($parsedPids));


       

        $sql = "UPDATE redcap_projects SET date_deleted = IF(date_deleted IS NULL, ?, ?) WHERE project_id IN (" . $qMarks . ")";
        $this->query($sql, $unpackedQueryParams);

        $postReturnData = [];
        $postResult = $this->query($sqlGetCurrentProjectStatuses, $mergedPids);
        while ($row = db_fetch_assoc($postResult)) {
            $postReturnData[] = $row;
        }
error_log(json_encode($postReturnData));
        // $postFinalValues = [];

        // foreach($postReturnData AS $project) {
        //     $postFinalValues[$project['project_id']] = $project['date_deleted'];
        // }

        // error_log(json_encode($postFinalValues));

        // foreach($postFinalValues AS $project) {
        //     error_log(json_encode($project));
        // }

        $confirmedRestored = [];
        $confirmedDeleted = [];
        $confirmedFailed = [];

        foreach($returnData AS $project) {
            foreach($postReturnData AS $project2) {
                if($project['project_id'] == $project2['project_id']) {
                    if($project['date_deleted'] != $project2['date_deleted']) {
                        if($project2['date_deleted'] == null) {
                            array_push($confirmedRestored, $project2['project_id']);
                            error_log($project2['project_id'] . " restored");
                            \REDCap::logEvent("Project restored via Quick Deleter", NULL, NULL, NULL, NULL, $project2['project_id']);

                        } else {
                            array_push($confirmedDeleted, $project2['project_id']);
                            error_log($project2['project_id'] . " deleted");
                            \REDCap::logEvent("Project deleted via Quick Deleter", NULL, NULL, NULL, NULL, $project2['project_id']);
                        }
                    } else {
                        array_push($confirmedFailed, $project2['project_id']);
                        error_log($project2['project_id'] . " failed to update status");
                    }
                }
            }
        }

        error_log(json_encode($confirmedRestored));
        error_log(json_encode($confirmedDeleted));
        error_log(json_encode($confirmedFailed));

        // foreach($pids AS $pid) {
        //     error_log($pid);

        //     $sqlExisting = "SELECT project_id, date_deleted FROM redcap_projects WHERE project_id = ?";

        //     $resultExisting = $this->query($sqlExisting, [$pid]);
        //     $row = db_fetch_assoc($resultExisting);

        //     $qMarks = str_repeat("?",13);


        //     $sql = "
        //         UPDATE redcap_projects
        //         SET date_deleted = IF(date_deleted IS NULL, ?, ?)
        //         WHERE project_id IN (" . $qMarks . ")
        //         ";

        //         $this->query($sql, [$pid]);

        //     if($row["date_deleted"] == "") {
        //         error_log("Deleting...");
                          
        //         $sql = "UPDATE redcap_projects SET date_deleted = '".NOW."'
        //         WHERE project_id = ? AND date_deleted is null";
        //         $this->query($sql, [$pid]);
        //         // \ToDoList::updateTodoStatus($pid, 'delete project','completed');
        //         // \Logging::logEvent($sql,"redcap_projects","MANAGE",$pid,"project_id = ".$pid,"Delete project");
        //     } else {
        //         error_log("already deleted.  Restoring...");
        //         $sql = "UPDATE redcap_projects SET date_deleted = null WHERE project_id = ?";
        //         if ($this->query($sql, [$pid])) {
        //             // Set response
        //             // $response = "1";
        //             // Logging
        //             // Logging::logEvent($sql,"redcap_projects","MANAGE",PROJECT_ID,"project_id = ".PROJECT_ID,"Restore/undelete project");
        //         }
        //     }

        //     error_log(json_encode($row));

        // }

       

        echo "change status received";
    }

 
    public function parsePids($pids) {

        $parsedPids = [];

        
        $explodedPids = explode(",", $pids);

        foreach($explodedPids AS $pid) {
            error_log($pid);
            if(filter_var((int)$pid, FILTER_VALIDATE_INT) !== false) {
                array_push($parsedPids, $pid);
          
            } else {
                error_log("false: " . $pid);
            }
        }

        error_log(json_encode($parsedPids));


        return implode(",", $parsedPids);
    }
 

}




?>

