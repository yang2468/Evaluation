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
use app\admin\model\content\TestManageModel;
use app\admin\model\content\DictModel;

class TestManageController extends Controller
{

    private $model;
    private $dictmodel;

    public function __construct()
    {
        $this->model = new TestManageModel();
        $this->dictmodel = new DictModel();
    }

    public function index()
    {
        $this->assign('list', true);
        if ((! ! $pcode = get('pcode', 'var'))) {
            //$result = $this->model->findUser($field, $keyword);
            
        } 
        
        $result = $this->model->getList();
		$tree = $this->model->getTree($pcode);
		$static = $this->model->getStaticList();
		$allparent = $this->model->getParent();
				//$sorts = $this->makeSortList($tree);
        foreach ($tree as $key => $value) {
            $dict = $this->dictmodel->getDict($value->checktypeid);
            if ($dict) {
                $value->checktypename = $dict->name;
            } else {
                $value->checktypename = '';
            }
            $value->parentname = $this->model->getParentName($value->parentId);
        }
        $this->assign('dicts', $result);
        $this->assign('static', $static);
        $this->assign('allparent', $allparent);
				
        $this->assign('sorts', $tree);
        // 测评维护列表
        $this->assign('checktypelist', $this->dictmodel->getList());
        
        $this->display('content/testmanage.html');
    }
    // 生成无限级内容栏目列表
    private function makeSortList($tree)
    {
        // 循环生成
        foreach ($tree as $value) {
            $this->count ++;
            $this->outData[$this->count] = new \stdClass();
            $this->outData[$this->count]->id = $value->id;
            $this->outData[$this->count]->blank = $this->blank;
            $this->outData[$this->count]->name = $value->name;
            //$this->outData[$this->count]->subname = $value->subname;
            //$this->outData[$this->count]->scode = $value->scode;
            $this->outData[$this->count]->parentId = $value->parentId;
            $this->outData[$this->count]->pname = $value->pname;
            //$this->outData[$this->count]->mcode = $value->mcode;
            //$this->outData[$this->count]->listtpl = $value->listtpl;
            //$this->outData[$this->count]->contenttpl = $value->contenttpl;
            //$this->outData[$this->count]->ico = $value->ico;
            //$this->outData[$this->count]->pic = $value->pic;
            //$this->outData[$this->count]->keywords = $value->keywords;
            //$this->outData[$this->count]->description = $value->description;
            //$this->outData[$this->count]->outlink = $value->outlink;
            $this->outData[$this->count]->sorting = $value->sorting;
            //$this->outData[$this->count]->status = $value->status;
            //$this->outData[$this->count]->filename = $value->filename;
            //$this->outData[$this->count]->type = $value->type;
            //$this->outData[$this->count]->urlname = $value->urlname;
            //$this->outData[$this->count]->create_user = $value->create_user;
            //$this->outData[$this->count]->update_user = $value->update_user;
            $this->outData[$this->count]->create_time = $value->create_time;
            $this->outData[$this->count]->update_time = $value->update_time;
            
            if ($value->son) {
                $this->outData[$this->count]->son = true;
            } else {
                $this->outData[$this->count]->son = false;
            }
            
            // 子菜单处理
            if ($value->son) {
                $this->blank .= '　　';
                $this->makeSortList($value->son);
            }
        }
        
        // 循环完后回归缩进位置
        $this->blank = substr($this->blank, 6);
        return $this->outData;
    }

     // 测评指标增加
     public function add()
     {
        if (! $_POST) {
            return;
        }   
				if(post('pnorm')==0){
					$haveSon=1;
				}
				else{
						 	   $haveSon=0;
				}
        $data = array(
            'name' => post('name'),
            'checktypeid' => post('checktypeid'),
            'type' => post('type'),
            'haveSon' => $haveSon,
            'parentId' => post('pnorm'),
            'sorting' => post('sorting'),
        );

        // 执行添加
        if ($this->model->addTestManage($data)) {
            $this->log('修改评测指标' . $data . '成功！');
            if (! ! $backurl = get('backurl')) {
                success('新增评测指标成功！', base64_decode($backurl));
            } else {
                success('新增评测指标成功！', url('admin/TestManage/index'));
            }
        } else {
            $this->log('新增评测指标' . $data . '失败！');
            error('新增评测指标失败！', - 1);
        }
     }

        // 测评维护删除
    public function del()
    {
        if (! $id = get('Id', 'var')) {
            error('传递的参数值错误！', - 1);
        }
        if ($this->model->delTestManage($id)) {
            $this->log('删除评测指标' . $id . '成功！');
            success('删除评测指标成功！', - 1);
        } else {
            $this->log('删除评测指标' . $id . '失败！');
            error('删除评测指标失败！', - 1);
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
             $name = post('name');
             $checktypeid = post('checktypeid');
             $type = post('type');
             if (! $name) {
                 alert_back('评测指标不能为空！');
             }
             if(post('pnorm')==0){
								 $haveSon=1;
						 }
						 else{
						 	   $haveSon=0;
						 }
             // 构建数据
             $data = array(
                 'name' => $name,
                 'checktypeid' => $checktypeid,
                 'type' => $type,
                 'sorting' => post('sorting'),
                 'haveSon' => $haveSon,
                 'parentId' => post('pnorm'),
             );
             
             // 执行修改
             if ($this->model->modTestManage($Id, $data)) {
                 $this->log('修改评测指标' . $Id . '成功！');
                 if (! ! $backurl = get('backurl')) {
                     success('修改评测指标成功！', base64_decode($backurl));
                 } else {
                     success('修改评测指标成功！', url('admin/TestManage/index'));
                 }
             } else {
                 location(- 1);
             }
         }else {
            $this->assign('mod', true);
            
            // 调取修改内容
            
            $this->assign('testinfo', $this->model->getTestManage($Id)); 
            $this->assign('allparents', $this->model->getParent());
            $this->assign('checktypelist', $this->dictmodel->getList());
            $this->display('content/testmanage.html');
        }
     }
}