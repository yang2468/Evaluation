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
use app\admin\model\content\TestRecordModel;
use app\admin\model\content\TestManageModel;
use app\admin\model\content\TestContentModel;
use app\admin\model\content\DictModel;
use app\admin\model\system\UserModel;

class TestRecordController extends Controller
{

    private $model;
    private $dictmodel;
    private $managemodel;
    private $usermodel;

    public function __construct()
    {
        $this->model = new TestRecordModel();
        $this->managemodel = new TestManageModel();
        $this->dictmodel = new DictModel();
        $this->usermodel = new UserModel();
        $this->contentmodel = new TestContentModel();
    }
		public function score()
		{
			if (! ! $submit = post('submit')) {
            switch ($submit) {
                case 'saveScore': // 修改列表排序
                    $listall = post('listall');
                    if ($listall) {
                        $scoreuser = session('ucode');
                        $score = post('score');
                        foreach ($listall as $key => $value) {
                            if ($score[$key] === '' || ! is_numeric($score[$key]))
                                $score[$key] = 0;
                               
                            $this->contentmodel->modTestContent($value, "score=" . $score[$key]);
                            $this->contentmodel->modTestContent($value, "scoreuser='" . $scoreuser."'");
                        }
                        $this->log('保存评分成功！');
                        //success('保存评分成功！', - 1);
                        success('保存评分成功！', url('admin/TestRecord/index'));
                    } else {
                        alert_back('保存评分失败！');
                    }
                    break;
            }
         }
        
			   if (! $Id = get('Id', 'var')) {
             error('传递的参数值错误！', - 1);
         }
         
         $rec = $this->model->getTestRecord($Id);
         
         $typelist = $this->managemodel->getListByCheckType($rec->typeid);
         //$reclist = $this->managemodel->getListByCheckType($rec->typeid);
         foreach($typelist as $value)
         {
         	
         foreach($value->sonArray as $sonvalue)
         {
         	     $sonvalue->content = $this->contentmodel->getListByRecord($Id,$sonvalue->Id);
         	    
         	     
         	
         }
         	
         	
         }
         
        // error(json_encode($typelist));
         
         $this->assign('score', true);
         $this->assign('typelist', $typelist);
         $this->display('content/testrecord.html');
		}
    public function index()
    {
        $this->assign('list', true);
        if ((! ! $pcode = get('pcode', 'var'))) {
            //$result = $this->model->findUser($field, $keyword);
            
        } 
        if ((! ! $pstatus = get('pstatus', 'var'))) {
            //$result = $this->model->findUser($field, $keyword);
            
        } 

        $ucode = session('ucode');
        $urole = $this->usermodel->getUserRole($ucode);
        if($urole=='R101'||$ucode=='10001'||$urole='R104')
            $result = $this->model->getList($pcode,$pstatus);
        else
            $result = $this->model->getListByUser($ucode);
        
        foreach ($result as $key => $value) {
            $dict = $this->dictmodel->getDict($value->typeid);
            if ($dict) {
                $value->typename = $dict->name;
            } else {
                $value->typename = '';
            }
        }
        $this->assign('dicts', $result);

        $this->assign('checktypelist', $this->dictmodel->getList());
        
        $this->assign('recordlist', $this->dictmodel->getList());
        
        $this->display('content/testrecord.html');
    }

     // 测评维护增加
     public function add()
     {
        if (! $_POST) {
            return;
        }

        $data = array(
            'name' => post('name'),
            'checktypeid' => post('checktypeid'),
            'type' => post('type'),
        );

        // 执行添加
        if ($this->model->addTestRecord($data)) {
            $this->log('修改测评维护' . $data . '成功！');
            if (! ! $backurl = get('backurl')) {
                success('新增测评维护成功！', base64_decode($backurl));
            } else {
                success('新增测评维护成功！', url('admin/TestRecord/index'));
            }
        } else {
            $this->log('修改测评维护' . $data . '失败！');
            error('新增测评维护失败！', - 1);
        }
     }

        // 测评维护删除
    public function del()
    {
        if (! $id = get('Id', 'var')) {
            error('传递的参数值错误！', - 1);
        }
        if ($this->model->delTestRecord($id)) {
            $this->log('删除测评维护' . $id . '成功！');
            success('删除测评维护成功！', - 1);
        } else {
            $this->log('删除测评维护' . $id . '失败！');
            error('删除测评维护失败！', - 1);
        }
    }
     
     // 测评维护修改
     public function mod()
     {
         if (! $Id = get('Id', 'var')) {
             error('传递的参数值错误！', - 1);
         }
         
         // 修改操作
         if ($_POST) {
             // 获取数据
             $typeid = post('typeid');
             $state = post('state');  
             $connect = post('connect');  
             $scoreuser = post('scoreuser');
             // 构建数据
             $data = array(
                 'typeid' => $typeid,
                 'state' => $state,
                 'connect' => $connect,
                 'scoreuser' => $scoreuser
             );
             
             // 执行修改
             if ($this->model->modTestRecord($Id, $data)) {
                 $this->log('测评审核' . $Id . '成功！');
                 if (! ! $backurl = get('backurl')) {
                     success('测评审核成功！', base64_decode($backurl));
                 } else {
                     success('测评审核成功！', url('admin/TestRecord/index'));
                 }
             } else {
                 location(- 1);
             }
         }else {
            $this->assign('mod', true);
            
            // 调取修改内容
            $result = $this->model->getTestRecord($Id);
            if (! $result) {
                error('编辑的内容已经不存在！', - 1);
            }
            $this->assign('dict', $result);
            // 测评维护列表
            
            $this->assign('userlist', $this->usermodel->getUserByRole('R105'));
            $this->assign('checktypelist', $this->dictmodel->getList());
            $this->display('content/testrecord.html');
        }
     }
}