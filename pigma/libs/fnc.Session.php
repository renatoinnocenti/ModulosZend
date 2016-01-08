<?php
// $Id: session.inc,v 1.44.2.7 2010/03/04 00:15:28 goba Exp $

/**
 * @file
 * User session handling functions.
 */

function sess_open($save_path, $session_name) {
  return TRUE;
}

function sess_close() {
  return TRUE;
}

function sess_read($key) {
  global $perfil,$cfg;

  // Write and Close handlers are called after destructing objects since PHP 5.0.5
  // Thus destructors can use sessions but session handler can't use objects.
  // So we are moving session closure before destructing objects.
  register_shutdown_function('session_write_close');

  // Handle the case of first time visitors and clients that don't store cookies (eg. web crawlers).
  if (!isset($_COOKIE[session_name()])) {
    $perfil = setGuest();
    return '';
  }
	$mysql = new MYSQL($cfg);
  // Otherwise, if the session is still active, we have a record of the client's session in the database.
  $perfil = mysql_fetch_object($mysql->SqlSelect("SELECT u.*, s.* FROM {users} u INNER JOIN {sessions} s ON u.ID_USER = s.ID_USER WHERE s.ID_SESSION = '%s'", $key));

  // We found the client's session record and they are an authenticated,
  // active user.
  if ($perfil && $perfil->ID_USER > 0 && $perfil->status == 1) {
    // This is done to unserialize the data member of $perfil
    $perfil = SB_unpack($perfil);

    // Add roles element to $perfil
    $perfil->roles = array();
    $perfil->roles[DRUPAL_AUTHENTICATED_RID] = 'authenticated user';
    $result = $mysql->SqlSelect("SELECT r.ID_ROLE, r.name FROM {role} r INNER JOIN {users_roles} ur ON ur.ID_ROLE = r.ID_ROLE WHERE ur.ID_USER = %d", $perfil->ID_USER);
    while ($role = mysql_fetch_object($result)) {
      $perfil->roles[$role->ID_ROLE] = $role->name;
    }
  }
  // We didn't find the client's record (session has expired), or they are
  // blocked, or they are an anonymous user.
  else {
    $session = isset($perfil->session) ? $perfil->session : '';
    $perfil = setGuest($session);
  }

  return $perfil->session;
}

function sess_write($key, $value) {
 global $perfil,$cfg;

 $mysql = new MYSQL($cfg); 
 // If saving of session data is disabled or if the client doesn't have a session,
  // and one isn't being created ($value), do nothing. This keeps crawlers out of
  // the session table. This reduces memory and server load, and gives more useful
  // statistics. We can't eliminate anonymous session table rows without breaking
  // the throttle module and the "Who's Online" block.
  if (!session_save_session() || ($perfil->ID_USER == 0 && empty($_COOKIE[session_name()]) && empty($value))) {
    return TRUE;
  }

  $mysql->SqlSelect("UPDATE {sessions} SET ID_USER = %d, cache = %d, hostname = '%s', session = '%s', timestamp = %d WHERE ID_SESSION = '%s'", $perfil->ID_USER, isset($perfil->cache) ? $perfil->cache : '', ip_address(), $value, time(), $key);
  if (mysql_affected_rows()) {
    // Last access time is updated no more frequently than once every 180 seconds.
    // This reduces contention in the users table.
    if ($perfil->ID_USER && time() - $perfil->access > variable_get('session_write_interval', 180)) {
      $mysql->SqlSelect("UPDATE {users} SET access = %d WHERE ID_USER = %d", time(), $perfil->ID_USER);
    }
  }
  else {
    // If this query fails, another parallel request probably got here first.
    // In that case, any session data generated in this request is discarded.
    @$mysql->SqlSelect("INSERT INTO {sessions} (ID_SESSION, ID_USER, cache, hostname, session, timestamp) VALUES ('%s', %d, %d, '%s', '%s', %d)", $key, $perfil->ID_USER, isset($perfil->cache) ? $perfil->cache : '', ip_address(), $value, time());
  }

  return TRUE;
}

/**
 * Called when an anonymous user becomes authenticated or vice-versa.
 */
function sess_regenerate() {
  global $perfil,$cfg;

 $mysql = new MYSQL($cfg); 
	$old_session_id = session_id();

  // We code around http://bugs.php.net/bug.php?id=32802 by destroying
  // the session cookie by setting expiration in the past (a negative
  // value).  This issue only arises in PHP versions before 4.4.0,
  // regardless of the Drupal configuration.
  // TODO: remove this when we require at least PHP 4.4.0
  if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
  }

  session_regenerate_id();

  $mysql->SqlSelect("UPDATE {sessions} SET ID_SESSION = '%s' WHERE ID_SESSION = '%s'", session_id(), $old_session_id);
}

/**
 * Counts how many users have sessions. Can count either anonymous sessions or authenticated sessions.
 *
 * @param int $timestamp
 *   A Unix timestamp representing a point of time in the past.
 *   The default is 0, which counts all existing sessions.
 * @param boolean $anonymous
 *   TRUE counts only anonymous users.
 *   FALSE counts only authenticated users.
 * @return  int
 *   The number of users with sessions.
 */
function sess_count($timestamp = 0, $anonymous = true) {
  global $cfg;
 $mysql = new MYSQL($cfg);
	$query = $anonymous ? ' AND ID_USER = 0' : ' AND ID_USER > 0';
  return $mysql->dbResult($mysql->SqlSelect('SELECT COUNT(ID_SESSION) AS count FROM {sessions} WHERE timestamp >= %d'. $query, $timestamp));
}

/**
 * Called by PHP session handling with the PHP session ID to end a user's session.
 *
 * @param  string $sid
 *   the session id
 */
function sess_destroy_sid($sid) {
  global $cfg;
 $mysql = new MYSQL($cfg);
	$mysql->SqlSelect("DELETE FROM {sessions} WHERE ID_SESSION = '%s'", $sid);
}

/**
 * End a specific user's session
 *
 * @param  string $ID_USER
 *   the user id
 */
function sess_destroy_uid($uid) {
  global $cfg;

 $mysql = new MYSQL($cfg);
	$mysql->SqlSelect('DELETE FROM {sessions} WHERE ID_USER = %d', $uid);
}

function sess_gc($lifetime) {
  global $cfg;

 $mysql = new MYSQL($cfg);
	// Be sure to adjust 'php_value session.gc_maxlifetime' to a large enough
  // value. For example, if you want user sessions to stay in your database
  // for three weeks before deleting them, you need to set gc_maxlifetime
  // to '1814400'. At that value, only after a user doesn't log in after
  // three weeks (1814400 seconds) will his/her session be removed.
  $mysql->SqlSelect("DELETE FROM {sessions} WHERE timestamp < %d", time() - $lifetime);

  return TRUE;
}

/**
 * Determine whether to save session data of the current request.
 *
 * This function allows the caller to temporarily disable writing of session data,
 * should the request end while performing potentially dangerous operations, such as
 * manipulating the global $perfil object.  See http://drupal.org/node/218104 for usage
 *
 * @param $status
 *   Disables writing of session data when FALSE, (re-)enables writing when TRUE.
 * @return
 *   FALSE if writing session data has been disabled. Otherwise, TRUE.
 */
function session_save_session($status = NULL) {
  static $save_session = TRUE;
  if (isset($status)) {
    $save_session = $status;
  }
  return ($save_session);
}
