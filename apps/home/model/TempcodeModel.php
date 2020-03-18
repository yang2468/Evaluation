<?php
/**
 * @copyright (C)2016-2099 Hnaoyun Inc.
 * @author XingMeng
 * @email hnxsh@foxmail.com
 * @date 2018年3月8日
 *  
 */
namespace app\home\model;

use core\basic\Model;

class TempcodeModel extends Model
{

    // 添加vscode
    public function addVscode(array $data)
    {
        $result = parent::table('ay_tempcode')->insert($data);
        return $result;
    }

    // 检查加vscode
    public function checkVscode($where)
    {
        return parent::table('ay_tempcode')->field('id')
            ->where($where)
            ->find();
    }
    // 修改加vscode
    public function updateVscode($phonenum, $data)
    {
        $result = parent::table('ay_tempcode')->where("username='$phonenum'")->update($data);
        return $result;
    }

}