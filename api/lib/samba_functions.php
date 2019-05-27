<?php

/*
 * Copyright (C) 2019 Nethesis S.r.l.
 * http://www.nethesis.it - nethserver@nethesis.it
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License,
 * or any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see COPYING.
*/

require_once("/usr/libexec/nethserver/api/lib/Helpers.php");

function query ($action = "read", $username = "", $address = "", $share = "", $operation = 0, $message = "", $from ="", $to = "") 
{
    if ($action == "read") {
        $qtxt="SELECT * FROM audit WHERE id>0 ";
    } else if ($action == "delete") {
        $qtxt="DELETE FROM audit WHERE id>0 ";
    } else {
        error();
    }

    $params = array();

    if($username != "") {
        $qtxt.=" AND user LIKE ?";
        $params[] = "%$username%";
    }

    if($address != "") {
        $qtxt.=" AND ip LIKE ?";
        $params[] = "%$address%";
    }

    if($share) {
        $qtxt.=" AND share = ?";
        $params[] = $share;
    }

    if($operation)
    {   
        // Valid operations: opendir open write unlink rename rmdir mkdir
        $qtxt.= " AND op = ? ";
        $params[] = $operation;
    }

    if($message) {
        $qtxt.=" AND arg LIKE ?";
        $params[] = "%$message%";;
    }

    if($from)
    {
        $qtxt.=" AND `when` >= FROM_UNIXTIME(?)";
        $params[] = $from;
    }

    if($to)
    {
        $qtxt.=" AND `when` <= FROM_UNIXTIME(?)";
        $params[] = $to;
    }

    try {
        $dbh = new PDO('mysql:host=localhost;dbname=smbaudit', 'smbd', 'smbpass');

        $stmt = $dbh->prepare($qtxt);
        $stmt->execute($params);

        if ($action == 'read') {
            $results = array();
            foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $result) {
                $tmp = explode("|", $result['arg']);
                $result['arg'] = $tmp[1];
                if ($result['op'] == 'open') {
                    if ($tmp[0] == 'r') {
                        $result['op'] = 'read';
                    } else if ($tmp[0] == 'w') {
                        $result['op'] = 'write';
                    }
                } else if ($result['op'] == 'unlink') {
                    $result['arg'] = $tmp[0];
                } else if ($result['op'] == 'rename') {
                    $result['arg'] = $tmp[0] . " -> ". $tmp[1];
                } else if ($result['op'] == 'mkdir') {
                    $result['arg'] = trim($tmp[0]);
                }

                $results[] = $result;
            }
            print json_encode($results);
        } else {
            print json_encode(array("state" => "success", "deleted" => $stmt->rowCount()));
        }

    } catch (PDOException $e) {
        error(array('type' => 'DatabaseError', 'message' => $e->getMessage()));
    }
}


