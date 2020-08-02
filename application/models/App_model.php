<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_model extends CI_Model {
	function __construct() {
        parent::__construct();
    }


    public function getRecords($table, $fields="", $condition="", $orderby="", $single_row=false,$limit=-1,$custom_condition='') //$condition is array 

    {

        if($fields != "")

        {

            $this->db->select($fields);

        }

         

        if($orderby != "")

        {

            $this->db->order_by($orderby); 

        }



        if($condition != "")
        {
            if(empty($custom_condition)){
                $rs = $this->db->get_where($table,$condition);
            }else{
                $this->db->where($condition); 
                $this->db->group_start();
                $this->db->or_where($custom_condition); 
                $this->db->group_end();
                $rs = $this->db->get($table);
            }
        }

        else

        {

            $rs = $this->db->get($table);

        }

        if($single_row)

        {  

            return $rs->row_array();

        }

        return $rs->result_array();



    }


    function getTableFields($table_name)

    {

        $query = "SHOW COLUMNS FROM $table_name";

        $rs = $this->db->query($query);

        return $rs->result_array();

    }


    function deleteRecords($table, $where)

    { 

        $this->db->delete($table, $where);

        return $this->db->affected_rows();

    }


 
    

    public function addEditRecords($table_name, $data_array, $where='')

    {

        if($table_name && is_array($data_array))

        {

            $columns = $this->getTableFields($table_name);

            foreach($columns as $coloumn_data)

                $column_name[]=$coloumn_data['Field'];

                      

            foreach($data_array as $key=>$val)

            {

                if(in_array(trim($key),$column_name))

                {

                    $data[$key] = $val;

                }

             }



            if($where == "")

            {   

                $query = $this->db->insert_string($table_name, $data);

                $this->db->query($query);

                return  $this->db->insert_id();

            }

            else

            {

                $query = $this->db->update_string($table_name, $data, $where);

                $this->db->query($query);

                return  $this->db->affected_rows();

            }

            

        }           

    }

    
    function getNumRecords($table, $fields="", $condition="") 

    {

        if($fields != "")

        {

            $this->db->select($fields);

        }

        if($condition != "")

        {

            $rs = $this->db->get_where($table,$condition);

        }

        else

        {

            $rs = $this->db->get($table);

        }       

        return $rs->num_rows();

    }


}
