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
select sc_notes_id, class_id, student_id, teacher_id, class_description
, sc_notes, work_name, attachment_name, class_date
, inactive, created_datetime, lastmodified_datetime
from llas_student_class_notes ";

$find_by_id = "
select sc_notes_id, class_id, student_id, teacher_id, class_description
, sc_notes, work_name, attachment_name, class_date
, inactive, created_datetime, lastmodified_datetime
from llas_student_class_notes
where sc_notes_id = ? ";

$update_by_id = "update llas_student_class_notes
set class_id = ? , student_id = ? , teacher_id = ? 
, class_description = ? , sc_notes = ? , work_name = ?
, attachment_name = ? , class_date = ? , lastmodified_datetime = now()
where sc_notes_id = ? ";

$find_all_classes = "select class_id, class_name from llas_classes where inactive = 0 ";
$find_class_by_id = "select class_id, class_name from llas_classes where class_id = ? ";
$find_all_students = "select m1.member_id as student_id, concat(m1.user_name,' (',m1.first_name,' ',m1.last_name,')') as Student
from llas_swpm_members_tbl m1 where m1.membership_level = 8 and inactive = 0 ";
$find_all_teachers = "select m1.member_id as student_id, concat(m1.user_name,' (',m1.first_name,' ',m1.last_name,')') as Student
from llas_swpm_members_tbl m1 where m1.membership_level IN (10,11) and inactive = 0 ";
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




?>
