<?php
// Pluggable datasource for PHP Weathermap 0.9
// - return current_value of traffic_in and traffic_out

// TARGET fping:host:service

class WeatherMapDataSource_centreon extends WeatherMapDataSource {

        var $server = "localhost";
        var $database = "centreon_storage";
        var $login = "root";
        var $password = "";

        function Init(&$map)
        {
                return(TRUE);
        }

        // this function will get called for every datasource, even if we replied FALSE to Init.
        // (so that we can warn the user that it *would* have worked, if only the plugin could run)
        // SO... don't do anything in here that relies on the things that Init looked for, because they might not exist!
        function Recognise($targetstring)
        {
                if(preg_match("/^centreon:(\S+):(\S+)$/",$targetstring,$matches))
                {
                        $this->host = $matches[1];
                        $this->service = $matches[2];
                        return TRUE;
                }
                else
                {
                        return FALSE;
                }
        }

        function ReadData($targetstring, &$map, &$item)
        {
                $value = array();
                $value["traffic_in"] = NULL;
                $value["traffic_out"] = NULL;

                if(preg_match("/^centreon:(\S+):(\S+)$/",$targetstring,$matches))
                {
                        $mysqli = new mysqli($this->server, $this->login, $this->password, $this->database);

                        $result = $mysqli->query('
                        select *
                        from metrics as m,
                        index_data as i
                        where m.index_id = i.id
                        and i.host_name = "'.$matches[1].'"
                        and i.service_description = "'.$matches[2].'"');

                        while($row = mysqli_fetch_object($result)) {
                                $value[$row->metric_name] = $row->current_value;
                        }
                }

                $data_time = 0;
                return( array($value["traffic_in"], $value["traffic_out"], $data_time) );
        }
}

// vim:ts=4:sw=4:
?>
