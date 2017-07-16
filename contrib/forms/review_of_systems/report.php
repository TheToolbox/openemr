<?php
include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");

function review_of_systems_report( $pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = formFetch("form_review_of_systems", $id);
    $sql = "SELECT name from form_review_of_systems_checks where foreign_id = '" . add_escape_custom($id) . "'";
    $results = sqlQ($sql);
    $data2 = array();
    while ($row = sqlFetchArray($results)) {
        $data2[] = $row['name'];
    }
    $data = array_merge($data,$data2);
    if ($data) {
        print "<table><tr>";
        foreach($data as $key => $value) {
            if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || $key == "authorized" || $key == "activity" || $key == "date" || $value == "" || $value == "0000-00-00 00:00:00") {
                continue;
            }
            if ($value == "on") {
                $value = "yes";
            }
    
            $key=ucwords(str_replace("_"," ",$key));
            if (is_numeric($key)){
                $key = "check";
            }
            print "<td><span class=bold>$key: </span><span class=text>$value</span></td>";
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }
}
