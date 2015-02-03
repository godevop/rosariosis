<?php

include('plugins/Moodle/getconfig.inc.php');

//modif Francois: Moodle plugin

//check Moodle plugin configuration options are set
if (MOODLE_URL && MOODLE_TOKEN && MOODLE_PARENT_ROLE_ID && ROSARIO_STUDENTS_EMAIL_FIELD_ID)
{
	//Register plugin functions to be hooked
	add_action('Students/Student.php|header', 'MoodleTriggered');
	add_action('Students/Student.php|create_student_checks', 'MoodleTriggered');
	add_action('Students/Student.php|create_student', 'MoodleTriggered');
	add_action('Students/Student.php|update_student_checks', 'MoodleTriggered');
	add_action('Students/Student.php|update_student', 'MoodleTriggered');
	add_action('Students/Student.php|upload_student_photo', 'MoodleTriggered');
	add_action('Students/Student.php|add_student_address', 'MoodleTriggered');
	add_action('Students/Student.php|update_student_address', 'MoodleTriggered');

	add_action('Students/AddUsers.php|user_assign_role', 'MoodleTriggered');
	add_action('Students/AddUsers.php|user_unassign_role', 'MoodleTriggered');

	add_action('Users/User.php|header', 'MoodleTriggered');
	add_action('Users/User.php|create_user_checks', 'MoodleTriggered');
	add_action('Users/User.php|create_user', 'MoodleTriggered');
	add_action('Users/User.php|update_user_checks', 'MoodleTriggered');
	add_action('Users/User.php|update_user', 'MoodleTriggered');
	add_action('Users/User.php|upload_user_photo', 'MoodleTriggered');
	add_action('Users/User.php|delete_user', 'MoodleTriggered');

	add_action('Users/Preferences.php|update_password_checks', 'MoodleTriggered');
	add_action('Users/Preferences.php|update_password', 'MoodleTriggered');

	add_action('Users/AddStudents.php|user_assign_role', 'MoodleTriggered');
	add_action('Users/AddStudents.php|user_unassign_role', 'MoodleTriggered');

	add_action('Custom/CreateParents.php|create_user', 'MoodleTriggered');
	add_action('Custom/CreateParents.php|user_assign_role', 'MoodleTriggered');

	add_action('Grades/Assignments.php|create_assignment', 'MoodleTriggered');
	add_action('Grades/Assignments.php|update_assignment', 'MoodleTriggered');
	add_action('Grades/Assignments.php|delete_assignment', 'MoodleTriggered');

	add_action('Scheduling/Courses.php|header', 'MoodleTriggered');
	add_action('Scheduling/Courses.php|create_course_subject', 'MoodleTriggered');
	add_action('Scheduling/Courses.php|create_course', 'MoodleTriggered');
	add_action('Scheduling/Courses.php|create_course_period', 'MoodleTriggered');
	add_action('Scheduling/Courses.php|update_course_subject', 'MoodleTriggered');
	add_action('Scheduling/Courses.php|update_course', 'MoodleTriggered');
	add_action('Scheduling/Courses.php|update_course_period', 'MoodleTriggered');
	add_action('Scheduling/Courses.php|delete_course_subject', 'MoodleTriggered');
	add_action('Scheduling/Courses.php|delete_course', 'MoodleTriggered');
	add_action('Scheduling/Courses.php|delete_course_period', 'MoodleTriggered');

	add_action('Scheduling/MassSchedule.php|schedule_student', 'MoodleTriggered');
	add_action('Scheduling/MassDrops.php|drop_student', 'MoodleTriggered');
	add_action('Scheduling/Schedule.php|drop_student', 'MoodleTriggered');
	add_action('Scheduling/Schedule.php|schedule_student', 'MoodleTriggered');
	add_action('Scheduling/Scheduler.php|schedule_student', 'MoodleTriggered');

	add_action('School_Setup/Calendar.php|event_field', 'MoodleTriggered');
	add_action('School_Setup/Calendar.php|create_calendar_event', 'MoodleTriggered');
	add_action('School_Setup/Calendar.php|update_calendar_event', 'MoodleTriggered');
	add_action('School_Setup/Calendar.php|delete_calendar_event', 'MoodleTriggered');

	add_action('School_Setup/PortalNotes.php|portal_note_field', 'MoodleTriggered', 2);
	add_action('School_Setup/PortalNotes.php|create_portal_note', 'MoodleTriggered');
	add_action('School_Setup/PortalNotes.php|update_portal_note', 'MoodleTriggered');
	add_action('School_Setup/PortalNotes.php|delete_portal_note', 'MoodleTriggered');

	add_action('School_Setup/Rollover.php|rollover_checks', 'MoodleTriggered');
	add_action('School_Setup/Rollover.php|rollover_staff', 'MoodleTriggered');
	add_action('School_Setup/Rollover.php|rollover_course_subjects', 'MoodleTriggered');
	add_action('School_Setup/Rollover.php|rollover_courses', 'MoodleTriggered');
	add_action('School_Setup/Rollover.php|rollover_course_periods', 'MoodleTriggered');
}


//Triggered function
//Will redirect to Moodle() function with the right WebService function name
function MoodleTriggered($hook_tag, $arg1 = '')
{
	global $error;

	//check Moodle plugin configuration options are set
	if (!MOODLE_URL || !MOODLE_TOKEN || !MOODLE_PARENT_ROLE_ID || !ROSARIO_STUDENTS_EMAIL_FIELD_ID)
		return false;

	list($modname, $action) = explode('|', $hook_tag);

	switch($hook_tag)
	{

/***************STUDENTS**/
		/*Students/Student.php*/
		case 'Students/Student.php|header':
			global $old_student_in_moodle;

			//propose to create student in Moodle: if 1) this is a creation, 2) this is an already created student but not in Moodle yet
			if (AllowEdit() && $_REQUEST['include']=='General_Info')
			{
				//2) verify the student is not in Moodle:
				if (UserStudentID())
					$old_student_in_moodle = IsMoodleStudent(UserStudentID());
			
				if ($_REQUEST['student_id']=='new' || !$old_student_in_moodle)
					DrawHeader('<label>'.CheckBoxOnclick('moodle_create_student').'&nbsp;'._('Create Student in Moodle').'</label>');
			}

		break;

		case 'Students/Student.php|create_student_checks':
			if ($_REQUEST['moodle_create_student'] && !MoodlePasswordCheck($_REQUEST['students']['PASSWORD']))
				$error[] = _('Please enter a valid password');

			//username, password, (email) required
			if ($_REQUEST['moodle_create_student'] && (empty($_REQUEST['students']['USERNAME']) || empty($_REQUEST['students']['CUSTOM_'.ROSARIO_STUDENTS_EMAIL_FIELD_ID])))
				$error[] = _('Please fill in the required fields');
			
		break;

		case 'Students/Student.php|create_student':
			if($_REQUEST['moodle_create_student'])
				Moodle($modname, 'core_user_create_users');

		break;

		case 'Students/Student.php|update_student_checks':
			if(!empty($_REQUEST['students']['PASSWORD']))
			{
				if (($_REQUEST['moodle_create_student'] || IsMoodleStudent(UserStudentID())) && !MoodlePasswordCheck($_REQUEST['students']['PASSWORD']))
					$error[] = _('Please enter a valid password');
			}
			elseif ($_REQUEST['moodle_create_student'])
				$error[] = _('Please enter a valid password');

		break;

		case 'Students/Student.php|update_student':
			if($_REQUEST['moodle_create_student'])
			{
				Moodle($modname, 'core_user_create_users');
				//relate parent if exists
				Moodle($modname, 'core_role_assign_roles');
			}
			else
				Moodle($modname, 'core_user_update_users');

		break;

		case 'Students/Student.php|upload_student_photo':
			Moodle($modname, 'core_files_upload');

		break;

		case 'Students/Student.php|add_student_address':
			if ($_REQUEST['values']['STUDENTS_JOIN_ADDRESS']['RESIDENCE'])
				Moodle($modname, 'core_user_update_users');

		break;

		case 'Students/Student.php|update_student_address':
			$residence = DBGet(DBQuery("SELECT RESIDENCE FROM STUDENTS_JOIN_ADDRESS WHERE ADDRESS_ID='".$_REQUEST['address_id']."'"));

			if ($residence[1]['RESIDENCE'] == 'Y')
				Moodle($modname, 'core_user_update_users');

		break;

		/*Students/AddUsers.php*/
		case 'Students/AddUsers.php|user_assign_role':
			Moodle($modname, 'core_role_assign_roles');

		break;

		case 'Students/AddUsers.php|user_unassign_role':
			Moodle($modname, 'core_role_unassign_roles');

		break;


/***************USERS**/
		/*Users/User.php*/
		case 'Users/User.php|header':
			global $old_user_in_moodle;

			//propose to create user in Moodle: if
			//1) this is a creation
			//2) this is an already created user but not in Moodle yet
			//3) the users have not been rolled yet
			if (AllowEdit() && $_REQUEST['include']=='General_Info')
			{
				//2) verify the user is not in Moodle:
				if (UserStaffID())
					$old_user_in_moodle = IsMoodleUser(UserStaffID());

				//3) verify the users have not been rolled yet:
				$users_rolled = false;
				if (count(DBGet(DBQuery("SELECT 'ROLLED' FROM STAFF WHERE SYEAR='".(UserSyear()+1)."'"))))
					$users_rolled = true;

				if (($_REQUEST['staff_id']=='new' || !$old_user_in_moodle) && !$users_rolled)
					DrawHeader('<label>'.CheckBoxOnclick('moodle_create_user').'&nbsp;'._('Create User in Moodle').'</label>');
			}

		break;

		case 'Users/User.php|create_user_checks':
			if ($_REQUEST['moodle_create_user'] && !MoodlePasswordCheck($_REQUEST['staff']['PASSWORD']))
				$error[] = _('Please enter a valid password');

			//username, email required
			if ($_REQUEST['moodle_create_user'] && (empty($_REQUEST['staff']['USERNAME']) || empty($_REQUEST['staff']['EMAIL'])))
			{
				$error[] = _('Please fill in the required fields');
			}

		break;

		case 'Users/User.php|create_user':
			if ($_REQUEST['moodle_create_user'])
			{
				Moodle($modname, 'core_user_create_users');
				Moodle($modname, 'core_role_assign_roles');
			}

		break;
			
		case 'Users/User.php|update_user_checks':
			if(!empty($_REQUEST['staff']['PASSWORD']))
			{
				if (($_REQUEST['moodle_create_user'] || IsMoodleUser(UserStaffID())) && !MoodlePasswordCheck($_REQUEST['staff']['PASSWORD']))
					$error[] = _('Please enter a valid password');
			}
			elseif ($_REQUEST['moodle_create_user'])
				$error[] = _('Please enter a valid password');

		break;
			
		case 'Users/User.php|update_user':
			if ($_REQUEST['moodle_create_user'])
			{
				Moodle($modname, 'core_user_create_users');
				Moodle($modname, 'core_role_assign_roles');
			}
			elseif (IsMoodleUser(UserStaffID()))
			{
				Moodle($modname, 'core_user_update_users');
				Moodle($modname, 'core_role_unassign_roles');
				Moodle($modname, 'core_role_assign_roles');
			}

		break;

		case 'Users/User.php|upload_user_photo':
			Moodle($modname, 'core_files_upload');

		break;

		case 'Users/User.php|delete_user':
			Moodle($modname, 'core_user_delete_users');

		break;

		/*Users/Preferences.php*/
		case 'Users/Preferences.php|update_password_checks':
			global $new_password, $error;

			if (!MoodlePasswordCheck($new_password))
				$error[] = _('Please enter a valid password');

		break;

		case 'Users/Preferences.php|update_password':
			Moodle($modname, 'core_user_update_users');

		break;

		/*Users/AddStudents.php*/
		case 'Users/AddStudents.php|user_assign_role':
			Moodle($modname, 'core_role_assign_roles');

		break;

		case 'Users/AddStudents.php|user_unassign_role':
			Moodle($modname, 'core_role_unassign_roles');

		break;


/***************CUSTOM**/
		/*Custom/CreateParents.php*/
		case 'Custom/CreateParents.php|create_user':
			Moodle($modname, 'core_user_create_users');

		break;

		case 'Custom/CreateParents.php|user_assign_role':
			Moodle($modname, 'core_role_assign_roles');

		break;


/***************GRADES**/
		/*Grades/Assignments.php*/
		case 'Grades/Assignments.php|create_assignment':
			//add course event to the Moodle calendar
			Moodle($modname, 'core_calendar_create_calendar_events');

		break;

		case 'Grades/Assignments.php|update_assignment':
			//delete event then recreate it!
			Moodle($modname, 'core_calendar_delete_calendar_events');
			Moodle($modname, 'core_calendar_create_calendar_events');

		break;

		case 'Grades/Assignments.php|delete_assignment':
			Moodle($modname, 'core_calendar_delete_calendar_events');

		break;


/***************SCHEDULING**/
		/*Scheduling/Courses.php*/
		case 'Scheduling/Courses.php|header':
			//propose to create course period in Moodle: if
			//1) this is a creation,
			//2) this is an already created course period but not in Moodle yet
			//AND 3) if the course is in Moodle
			if (AllowEdit())
			{
				//2) verify if the course period is in Moodle:
				$old_course_period_in_moodle = false;
				if ($_REQUEST['course_period_id'] != 'new')
					$old_course_period_in_moodle = IsMoodleCoursePeriod($_REQUEST['course_period_id']);
					
				//3) verify if the course is in Moodle:
				$course_in_moodle = false;
				if ($_REQUEST['course_id'] != 'new')
					$course_in_moodle = IsMoodleCourse($_REQUEST['course_id']);
				
				if ($course_in_moodle && ($_REQUEST['course_period_id']=='new' || !$old_course_period_in_moodle))
					DrawHeader('<label>'.CheckBoxOnclick('moodle_create_course_period').'&nbsp;'._('Create Course Period in Moodle').'</label>');
			}
			
		case 'Scheduling/Courses.php|create_course_subject':
		case 'Scheduling/Courses.php|create_course':
			Moodle($modname, 'core_course_create_categories');

		break;

		case 'Scheduling/Courses.php|create_course_period':
			if($_REQUEST['moodle_create_course_period'])
			{
				Moodle($modname, 'core_course_create_courses');
				Moodle($modname, 'core_role_assign_roles');
			}

		break;

		case 'Scheduling/Courses.php|update_course_subject':
		case 'Scheduling/Courses.php|update_course':
			Moodle($modname, 'core_course_update_categories');

		break;

		case 'Scheduling/Courses.php|update_course_period':
			//if Course Period is already in Moodle
			if(IsMoodleCoursePeriod($_REQUEST['course_period_id']))
			{
				Moodle($modname, 'core_course_update_courses');

				//update teacher too
				global $columns, $current;
				if ($columns['TEACHER_ID'] && $columns['TEACHER_ID'] != $current[1]['TEACHER_ID'])
				{
					Moodle($modname, 'core_role_unassign_roles');
					Moodle($modname, 'core_role_assign_roles');
				}
			}
			//this is an already created course period but not in Moodle yet TODO: TEST!!
			elseif ($_REQUEST['moodle_create_course_period'])
			{
				Moodle($modname, 'core_course_create_courses');
				Moodle($modname, 'core_role_assign_roles');
			}

		break;

		case 'Scheduling/Courses.php|delete_course_subject':
		case 'Scheduling/Courses.php|delete_course':
			Moodle($modname, 'core_course_delete_categories');

		break;

		case 'Scheduling/Courses.php|delete_course_period':
			Moodle($modname, 'core_course_delete_courses');

		break;

		/*Scheduling/MassSchedule.php*/
		case 'Scheduling/MassSchedule.php|schedule_student':
			Moodle($modname, 'enrol_manual_enrol_users');

		break;

		/*Scheduling/MassDrops.php*/
		case 'Scheduling/MassDrops.php|drop_student':
			Moodle($modname, 'core_role_unassign_roles');

		break;

		/*Scheduling/Schedule.php*/
		case 'Scheduling/Schedule.php|schedule_student':
			Moodle($modname, 'enrol_manual_enrol_users');
		break;

		case 'Scheduling/Schedule.php|drop_student':
			Moodle($modname, 'core_role_unassign_roles');
		break;

		/*Scheduling/Scheduler.php*/
		case 'Scheduling/Scheduler.php|schedule_student':
			Moodle($modname, 'enrol_manual_enrol_users');
		break;


/***************SCHOOL_SETUP**/
		/*School_Setup/Calendar.php*/
		case 'School_Setup/Calendar.php|event_field':
			//only if new event
			if($_REQUEST['event_id']=='new')
				echo '<TR><TD>'._('Publish Event in Moodle?').'</TD><TD><label><INPUT type="checkbox" name="MOODLE_PUBLISH_EVENT" value="Y" checked> '._('Yes').'</label></TD></TR>';

		break;

		case 'School_Setup/Calendar.php|create_calendar_event':
			global $error;

			if ($_REQUEST['MOODLE_PUBLISH_EVENT'])
			{
				Moodle($modname, 'core_calendar_create_calendar_events');
				if (!empty($error))
				{
					echo ErrorMessage($error, 'fatal');//display inside popup, before JS closing
				}
			}

		break;

		case 'School_Setup/Calendar.php|update_calendar_event':
			global $error;

			$isMoodleEvent = count(DBGet(DBQuery("SELECT 1 FROM moodlexrosario WHERE rosario_id='".$_REQUEST['event_id']."' AND \"column\"='calendar_event_id'")));

			if ($isMoodleEvent)
			{
				//delete event then recreate it!
				Moodle($modname, 'core_calendar_delete_calendar_events');
				if (!empty($error))
				{
					echo ErrorMessage($error, 'fatal');//display inside popup, before JS closing
				}

				Moodle($modname, 'core_calendar_create_calendar_events');
				if (!empty($error))
				{
					echo ErrorMessage($error, 'fatal');//display inside popup, before JS closing
				}
			}

		break;

		case 'School_Setup/Calendar.php|delete_calendar_event':
			global $error;

			Moodle($modname, 'core_calendar_delete_calendar_events');
			if (!empty($error))
			{
				echo ErrorMessage(array($error), 'fatal');//display inside popup, before JS closing
			}

		break;

		/*School_Setup/PortalNotes.php*/
		case 'School_Setup/PortalNotes.php|portal_note_field':
			$id = $arg1;
			global $return;

			//only if new note
			if ($id == 'new')
				$return .= '<TR class="st"><TD colspan="2"><B>'._('Publish Note in Moodle?').'</B> <label><INPUT type="checkbox" name="MOODLE_PUBLISH_NOTE" value="Y" /> '._('Yes').'</label></TD></TR>';

		break;

		case 'School_Setup/PortalNotes.php|create_portal_note':
			if ($_REQUEST['MOODLE_PUBLISH_NOTE'])
				Moodle($modname, 'core_notes_create_notes');

		break;

		case 'School_Setup/PortalNotes.php|update_portal_note':
			global $columns;

			//update note if title or content modified
			if (isset($columns['TITLE']) || isset($columns['CONTENT']))
				Moodle($modname, 'core_notes_update_notes');

		break;

		case 'School_Setup/PortalNotes.php|delete_portal_note':
			Moodle($modname, 'core_notes_delete_notes');

		break;

		/*School_Setup/Rollover.php*/
		case 'School_Setup/Rollover.php|rollover_checks':
			//prevent RE roll staff or courses (creates DB incoherence)
			
			global $table, $exists_RET, $error;

			if (($table == 'COURSES' && $exists_RET['COURSES'][1]['COUNT']) || ($table == 'STAFF' && $exists_RET['STAFF'][1]['COUNT']))
				$error[] = 'Moodle: '._('You cannot re-roll Users or Courses');

		break;

		case 'School_Setup/Rollover.php|rollover_staff':
			global $next_syear;

			$staff_RET = DBGet(DBQuery("SELECT STAFF_ID,ROLLOVER_ID FROM STAFF WHERE SYEAR='".$next_syear."' AND ROLLOVER_ID IS NOT NULL"));

			foreach($staff_RET as $value)
				DBQuery("UPDATE MOODLEXROSARIO SET ROSARIO_ID='".$value['STAFF_ID']."' WHERE ROSARIO_ID='".$value['ROLLOVER_ID']."' AND \"column\"='staff_id'");

		break;

		case 'School_Setup/Rollover.php|rollover_course_subjects':
			global $next_syear;

			$course_subjects_RET = DBGet(DBQuery("SELECT SUBJECT_ID,ROLLOVER_ID FROM COURSES WHERE SYEAR='".$next_syear."' AND SCHOOL_ID='".UserSchool()."' AND ROLLOVER_ID IS NOT NULL"));

			foreach($course_subjects_RET as $value)
				DBQuery("UPDATE MOODLEXROSARIO SET ROSARIO_ID='".$value['SUBJECT_ID']."' WHERE ROSARIO_ID='".$value['ROLLOVER_ID']."' AND \"column\"='subject_id' AND SCHOOL_ID='".UserSchool()."'");

		break;

		case 'School_Setup/Rollover.php|rollover_courses':
			global $next_syear;

			$courses_RET = DBGet(DBQuery("SELECT COURSE_ID,ROLLOVER_ID FROM COURSES WHERE SYEAR='".$next_syear."' AND SCHOOL_ID='".UserSchool()."' AND ROLLOVER_ID IS NOT NULL"));

			foreach($courses_RET as $value)
				DBQuery("UPDATE MOODLEXROSARIO SET ROSARIO_ID='".$value['COURSE_ID']."' WHERE ROSARIO_ID='".$value['ROLLOVER_ID']."' AND \"column\"='course_id'");

		break;

		case 'School_Setup/Rollover.php|rollover_course_periods':
			global $next_syear, $rolled_course_period;

			$course_periods_RET = DBGet(DBQuery("SELECT cp.COURSE_PERIOD_ID, cp.COURSE_ID, cp.SHORT_NAME, cp.MARKING_PERIOD_ID, cp.TEACHER_ID FROM COURSE_PERIODS cp, MOODLEXROSARIO mxc WHERE cp.SYEAR='".$next_syear."' AND cp.SCHOOL_ID='".UserSchool()."' AND cp.ROLLOVER_ID IS NOT NULL AND cp.ROLLOVER_ID=mxc.ROSARIO_ID AND mxc.\"column\"='course_period_id'"));

			foreach($course_periods_RET as $rolled_course_period)
			{
				Moodle($modname, 'core_course_create_courses');
				Moodle($modname, 'core_role_assign_roles');
			}

		break;

		default:
			return false;
	}

	return true;
}

//modif Francois: Moodle integrator

//The function {moodle_functionname}_object() is in charge of creating the object
//The function moodle_xmlrpc_call() sends the object to Moodle via XML-RPC
function Moodle($modname, $moodle_functionname)
{
	require_once('plugins/Moodle/'.$modname);
	require_once('plugins/Moodle/client.php');

	//first, get the right object corresponding to the web service
	$object = call_user_func($moodle_functionname.'_object');

	//finally, send the object
	moodle_xmlrpc_call($moodle_functionname, $object);
}

//modif Francois: Moodle integrator / password
//The password must have at least 8 characters, at least 1 digit, at least 1 lower case letter, at least 1 upper case letter, at least 1 non-alphanumeric character
function MoodlePasswordCheck($password)
{
	if (mb_strlen($password)<8 || !preg_match('/[^a-zA-Z0-9]+/', $password) || !preg_match('/[a-z]+/', $password) || !preg_match('/[A-Z]+/', $password) || !preg_match('/[0-9]+/', $password))
	{
		return false;
	}
	return true;
}

function IsMoodleStudent($student_id)
{
	return count(DBGet(DBQuery("SELECT 1 FROM moodlexrosario WHERE rosario_id='".$student_id."' AND \"column\"='student_id'")));
}

function IsMoodleUser($staff_id)
{
	return count(DBGet(DBQuery("SELECT 1 FROM moodlexrosario WHERE rosario_id='".$staff_id."' AND \"column\"='staff_id'")));
}

function IsMoodleCourse($course_id)
{
	return count(DBGet(DBQuery("SELECT 1 FROM moodlexrosario WHERE rosario_id='".$course_id."' AND \"column\"='course_id'")));
}

function IsMoodleCoursePeriod($course_period_id)
{
	return count(DBGet(DBQuery("SELECT 1 FROM moodlexrosario WHERE rosario_id='".$course_period_id."' AND \"column\"='course_period_id'")));
}

?>
