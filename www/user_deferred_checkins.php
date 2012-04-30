<?php

	include("include/init.php");

	loadlib("foursquare_users");

	if (! $GLOBALS['cfg']['enable_feature_deferred_checkins']){
		error_disabled();
	}

	$fsq_id = get_int32("foursquare_id");

	if (! $fsq_id){
		error_404();
	}

	login_ensure_loggedin();

	$fsq_user = foursquare_users_get_by_foursquare_id($fsq_id);

	if (! $fsq_user){
		error_404();
	}

	$owner = users_get_by_id($fsq_user['user_id']);
	$is_own = ($owner['id'] == $GLOBALS['cfg']['user']['id']) ? 1 : 0;

	# for now...

	if (! $is_own){
		error_403();
	}

	$GLOBALS['smarty']->assign_by_ref("owner", $owner);
	$GLOBALS['smarty']->assign_by_ref("is_own", $is_own);

	$search_crumb = crumb_generate("api", "privatesquare.venues.search");
	$GLOBALS['smarty']->assign("search_crumb", $search_crumb);

	$GLOBALS['smarty']->display("page_user_deferred_checkins.txt");
	exit();
?>