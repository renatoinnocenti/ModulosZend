<?php
function defultRoles($username) {
	switch ($username) {
		case - 1 :
			$rules [0] = 'banned user';
			break;
		case 0 :
			$rules [ANONYMOUS_ID_ROLE] = 'anonymous user';
			break;
		default :
			$rules [AUTHENTICATED_ID_ROLE] = 'authenticated user';
			break;
	}
	return $rules;
}
function myRoles($username, $defult = array(ANONYMOUS_ID_ROLE=>'anonymous user')) {
	global $cfg;
	if ($username >= 0) {
		$mysql = new MYSQL ( $cfg );
		$key = key ( $defult );
		$valor = array_values ( $defult );
		$sql = "
			SELECT r.ID_ROLE, r.name, p.permission, (SELECT permission FROM {permission} WHERE ID_ROLE = '{$key}')as `{$valor[0]}`
			FROM {users_roles} ur
			NATURAL JOIN {role} r
			LEFT JOIN {permission} p ON p.ID_ROLE = ur.ID_ROLE
			WHERE ID_USER = '{$username}' ORDER BY r.weight DESC
			";
		$result = $mysql->SqlSelect ( $sql );
		$perm ['permission'] = array ();
		while ( $row = mysql_fetch_assoc ( $result ) ) {
			$perm ['permission'] = explode ( ",", $row ['permission'] ) + explode ( ",", $row [$valor [0]] );
			$perm [$row ['ID_ROLE']] = $row ['name'];
		}
		return $perm;
	}
}