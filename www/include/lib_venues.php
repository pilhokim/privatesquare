<?php

	#################################################################

	function venues_get_by_venue_id($id){

		$enc_id = AddSlashes($id);
		$sql = "SELECT * FROM Venues WHERE venue_id='{$enc_id}'";

		$rsp = db_fetch($sql);
		$row = db_single($rsp);

		return $row;
	}

	#################################################################

	function venues_get_by_venue_id_for_provider($venue_id, $provider_id){

		$enc_provider = AddSlashes($provider_id);
		$enc_venue = AddSlashes($venue_id);

		$sql = "SELECT * FROM Venues WHERE provider_id='{$enc_id}' AND provider_venue_id='{$enc_venue}'";

		$rsp = db_fetch($sql);
		$row = db_single($rsp);

		return $row;
	}

	#################################################################

	function venues_archive_venue_for_provider($venue_id, $provider_id){

		$map = venues_providers_map();
		$provider = $map[$provider_id];

		if ($provider == 'foursquare'){

			$rsp = venues_foursquare_fetch_venue($venue_id);

			if (! $rsp['ok']){
				return $rsp;
			}

			$data = $rsp['rsp']['venue'];

			$lat = $data['location']['lat'];
			$lon = $data['location']['lng'];

			$venue = array(
				'venue_id' => $data['id'],
				'name' => $data['name'],
				'latitude' => $lat,
				'longitude' => $lon,
				'data' => json_encode($data),
			);
		}

		else if ($provider == 'stateofmind'){

			$data = venues_stateofmind_fetch_venue($venue_id);

			$venue = array(
				'venue_id' => $data['id'],
				'name' => $data['name'],
				'data' => json_encode($data),
			);
		}

		else {
			return array('ok' => 0, 'error' => 'Unknown provider');
		}

		if ((isset($venue['latitude'])) && (isset($venue['longitude']))){
			venues_geo_append_hierarchy($venue['latitude'], $venue['longitude'], $venue);
		}

		return venues_add_venue($venue);
	}

	#################################################################

	function venues_add_venue($venue){

		$rsp = privatesquare_utils_generate_id(64);

		if (! $rsp['ok']){
			return $rsp;
		}

		$venue['id'] = $rsp['id'];

		$insert = array();

		foreach ($venue as $k => $v){
			$insert[$k] = AddSlashes($v);
		}	

		$rsp = db_insert('Venues', $insert);

		if (($rsp['ok']) || ($rsp['error_code'] == 1062)){
			$rsp['venue'] = $venue;
		}

		return $rsp;
	}

	#################################################################

	# the end