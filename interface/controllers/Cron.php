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
        $result = $this->ProfessionToCollege->selectData();

        $data = array();
        $index = 0;
        foreach ($result as $key => $val) {
            if(!empty($result[$key+1])) {
                $res = $this->compare($result[$key]['profession_code'],$result[$key+1]['profession_code']);
                if(!$res && $key == 0) {
                    $data[$index]['id'] = $index+1;
                    $data[$index]['profession'] = $result[$key+1]['profession'];
                    $data[$index]['profession_code'] = explode('：',$result[$key+1]['profession_code'])[1];
                    $data[$index]['college'] .= $result[$key]['college'].$result[$key+1]['college'];
                    $data[$index]['college_url'] .= $result[$key]['college_url'].$result[$key+1]['college_url'];
                    $data[$index]['college_code'] = '';
                }else if(!$res && $key != 0){
                    $data[$index]['id'] = $index+1;
                    $data[$index]['profession'] = $result[$key+1]['profession'];
                    $data[$index]['profession_code'] = explode('：',$result[$key+1]['profession_code'])[1];
                    $data[$index]['college'] .= $result[$key+1]['college'];
                    $data[$index]['college_url'] .= $result[$key+1]['college_url'];
                    $data[$index]['college_code'] = '';
                } else {
                    $index++;
                    $data[$index]['id'] = $index+1;
                    $data[$index]['profession'] = $result[$key+1]['profession'];
                    $data[$index]['profession_code'] = explode('：',$result[$key+1]['profession_code'])[1];
                    $data[$index]['college'] = $result[$key+1]['college'];
                    $data[$index]['college_url'] = $result[$key+1]['college_url'];
                    $data[$index]['college_code'] = '';
                }
            }
        }

        $field = 'id';
        $this->ProfessionToCollege->updateData($data,$field);

    }



    protected function compare($a,$b)
    {
        if($a == $b) return 0 ;
        return 1;
    }

}
