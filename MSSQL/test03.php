


<?php

session_start();

try { 
$dbt = new PDO(  );
}
catch (Exception $e) {
	echo $e->getMessage();
}

$altSQL = "
update llas_classes
set inactive = case when inactive = 0 then 1
when inactive = 1 then 0 end
where class_id = ? ";

$delSQL = "
delete from llas_classes
where class_id = ? ";

$listSQL = "
select class_id, class_name, class_code, class_description
, inactive, created_datetime, lastmodified_datetime
from llas_classes ";

$edtSQL = "
select class_id, class_name, class_code, class_description
, inactive, created_datetime, lastmodified_datetime
from llas_classes 
where class_id = ? ";

$updateSQL = "update llas_classes
set class_name = ? , class_code = ? , class_description = ? 
, lastmodified_datetime = now() 
where class_id = ? ";

$insertSQL = "insert into llas_classes (class_name, class_code, class_description, inactive, created_datetime, lastmodified_datetime)
values (?, ?, ?, 0, now(), now() )";

//*************************************************************************************** */
if (isset($_GET["del"])) {  
  $stmt = $dbt->prepare($delSQL);
  $stmt->execute([$_GET["del"]]);
  header("Location: ?");
  die;}
  
  //*************************************************************************************** */
if (isset($_GET["alt"])) {  
$stmt = $dbt->prepare($altSQL);
$stmt->execute([$_GET["alt"]]);
header("Location: ?");
die;}

//************************************************************************************** */
if (isset($_POST['edit_submit'])) {
$stmt = $dbt->prepare($edtSQL);
$stmt->execute([$_POST['class_id']]);
$row = $stmt->fetch(PDO::FETCH_BOTH);

if ($row['1'] != $_POST['class_name']
|| $row['2'] != $_POST['class_code']
|| $row['3'] != $_POST['class_description']) {

$stmt = $dbt->prepare($updateSQL);
$stmt->execute([$_POST['class_name'],$_POST['class_code'],$_POST['class_description'],$_POST['class_id']]);
$updated = $stmt->rowcount();
if ($updated > 0) {
  $_SESSION['msg'] = "{$updated} row of record of classes was updated.";
  header("Location: ?list=1");
  die;
}

}

if (!isset($updated)
|| empty($updated)) {
  $_SESSION['msg'] = "No record was changed.";
  header("Location: ?list=1");
  die;
}

}


//************************************************************************************** */
if (isset($_GET["edt"]) && !empty($_GET["edt"])) {
$stmt = $dbt->prepare($edtSQL);
$stmt->execute([$_GET["edt"]]);
$row = $stmt->fetch(PDO::FETCH_BOTH);

echo '<form action="?" method="post">';
echo '<input type = "hidden" name="class_id" value="'. $row['0'] .'" />';
echo '<table>';
echo '<tr><th>Field</th><th>Value</th></tr>';
echo '<tr><td>Class Status</td><td>' . ($row['4']==0 ? 'active' : 'inactive') . '<a href = "?alt=' . $row['0'] . '">Change</a></td></tr>';
echo '<tr><td>Class Name</td><td><input name="class_name" value="' . $row['1'] . '" /></td></tr>';
echo '<tr><td>Class Code</td><td><input name="class_code" value="' . $row['2'] . '" /></td></tr>';
echo '<tr><td>Class Description</td><td><textarea name="class_description" rows="6" cols="40">'. $row['3'] .'</textarea></td></tr>';
echo '<tr><td></td><td><input type="submit" name="edit_submit" value="Submit" /></td></tr>';
echo '</table>';
echo '</form>';

echo '<hr>';

echo '<a class="button-minimal" href="?list=1">Save no changes and <br /> Go back to classes review</a>';
}

//************************************************************************************** */
if ( (isset($_GET["new"]) && !empty($_GET["new"]) && $_GET['new'] == 1) ) {

  if ( (isset($_SESSION['msg']) && !empty($_SESSION['msg'])) )
{ echo $_SESSION['msg'].'<br />'; $_SESSION['msg'] =''; }
  
  echo '<form action="?" method="post">';
  ///echo '<input type = "hidden" name="class_id" value="'. $row['0'] .'" />';
  echo '<table>';
  echo '<tr><th>Field</th><th>Value</th></tr>';
  //echo '<tr><td>Class Status</td><td>' . ($row['4']==0 ? 'active' : 'inactive') . '<a href = "?alt=' . $row['0'] . '">Change</a></td></tr>';
  echo '<tr><td>Class Name</td><td><input name="class_name" /></td></tr>';
  echo '<tr><td>Class Code</td><td><input name="class_code" /></td></tr>';
  echo '<tr><td>Class Description</td><td><textarea name="class_description" rows="6" cols="40"></textarea></td></tr>';
  echo '<tr><td></td><td><input type="submit" name="addnew_submit" value="Submit" /></td></tr>';
  echo '</table>';
  echo '</form>';
  
echo '<hr>';
echo '<a class="button-minimal" href="?list=1">Save no changes and <br /> Go back to classes review</a>';
}

//**************************************************************************************** */
if (isset($_POST['addnew_submit'])) {

  if ( isset($_POST['class_name']) && !empty($_POST['class_name'])
  && isset($_POST['class_code']) && !empty($_POST['class_code']) 
  && isset($_POST['class_description']) && !empty($_POST['class_description'])) {
$stmt = $dbt->prepare($insertSQL);
$stmt->execute([$_POST['class_name'],$_POST['class_code'],$_POST['class_description']]);
$inserted = $stmt->rowCount();
if ($inserted > 0) {
  $_SESSION['msg'] = "1 row of new class was inserted. ";
header("Location: ?list=1");
die;
} else {
  $_SESSION['msg'] = "No row was inserted. Insert new class failed. ";
  header("Location: ?new=1");
  die;
}
  } else {
    $_SESSION['msg'] = "No row was inserted. Please fill all the fields for submission. ";
    header("Location: ?new=1");
    die;
  }

}


//************************************************************************************** */
if ( (empty($_GET["alt"]) && empty($_GET["edt"]) && empty($_GET["new"]) )
|| (isset($_GET['list']) && !empty($_GET['list']) && $_GET['list'] == 1) ) {

$stmt = $dbt->query($listSQL);
$results = $stmt->fetchall(PDO::FETCH_BOTH);

if (isset($_SESSION['msg']) && !empty($_SESSION['msg'])) 
{ echo $_SESSION['msg'].'<br />'; $_SESSION['msg'] =''; }

echo '<table 
 border="2"
 align="center"
 cellpadding="5"
 cellspacing="3"
 style="font-family:arial,helvetica,sans-serif;"><tr>
                <th>Class Name</th>
                <th>Class Code</th>
                <th>Class Description</th>
                <th>Status</th></tr>';

foreach ($results as $row) {
  echo '<tr>';
  echo '<td>'.htmlentities($row['1']);
  echo '<br /><a href = "?edt='.$row['0'].'">Edit</a></td>';
  echo '<td>'.htmlentities($row['2']).'</td>';
  echo '<td>'.(strlen(htmlentities($row['3']))>25?substr(htmlentities($row['3']),0,25)."...":htmlentities($row['3'])).'</td>';
  echo '<td>'.($row['4']==0 ? 'active' : 'inactive');
  echo '<br /><a href = "?alt='.htmlentities($row['0']).'">Change</a></td>';
  echo '</tr>';
}  
echo '</table>';

echo '<hr>';
echo '<a class="button-minimal" href="?new=1">Add a new class</a>';
}

?>