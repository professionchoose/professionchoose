<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/libraries/config.html
 */
class CI_Model {


    protected static $_db = array(); //db连接(主从)
    protected static $_lock;
    protected $table_name;
    protected $ci;

    public function __construct()
    {
        $this->ci = get_instance();
    }



    /**
     * 主库事务开启
     * @author <lik@chuchujie.com> 16/8/17
     */
    public function begin_trans()
    {
        $this->getDb(true)->trans_begin();
    }

    /**
     * getDb 主从DB实例获取
     * @author <lik@chuchujie.com> 16/8/17
     * @param bool $master
     * @return mixed
     * @throws Exception
     */
    protected function getDb($master = false,$choose_master = 'master',$choose_slave = 'slave')
    {
        try {
            if ($master) {
                if (empty(self::$_db[$choose_master])) {
                    self::$_db[$choose_master] = $this->ci->load->database($choose_master, true);
                }
                return self::$_db[$choose_master];
            } else {
                if (empty(self::$_db[$choose_slave])) {
                    self::$_db[$choose_slave] = $this->ci->load->database($choose_slave, true);
                }
                return self::$_db[$choose_slave];
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

    }

    /**
     * 主库事务提交
     * @author <lik@chuchujie.com> 16/8/17
     */
    public function commit_trans()
    {
        $this->getDb(true)->trans_commit();
    }

    /**
     * 主库事务回滚
     * @author <lik@chuchujie.com> 16/8/17
     */
    public function rollback_trans()
    {
        $this->getDb(true)->trans_rollback();
    }

    /**
     * 事务状态
     * @author <lik@chuchujie.com> 16/8/17
     * @return mixed
     */
    public function status_trans()
    {
        return $this->getDb(true)->trans_status();
    }

    /**
     * 最后的sql
     * @author <lik@chuchujie.com> 16/09/06
     * @param $master
     * @return string
     */
    public function last_sql($master = false)
    {
        return $this->getDb($master)->last_query();
    }

    /**
     * 从库执行原生sql
     * @author <lik@chuchujie.com> 16/8/17
     * @param string $sql
     * @param bool $master
     * @param array $binds
     * @return bool
     * @throws Exception
     */
    protected function querySql($sql = '', $master = false, $binds = null)
    {
        return $this->getDb($master)->query($sql, $binds)->result_array();
    }

    /**
     * 重载方法
     * @param $method
     * @param $args
     * @return mixed
     * @throws Exception
     */
//    public function __call($method, $args)
//    {
//        if (method_exists($this->getDb(), $method)) {
//            $master = $args[0];
//            unset($args[0]);
//            return call_user_func_array(array($this->getDb($master), $method), $args);
//        }else{
//            throw new Exception('CI_Model不存在此方法');
//        }
//    }

    /**
     * 插入操作
     * @author <lik@chuchujie.com> 16/8/17
     * @param array $data
     * @return bool
     */
    protected function insert($data = array())
    {
        if (empty($data)) {
            return false;
        }
        $rlt = $this->getDb(true)->insert($this->table_name, $data);
        return $rlt;

    }

    /**
     * 更新操作
     * @author <lik@chuchujie.com> 16/8/17
     * @param $data
     * @param $where
     * @param $limit
     * @return bool
     */
    protected function update($data, $where, $limit = true,$set_key = '',$set_value = '')
    {
        if (empty($where)) {
            return false;
        }

        if ($limit) {
            $this->getDb(true)->limit(1);
        }
        if(!empty($set_key)) {
            $this->getDb(true)->set($set_key,$set_value,false);
        }

        $rlt = $this->getDb(true)->where($where)->update($this->table_name, $data);
        return $rlt;
    }

    /**
     * 设置更新条件
     * @param $key
     * @param string $value
     * @param null $escape
     */
    protected function set($key, $value = '', $escape = NULL)
    {
        $this->getDb(true)->set($key, $value, $escape);

    }

    /**
     * 删除数据
     * @param $where
     * @return bool
     * author        : lianghuiju@chuchujie.com
     * function_name : delete
     * description   :
     */
    protected function delete($where,$limit = true)
    {
        if (empty($where)) {
            return false;
        }
        if ($limit) {
            $this->getDb(true)->limit(1);
        }
        $rlt = $this->getDb(true)->where($where)->delete($this->table_name);
        return $rlt;
    }

    /**
     * 批量删除
     * @param $where
     * @return bool
     * author        : lianghuiju@chuchujie.com
     * function_name : delete
     * description   :
     */
    protected function delete_batch($where)
    {
        if (empty($where)) {
            return false;
        }
        $rlt = $this->getDb(true)->where($where)->delete($this->table_name);
        return $rlt;
    }

    /**
     * 批量更新
     * @author   <liangzh@chuchujie.com>
     * @param array $data
     * @param       $field
     * @return bool
     */
    protected function update_batch($data, $field)
    {
        if (empty($field) || empty($data)) {
            return false;
        }
        $rlt = $this->getDb(true)->update_batch($this->table_name, $data, $field);
        return $rlt;
    }

    /**
     * 影响行数
     * @author <lik@chuchujie.com> 16/09/06
     * @return int
     */
    protected function update_row()
    {
        return $this->getDb(true)->affected_rows();
    }

    /**
     * 当执行 INSERT 语句时，这个方法返回新插入行的ID。
     * @author <lik@chuchujie.com> 16/09/06
     * @return int
     */
    protected function insert_id()
    {
        return $this->getDb(true)->insert_id();
    }

    /**
     * find
     * @author <lik@chuchujie.com> 16/8/17
     * @param array $where
     * @param string $field
     * @param bool $master
     * @return bool
     * @throws Exception
     */
    protected function find($where = array(), $field = '*', $master = false)
    {
        if (empty($where)) {
            return false;
        }

        if (self::$_lock == true) {
            $master = true;
        }

        $rlt = $this->getDb($master)->where($where)->select($field)->get($this->table_name)->row_array();
        return $rlt;
    }

    /**
     * select
     * @author <lik@chuchujie.com> 16/8/17
     * @param array $where
     * @param string $field
     * @param string $order
     * @param int $page
     * @param int $limit
     * @param bool $master
     * @param string $group_by
     * @return bool
     * @throws Exception
     */
    protected function select($where = array(), $field = '*', $order = '', $page = 1, $limit = 10, $master = false, $group_by = '')
    {
        if (self::$_lock == true) {
            $master = true;
        }

        $this->getDb($master)->select($field);

        if (isset($where)) {
            $this->getDb($master)->where($where);
        }

        if (!empty($order)) {
            $this->getDb($master)->order_by($order);
        }

        if(!is_numeric($page) || $page <= 0) {
            $page = 1;
        }

        if (is_numeric($limit) && $limit > 0) {
            $limit = (int)$limit;
            $offset = ($page - 1) * $limit;
            $this->getDb($master)->limit($limit, $offset);
        }

        if(!empty($group_by)) {
            $this->getDb($master)->group_by($group_by);
        }

        $rlt = $this->getDb($master)->get($this->table_name)->result_array();
        $this->lock(false);
        return $rlt;
    }

    /**
     * lock
     * @author <lik@chuchujie.com> 16/8/26
     * @param bool $lock
     * @return $this
     * @throws Exception
     */
    public function lock($lock)
    {
        $this->getDb(true)->lock($lock);
        self::$_lock = $lock;
        return $this;
    }

    /**
     * 设置查询in条件
     * @param $key
     * @param string $value
     * @param bool $master
     * @param null $escape
     */
    protected function where_in($key, $value = '', $master = false, $escape = NULL)
    {
        $this->getDb($master)->where_in($key, $value, $escape);

    }

    /**
     * 设置查询not_in条件
     * @param $key
     * @param string $value
     * @param bool $master
     * @param null $escape
     */
    protected function where_not_in($key, $value = '', $master = false, $escape = NULL)
    {
        $this->getDb($master)->where_not_in($key, $value, $escape);

    }

    /**
     * select_where_in 批量获取
     * @author <wangsiyuan@chuchujie.com> 16/09/29
     * @param string $key
     * @param array $values
     * @param string $field
     * @param string $order
     * @param int $page
     * @param int $limit
     * @param bool $master
     * @return bool
     * @throws Exception
     */
    protected function select_where_in($key, $values = array(), $field = '*', $order = '', $page = 1, $limit = 10, $master = false)
    {
        if (empty($key)) {
            return false;
        }

        if (self::$_lock == true) {
            $master = true;
        }

        $this->getDb($master)->where_in($key, $values);
        $this->getDb($master)->select($field);


        if (!empty($order)) {
            $this->getDb($master)->order_by($order);
        }
        if (is_numeric($limit) && $limit > 0) {
            $limit = (int)$limit;
            $offset = ($page - 1) * $limit;
            $this->getDb($master)->limit($limit, $offset);
        }

        $rlt = $this->getDb($master)->get($this->table_name)->result_array();
        $this->lock(false);
        return $rlt;
    }


    /**
     * { function_description }
     *获取筛选条件下某个字段的总和
     * @param      <type>   $where   The where
     * @param      <type>   $field   The field
     * @param      string   $alias   The alias
     * @param      boolean  $master  The master
     *
     * @return     boolean  ( description_of_the_return_value )
     */
    protected function select_sum($where = null,$field,$alias = '',$master = false)
    {
        if (isset($where)) {
            $this->getDb($master)->where($where);
        }

        if(empty($field)) {
            return false;
        }

        $this->getDb($master)->select_sum($field,$alias);

        return $this->getDb($master)->get($this->table_name)->row_array();
    }

    /**
     * [delete_where_in description]
     * where_in 删除
     * @Author:  wangsiyuan@chuchujie.com
     * @param:   [type]
     * @DateTime 2016-12-22T16:52:14+0800
     * @param    [type]                   $key    [description]
     * @param    array                    $values [description]
     * @return   [type]
     */
    protected function delete_where_in($key, $values = array())
    {
        if(empty($key)) {
            return false;
        }

        $this->getDb(true)->where_in($key, $values);
        $rlt = $this->getDb(true)->delete($this->table_name);

        return $rlt;
    }

    /**
     * where_in 更新数据
     * @author yangshengkai@chuchujie.com
     * @time 2017/03/07
     * @param $key where_in中的 键
     * @param $values where_in中的 值
     * @param $data 要更改的数据
     * @return bool
     */
    protected function update_where_in($key, $values = array(), $data)
    {
        if (empty($key) || empty($values)) {
            return false;
        }

        $rlt = $this->getDb(true)->where_in($key, $values)->update($this->table_name, $data);

        return $rlt;
    }

    /**
     * count
     * @author <lik@chuchujie.com> 16/8/26
     * @param array $where
     * @param string $order
     * @return bool
     * @throws Exception
     */
    protected function count($where = array(), $order = '')
    {
        if(isset($where)){
            $this->getDb()->where($where);
        }

        if (!empty($order)) {
            $this->getDb()->order_by($order);
        }

        return $this->getDb()->count_all_results($this->table_name);
    }

    /**
     * 数据库里面去重
     * @param bool $master
     * author        : lianghuiju@chuchujie.com
     * function_name : distinct
     * description   :
     */
    protected function distinct($master = false)
    {
        $this->getDb($master)->distinct();
    }

    /**
     * 模糊查询
     * @param $like
     * @param $side
     * @param bool $master
     * @return mixed
     * author        : lianghuiju@chuchujie.com
     * function_name : like
     * description   :
     */
    protected function like($like,$side = 'both',$master = false)
    {
        $rlt = $this->getDb($master)->like($like,$master,$side);
        return $rlt;
    }

    /**
     * 多条数据同时插入
     * @param array $data
     * @return bool
     * author        : lianghuiju@chuchujie.com
     * function_name : insert_batch
     * description   :
     */
    protected function insert_batch($data = array())
    {
        if (empty($data)) {
            return false;
        }
        $rlt = $this->getDb(true)->insert_batch($this->table_name, $data);
        return $rlt;
    }

    /**
     * table_name转换
     * @author <lik@chuchujie.com> 16/8/17
     * @param $table_name
     * @return $this
     */
    protected function table($table_name)
    {
        $this->table_name = $table_name;
        return $this;
    }

}
