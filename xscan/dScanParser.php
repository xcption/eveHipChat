<?php

/**
 * 
 * @author xcption
 *
 */
class dScanParser {
	/**
	 * The raw data copied and pasted from the DScan window
	 * @var string
	 */
	private $rawScan;	
	/**
	 *  Total number of ship in the scan
	 * @var integer
	 */
	private $totalShips;
	/**
	 * Name of the 
	 * @var string
	 */
	private $solarSystemName;
	
	private $scanTimestamp;
	/**
	 * ID from the database upon inserting parent record
	 * @var integer
	 */
	private $dbParentId;
	/**
	 * 
	 * @var mysqli
	 */
	private $dbConn;
	
	private $insertParentQuery;
	
	private $finalizeParentQuery;
	
	private $insertScanDetailQuery;
	
	private $selectShipQuery;
	
	private $selectParentQuery;
	
	private $selectDetailQuery;
	
	
	/**
	 * 
	 * @param string $rawScan
	 * @param mysqli $dbConn
	 * @param string $scanParentTable
	 * @param string $scanDetailTable
	 * @param string $shipTable
	 */
	function __construct($rawScan, $dbConn, $scanParentTable, $scanDetailTable, $shipTable) {
		$this->dbParentId = false;
		$this->solarSystemName = "UNKNOWN";
		$this->totalShips = 0;
		
		$this->rawScan = $rawScan;
		$this->dbConn = $dbConn;
		
		$this->insertParentQuery = "INSERT INTO " . $scanParentTable . " (createDate) VALUES (UTC_TIMESTAMP())";
		$this->finalizeParentQuery = "UPDATE " . $scanParentTable . " SET systemName = ?, totalShips = ? WHERE idrawScan = ?";
		
		$this->insertScanDetailQuery = "INSERT INTO " . $scanDetailTable . " (parentId, type, category, role, count) VALUES (?,?,?,?,?)";
		
		$this->selectShipQuery = "SELECT category, role FROM " . $shipTable . " WHERE name = ?";
		
		$this->selectParentQuery = "SELECT idrawScan, systemName, totalShips, createDate FROM " . $scanParentTable . " WHERE md5(idrawScan) = ?";
		$this->selectDetailQuery = "SELECT type, category, role, count FROM " . $scanDetailTable . " WHERE parentId = ?";
		
	}

	/**
	 * 
	 * @return array
	 */
	function parseScan() {
		$this->rawScan = str_replace("\r\n", "\n", $this->rawScan);
		foreach (explode("\n", $this->rawScan) as $row) {
			$rcd = explode("\t", $row);
			$item = $rcd[1];
			$tmp[$item]++;
			if ($this->solarSystemName == "UNKNOWN") {
				$this->checkCelestial($rcd[0], $item);
			}
		}
		return $tmp;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	function insertParent() {
		$stmt = $this->dbConn->prepare($this->insertParentQuery);
		if ($stmt) {
			$insert = $stmt->execute();
			if ($insert) {
				$this->dbParentId = $this->dbConn->insert_id;
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * 
	 * @return boolean
	 */
	function finalizeParent() {
		$stmt = $this->dbConn->prepare($this->finalizeParentQuery);
		if ($stmt) {
			if ($stmt->bind_param('sii', $this->solarSystemName, $this->totalShips, $this->dbParentId)) {
				return $stmt->execute();
			} else {
				throw new Exception('dScanParser.finalizeParent query bind_param error');;
			}
		} else {
			throw new Exception('dScanParser.finalizeParent query prepare error');;
		}
	}
	
	function removeParent() { 
		//$stmt = $this->dbConn->prepare($this->removeParentQuery);
		
	}
	
	/**
	 * 
	 * @param string $shipName
	 * @param string $category
	 * @param string $role
	 * @param integer $count
	 * @return boolean
	 */
	function insertDetailRow($type, $category, $role, $count) {
		$stmt = $this->dbConn->prepare($this->insertScanDetailQuery);
		if ($stmt) {
			if ($stmt->bind_param('isssi', $this->dbParentId, $type, $category, $role, $count)) {
				return $stmt->execute();
			} else {
				throw new Exception('dScanParser.insertDetailRow query bind_param error');
			}
		} else {
			throw new Exception('dScanParser.insertDetailRow query prepare error');
		}
	}
	
	/**
	 * 
	 * @param string $name
	 * @param string $type
	 * @return boolean
	 */
	function checkCelestial($name, $type) {
		if (substr_count($type, "Sun")  || $type == "Moon" || substr_count($type, "Asteroid Belt") ||
				substr_count($type, "Planet ") || substr_count($type, " Station") || substr_count($type, " Post") ||
				substr_count($type, " Outpost") || substr_count($type, " Hub") || substr_count($type, " Citadel") ||
				substr_count($type, " Starport")) {
					$this->solarSystemName = explode(" ", $name)[0];
					return true;
		} else
			return false;
	}
	
	/**
	 * 
	 * @param string $category
	 * @return Array|boolean
	 */
	function getShipDetails($type) {
		$stmt = $this->dbConn->prepare($this->selectShipQuery);
		if ($stmt) {
			if ($stmt->bind_param('s', $type)) {
				if ($stmt->execute()){
					$stmt->store_result();
					if ($stmt->num_rows > 0) {
						$stmt->bind_result($ship["category"], $ship["role"]);
						$stmt->fetch();
						return $ship;						
					} else {
						return false;
					}
				} else {
					throw new Exception('dScanParser.ShipDetail query execute error');
				}
			} else {
				throw new Exception('dScanParser.ShipDetail query bind_param error');
			}
		} else {
			throw new Exception('dScanParser.ShipDetail query prepare error');
		}
	}
	
	
	function loadParentData($parentId) {
		$stmt = $this->dbConn->prepare($this->selectParentQuery);
		if ($stmt) {
			if ($stmt->bind_param('s', $parentId)) {
				if ($stmt->execute()){
					$stmt->store_result();
					if ($stmt->num_rows > 0) {
						$stmt->bind_result($parent["id"], $parent["systemName"], $parent["totalShips"], $parent["createDate"]);
						$stmt->fetch();
						$this->dbParentId = $parent["id"];
						$this->solarSystemName = $parent["systemName"];
						$this->totalShips = $parent["totalShips"];
						$this->scanTimestamp = $parent["createDate"];
						return true;
					} else {
						return false;
					}
				} else {
					throw new Exception('dScanParser.loadParentData query execute error');
				}
			} else {
				throw new Exception('dScanParser.loadParentData query bind_param error');
			}
		} else {
			throw new Exception('dScanParser.loadParentData query prepare error');
		}
	}		
	

	function getDetailRecords() {
		$query = "SELECT type, category, role, count FROM testTable WHERE parentId = " . $this->dbParentId . ";";
		$result = $this->dbConn->query($query);
		
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_array(MYSQL_ASSOC)) {
				$data[] = $row;
			}		
			return $data;
		} else {
			return false;
		}
	}
	
	/**
	 * getSolarSystemName Accessor Method
	 * @return string
	 */
	function getSolarSystemName(){
		return $this->solarSystemName;
	}
	
	/**
	 * dbParentId Accessor Method
	 * @return number
	 */
	function getDbParentId(){
		return $this->dbParentId;
	}
	
	function incrementTotalShips($count) {
		$this->totalShips += $count;
	}
	
	function getTotalShips() {
		return $this->totalShips;
	}
	
	function getScanTimestamp() {
		return $this->scanTimestamp;
	}
}