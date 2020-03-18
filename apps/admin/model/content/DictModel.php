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

class DictModel extends Model
{

    // 获取列表
    public function getList()
    {
        $result = parent::table('ay_check_type')->order('Id')->select();
        return $result;
    }

    // 获取详情
    public function getDict($id)
    {
        $result = parent::table('ay_check_type')->where("Id='$id'")->find();
        return $result;
    }

    // 新增
    public function addDict(array $data)
    {
        $result = parent::table('ay_check_type')->autoTime()->insert($data);
        return $result;
    }

    // 删除
    public function delDict($id)
    {
        $result = parent::table('ay_check_type')->where("Id='$id'")->delete();
        return $result;
    }

    // 修改
    public function modDict($id, $data)
    {
        $result = parent::table('ay_check_type')->where("Id='$id'")
            ->autoTime()
            ->update($data);
        return $result;
    }
}

