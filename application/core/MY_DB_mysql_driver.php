<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_DB_mysql_driver extends CI_DB_mysql_driver {
    final public function __construct($params) {
        parent::__construct($params);

        log_message('debug', 'Extended DB driver class instantiated!');
    }


    function load_rdriver()
    {

        $my_driver = config_item('subclass_prefix').'DB_'.$this->dbdriver.'_result';
        $my_driver_file = APPPATH.'core/'.$my_driver.EXT;

       // $driver = 'CI_DB_'.$this->dbdriver.'_result';

        if ( ! class_exists($my_driver))
        {
            include_once(BASEPATH.'database/DB_result.php');
            include_once(BASEPATH.'database/drivers/'.$this->dbdriver.'/'.$this->dbdriver.'_result.php');
            include_once($my_driver_file);
        }

        return $my_driver;
    }


}

?>