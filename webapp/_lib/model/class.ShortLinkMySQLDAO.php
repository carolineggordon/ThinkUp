<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.ShortLinkMySQLDAO.php
 *
 * Copyright (c) 2012 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * Short Link Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class ShortLinkMySQLDAO extends PDODAO {
    public function insert($link_id, $short_url) {
        $q  = "INSERT INTO #prefix#links_short ";
        $q .= "(link_id, short_url) ";
        $q .= "VALUES (:link_id , :short_url) ";
        $vars = array(
            ':link_id'=>(int)$link_id,
            ':short_url'=>$short_url
        );
        $ps = $this->execute($q, $vars);
        return $this->getInsertId($ps);
    }

    public function getLinksToUpdate($url) {
        $q  = "SELECT sl.short_url ";
        $q .= "FROM #prefix#links_short AS sl ";
        $q .= "WHERE sl.first_seen >= date_sub(current_date, INTERVAL 2 day) ";
        $q .= "AND sl.short_url LIKE :short_url ";
        $q .= "GROUP BY sl.short_url ";
        $vars = array( ':short_url'=>$url."%" );

        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);

        $rows = $this->getDataRowsAsArrays($ps);
        $urls = array();
        foreach($rows as $row){
            $urls[] = $row['short_url'];
        }
        return $urls;
    }

    public function saveClickCount($short_url, $click_count) {
        $q  = "UPDATE #prefix#links_short ";
        $q .= "SET click_count=:click_count WHERE short_url=:short_url; ";
        $vars = array(
            ':click_count'=>(int)$click_count,
            ':short_url'=>$short_url
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);

        return $this->getUpdateCount($ps);
    }
}