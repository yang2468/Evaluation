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

class TestManageModel extends Model
{
	  
	  
	  //获取列表并生成树
	  public function getTree($tcode)
	  {
	  	$field = array(
            'a.*',
            'b.name as tname'
            
        );
        $join = array(
            'ay_check_type b',
            'a.checktypeid = b.Id',
            'LEFT'
        );
        if(!$tcode)
        {
            $result = parent::table('ay_test_manage a')->field($field)
            ->join($join)
            ->order('a.checktypeid,a.parentid,a.sorting,a.id')
            
            ->select();
        }
        else{
            $result = parent::table('ay_test_manage a')->field($field)
            ->join($join)
            ->order('a.checktypeid,a.parentid,a.sorting,a.id')
            ->where("a.checktypeid='$tcode'")
            ->select();
        }
        
        
        return $result;
	  }
	  //获取列表并生成树
	  public function getStaticList()
	  {
	  	$field = array(
	  				'b.Id',
            'b.name as tname',
            'count(a.Id) as amount',
            'b.create_time as ctime'
            
        );
        $join = array(
            'ay_check_type b',
            'a.checktypeId = b.Id',
            'LEFT'
        );
        $result = parent::table('ay_test_manage a')->field($field)
            ->join($join)
            ->order('a.Id')
            ->group('b.Id')
            ->select();
        
        //error(json_encode($result));
        return $result;
	  }

    // 获取列表
    public function getList()
    {
        $result = parent::table('ay_test_manage')->order('checktypeid DESC,sorting')->select();
        return $result;
    }

    // 获取列表
    public function getListByCheckType($checktypeid)
    {
        // $resultArray=array();
        $result = parent::table('ay_test_manage')->where("checktypeid='$checktypeid' and haveSon = 1")->order('sorting')->select();
        foreach ($result as $key => $value) {
            // $singleResult = parent::table('ay_test_manage')->where("checktypeid='$value->checktypeid' and parentId = '$value->Id'")->order('sorting')->select();
            $singleResult = parent::table('ay_test_manage')->where("checktypeid='$value->checktypeid' and parentid ='$value->Id'")->order('sorting')->select();
           
            $value->sonArray = $singleResult;
            //error(count($singleResult));
            //  $resultArray = array_merge($resultArray,$singleResult);
        }
        return $result;
    }
		//获取父节点名
		public function getParentName($id)
		{
			   $result = parent::table('ay_test_manage')->where("parentId='$id'")->find();
			   if($result)
             return $result->name;
         else
             return '';
			
		}
		// 获取顶级指标
    public function getParent()
    {
        $field = array(
            'a.*',
            'b.name as tname'
            
        );
        $join = array(
            'ay_check_type b',
            'a.checktypeid = b.Id',
            'LEFT'
        );
        $result = parent::table('ay_test_manage a')->field($field)
            ->join($join)
            ->order('a.checktypeid,a.sorting,a.id')
            ->where("haveSon=1")->select();
        
        return $result;
    }
    // 获取详情
    public function getTestManage($id)
    {
        $result = parent::table('ay_test_manage')->where("Id='$id'")->find();
        return $result;
    }

    // 新增
    public function addTestManage(array $data)
    {
        $result = parent::table('ay_test_manage')->autoTime()->insert($data);
        return $result;
    }

    // 删除
    public function delTestManage($id)
    {
        $result = parent::table('ay_test_manage')->where("Id='$id'")->delete();
        return $result;
    }

    // 修改
    public function modTestManage($id, $data)
    {
        $result = parent::table('ay_test_manage')->where("Id='$id'")
            ->autoTime()
            ->update($data);
        return $result;
    }


    
    // 获取列表
    public function showScore($checktypeid,$recordid)
    {
        $result = parent::table('ay_test_manage')->where("checktypeid='$checktypeid' and haveSon = 1")->order('sorting')->select();
        foreach ($result as $key => $value) {
            $singleResult = parent::table('ay_test_manage')->where("checktypeid='$value->checktypeid' and parentid ='$value->Id'")->order('sorting')->select();
            foreach ($singleResult as $singleKey => $singleValue) {
                $contentResult = parent::table('ay_test_content')->where("manageid='$singleValue->Id' and recordid ='$recordid'")->find();
                $singleValue->content = $contentResult->content;
                $singleValue->score = $contentResult->score;
            }
            $value->sonArray = $singleResult;
        }
        return $result;
    }
}

