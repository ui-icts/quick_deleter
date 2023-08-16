<?php

namespace UIOWA\QuickDeleter;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

class QuickDeleter extends AbstractExternalModule {



    public function generateJavascriptObject() {
        if(SUPER_USER == "1") {
            return htmlspecialchars(json_encode([
                'urlLookup' => array(
                    'redcapBase' => (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . SERVER_NAME . APP_PATH_WEBROOT,
                    'post' => $this->getUrl("requestHandler.php")
                ),
                'reportId' => $_GET['report-id'],
                'redcap_csrf_token' => $this->getCSRFToken()
            ]), ENT_QUOTES);
        } else {
            echo "Something went wrong";
        }
    }

    public function generateNavLinks() {
        if(SUPER_USER == "1") {
            ?> <div style='display: flex; gap: 20px; align-items: center; justify-content: center;'>
            <a href="<?= $this->getUrl("index.php?report-id=1") ?>">My Projects</a>
            <a href="<?= $this->getUrl("index.php?report-id=2") ?>">All Projects</a>
            <input id="qdCustomPids" placeholder="Enter json of projects or pid csv" style="width: 200px;"></input>
            <button type="button" id="qdSubmitCustom" style="visibility: hidden;" class="buttonFormatting bigButtonFormatting">Search Custom</button>
        </div> <?php
        } else {
            echo "Something went wrong";
        }
    }

    public function getQuery($reportId, $pids) {
        if(SUPER_USER == "1") {
            $query = "";
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

                foreach($pids AS $i => $pid) {
                    $pids[$i] = "?";
                }
                
                $qMarks = implode(',', $pids);
        
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
        } else {
            echo "Something went wrong";
        }
    }
   
    public function getReportData($params) {
      
        $reportId = $params['report-id'];
        $validReportIds = [1,2,3];
        
        if(SUPER_USER == "1" && in_array($reportId, $validReportIds)) {
            $customPids = "";
         
            if($reportId == 3) {
             
                $customPids = $this->parsePids($params["pids"]);
                $arrayizedPids = explode(",", $customPids);
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

        } else {
            echo "something went wrong";
        }

    }

    public function changeProjectStatus($params) {

        if(SUPER_USER == "1") {
            $parsedPids = [];
            foreach(json_decode($params['pids']) AS $pid) {
                if(filter_var((int)$pid, FILTER_VALIDATE_INT) !== false) {
                    array_push($parsedPids, $pid);
                }
            }
    
            $qMarks = str_repeat("?,",count($parsedPids)-1) . "?";
            
            $pidArray = [];
            $mergedPids = array_merge($pidArray, $parsedPids);
            $returnData = array();
    
            $sqlGetCurrentProjectStatuses = "SELECT project_id, date_deleted FROM redcap_projects WHERE project_id IN (" . $qMarks . ")";
            $result = $this->query($sqlGetCurrentProjectStatuses, $mergedPids);
            while ($row = db_fetch_assoc($result)) {
                $returnData[] = $row;
            }
        
            $timeParams = [NOW, NULL];
            $unpackedQueryParams = array_merge($timeParams, $parsedPids);
    
            $sql = "UPDATE redcap_projects SET date_deleted = IF(date_deleted IS NULL, ?, ?) WHERE project_id IN (" . $qMarks . ")";
            $this->query($sql, $unpackedQueryParams);
    
            $postReturnData = [];
            $postResult = $this->query($sqlGetCurrentProjectStatuses, $mergedPids);
            while ($row = db_fetch_assoc($postResult)) {
                $postReturnData[] = $row;
            }
    
            $confirmedRestored = [];
            $confirmedDeleted = [];
            $confirmedFailed = [];
    
            foreach($returnData AS $project) {
                foreach($postReturnData AS $project2) {
                    if($project['project_id'] == $project2['project_id']) {
                        if($project['date_deleted'] != $project2['date_deleted']) {
                            if($project2['date_deleted'] == null) {
                                array_push($confirmedRestored, $project2['project_id']);
                                \REDCap::logEvent("Project restored via Quick Deleter", NULL, NULL, NULL, NULL, $project2['project_id']);
                            } else {
                                array_push($confirmedDeleted, $project2['project_id']);
    
                                \REDCap::logEvent("Project deleted via Quick Deleter", NULL, NULL, NULL, NULL, $project2['project_id']);
                            }
                        } else {
                            array_push($confirmedFailed, $project2['project_id']);
                        }
                    }
                }
            }
    
            echo "change status received";
        } else {
            echo "Something went wrong.";
        }
  

    }

    public function parsePids($pids) {

        if(SUPER_USER == "1") {

            $parsedPids = [];
            $explodedPids = explode(",", $pids);

            foreach($explodedPids AS $pid) {
                
                if(filter_var((int)$pid, FILTER_VALIDATE_INT) !== false) {
                    array_push($parsedPids, $pid);
                }
            }

            return implode(",", $parsedPids);
        } else {
            echo "Something went wrong";
        }
    }

}

?>