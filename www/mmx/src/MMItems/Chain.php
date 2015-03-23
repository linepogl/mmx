<?php

/**
 * @property-read int     Seasons
 * @property-read int     Episodes
 * @property-read int     YearTill
 */
class Chain extends Movie {

	/** @var int     */ public $_Seasons;
	/** @var int     */ public $_Episodes;
	/** @var int     */ public $_YearTill;

	const TMDb_TYPE = 'tv';
	private function __construct($iid) {
		$this->iid = $iid;
		$this->Type = self::TMDb_TYPE;
	}


	public function GetKey(){ return 'C'.$this->iid; }
	public function GetCaption() { $this->Load(); return $this->_Title . ($this->_Year === null ? '' : ' ('.$this->_Year.($this->_YearTill === null ? '' : '-'.$this->_YearTill).')'); }

	public function ToArray() {
		$r = parent::ToArray();
		$r['Seasons'] = $this->Seasons;
		$r['Episodes'] = $this->Episodes;
		$r['YearTill'] = $this->YearTill;
		return $r;
	}

	protected function OnLoad() {
		$info = TMDb::GetTVInfo($this->iid);
		if ($info === null) return false;
		$this->_Title = @$info['name'];
		$this->_OriginalTitle = @$info['original_name']; if ($this->_OriginalTitle === $this->_Title) $this->_OriginalTitle = null;
		$this->_Overview = @$info['overview'];
		$this->_Image = @$info['backdrop_path'];
		$this->_Countries = []; $a = @$info['origin_country']; if (is_array($a)) foreach ($a as $key) $this->_Countries[] = $key;
		$this->_Languages = []; $a = @$info['languages']; if (is_array($a)) foreach ($a as $key) $this->_Languages[] = $key;
		$this->_Genres = []; $a = @$info['genres']; if (is_array($a)) foreach ($a as $aa) { $key = @$aa['id']; $name = @$aa['name']; if ($key !== null && $name !== null) $this->_Genres[$key] = $name; }
		$this->_Keywords = []; $a = @$info['keywords']['keywords']; if (is_array($a)) foreach ($a as $aa) { $key = @$aa['id']; $name = @$aa['name']; if ($key !== null && $name !== null) $this->_Keywords[$key] = $name; }
		$this->_Seasons = null; $this->_Episodes = null; $a = @$info['seasons']; if (is_array($a)) foreach ($a as $aa) { $this->_Seasons++; $this->_Episodes += intval(@$aa['episode_count']); }
		$this->_Year = strlen($d = @$info['first_air_date'])<4 ? null : intval(substr($d,0,4));
		$this->_YearTill = strlen($d = @$info['last_air_date'])<4 ? null : intval(substr($d,0,4)); if ($this->_YearTill===$this->_Year) $this->_YearTill = null;
		$this->LoadCredits($info);
		return true;
	}


	private static $cache = [];
	/** @return Chain */
	public static function Find($iid) {
		if (!array_key_exists($iid, self::$cache)) self::$cache[$iid] = new self($iid);
		return self::$cache[$iid];
	}

	public static function Search($search_string,$page=1,&$pages=1,&$count=0){
		$r = [];
		$c = TMDb::SearchTV($search_string,$page);
		$a = @$c['results'];
		if (is_array($a)) foreach ($a as $aa) {
			$iid = @$aa['id'];
			$x = Chain::Find($iid);
			if ($x->NotFound()) continue;
			$r[] = $x;
		}
		$pages = intval(@$c['total_pages']);
		$count = intval(@$c['total_results']);
		return $r;
	}

}
