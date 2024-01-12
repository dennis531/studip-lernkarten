<?php

namespace Lernkarten\StudIP;

trait Datenschutz
{
    /**
     * Returns the tables containing user data. The array consists of the tables containing user data the expected
     * format for each table is:.
     *
     * `$array[ table display name ] = [ 'table_name' => name of the table, 'table_content' => array of db rows containing userdata]`
     *
     * @param string $userId
     *
     * @return array
     */
    public static function getUserdataInformation($userId)
    {
        return [];
    }

    /**
     * Returns the filerefs of given user.
     *
     * @param string $user_id
     *
     * @return array
     */
    public static function getUserFileRefs($userId)
    {
        return [];
    }

    /**
     * Deletes the table content containing user data.
     *
     * @param string $user_id
     * @return boolean
     */
    public static function deleteUserdata($userId)
    {
        return true;
    }
}
