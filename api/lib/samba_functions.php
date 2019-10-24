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

/* Make sure to allocate enough RAM, even if it shouldn't be necessary */
ini_set('memory_limit','512M');

require_once("/usr/libexec/nethserver/api/lib/Helpers.php");

function query_file_access_details ($username = "", $share = "", $message = "", $from ="", $to = "") 
{
    $qtxt="SELECT `when`,op,arg FROM audit WHERE id>0 ";
    $params = array();

    if($username != "") {
        $qtxt.=" AND user = ?";
        $params[] = "$username";
    }

    if($share) {
        $qtxt.=" AND share = ?";
        $params[] = $share;
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
        $pass = trim(file_get_contents('/var/lib/nethserver/secrets/smbd'));
        $dbh = new PDO('mysql:host=localhost;dbname=smbaudit', 'smbd', $pass);

        $qtxt .= " ORDER BY `when` DESC LIMIT 500";
        $stmt = $dbh->prepare($qtxt);
        $stmt->execute($params);

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

            unset($result['arg']);
            $results[] = $result;
        }
        print json_encode($results);

    } catch (PDOException $e) {
        error(array('type' => 'DatabaseError', 'message' => $e->getMessage()));
    }
}

function query_file_access ($message, $host = '') 
{
    $qtxt = "SELECT `when`,user,share,op,arg FROM audit WHERE id IN (SELECT MAX(id) FROM audit WHERE arg LIKE ? GROUP BY user) GROUP BY user;";
    $params[] = "%$message%";

    try {
        $pass = trim(file_get_contents('/var/lib/nethserver/secrets/smbd'));
        $dbh = new PDO('mysql:host=localhost;dbname=smbaudit', 'smbd', $pass);

        $stmt = $dbh->prepare($qtxt);
        $stmt->execute($params);

        $results = array();
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $result) {
            $result['raw_arg'] = $result['arg'];
            $tmp = explode("|", $result['arg']);
            $result['arg'] = @$tmp[1];
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
            } else if ($result['op'] == 'rmdir') {
                $result['arg'] = $result['raw_arg'];
            }

            $results[] = $result;
        }

        $stmt = $dbh->prepare("SELECT UNIX_TIMESTAMP(lastupdate) AS lastupdate FROM last_update");
        $stmt->execute();
        $updated = $stmt->fetch(PDO::FETCH_ASSOC);
        $db = new EsmithDatabase('configuration');
        $alias = $db->getProp('smb', 'AuditAlias');

        print json_encode(array("list" => $results, "updated" => $updated['lastupdate'], "alias" => "https://$host:980/$alias"));

    } catch (PDOException $e) {
        error(array('type' => 'DatabaseError', 'message' => $e->getMessage()));
    }
}


