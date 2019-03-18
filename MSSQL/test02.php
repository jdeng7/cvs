<?php

session_start();

try { 
$dbt = new PDO(  );
}
catch (Exception $e) {
	echo $e->getMessage();
}

$change_by_id = "
update llas_student_class_notes
set inactive = case when inactive = 0 then 1
when inactive = 1 then 0 end
where sc_notes_id = ? ";

$delete_by_id = "
delete from llas_student_class_notes
where sc_notes_id = ? ";

$find_all = "
select n.sc_notes_id, n.class_id, c.class_name, n.class_description
, n.student_id, concat(m1.user_name,' (',m1.first_name,' ',m1.last_name,')') as Student
, n.teacher_id, concat(m2.user_name,' (',m2.first_name,' ',m2.last_name,')') as Teacher
, n.sc_notes, n.work_name, n.attachment_name, n.class_date
, n.inactive, n.created_datetime, n.lastmodified_datetime
from llas_student_class_notes n
join llas_classes c on c.class_id = n.class_id
join llas_swpm_members_tbl m1 on m1.member_id = n.student_id
join llas_swpm_members_tbl m2 on m2.member_id = n.teacher_id";

$find_by_id = "
select sc_notes_id, class_id, student_id, teacher_id, class_description
, sc_notes, work_name, attachment_name, class_date
, inactive, created_datetime, lastmodified_datetime
from llas_student_class_notes
where sc_notes_id = ? ";

$update_by_id = "update llas_student_class_notes
set class_id = ? , student_id = ? , teacher_id = ? 
, class_description = ? , sc_notes = ? 
, class_date = ? , lastmodified_datetime = now()
where sc_notes_id = ? ";

$find_all_classes = "select class_id, class_name from llas_classes where inactive = 0 ";
$find_class_by_id = "select class_id, class_name from llas_classes where class_id = ? ";
$find_all_students = "select m1.member_id as student_id, concat(m1.user_name,' (',m1.first_name,' ',m1.last_name,')') as Student
from llas_swpm_members_tbl m1 where m1.membership_level = 8 and account_state = 'active' ";
$find_all_teachers = "select m1.member_id as teacher_id, concat(m1.user_name,' (',m1.first_name,' ',m1.last_name,')') as Teacher
from llas_swpm_members_tbl m1 where m1.membership_level IN (10,11) and account_state = 'active' ";
$find_member_by_id = "select m1.member_id, concat(m1.user_name,' (',m1.first_name,' ',m1.last_name,')') as member_name
from llas_swpm_members_tbl m1 where m1.member_id = ? ";

//*************************************************************************************** */
if (isset($_GET["del"])) {  
    $stmt = $dbt->prepare($delete_by_id);
    $stmt->execute([$_GET["del"]]);
    header("Location: ?");
    die;}
    
//*************************************************************************************** */
if (isset($_GET["alt"])) {  
  $stmt = $dbt->prepare($change_by_id);
  $stmt->execute([$_GET["alt"]]);
  header("Location: ?");
  die;}

//**************************************************************************************** */
if (isset($_GET["edt"])) {
  $stmt = $dbt->prepare($find_by_id);
  $stmt->execute([$_GET["edt"]]);
  $sc_notes = $stmt->fetch(PDO::FETCH_BOTH);

  $stmt = $dbt->prepare($find_class_by_id);
  $stmt->execute([$sc_notes['class_id']]);
  $this_class = $stmt->fetch(PDO::FETCH_BOTH);

  $stmt = $dbt->prepare($find_all_classes);
  $stmt->execute();
  $all_classes = $stmt->fetchall(PDO::FETCH_BOTH);

  $stmt = $dbt->prepare($find_member_by_id);
  $stmt->execute([$sc_notes['student_id']]);
  $this_student = $stmt->fetch(PDO::FETCH_BOTH);

  $stmt = $dbt->prepare($find_all_students);
  $stmt->execute();
  $all_students = $stmt->fetchall(PDO::FETCH_BOTH);

  $stmt = $dbt->prepare($find_member_by_id);
  $stmt->execute([$sc_notes['teacher_id']]);
  $this_teacher = $stmt->fetch(PDO::FETCH_BOTH);

  $stmt = $dbt->prepare($find_all_teachers);
  $stmt->execute();
  $all_teachers = $stmt->fetchall(PDO::FETCH_BOTH);

  echo '<form action="?" method="post">';
  echo '<input type = "hidden" name="sc_notes_id" value="'. $sc_notes['sc_notes_id'] .'" />';
  echo '<table>';
  echo '<tr><th>Field</th><th>Value</th></tr>';
  echo '<tr><td>Class Notes Status</td><td>' . ($sc_notes['inactive']==0 ? 'active' : 'inactive') . '<a href = "?alt=' . $sc_notes['0'] . '">Change</a></td></tr>';
  echo '<tr><td>Class Name</td><td><select name="class_id">';
                          foreach ($all_classes as $c) {
          echo '<option value ="'.$c['class_id'].'"'.($this_class['class_id'] == $c['class_id'] ? ' selected' : '').'>'.$c['class_name'].'</option>';
                                    } echo '</select></td></tr>';
  echo '<tr><td>Class Date</td><td><input name="class_date" value="'.$sc_notes['class_date'].'" /></td></tr>';
  echo '<tr><td>Student Name</td><td><select name="student_id">';
                          foreach ($all_students as $s) {
          echo '<option value ="'.$s['student_id'].'"'.($this_student['member_id'] == $s['student_id'] ? ' selected' : '').'>'.$s['Student'].'</option>';
                                    } echo '</select></td></tr>';
  echo '<tr><td>Teacher Name</td><td><select name="teacher_id">';
                          foreach ($all_teachers as $t) {
          echo '<option value ="'.$t['teacher_id'].'"'.($this_teacher['member_id'] == $t['teacher_id'] ? ' selected' : '').'>'.$t['Teacher'].'</option>';
                                    } echo '</select></td></tr>';
  echo '<tr><td>Class Description</td><td><textarea name="class_description" rows="6" cols="40">'.$sc_notes['class_description'].'</textarea></td></tr>';
  echo '<tr><td>Student Class Notes</td><td><textarea name="sc_notes" rows="6" cols="40">'.$sc_notes['sc_notes'].'</textarea></td></tr>';
  echo '<tr><td>Work Name</td><td><a href="'.site_url().'/wp-content/uploads/classworks/'.$sc_notes['attachment_name'].'" target="-blank">'.$sc_notes['work_name'].'</a></td></tr>';
  echo '<tr><td></td><td><input type="submit" name="edit_submit" value="Submit" /></td></tr>';
  echo '</table>';
  echo '</form>';
  
  echo '<hr>';
  echo '<a class="button-minimal" href="?list=1">Save no changes and <br /> Go back to review list</a>';

  echo '<hr>';
  echo '<a class="button-minimal" href="'.site_url().'/student-class-notes-form/'.'">Add a new class notes</a>';
 
}


//**************************************************************************************** */
if ( isset($_POST["edit_submit"]) )
{
  $error_msg ="";

	$d = (DateTime::createFromFormat('Y-m-d', $_POST['class_date']));
if ( $d && $d->format('Y-m-d') !== $_POST['class_date']) 
  $error_msg .= "The class date entered is not valid: {$_POST['class_date']}. <br />";

  $stmt = $dbt->prepare($find_by_id);
  $stmt->execute([$_POST["sc_notes_id"]]);
  $sc_notes = $stmt->fetch(PDO::FETCH_BOTH);

	$d0 = (DateTime::createFromFormat('Y-m-d', $sc_notes['class_date']));

if (
  $sc_notes['class_id'] == $_POST['class_id']
  && $d->format('Y-m-d') === $_POST['class_date']
  && $sc_notes['student_id'] == $_POST['student_id']
  && $sc_notes['teacher_id'] == $_POST['teacher_id']
  && $sc_notes['class_description'] == $_POST['class_description']
  && $sc_notes['sc_notes'] == $_POST['sc_notes']
)
  $error_msg .= "Nothing was changed from the edit submission. No change was saved. <br />";

if (!empty($error_msg)) {
  $_SESSION['msg'] = $error_msg;
  header("Location: ?list=1");
  die;
} else {

  $stmt = $dbt->prepare($update_by_id);
  $stmt->execute(
  [$_POST["class_id"]
  ,$_POST["student_id"]
  ,$_POST["teacher_id"]
  ,$_POST["class_description"]
  ,$_POST["sc_notes"]
  ,$_POST["class_date"]
  ,$_POST["sc_notes_id"]]);
  $updated = $stmt->rowCount();

if ($updated > 0) {
  $_SESSION['msg'] = 'One row updated.';
  header("Location: ?list=1");
  die;
} else {
  $_SESSION['msg'] = 'Update failed. No change saved.';
  header("Location: ?list=1");
  die;
}

}
  
}


//**************************************************************************************** */
if ( (empty($_GET["edt"]) && empty($_GET["alt"]) && empty($_GET["del"]) )
|| (isset($_GET["list"]) &&  !empty($_GET["list"]) && $_GET["list"] == 1 ) )
{
  $stmt = $dbt->prepare($find_all);
  $stmt->execute();
  $all_sc_notes = $stmt->fetchall(PDO::FETCH_BOTH);

  if (isset($_SESSION['msg']) && !empty($_SESSION['msg'])) 
  { echo $_SESSION['msg'].'<br />'; $_SESSION['msg'] =''; }

  echo '<table 
  border="2"
  align="center"
  cellpadding="5"
  cellspacing="3"
  style="font-family:arial,helvetica,sans-serif;"><tr>
                 <th>SC Status</th>
                 <th>Class Name</th>
                 <th>Class Date</th>
                 <th>Student Name</th>
                 <th>Teacher Name</th>
                 <th>Class Description</th>
                 <th>SC Notes</th>
                 <th>SC Work</th>
                 <th>Created</th>
                 <th>Lastmodified</th></tr>';
 
 foreach ($all_sc_notes as $row) {
   echo '<tr><td>'.($row['inactive']==0 ? 'active' : 'inactive');
   echo '<br /><a href = "?alt='.htmlentities($row['0']).'">Change</a></td>';
   echo '<td>'.htmlentities($row['class_name']);
   echo '<br /><a href = "?edt='.$row['sc_notes_id'].'">Edit</a></td>';
   echo '<td>'.htmlentities($row['class_date']).'</td>';
   echo '<td>'.htmlentities($row['Student']).'</td>';
   echo '<td>'.htmlentities($row['Teacher']).'</td>';
   echo '<td>'.(strlen(htmlentities($row['class_description']))>25?substr(htmlentities($row['class_description']),0,25)."...":htmlentities($row['class_description'])).'</td>';
   echo '<td>'.(strlen(htmlentities($row['sc_notes']))>25?substr(htmlentities($row['sc_notes']),0,25)."...":htmlentities($row['sc_notes'])).'</td>';
   echo '<td><a href="'.site_url().'/wp-content/uploads/classworks/'.$row['attachment_name'].'" target="-blank">'.$row['work_name'].'</a></td>';
   echo '<td>'.$row['created_datetime'].'</td>';
   echo '<td>'.$row['lastmodified_datetime'].'</td>';
   echo '</tr>';
 }  
 echo '</table>';
 
 echo '<hr>';
 echo '<a class="button-minimal" href="'.site_url().'/student-class-notes-form/'.'">Add a new class notes</a>';

}

?>