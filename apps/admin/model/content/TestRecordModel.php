<?php
/**
 * @copyright (C)2016-2099 Hnaoyun Inc.
 * @author XingMeng
 * @email hnxsh@foxmail.com
 * @date 2017年4月3日
 *  菜单管理模型类
 */
namespace app\admin\model\content;

use core\basic\Model;

class TestRecordModel extends Model
{

    // 获取列表
    public function getList($pcode,$pstatus)
    {
        //$result = parent::table('ay_test_record')->order('Id')->select();
        $field = array(
            'a.*',
            'b.username'
            
        );
        $join = array(
            'ay_user b',
            'a.scoreuser = b.ucode',
            'LEFT'
        );
        $where = array();
        if($pcode)
            $where[] = "a.typeid='$pcode'";
        if($pstatus)    
            $where[] = "a.state='$pstatus'";
        
        
            $result = parent::table('ay_test_record a')->field($field)
                ->join($join)
                ->order('a.create_time DESC')
                ->where($where)
                ->select();
        
       
        return $result;
    }
    // 获取列表
    public function getListByUser($ucode)
    {
        //$result = parent::table('ay_test_record')->order('Id')->select();
        $field = array(
            'a.*',
            'b.username'
            
        );
        $join = array(
            'ay_user b',
            'a.scoreuser = b.ucode',
            'LEFT'
        );
        
        $result = parent::table('ay_test_record a')->field($field)
            ->join($join)
            ->where("a.scoreuser='$ucode'")
            ->order('a.create_time DESC')
            ->select();
        
        
        return $result;
    }


    // 获取详情
    public function getTestRecord($id)
    {
        $result = parent::table('ay_test_record')->where("Id='$id'")->find();
        return $result;
    }

     // 获取详情
     public function getTestRecordByUseridandTypeid($userid,$typeid)
     {
         $result = parent::table('ay_test_record')->where("userid = '$userid' and typeid = '$typeid'")->order('Id')->find();
         return $result;
     } 

    // 新增
    public function addTestRecord(array $data)
    {
        $result = parent::table('ay_test_record')->autoTime()->insertGetId($data);
        return $result;
    }

    // 删除
    public function delTestRecord($id)
    {
        $result = parent::table('ay_test_record')->where("Id='$id'")->delete();
        return $result;
    }

    // 修改
    public function modTestRecord($id, $data)
    {
        $result = parent::table('ay_test_record')->where("Id='$id'")
            ->autoTime()
            ->update($data);
        return $result;
    }

    // 检查用户是否可以报名参与评测
    public function checkUser($where)
    {
        return parent::table('ay_test_record')
            ->where($where)
            ->select();
    }
    
      // 获取最后一个code
      public function getLastId($where)
      {
          return parent::table('ay_test_record')->where($where)->order('Id DESC')->value('Id');
      }
    // 用户该用户已填写记录
    public function getListByUserid($userid)
    {
        $result = parent::table('ay_test_record')->where("userid = '$userid'")->order('Id DESC')->select();
        return $result;
    }
}

