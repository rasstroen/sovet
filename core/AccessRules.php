<?php

class AccessRules {

	public static $actions = array(
	    // false if cant, true if can, role(number) for maximum role for this action to target user
	    User::ROLE_ANON => array(
		'add_comments' => false,
		'edit_news' => false,
	    ),
	    User::ROLE_VANDAL => array(
		'add_comments' => false,
		'edit_news' => false,
	    ),
	    User::ROLE_READER_UNCONFIRMED => array(
		'add_comments' => true,
		'edit_news' => false,
	    ),
	    User::ROLE_READER_CONFIRMED => array(
		'add_comments' => true,
		'edit_news' => false,
	    ),
	    User::ROLE_MODERATOR => array(
		'add_comments' => true,
		'edit_news' => false,
	    ),
	    User::ROLE_SITE_ADMIN => array(
		'add_comments' => true,
		'edit_news' => true,
	    ),
	);

	public static function can($user, $action, $target_user = false, $throwError = false) {
		/* @var $user User */
		$user_role = max(User::ROLE_ANON, $user->getRole());
		if (!isset(self::$actions[$user_role]))
			throw new Exception('no role #' . $user_role . ' AccessRules::can()');

		if (!isset(self::$actions[$user_role][$action]))
			throw new Exception('no action #' . $action . ' AccessRules::can() for role #' . $user_role);

		$rule = self::$actions[$user_role][$action];
		if ($rule === false) {
			return $throwError ? Error::CheckThrowAuth() : false;
		}
		if ($rule === true) {
			return true;
		}
		// if it is user's role
		if ($rule >= $target_user->getRole()) {
			return true;
		}
		return $throwError ? Error::CheckThrowAuth($rule) : false;
	}

	public static function getRules() {
		global $current_user;
		/* @var $current_user CurrentUser */
		$role = $current_user->authorized ? $current_user->getRole() : User::ROLE_ANON;
		$a = self::$actions[$role];
		$out = array();
		foreach ($a as $rule => $value) {
			if ($value !== false) {
				if ($value === true) {
					$out[$rule] = array();
				} else {
					$out[$rule] = array('max_role' => $value);
				}
			}
		}
		return $out;
	}

}