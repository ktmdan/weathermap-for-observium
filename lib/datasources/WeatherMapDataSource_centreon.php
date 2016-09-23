<?php
// Pluggable datasource for PHP Weathermap 0.9
// - return current_value of traffic_in and traffic_out

// TARGET fping:host:service

class WeatherMapDataSource_centreon extends WeatherMapDataSource {

        var $host = "";
        var $service = "";
        var $results = array();

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
            $mysqli = new mysqli("localhost", "root", "", "centreon_storage");
            
            $result = $mysqli->query('
            select *
            from metrics as m,
            index_data as i
            where m.index_id = i.id
            and i.host_name like "%'.$this->host.'%"
            and i.service_description like "%'.$this->service.'%"');
            
            $value = array();
            while($row = mysqli_fetch_object($result)) {
              $value[$row->metric_name] = $row->current_value;
            }
            
            $data_time = 0;
            return( array($value["traffic_in"], $value["traffic_out"], $data_time) );
        }
}

// vim:ts=4:sw=4:
?>
