<?php
/**
 * @copyright (C)2016-2099 Hnaoyun Inc.
 * @author XingMeng
 * @email hnxsh@foxmail.com
 * @date 2017年04月17日
 *  公司设置控制器 
 */
namespace app\admin\controller\content;

use core\basic\Controller;
use app\admin\model\content\DictModel;

class DictController extends Controller
{

    private $model;

    public function __construct()
    {
        $this->model = new DictModel();
    }

    public function index()
    {
        $this->assign('list', true);
        $this->assign('dicts', $this->model->getList());
        $this->display('content/dict.html');
    }

     // 项目类型增加
     public function add()
     {
        if (! $_POST) {
            return;
        }
        $enroll_start  = post('enroll_start');
        $enroll_end = post('enroll_end');
        $upload_start = post('upload_start');
        $upload_end = post('upload_end');
        $data = array(
            'name' => post('name'),
            'enroll_start' =>$enroll_start,
            'enroll_end' =>$enroll_end,
            'upload_start' =>$upload_start,
            'upload_end' =>$upload_end,
            'create_user' =>session('username'),
            'update_user' =>session('username'),
            'status' =>1,

        );

        // 执行添加
        if ($this->model->addDict($data)) {
            $this->log('修改评测项目' . $data . '成功！');
            if (! ! $backurl = get('backurl')) {
                success('新增评测项目成功！', base64_decode($backurl));
            } else {
                success('新增评测项目成功！', url('admin/Dict/index'));
            }
        } else {
            $this->log('修改评测项目' . $data . '失败！');
            error('新增评测项目失败！', - 1);
        }
     }

        // 项目类型删除
    public function del()
    {
        if (! $id = get('Id', 'var')) {
            error('传递的参数值错误！', - 1);
        }
        if ($this->model->delDict($id)) {
            $this->log('删除评测项目' . $id . '成功！');
            success('删除评测项目成功！', - 1);
        } else {
            $this->log('删除评测项目' . $id . '失败！');
            error('删除评测项目失败！', - 1);
        }
    }
     
     // 项目类型修改
     // 角色修改
     public function mod()
     {
         if (! $Id = get('Id', 'var')) {
             error('传递的参数值错误！', - 1);
         }
         // 单独修改状态
        if (($field = get('field', 'var')) && ! is_null($value = get('value', 'var'))) {
            if ($this->model->modDict($Id, "$field='$value',update_user='" . session('username') . "'")) {
                $this->log('修改评测项目' . $Id . '状态' . $value . '成功！');
                location(- 1);
            } else {
                $this->log('修改评测项目' . $Id . '状态' . $value . '失败！');
                alert_back('修改失败！');
            }
        }
         // 修改操作
         if ($_POST) {
             // 获取数据
             $name = post('name');
             $enroll_start  = post('enroll_start');
             $enroll_end = post('enroll_end');
             $upload_start = post('upload_start');
             $upload_end = post('upload_end');
             if (! $name) {
                 alert_back('评测项目不能为空！');
             }
             
             // 构建数据
             $data = array(
                 'name' => $name,
                 'enroll_start' =>$enroll_start,
                'enroll_end' =>$enroll_end,
                'upload_start' =>$upload_start,
                'upload_end' =>$upload_end,
                //'create_user' =>session('username'),
                'update_user' =>session('username'),
                
             );
             
             // 执行修改
             if ($this->model->modDict($Id, $data)) {
                 $this->log('修改评测项目' . $Id . '成功！');
                 if (! ! $backurl = get('backurl')) {
                     success('修改评测项目成功！', base64_decode($backurl));
                 } else {
                     success('修改评测项目成功！', url('admin/Dict/index'));
                 }
             } else {
                 location(- 1);
             }
         }else {
            $this->assign('mod', true);
            
            // 调取修改内容
            $result = $this->model->getDict($Id);
            if (! $result) {
                error('编辑的内容已经不存在！', - 1);
            }
            $this->assign('dict', $result);
            $this->display('content/dict.html');
        }
     }
}