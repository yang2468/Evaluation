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

class TestContentModel extends Model
{

    // 获取列表
    public function getList()
    {
        $result = parent::table('ay_test_content')->order('Id')->select();
        return $result;
    }
    // 根据recordid获取列表
    public function getListByRecord($rid,$mid)
    {
        $result = parent::table('ay_test_content')->where("recordid='$rid' and manageid='$mid'")->find();
        return $result;
    }

    // 获取详情
    public function getTestContent($id)
    {
        $result = parent::table('ay_test_content')->where("Id='$id'")->find();
        return $result;
    }

    // 新增
    public function addTestContent(array $data)
    {
        $result = parent::table('ay_test_content')->autoTime()->insert($data);
        return $result;
    }

    // 删除
    public function delTestContent($id)
    {
        $result = parent::table('ay_test_content')->where("Id='$id'")->delete();
        return $result;
    }

    // 修改
    public function modTestContent($id, $data)
    {
        $result = parent::table('ay_test_content')->where("Id='$id'")
            ->autoTime()
            ->update($data);
        return $result;
    }


    // 获取列表
    public function getListByRecordId($recordid)
    {
        $result = parent::table('ay_test_content')->where("recordid='$recordid'")->select();
        return $result;
    }

    
    // 根据recordId 删除相关内容
    public function delTestContentByRecordId($recordid)
    {
        $result = parent::table('ay_test_content')->where("recordid='$recordid'")->delete();
        return $result;
    }
}

