<?php

/**
 * Created by PhpStorm.
 * User: likun
 * Date: 2016/11/12
 * Time: 下午13:49
 */
class ProfessionToCollege extends CI_Model
{
    protected $table_name = 'profession_to_college';

    public function __construct()
    {
        parent::__construct();
    }


    public function selectData($where=array(),$field='*',$order='',$page=1,$limit=10)
    {
        $result = $this->select($where,$field,$order,$page,$limit);
        var_dump($result);
    }

}