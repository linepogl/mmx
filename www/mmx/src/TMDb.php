<?php

define('DAILY',XDate::Today()->Format('Ymd'));
define('WEEKLY',XDate::Today()->Format('Y\wW'));
define('MONTHLY',XDate::Today()->Format('Ym'));

define('ACTOR_FOLDER',Oxygen::GetDataFolder().'/actor');
define('MOVIE_FOLDER',Oxygen::GetDataFolder().'/movie');
define('CHAIN_FOLDER',Oxygen::GetDataFolder().'/chain');

class TMDb {
	private static $api_base = 'http://api.themoviedb.org/3/';
	private static $api_key = '5575cbca2608451f50c914555304c4d8';

	private static $calls = [];
	private static $checked_nulls = [];
	private static function Call($service,$cache_key = null){
		$use_cache = $cache_key !== null;
		$key = "TMDb:$cache_key:$service";
		$r = $use_cache ? Scope::$APPLICATION[$key] : null;
		if ($r === null && !array_key_exists($key,self::$checked_nulls)) {
			$url = self::$api_base . $service . (strrpos($service,'?')===false?'?':'&') . 'api_key='. self::$api_key.'&lang=en&include_adult=true';
			self::$calls[] = $url;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));
			$r = curl_exec($ch);
			curl_close($ch);
			$r = json_decode($r,true);
			if ($r === null || isset($r['status_code'])) { self::$checked_nulls[$key] = true; return null; }
			$r['timestamp'] = XDateTime::Now()->AsInt();
			if ($use_cache) Scope::$APPLICATION[$key] = $r;
		}
		return $r;
	}
	public static function CountCalls(){ return count(self::$calls); }


	public static function Filter($a){

		$r = [];
		foreach ($a as $key => $value) {
			if ($value === null) continue;



		}


		unset($a['also_known_as']);
		unset($a['adult']);
		unset($a['homepage']);
		unset($a['popularity']);
		if (isset($a['combined_credits']['cast'])) foreach ($a['combined_credits']['cast'] as &$aa) {
			unset($aa['adult']);
			unset($aa['department']);
			unset($aa['original_title']);
			unset($aa['poster_path']);
			unset($aa['title']);
		}
		if (isset($a['combined_credits']['crew'])) foreach ($a['combined_credits']['crew'] as &$aa) {
			unset($aa['adult']);
			unset($aa['department']);
			unset($aa['original_title']);
			unset($aa['poster_path']);
			unset($aa['title']);
		}

		$a = array_filter($a,function($v){return $v!==null;});
		dump(self::Export($a));

	}

	public static function Export($a){
		if (is_array($a)) {
			$is_numeric = true; $i = 0; foreach ($a as $key=>$aa) if ($key !== $i++) { $is_numeric = false; break; }
			ob_start();
			echo "[\n";
			foreach ($a as $key => $aa) {
				if ($is_numeric)
					echo self::export($aa),",\n";
				else
					echo self::export($key),'=>',self::export($aa),",\n";
			}
			echo ']';
			return ob_get_clean();
		}
		elseif ($a===null)
			return 'null';
		else
			return var_export($a,true);
	}



	public static function GetConfiguration($p=DAILY){ return self::Call( "configuration" , $p ); }
	public static function Find($id,$p=DAILY){ return self::Call( "find/$id?external_source=imdb" , $p ); }

	public static function GetMovieInfo($iid,$p=DAILY){ return self::Call("movie/$iid?append_to_response=credits,keywords,images" , $p ); }
	public static function GetMovieCredits($iid,$p=WEEKLY){ return self::Call( "movie/$iid/credits" , $p ); }
	public static function GetMovieImages($iid,$p=WEEKLY){ return self::Call( "movie/$iid/images" , $p ); }
	public static function GetMovieSimilarMovies($iid,$page = 1,$p=WEEKLY){ return self::Call( "movie/$iid/similar_movies?page=$page" , $p ); }
	public static function GetMovieLatest($p=DAILY){ return self::Call( "movie/latest" , $p ); }

	public static function GetActorInfo($iid,$p=DAILY){ return self::Call("person/$iid?append_to_response=combined_credits,images" ,$p); }
	public static function GetActorCredits($iid,$p=WEEKLY){ return self::Call( "person/$iid/combined_credits" , $p ); }
	public static function GetActorImages($iid,$p=WEEKLY){ return self::Call( "person/$iid/images" , $p ); }
	public static function GetActorLatest($p=DAILY){ return self::Call( "person/latest" , $p ); }

	public static function GetChainInfo($iid,$p=DAILY){ return self::Call("tv/$iid?append_to_response=credits,keywords,images" , $p); }
	public static function GetChainCredits($iid,$p=WEEKLY){ return self::Call( "tv/$iid/credits" , $p ); }
	public static function GetChainImages($iid,$p=WEEKLY){ return self::Call( "tv/$iid/images" , $p ); }
	public static function GetChainLatest($p=DAILY){ return self::Call( "tv/latest" , $p ); }

	public static function Search($searchstring,$page = 1,$p=DAILY){ return self::Call( "search/multi?query=".new Url($searchstring).'&page='.$page , $p); }
	public static function SearchMovie($searchstring,$page = 1,$p=DAILY){ return self::Call( "search/movie?query=".new Url($searchstring).'&page='.$page , $p); }
	public static function SearchChain($searchstring,$page = 1,$p=DAILY){ return self::Call( "search/tv?query=".new Url($searchstring).'&page='.$page , $p); }
	public static function SearchActor($searchstring,$page = 1,$p=DAILY){ return self::Call( "search/person?query=".new Url($searchstring).'&page='.$page , $p); }










	const BACKDROP_W300 = 'w300';
	const BACKDROP_W780 = 'w780';
	const BACKDROP_W1280 = 'w1280';
	const BACKDROP_ORIGINAL = 'original';
	const PROFILE_W45 = 'w45';
	const PROFILE_W185 = 'w185';
	const PROFILE_H632 = 'h632';
	const PROFILE_ORIGINAL = 'original';
	const POSTER_W92 = 'w92';
	const POSTER_W154 = 'w154';
	const POSTER_W185 = 'w185';
	const POSTER_W342 = 'w342';
	const POSTER_W500 = 'w500';
	const POSTER_ORIGINAL = 'original';
	public static function GetImageSrc( $path , $size ) {
		$base = self::GetConfiguration()['images']['base_url'];
		return "$base$size$path";
	}




}


