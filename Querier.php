<?php

require "DB.php";

class Querier {

    private static $getInstance;
    private $db;

    /**
     * This returns a singleton for the Query Class.
     * @return Query
     */
    public static function getInstance()
    {
        return self::$getInstance ?: (self::$getInstance = new self());
    }

    /**
     * A simple constructor to initialize DB.
     */
    public function __construct()
    {
        return $this->db = new DB();
    }

    public function fetchAllPlatforms()
    {
        $allPlatformsQueryString = "select platforms.name from platforms";
        return $this->db->query($allPlatformsQueryString)->fetchAssoc();
    }

    /**
     * This method queries a platform and gets all its associated details.
     * @param $platformName
     * @return mixed
     */
    public function getPlatformDetails($platformName)
    {
        $platformQueryString = "select * from platforms where platforms.name = '$platformName'";
        return $this->db->query($platformQueryString)->fetchRow();
    }

    public function fetchNetworkTags()
    {
        $networkTagsQueryString = "select * from network_tag";
        return $this->db->query($networkTagsQueryString)->fetchAssoc();
    }

    public function clearFailure($platform)
    {
        $platformFlagQUpdateString = "UPDATE platforms SET flag = 0 WHERE platforms.name = '$platform'";
        $this->db->query($platformFlagQUpdateString);
    }

    public function setFirstFail($platform)
    {
        $platformFlagQUpdateString = "UPDATE platforms SET flag = 1 WHERE platforms.name = '$platform'";
        $this->db->query($platformFlagQUpdateString);
    }

    public function updateFlag($blFlag, $bcFlag, $sevFlag, $platformName)
    {
        $platformFlagQUpdateString = "UPDATE platforms SET bl_flag = '$blFlag',  bc_flag = '$bcFlag',
                                    sev_flag = '$sevFlag' WHERE platforms.name = '$platformName'";
        $this->db->query($platformFlagQUpdateString);
    }

}
