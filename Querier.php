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
        /*if (isset(self::$getInstance))
            return self::$getInstance;
        return self::$getInstance = new self();*/
        return self::$getInstance ?: (self::$getInstance = new self());
    }

    /**
     * A simple constructor to initialize DB.
     */
    public function __construct()
    {
        $this->db = new DB();
    }

    /**
     * This returns an array containing the id and flag/state of this platform.
     * @param $platform
     * @return mixed
     */
    public function getStateWithId($platform)
    {
        $platformFlagQueryString = "select id, flag from platforms where platforms.name = '$platform'";
        return $this->db->query($platformFlagQueryString)->fetchColumn();
    }

    /**
     * This returns an array containing the participating users and their respective roles for this platform.
     * @param $platformId
     * @return mixed
     */
    public function getManagers($platformId)
    {
        $recipientsQuery = "select users.name, users.email, users.role from users inner join platform_users on
                                    users.id = platform_users.user_id where platform_users.platform_id = '$platformId'";
        return $this->db->query($recipientsQuery)->fetchAssoc();
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

}