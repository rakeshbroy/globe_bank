<?php

//subjects

function find_all_subjects($options=[]) {

	global $db;

	$visible = $options['visible'] ?? false;

	$sql = "select * from subjects ";

	if($visible) {
		$sql .= "WHERE visible = true ";
	}

	$sql .= "order by position asc";
	$result = mysqli_query($db, $sql);
	confirm_result_set($result);
	return $result;
}

function find_subject_by_id($id, $options=[]) {

	global $db;

	$visible = $options['visible'] ?? false;

	$sql = "SELECT * FROM subjects ";
	$sql .= "WHERE id ='" . db_escape($db, $id) . "' ";

	if($visible) {
		$sql .= "AND visible = true";
	}
	// echo $sql;
	$result = mysqli_query($db, $sql);
	confirm_result_set($result);

	$subject = mysqli_fetch_assoc($result);
	mysqli_free_result($result);
	return $subject;
}

function shift_subject_position($start_pos, $end_pos, $current_id=0) {
	global $db;

	if($start_pos == $end_pos) { return; }

	$sql = "update subjects ";
	if($start_pos == 0) {
		// new item, +1 to items greater than $end_pos
		$sql .= "set position = position +1 ";
		$sql .= "where position >= '" . db_escape($db, $end_pos) . "' ";
	} elseif ($end_pos == 0) {
		// delete item, -1 to items greater than $start_pos
		$sql .= "set position = position - 1 ";
		$sql .= "where position > '" . db_escape($db, $start_pos) . "' ";
	} elseif ($start_pos < $end_pos) {
		// move later, -1 from item between including $end_pos
		$sql .= "set position = position - 1 ";
		$sql .= "where position > '" . db_escape($db, $start_pos) . "' ";
		$sql .= "and position <= '" . db_escape($db, $end_pos) . "' ";
	} elseif ($start_pos > $end_pos) {
		// move earlier, +1 to items between (including $end_pos)
		$sql .= "set position = position + 1 ";
		$sql .= "where position >= '" . db_escape($db, $end_pos) ."' ";
		$sql .= "and position < '" . db_escape($db, $start_pos) . "' ";
	}
	// Exclude the curent_id in the sql where clause
	$sql .= "and id != '" . db_escape($db, $current_id) . "' ";

	$result = mysqli_query($db, $sql);
	// For update statement, $result is true/false
	if($result) {
		return true;
	} else {
		// update failed
		echo mysqli_error();
		db_disconnect();
		exit;
	}
}

function validate_subject($subject) {

	$errors = [];
	// menu_name
	if(is_blank($subject['menu_name'])) {
		$errors[] = "Name can not be blank.";
	}elseif(!has_length($subject['menu_name'],['min' => 2, 'max' => 225])) {
		$errors[] = "Name must be between 2 and 255 characters.";
	}

	// positon
	// Make sure we are working with integer
	$position_int = (int) $subject['position'];

	if($position_int <= 0) {
		$errors[] = "Position must be greater than zero.";
	}
	if($position_int > 999) {
		$errors[] = "Position must be less than 999.";
	}

	// visible
	// Make sure we are working with a string
	$visible_str = (string) $subject['visible'];
	if(!has_inclusion_of($visible_str,["0", "1"])) {
		$errors[] = "Visible must be true or false.";
	}

	return $errors;
}

function insert_subject($subject) {

	global $db;

	$errors = validate_subject($subject);

	if(!empty($errors)) {
		return $errors;
	}

	shift_subject_position(0, $subject['position']);

	$sql = "insert into subjects ";
	$sql .= "(menu_name, position, visible) ";
	$sql .= "values(";
	$sql .= "'" . db_escape($db, $subject['menu_name']) . "', ";
	$sql .= "'" . db_escape($db, $subject['position']) . "', ";
	$sql .= "'" . db_escape($db, $subject['visible']) . "'";
	$sql .= ")";

	$result = mysqli_query($db, $sql);
	//For INSERT statement, result is true/false
	if($result) {
		return true;
	} else {
		//INSERT failed
		echo mysqli_error($db);
		db_disconnect($db);
		exit;
	}
}

function update_subject($subject) {
	global $db;

	$errors = validate_subject($subject);

	if(!empty($errors)) {
		return $errors;
	}

	$old_subject = find_subject_by_id($subject['id']);
	$old_position = $old_subject['position'];
	shift_subject_position($old_position, $subject['position'], $subject['id']);


	$sql = "UPDATE subjects SET ";
	$sql .= "menu_name='" . db_escape($db, $subject['menu_name']) . "', ";
	$sql .= "position='" . db_escape($db, $subject['position']) . "', ";
	$sql .= "visible='" . db_escape($db, $subject['visible']) . "' ";
	$sql .= "WHERE id='" . db_escape($db, $subject['id']) ."' ";
	$sql .= "LIMIT 1";

	$result = mysqli_query($db, $sql);

	if($result) {
		
		return true;

	} else {
		//UPDATE failed
		echo mysqli_error($db);
		db_disconnect($db);
		exit;
	}
}

function validate_delete_subject($id) {

	$errors = [];

	if(is_referenced($id)) {
		$errors[] = "Subject is being refereced.";
		$errors[] = "Related pages must be deleted first.";
	}

	return $errors;

}

function delete_subject($id) {
	global $db;

	$errors = validate_delete_subject($id);

	if(!empty($errors)) {
		return $errors;
	}

	$old_subject = find_subject_by_id($id);
	$old_position = $old_subject['position'];
	shift_subject_position($old_position, 0, $id);

	$sql = "DELETE FROM subjects ";
	$sql .= "WHERE id='" . db_escape($db, $id) ."' ";
	$sql .= "LIMIT 1";

	$result = mysqli_query($db, $sql);

	if($result) {
		return true;
	} else {
		//DELETE failed
		echo mysqli_error($db);
		db_disconnect($db);
		exit;
	}
}

//pages

function find_all_pages() {

	global $db;

	$sql = "select * from pages ";
	$sql .= "order by subject_id asc, position asc";
	$result = mysqli_query($db, $sql);
	confirm_result_set($result);
	return $result;
}

function find_page_by_id($id, $option=[]) {

	global $db;

	$visible = $option['visible'] ?? false;

	$sql = "SELECT * FROM pages ";
	$sql .= "WHERE id ='" . db_escape($db, $id) . "' ";

	if($visible) {
		$sql .= "AND visible = true";
	}

	$result = mysqli_query($db, $sql);
	confirm_result_set($result);

	$page = mysqli_fetch_assoc($result);
	mysqli_free_result($result);
	return $page;
}

function validate_page($page) {

	$errors = [];
	// subject_id
	if(is_blank($page['subject_id'])) {
		$errors[] = "Subject can't be blank.";
	}

	// menu_name
	if(is_blank($page['menu_name'])) {
		$errors[] = "Name can't be blank.";
	} elseif(!has_length($page['menu_name'],['min' => 2, 'max' => 225])) {
		$errors[] = "Name must be between 2 and 225 characters.";
	}
	$current_id = $page['id'] ?? '0';
	if(!has_unique_page_menu_name($page['menu_name'], $current_id)) {
		$errors[] = "Menu name must be unique.";
	}

	// position
	// Make sure we are working with an integer.
	$position_int = (int) $page['position'];

	if($position_int <= 0) {
		$errors[] = "Position must be greater than zero.";
	}
	if($position_int > 999) {
		$errors[] = "Position must be less than 999.";
	}

	// visible
	// Make sure we are working with a string.
	$visible_str = (string) $page['visible'];
	if(!has_inclusion_of($page['visible'],["0", "1"])) {
		$errors[] = "Visible must be TRUE or FALSE.";
	}

	// content
	if(is_blank($page['content'])) {
		$errors[] = "Content can't be blank.";
	}

	return $errors;
}

function shift_page_position($start_pos, $end_pos, $subject_id, $current_id=0) {
	global $db;

	if($start_pos == $end_pos) { return; }

	$sql = "update pages ";
	if($start_pos == 0) {
		// new item, +1 to items greater than $end_pos
		$sql .= "set position = position +1 ";
		$sql .= "where position >= '" . db_escape($db, $end_pos) . "' ";
	} elseif ($end_pos == 0) {
		// delete item, -1 to items greater than $start_pos
		$sql .= "set position = position - 1 ";
		$sql .= "where position > '" . db_escape($db, $start_pos) . "' ";
	} elseif ($start_pos < $end_pos) {
		// move later, -1 from item between including $end_pos
		$sql .= "set position = position - 1 ";
		$sql .= "where position > '" . db_escape($db, $start_pos) . "' ";
		$sql .= "and position <= '" . db_escape($db, $end_pos) . "' ";
	} elseif ($start_pos > $end_pos) {
		// move earlier, +1 to items between (including $end_pos)
		$sql .= "set position = position + 1 ";
		$sql .= "where position >= '" . db_escape($db, $end_pos) ."' ";
		$sql .= "and position < '" . db_escape($db, $start_pos) . "' ";
	}
	// Exclude the curent_id in the sql where clause
	$sql .= "and id != '" . db_escape($db, $current_id) . "' ";
	$sql .= "and subject_id ='" . db_escape($db, $subject_id) . "' ";

	$result = mysqli_query($db, $sql);
	// For update statement, $result is true/false
	if($result) {
		return true;
	} else {
		// update failed
		echo mysqli_error();
		db_disconnect();
		exit;
	}
}

function insert_page($page) {

	global $db;

	$errors = validate_page($page);

	if(!empty($errors)) {
		return $errors;
	}

	shift_page_position(0, $page['position'], $page['subject_id']);

	$sql = "insert into pages ";
	$sql .= "(subject_id, menu_name, position, visible, content) ";
	$sql .= "values(";
	$sql .= "'" . db_escape($db, $page['subject_id']) . "', ";
	$sql .= "'" . db_escape($db, $page['menu_name']) . "', ";
	$sql .= "'" . db_escape($db, $page['position']) . "', ";
	$sql .= "'" . db_escape($db, $page['visible']) . "', ";
	$sql .= "'" . db_escape($db, $page['content']) . "'";
	$sql .= ")";

	$result = mysqli_query($db, $sql);
	//For INSERT statement, result is true/false
	if($result) {
		return true;
	} else {
		//INSERT failed
		echo mysqli_error($db);
		db_disconnect($db);
		exit;
	}
}

function update_page($page) {
	global $db;

	$errors = validate_page($page);

	if(!empty($errors)) {
		return $errors;
	}

	$old_page = find_page_by_id($page['id']);
	$old_position = $old_page['position'];
	shift_page_position($old_position, $page['position'], $page['subject_id']);

	$sql = "UPDATE pages SET ";
	$sql .= "subject_id='" . db_escape($db, $page['subject_id']) . "', ";
	$sql .= "menu_name='" . db_escape($db, $page['menu_name']) . "', ";
	$sql .= "position='" . db_escape($db, $page['position']) . "', ";
	$sql .= "visible='" . db_escape($db, $page['visible']) . "', ";
	$sql .= "content='" . db_escape($db, $page['content']) . "' ";
	$sql .= "WHERE id='" . db_escape($db, $page['id']) ."' ";
	$sql .= "LIMIT 1";

	$result = mysqli_query($db, $sql);

	if($result) {
		
		return true;

	} else {
		//UPDATE failed
		echo mysqli_error($db);
		db_disconnect($db);
		exit;
	}
}

function delete_page($id) {
	global $db;

	$old_page = find_page_by_id($id);
	$old_position = $old_page['position'];
	shift_page_position($old_position, 0, $page['subject_id'], $id);

	$sql = "DELETE FROM pages ";
	$sql .= "WHERE id='" . db_escape($db, $id) ."' ";
	$sql .= "LIMIT 1";

	$result = mysqli_query($db, $sql);

	if($result) {
		return true;
	} else {
		//DELETE failed
		echo mysqli_error($db);
		db_disconnect($db);
		exit;
	}
}

function find_page_by_subject_id($subject_id, $options=[]) {

	global $db;

	$visible = $options['visible'] ?? false;

	$sql = "SELECT * FROM pages ";
	$sql .= "WHERE subject_id ='" . db_escape($db, $subject_id) . "' ";

	if($visible) {
		$sql .= "AND visible = true ";
	}

	$sql .= "ORDER BY POSITION ASC";
	$result = mysqli_query($db, $sql);
	confirm_result_set($result);
	return $result;
}

function count_page_by_subject_id($subject_id, $options=[]) {

	global $db;

	$visible = $options['visible'] ?? false;

	$sql = "SELECT count(id) FROM pages ";
	$sql .= "WHERE subject_id ='" . db_escape($db, $subject_id) . "' ";

	if($visible) {
		$sql .= "AND visible = true ";
	}

	$sql .= "ORDER BY POSITION ASC";
	$result = mysqli_query($db, $sql);
	confirm_result_set($result);
	$row = mysqli_fetch_row($result);
	mysqli_free_result($result);
	$count = $row[0];
	return $count;
}

// admins


// Find all admins, orderd by last_name, first_name
function find_all_admins() {
	global $db;

	$sql = "select * from admins ";
	$sql .= "order by last_name asc, first_name asc";
	$result = mysqli_query($db, $sql);
	confirm_result_set($result);
	return $result;
}

function find_admin_by_id($id) {
	global $db;

	$sql = "select * from admins ";
	$sql .= "where id='" . db_escape($db, $id) . "' ";
	$sql .= "LIMIT 1";
	$result = mysqli_query($db, $sql);
	confirm_result_set($result);
	$admin = mysqli_fetch_assoc($result); // find first
	mysqli_free_result($result); 
	return $admin; // returns an assoc. array
}

function find_admin_by_username($username) {
	global $db;

	$sql = "select * from admins ";
	$sql .= "where username='" . db_escape($db, $username) . "' ";
	$sql .= "LIMIT 1";
	$result = mysqli_query($db, $sql);
	confirm_result_set($result);
	$admin = mysqli_fetch_assoc($result); // find first
	mysqli_free_result($result); 
	return $admin; // returns an assoc. array
}

function validate_admin($admin, $options=[]) {
	$errors = [];

	$password_required = $options['password_required'] ?? true;

	// first_name
	if(is_blank($admin['first_name'])) {
		$errors[] = "First name can't be blank.";
	} elseif(!has_length($admin['first_name'], ['min' => 2, 'max' => 255])) {
		$errors[] = "First name must be between 2 and 225 characters.";
	}

	// last_name
	if(is_blank($admin['last_name'])) {
		$errors[] = "Last name can't be blank.";
	} elseif(!has_length($admin['last_name'], ['min' => 2, 'max' => 255])) {
		$errors[] = "Last name must be between 2 and 225 characters.";
	}

	// email
	if(is_blank($admin['email'])) {
		$errors[] = "Email can't be blank.";
	} elseif(!has_length($admin['email'], ['min' => 2, 'max' => 255])) {
		$errors[] = "First name must be between 2 and 225 characters.";
	} elseif(!has_valid_email_format($admin['email'])) {
		$errors[] = "Email must be a valid format.";
	}

	// username
	if(is_blank($admin['username'])) {
		$errors[] = "Username can't be blank.";
	} elseif(!has_length($admin['username'], ['min' => 8, 'max' => 255])) {
		$errors[] = "User Name must be between 8 and 225 characters.";
	} elseif(!has_unique_username($admin['username'], $admin['id'] ?? 0)) {
		$errors[] = "Username not allowed. Try another.";
	}

	// password
	if($password_required) {

		if(is_blank($admin['password'])) {
			$errors[] = "Password can't be blank.";
		} elseif(!has_length($admin['password'], array('min' => 12))) {
			$errors[] = "Password must contain 12 or more characters.";
		} elseif (!preg_match('/[A-Z]/', $admin['password'])) {
			$errors[] = "Password must contain at least 1 uppercase letter.";
		} elseif(!preg_match('/[a-z]/', $admin['password'])) {
			$errors[] = "Password must contain at least 1 smallcase letter.";
		} elseif(!preg_match('/[0-9]/', $admin['password'])) {
			$errors[] = "Password must contain at least 1 number.";
		} elseif(!preg_match('/[^A-Za-z0-9\s]/', $admin['password'])) {
			$errors[] = "Password must contain at least 1 symbol.";
		}

		// confirm_password
		if(is_blank($admin['confirm_password'])) {
			$errors[] = "Confirm password cannot be blnak.";
		} elseif($admin['password'] !== $admin['confirm_password']) {
			$errors[] = "Password and confirm password must match.";
		}

	}

	return $errors;
}

function insert_admin($admin) {
	global $db;

	$errors = validate_admin($admin);

	if(!empty($errors)) {
		return $errors;
	}

	$hashed_password = password_hash($admin['password'], PASSWORD_BCRYPT);

	$sql = "insert into admins ";
	$sql .= "(first_name, last_name, email, username, hashed_password) ";
	$sql .= "values(";
	$sql .= "'" . db_escape($db, $admin['first_name']) . "', ";
	$sql .= "'" . db_escape($db, $admin['last_name']) . "', ";
	$sql .= "'" . db_escape($db, $admin['email']) . "', ";
 	$sql .= "'" . db_escape($db, $admin['username']) . "', ";
	$sql .= "'" . db_escape($db, $hashed_password) . "' ";
	$sql .= ")";

	$result = mysqli_query($db, $sql);
	// For INSERT statement, result is true/false

	if($result) {
		return true;
	} else {
		// INSERT failed
		echo mysqli_error($db);
		db_disconnect($db);
		exit;
	}
}

function update_admin($admin) {
	global $db;

	$password_sent = !is_blank($admin['password']);

	$errors = validate_admin($admin, ['password_required' => $password_sent]);

	if(!empty($errors)) {
		return $errors;
	}

	$hashed_password = password_hash($admin['password'], PASSWORD_BCRYPT);

	$sql = "UPDATE admins SET ";
	$sql .= "first_name='" . db_escape($db, $admin['first_name']) . "', ";
	$sql .= "last_name='" . db_escape($db, $admin['last_name']) . "', ";
	$sql .= "email='" . db_escape($db, $admin['email']) . "', ";

	if($password_sent) {

		$sql .= "hashed_password='" . db_escape($db, $hashed_password) . "' ";

	}

	$sql .= "username='" . db_escape($db, $admin['username']) . "' ";
	$sql .= "WHERE id='" . db_escape($db, $admin['id']) ."' ";
	$sql .= "LIMIT 1";

	$result = mysqli_query($db, $sql);

	if($result) {
		
		return true;

	} else {
		//UPDATE failed
		echo mysqli_error($db);
		db_disconnect($db);
		exit;
	}
}

function delete_admin($admin) {
	global $db;

	$sql = "DELETE FROM admins ";
	$sql .= "WHERE id='" . db_escape($db, $admin['id']) ."' ";
	$sql .= "LIMIT 1";

	$result = mysqli_query($db, $sql);

	if($result) {
		return true;
	} else {
		//DELETE failed
		echo mysqli_error($db);
		db_disconnect($db);
		exit;
	}
}

?>