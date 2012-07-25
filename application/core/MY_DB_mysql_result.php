<?php
/**
 * Created by JetBrains PhpStorm.
 * User: nightwriter
 * Date: 24.07.12
 * Time: 23:52
 * To change this template use File | Settings | File Templates.
 */
class MY_DB_mysql_result extends CI_DB_mysql_result
{
    var $result_obj;
    var $base_table;
    var $is_recursive = false;
    var $result_fields;
    var $steps = 1;

    function result($foreigns=false,$table=NULL,$recursive=false)
    {

        $this->base_table = $table;
        $this->is_recursive = $recursive;
        $this->result_fields = $this->list_fields();
        $this->result_obj = parent::result();
        if ($foreigns){
            $this->get_foreigners($this->result_obj);
            $this->get_one_to_many($this->result_obj);
        }

        return $this->result_obj;
    }

    function row($foreigns=false,$table=NULL,$recursive=false){

        $this->base_table = $table;
        $this->is_recursive = $recursive;
        $this->result_fields = $this->list_fields();
        $this->result_obj = parent::row();
        if ($foreigns)
            $this->get_foreigners($this->result_obj);

        return $this->result_obj;
    }


    public function get_foreigners($res)
    {
        $CI = &get_instance();
        $fields = $this->result_fields;

        // many to one
        foreach($fields as $field){
            $sql = "SELECT * FROM (
                            SELECT RC.TABLE_NAME, RC.REFERENCED_TABLE_NAME, KCU.COLUMN_NAME, KCU.REFERENCED_COLUMN_NAME
                            FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS RC
                            JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE KCU
                            USING ( CONSTRAINT_NAME )
                            ) AS foreigns_view WHERE TABLE_NAME='".$this->base_table."' AND COLUMN_NAME='".$field."'";
            $result = $CI->db->query($sql)->row();

            if(!empty($result)){
                if (!is_object($res))
                    foreach($res as &$row){
                        $field_name = $result->REFERENCED_TABLE_NAME;
                        $CI->db->from($field_name);
                        $CI->db->where($result->REFERENCED_COLUMN_NAME,$row->$field);
                        $query = $CI->db->get();
                        $row->$field_name = $query->row(true,$field_name);
                    }
                else {

                    $field_name = $result->REFERENCED_TABLE_NAME;
                    $CI->db->from($field_name);
                    $CI->db->where($result->REFERENCED_COLUMN_NAME,$res->$field);
                    $query = $CI->db->get();
                    $this->result_fields = $query->list_fields();

                    $res->$field_name = $query->row(true,$field_name);

                }
            }
        }
    }

    public function get_one_to_many($res)
    {
        $CI = &get_instance();

        $sql = "SELECT * FROM (
                            SELECT RC.TABLE_NAME, RC.REFERENCED_TABLE_NAME, KCU.COLUMN_NAME, KCU.REFERENCED_COLUMN_NAME
                            FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS RC
                            JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE KCU
                            USING ( CONSTRAINT_NAME )
                            ) AS foreigns_view WHERE REFERENCED_TABLE_NAME='".$this->base_table."'";
        $result = $CI->db->query($sql)->result();
        foreach($res as &$row){
            foreach ($result as $f_table){
                    $field_name = $f_table->TABLE_NAME;
                    $refname = $f_table->REFERENCED_COLUMN_NAME;
                    $CI->db->from($field_name);
                    $CI->db->where($f_table->COLUMN_NAME,$row->$refname);
                    $query = $CI->db->get();
                    $row->$field_name = $query->result();
            }
        }

    }

}
