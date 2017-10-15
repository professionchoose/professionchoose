<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

    /**
     * @Description: 处理数据脚本
     * @Auther: wangsiyuan@chuchujie.com
     * @Date&Time ct
     */
    public function index()
    {
        $this->load->model('ProfessionToCollege');
        $this->ProfessionToCollege->selectData();
    }


}
