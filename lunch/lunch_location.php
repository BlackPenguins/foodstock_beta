<?php
class Location {
	public $name = "";
	public $abbrev = "";
	public $category = "";
	public $punchline = "";
	public $description = "";
	public $distanceType = "";
	public $longitude = "";
	public $latitude = "";
	public $menuName = "";

	public function __construct( $name, $abbrev, $category, $punchline, $description, $distanceType, $latitude, $longitude, $menuName ) {
		$this->name = $name;
		$this->abbrev = $abbrev;
		$this->category = $category;
        $this->punchline = $punchline;
        $this->description = $description;
		$this->distanceType = $distanceType;
		$this->longitude = $longitude;
		$this->latitude = $latitude;
		$this->menuName = $menuName;
	}
	
	public function getName() {
		return $this->name;
	}

	public function getCategory() {
		return $this->category;
	}

	public function getPunchline() {
		return $this->punchline;
	}

	public function getDescription() {
		return $this->description;
	}

	public function getDistanceType() {
		return $this->distanceType;
	}

	public function getAbbreviation() {
	    return $this->abbrev;
    }

    public function getLongitude() {
	    return $this->longitude;
    }

    public function getLatitude() {
	    return $this->latitude;
    }

    public function getMenuName() {
	    return $this->menuName;
    }
}
?>