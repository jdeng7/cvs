<?php

try { 
$dbt = new PDO(  );
}
catch (Exception $e) {
	echo $e->getMessage();
}

function generateRandomString($length = 12) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
}


//**********check member role*******************************************************
$userid = get_current_user_id();

$SQL1 = "select m.member_id, m.user_name, ms.alias
from llas_swpm_members_tbl m
join llas_users u on u.user_email = m.email
and u.ID = ?
join llas_swpm_membership_tbl ms on ms.id = m.membership_level";

$stmt = $dbt->prepare($SQL1);
$stmt->execute([$userid]);
$memberinfo = $stmt->fetch();

if ($memberinfo['alias'] != 'Teacher' && $memberinfo['alias'] != 'Manager') {
	echo '<h4>This page is only for teacher or manager members to enter, edit and review their student class notes entries.</h4>';
	return;
} else {
	echo '<h4>A teacher, or manager, member can enter student class notes here. </h4>';
}


//***no step taken yet, so give form1***********************
if (!isset($_POST['sc_notes_step'])) {
	
//*****************get class info***********************************************************
if (isset($_REQUEST['class_id']) && !empty($_REQUEST['class_id']) && ctype_digit($_REQUEST['class_id'])) {
	$class_id = $_REQUEST['class_id'];
} else {
	$class_id = 1;
}

$SQL2 = "select c.class_id, c.class_name, c.class_code, c.class_description
from llas_classes c
where c.class_id = ? ";

$stmt2 = $dbt->prepare($SQL2);
$stmt2->execute([$class_id]);
$classinfo = $stmt2->fetch();

//************************get all classes*********************************************************
$SQL2b = "select c.class_id, c.class_name, c.class_code, c.class_description
from llas_classes c
where c.inactive = 0 
order by case when c.class_id = ? then 0 else 1 end,
c.class_name";

$stmt2b = $dbt->prepare($SQL2b);
$stmt2b->execute([$class_id]);
$classes = $stmt2b->fetchall(PDO::FETCH_BOTH);

//****************************get all teachers***************************************************
$SQL4 = "select distinct
concat(m2.user_name,' (',m2.first_name,' ',m2.last_name,')') as Teacher, m2.member_id as teacher_id
from llas_swpm_members_tbl m2 
where m2.membership_level in (10,11)
order by case when m2.member_id = ? then 0 else 1 end, 
concat(m2.user_name,' (',m2.first_name,' ',m2.last_name,')')";

$stmt4 = $dbt->prepare($SQL4);
$stmt4->execute([$memberinfo['member_id']]);
$teachers = $stmt4->fetchall(PDO::FETCH_BOTH);

//****single student class notes entry STEP1 form START*************************************************************
echo '<form action="" method="post">';
echo '<input type = "hidden" name="sc_notes_step" value="1" />';
echo '<table>';
echo '<tr><th>Field</th><th>Value</th></tr>';

echo '<tr><td>Class Name</td><td><select name="class_id">';
			foreach ($classes as $c) {
				echo '<option value ="'.$c['class_id'].'">'.$c['class_name'].'</option>';
			} echo '</select></td></tr>';
echo '<tr><td>Class Date</td><td><input name="class_date" value="'.date('Y-m-d').'" /></td></tr>';
echo '<tr><td>Teacher Name</td><td><select name="teacher_id">';
			foreach ($teachers as $t) {
				echo '<option value ="'.$t['teacher_id'].'">'.$t['Teacher'].'</option>';
			} echo '</select></td></tr>';

echo '<tr><td>[Step 1/3]</td>
<td><input type="submit" name="single_sc_notes" value="Submit" /></td></tr>';
echo '</table>';
echo '</form>';
//****single student class notes entry STEP1 form END*************************************************************
}


//*****received submission from step 1 START*********************************************
if (isset($_POST['sc_notes_step']) 
&& !empty($_POST['sc_notes_step']) 
&& $_POST['sc_notes_step'] == 1) {
	$d = (DateTime::createFromFormat('Y-m-d', $_POST['class_date']));
if ( $d && $d->format('Y-m-d') === $_POST['class_date']) {

//**************************get class students************************************
$SQL3 = "select distinct 
concat(m1.user_name,' (',m1.first_name,' ',m1.last_name,')') as Student
, m1.member_id as student_id
from llas_classes c
join llas_student_class_notes scn on scn.class_id = c.class_id
join llas_swpm_members_tbl m1 on m1.member_id = scn.student_id and m1.membership_level = 8
where c.class_id = ? and c.inactive = 0 and scn.inactive = 0
order by Student";

$stmt3 = $dbt->prepare($SQL3);
$stmt3->execute([$_POST['class_id']]);
$class_students = $stmt3->fetchall(PDO::FETCH_BOTH);

//******************************get other students***********************************************
$SQL3b = "select distinct 
concat(m1.user_name,' (',m1.first_name,' ',m1.last_name,')') as Student
, m1.member_id as student_id
from llas_swpm_members_tbl m1 left join 
(select scn.student_id from llas_classes c
join llas_student_class_notes scn on scn.class_id = c.class_id
where c.class_id = ? and scn.inactive = 0) c
on m1.member_id = c.student_id
where c.student_id is null and m1.membership_level = 8
order by Student";

$stmt3b = $dbt->prepare($SQL3b);
$stmt3b->execute([$_POST['class_id']]);
$other_students = $stmt3b->fetchall(PDO::FETCH_BOTH);

//******************************get all guests***********************************************
$SQL3c = "select distinct
concat(m1.user_name,' (',m1.first_name,' ',m1.last_name,')') as Student
, m1.member_id as student_id
from llas_swpm_members_tbl m1 
where m1.membership_level = 12
order by Student";

$stmt3c = $dbt->prepare($SQL3c);
$stmt3c->execute();
$guests = $stmt3c->fetchall(PDO::FETCH_BOTH);

//***********************single student class notes entry STEP2 form******************************************
echo '<form action="" method="post">';
echo '<input type = "hidden" name="sc_notes_step" value="2" />';
echo '<input type = "hidden" name="class_id" value="'.$_POST['class_id'].'" />';
echo '<input type = "hidden" name="class_date" value="'.$_POST['class_date'].'" />';
echo '<input type = "hidden" name="teacher_id" value="'.$_POST['teacher_id'].'" />';
echo '<table>';
echo '<tr><th>Options</th><th>Student</th></tr>';

echo '<tr><td>In class</td><td><select name="class_student_id">';
			echo '<option value ="0">Please choose</option>';
			foreach ($class_students as $s) {
				echo '<option value ="'.$s['student_id'].'">'.$s['Student'].'</option>';
			} echo '</select></td></tr>';
echo '<tr><td>In studio</td><td><select name="other_student_id">';
			echo '<option value ="0">Please choose</option>';
			foreach ($other_students as $s) {
				echo '<option value ="'.$s['student_id'].'">'.$s['Student'].'</option>';
			} echo '</select></td></tr>';
echo '<tr><td>In guests</td><td><select name="guest_id">';
			echo '<option value ="0">Please choose</option>';
			foreach ($guests as $s) {
				echo '<option value ="'.$s['student_id'].'">'.$s['Student'].'</option>';
			} echo '</select></td></tr>';
echo '<tr><td>New member</td><td>  	<input type="text" name="username" placeholder="Username" /><br>
									<input type="text" name="fname" placeholder="First name" /><br>
									<input type="text" name="lname" placeholder="Last name" /></td></tr>';
			
echo '<tr><td>[Step 2/3]</td><td><input type="submit" name="single_sc_notes" value="Submit" /></td></tr>';
echo '</table>';
echo '</form>';
} else {
	echo "The class date entered is not valid: {$_POST['class_date']}. Please re-enter a valid class date.";
//****single student class notes entry STEP1 form RESENDING START*************************************************************
//*****************get class info***********************************************************
if (isset($_REQUEST['class_id']) && !empty($_REQUEST['class_id']) && ctype_digit($_REQUEST['class_id'])) {
	$class_id = $_REQUEST['class_id'];
} else {
	$class_id = 1;
}

$SQL2 = "select c.class_id, c.class_name, c.class_code, c.class_description
from llas_classes c
where c.class_id = ? ";

$stmt2 = $dbt->prepare($SQL2);
$stmt2->execute([$class_id]);
$classinfo = $stmt2->fetch();

//************************get all classes*********************************************************
$SQL2b = "select c.class_id, c.class_name, c.class_code, c.class_description
from llas_classes c
where c.inactive = 0 
order by case when c.class_id = ? then 0 else 1 end,
c.class_name";

$stmt2b = $dbt->prepare($SQL2b);
$stmt2b->execute([$class_id]);
$classes = $stmt2b->fetchall(PDO::FETCH_BOTH);

//****************************get all teachers***************************************************
$SQL4 = "select distinct
concat(m2.user_name,' (',m2.first_name,' ',m2.last_name,')') as Teacher, m2.member_id as teacher_id
from llas_swpm_members_tbl m2 
where m2.membership_level in (10,11)
order by case when m2.member_id = ? then 0 else 1 end, 
concat(m2.user_name,' (',m2.first_name,' ',m2.last_name,')')";

$stmt4 = $dbt->prepare($SQL4);
$stmt4->execute([$memberinfo['member_id']]);
$teachers = $stmt4->fetchall(PDO::FETCH_BOTH);

echo '<form action="" method="post">';
echo '<input type = "hidden" name="sc_notes_step" value="1" />';
echo '<table>';
echo '<tr><th>Field</th><th>Value</th></tr>';

echo '<tr><td>Class Name</td><td><select name="class_id">';
			foreach ($classes as $c) {
				echo '<option value ="'.$c['class_id'].'"'.($c['class_id'] == $_POST['class_id'] ? ' selected' : '').'>'.$c['class_name'].'</option>';
			} echo '</select></td></tr>';
echo '<tr><td>Class Date</td><td><input name="class_date" value="'.$_POST["class_date"].'" /></td></tr>';
echo '<tr><td>Teacher Name</td><td><select name="teacher_id">';
			foreach ($teachers as $t) {
				echo '<option value ="'.$t['teacher_id'].'"'.($t['teacher_id'] == $_POST['teacher_id'] ? ' selected' : '').'>'.$t['Teacher'].'</option>';
			} echo '</select></td></tr>';

echo '<tr><td>[Step 1/3]</td>
<td><input type="submit" name="single_sc_notes" value="Submit" /></td></tr>';
echo '</table>';
echo '</form>';
//****single student class notes entry STEP1 form RESENDING END*************************************************************	
}

//*****received submission from step 1 START*********************************************
}


//***received submission from step 2 START********************************************************************
if (isset($_POST['sc_notes_step']) 
&& !empty($_POST['sc_notes_step']) 
&& $_POST['sc_notes_step'] == 2
) {
	
	if (isset($_POST['class_student_id']) && !empty($_POST['class_student_id']) && $_POST['class_student_id'] != 0) {
		$student_id = $_POST['class_student_id'];
	} elseif (isset($_POST['other_student_id']) && !empty($_POST['other_student_id']) && $_POST['other_student_id'] != 0) {
			$student_id = $_POST['other_student_id'];
		} elseif (isset($_POST['guest_id']) && !empty($_POST['guest_id']) && $_POST['guest_id'] != 0) {
				$student_id = $_POST['guest_id'];
				$SQL4b = "update llas_swpm_members_tbl set membership_level = 8 where member_id = ? ;";
				$stmt4b = $dbt->prepare($SQL4b);
				$stmt4b->execute([$student_id]);
				$updated = $stmt4b->rowcount();
				if ($updated > 0) {
					echo $updated . " row of member changed from guest to student.";
				}
			} elseif (empty($student_id) 
			&& isset($_POST['username']) && !empty($_POST['username']) 
			&& isset($_POST['fname']) && !empty($_POST['fname'])
			&& isset($_POST['lname']) && !empty($_POST['lname'])
			) {
				$SQL7 = "select member_id from llas_swpm_members_tbl where user_name = ? ;";
				$stmt7 = $dbt->prepare($SQL7);
				$stmt7->execute([$_POST['username']]);
				$found7 = $stmt7->rowcount();
				if ($found7 > 0) {
					echo "The username {$_POST['username']} was already used by another user. <br />Please choose a different Username.";
					
//***********************single student class notes entry STEP2 form RESENDING******************************************
echo '<form action="" method="post">';
echo '<input type = "hidden" name="sc_notes_step" value="2" />';
echo '<input type = "hidden" name="class_id" value="'.$_POST['class_id'].'" />';
echo '<input type = "hidden" name="class_date" value="'.$_POST['class_date'].'" />';
echo '<input type = "hidden" name="teacher_id" value="'.$_POST['teacher_id'].'" />';
echo '<table>';
echo '<tr><th>Options</th><th>Student</th></tr>';

echo '<tr><td>In class</td><td><select name="class_student_id">';
			echo '<option value ="0">Please choose</option>';
			foreach ($class_students as $s) {
				echo '<option value ="'.$s['student_id'].'">'.$s['Student'].'</option>';
			} echo '</select></td></tr>';
echo '<tr><td>In studio</td><td><select name="other_student_id">';
			echo '<option value ="0">Please choose</option>';
			foreach ($other_students as $s) {
				echo '<option value ="'.$s['student_id'].'">'.$s['Student'].'</option>';
			} echo '</select></td></tr>';
echo '<tr><td>In guests</td><td><select name="guest_id">';
			echo '<option value ="0">Please choose</option>';
			foreach ($guests as $s) {
				echo '<option value ="'.$s['student_id'].'">'.$s['Student'].'</option>';
			} echo '</select></td></tr>';
echo '<tr><td>New member</td><td>  	<input type="text" name="username" placeholder="Username" value = "'.$_POST["username"].'" /><br>
									<input type="text" name="fname" placeholder="First name" value = "'.$_POST["fname"].'" /><br>
									<input type="text" name="lname" placeholder="Last name" value = "'.$_POST["lname"].'" /></td></tr>';
			
echo '<tr><td>[Step 2/3]</td><td><input type="submit" name="single_sc_notes" value="Submit" /></td></tr>';
echo '</table>';
echo '</form>';
				} else {
				
					$username = $_POST['username'];
      				$fname = $_POST['fname'];
					$lname = $_POST['lname'];
					$randstr = generateRandomString(8).'@test.com';
					try {
							$SQL4 = "insert into llas_users (user_login, user_pass, user_nicename, user_email, user_url, user_registered, User_activation_key, User_status, display_name) values (?,?,?,?,'',now(),'',0,?);";
							$SQL4b = "insert into llas_swpm_members_tbl  (user_name, first_name, last_name, password, member_since, membership_level,account_state, last_accessed, last_accessed_from_ip, email,subscription_starts) values (?,?,?,?,now(),8,'active',now(),'192.168.0.1',?,now());";
							$upw = '$P$Blzr8VLsn0Q6tj.qefHjEkZff7eiI8/';
							$mpw ='$P$Bn4yR7OvubwW73MgmLCs7/yUfQPnCd/';
							try {
								$dbt->beginTransaction();
								$stmt4 = $dbt->prepare($SQL4);
                                $stmt4->execute([$username,$upw,$username,$randstr,$username]);
								$inserted = $dbt->lastInsertId();
								if ($inserted > 0) {
									$stmt4b = $dbt->prepare($SQL4b);
									$stmt4b->execute([$username,$fname,$lname,$mpw,$randstr]);
									$inserted = $dbt->lastInsertId();
									if ($inserted > 0) {
										$student_id = $inserted;
										echo "New memebr, {$_POST['username']}({$_POST['fname']} {$_POST['lname']}), added with student membership.<br /> Please tell the student complete the profile and registration form ASAP.";
										$dbt->commit();
									}
								}
 							} catch (PDOException $e) {
								$stmt4->rollback();
								print "Error!: " . $e->getMessage() . "</br>"; 
							}
						} catch (PDOException $e) {
							print "Error!: " . $e->getMessage() . "</br>"; 
							}
							

				
				}
			} elseif (empty($student_id) 
			&& ( !isset($_POST['username']) || empty($_POST['username']) 
			|| !isset($_POST['fname']) || empty($_POST['fname'])
			|| !isset($_POST['lname']) || empty($_POST['lname']) )
			) {
				echo 'Please fill in all of "UserName", "FirstName" and "LastName" for the new member. <br />Or, choose a student from the list.';
				
//***single student class notes entry STEP2 form RESENDING START**************************************************************
echo '<form action="" method="post">';
echo '<input type = "hidden" name="sc_notes_step" value="2" />';
echo '<input type = "hidden" name="class_id" value="'.$_POST['class_id'].'" />';
echo '<input type = "hidden" name="class_date" value="'.$_POST['class_date'].'" />';
echo '<input type = "hidden" name="teacher_id" value="'.$_POST['teacher_id'].'" />';
echo '<table>';
echo '<tr><th>Options</th><th>Student</th></tr>';

echo '<tr><td>In class</td><td><select name="class_student_id">';
			echo '<option value ="0">Please choose</option>';
			foreach ($class_students as $s) {
				echo '<option value ="'.$s['student_id'].'">'.$s['Student'].'</option>';
			} echo '</select></td></tr>';
echo '<tr><td>In studio</td><td><select name="other_student_id">';
			echo '<option value ="0">Please choose</option>';
			foreach ($other_students as $s) {
				echo '<option value ="'.$s['student_id'].'">'.$s['Student'].'</option>';
			} echo '</select></td></tr>';
echo '<tr><td>In guests</td><td><select name="guest_id">';
			echo '<option value ="0">Please choose</option>';
			foreach ($guests as $s) {
				echo '<option value ="'.$s['student_id'].'">'.$s['Student'].'</option>';
			} echo '</select></td></tr>';
echo '<tr><td>New member</td><td>  	<input type="text" name="username" placeholder="Username" value = "'.$_POST["username"].'" /><br>
									<input type="text" name="fname" placeholder="First name" value = "'.$_POST["fname"].'" /><br>
									<input type="text" name="lname" placeholder="Last name" value = "'.$_POST["lname"].'" /></td></tr>';
			
echo '<tr><td>[Step 2/3]</td><td><input type="submit" name="single_sc_notes" value="Submit" /></td></tr>';
echo '</table>';
echo '</form>';
//***single student class notes entry STEP2 form RESENDING END**************************************************************
			}

if (isset($student_id)	&& !empty($student_id)) {
	
				//*****************get class info***********************************************************
				if (isset($_POST['class_id']) && !empty($_POST['class_id']) && ctype_digit($_POST['class_id'])) {
					$class_id = $_POST['class_id'];
				} else {
					$class_id = 3;
				}

				$SQL2 = "select c.class_id, c.class_name, c.class_code, c.class_description
				from llas_classes c
				where c.class_id = ? ";

				$stmt2 = $dbt->prepare($SQL2);
				$stmt2->execute([$class_id]);
				$classinfo = $stmt2->fetch();

				//***********************single student class notes entry STEP3 form******************************************
				echo '<form action="" method="post" enctype="multipart/form-data">';
				echo '<input type = "hidden" name="sc_notes_step" value="3" />';
				echo '<input type = "hidden" name="class_id" value="'.$_POST['class_id'].'" />';
				echo '<input type = "hidden" name="class_date" value="'.$_POST['class_date'].'" />';
				echo '<input type = "hidden" name="teacher_id" value="'.$_POST['teacher_id'].'" />';
				echo '<input type = "hidden" name="student_id" value="'.$student_id.'" />';

				echo '<table>';
				echo '<tr><th>Field</th><th>Value</th></tr>';

				echo '<tr><td>Class Description</td><td><textarea name="class_description" rows="6" cols="40">'.$classinfo['class_description'].'</textarea></td></tr>';
				echo '<tr><td>Teacher comments</td><td><textarea name="sc_notes" rows="6" cols="40"></textarea></td></tr>';
				echo '<tr><td>Attachment</td><td><input type="file" name="attachment" /></td></tr>';
							
				echo '<tr><td>[Step 3/3]</td><td><input type="submit" name="single_sc_notes" value="Submit" /></td></tr>';
				echo '</table>';
				echo '</form>';
	
}

//***received submission from step 2 END********************************************************************
}


//****received and process final STEP3 form request START*************************************************************
if (isset($_POST['sc_notes_step']) 
&& !empty($_POST['sc_notes_step']) 
&& $_POST['sc_notes_step'] == 3) {
	
	if (//in case having attachment and notes
		   isset($_POST['single_sc_notes'])
		&& isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'])
	   ){
		
		$errors = array();
		$file_name = $_FILES['attachment']['name'];
		$file_size = $_FILES['attachment']['size'];
		$file_tmp = $_FILES['attachment']['tmp_name'];
		$file_type = $_FILES['attachment']['type'];
		$file_ext = strtolower(end(explode('.',$_FILES['attachment']['name'])));
		$file_basename = current(explode('.',$_FILES['attachment']['name']));
		
		$extensions= array("jpg","png","bmp","gif");
		if(in_array($file_ext,$extensions) != true){
			$error[] = '"$file_ext"'.' extension is not allowed, please choose a different files.';
		}
		
		if($file_size > 11000000) {
			$errors[]='File size must be less than 10 MB';
		}
		
		$dest_folder = "wp-content/uploads/classworks";
		defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
		$dest_name = 'C'.$_POST['class_id'].'-D'.date('Ymd').'-S'.$_POST['student_id'].'-T'.$_POST['teacher_id'].'-'.date('His').generateRandomString().".".$file_ext;
		
		if (empty($errors) == true) {
			if (move_uploaded_file($file_tmp,(!empty($dest_folder)?$dest_folder.DS:'').$dest_name) == true) {
				echo "A file uploaed<br />";
			
			$SQL5 = "insert into llas_student_class_notes (class_id, student_id, teacher_id, class_description, sc_notes, work_name, attachment_name, class_date, inactive, created_datetime) values (?,?,?,?,?,?,?,?,0,now());";
			$stmt = $dbt->prepare($SQL5);
			$stmt->execute([$_POST['class_id'],$_POST['student_id'],$_POST['teacher_id'],$_POST['class_description'],$_POST['sc_notes'],$_FILES['attachment']['name'],$dest_name,$_POST['class_date']]);
			$inserted = $stmt->rowcount();
			echo $inserted . " row of student class notes was inserted.";
				
			} else {
				echo "File upload failed.";
			}
			
		} else {
			print_r($errors);
		}

	} elseif (!empty($_POST['sc_notes'])) {
			$SQL6 = "insert into llas_student_class_notes (class_id, student_id, teacher_id, class_description, sc_notes, work_name, attachment_name, class_date, inactive, created_datetime) values (?,?,?,?,?,?,?,?,0,now());";
			$stmt = $dbt->prepare($SQL6);
			$stmt->execute([$_POST['class_id'],$_POST['student_id'],$_POST['teacher_id'],$_POST['class_description'],$_POST['sc_notes'],'','',$_POST['class_date']]);
			$inserted = $stmt->rowcount();
			if ($inserted > 0) {
			echo $inserted . " row of student class notes was inserted.";
			} else {
				echo "Add class notes failed.";
			}		
	} else {
		echo "No file attached and No student class notes entered. No record of notes added.";
	}
//****received and process final STEP3 form request END*************************************************************
	}

//***A button of reset at the END**********************************************************
echo '<hr>';

echo '<a class="button-minimal" href="">Enter another new entry <br />OR Reset current entry</a>';
?>