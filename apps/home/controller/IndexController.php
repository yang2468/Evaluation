<?php
/**
 * @copyright (C)2016-2099 Hnaoyun Inc.
 * @author XingMeng
 * @email hnxsh@foxmail.com
 * @date 2018年2月14日
 *  首页控制器
 */
namespace app\home\controller;

use core\basic\Controller;

use app\home\model\ParserModel;
use app\admin\model\IndexModel;
use app\admin\model\system\UserModel;
use app\home\model\TempcodeModel;
use app\admin\model\content\DictModel;

use app\admin\model\content\TestRecordModel;
use app\admin\model\content\TestManageModel;
use app\admin\model\content\TestContentModel;



//短信配置信息
require __DIR__ . "/../../sms/index.php";
use sms\SmsSingleSender;
use sms\SmsSenderUtil;

class IndexController extends Controller
{

    protected $parser;

    protected $model;
    protected $indexModel;
    protected $usermodel;
    protected $tempcodeModel;
    protected $dictModel;
    protected $testRecordModel;
    protected $testManageModel;
    protected $testContentModel;
    

    protected $sms_appid; // 短信应用SDK AppID1400开头
    protected $sms_appkey; // 短信应用SDK AppKey
    protected $templateId;// 短信模板ID，需要在短信应用中申请
    protected $smsSign; // 签名

    public function __construct()
    {
        $this->parser = new ParserController();
        $this->model = new ParserModel();
        $this->indexModel = new IndexModel();
        $this->usermodel = new UserModel();
        $this->tempcodeModel = new TempcodeModel();
        $this->dictModel = new DictModel();
        $this->testRecordModel = new TestRecordModel();
        $this->testManageModel = new TestManageModel();
        $this->testContentModel = new TestContentModel();
        

        $this->sms_appid = 1400315394;
        $this->sms_appkey = "cf749878fbf3a788e51c7bf1a67306aa";
        $this->templateId = 535155;
        $this->smsSign = "智宏大数据";
    }

    // 空拦截器, 实现文章路由转发
    public function _empty()
    {
        // 地址类型
        $url_rule_type = $this->config('url_rule_type') ?: 3;
        $sourceUrl = '';
        if (P) { // 采用pathinfo模式及p参数模式
                 
            // 禁止伪静态时带index.php访问
            if ($url_rule_type == 2 && stripos(URL, $_SERVER['SCRIPT_NAME']) !== false) {
                _404('您访问的内容不存在，请核对后重试！');
            }
            
            $path = explode('/', P);
            if (! defined('URL_BIND')) {
                array_shift($path); // 去除模块部分
            }
        } elseif ($url_rule_type == 3 && isset($_SERVER["QUERY_STRING"]) && $qs = $_SERVER["QUERY_STRING"]) { // 采用简短传参模式
            $sourceUrl = $qs;
            parse_str($qs, $output);
            unset($output['page']); // 去除分页
            if ($output && ! current($output)) {
                
           
                $path = key($output); // 第一个参数为路径信息，注意PHP数组会自动将key点符号转换下划线
                $path = trim($path, '/'); // 去除两端斜杠
                $url_rule_suffix = substr($this->config('url_rule_suffix'), 1);
                if (! ! $pos = strripos($path, '_' . $url_rule_suffix)) {
                    $path = substr($path, 0, $pos); // 去扩展
                }
                if (! preg_match('/^[\w\-\.\/]+$/', $path)) {
                    _404('您访问的地址有误，请核对后重试！');
                }
                $path = explode('/', $path);
            } elseif (isset($output['keyword'])) { // 支持兼容模式普通搜索
                $this->search();
                exit();
            }
        }
        if (isset($path) && is_array($path)) {
            
            // 地址分隔符
            $url_break_char = $this->config('url_break_char') ?: '_';
            
            // 判断第一个参数中组合信息
            if (strpos($path[0], $url_break_char)) {
                $param = explode($url_break_char, $path[0]);
            } else {
                $param[] = $path[0];
            }
            // 判断第一个参数是模型还是自定义分类
            if (! ! ($model = $this->model->checkModelUrlname($param[0])) || preg_match('/^(list_[0-9]+)|(^about_[0-9]+)/', $path[0])) {
                $scode = $param[1];
                if (isset($param[2])) {
                    $_GET['page'] = $param[2]; // 分页
                }
            } else {
                define('CMS_PAGE_CUSTOM', true); // 自定义名称后比正常少了一个参数
                $scode = $param[0];
                if (isset($param[1])) {
                    $_GET['page'] = $param[1]; // 分页
                }
            }
            // 路由
            switch ($param[0]) {
                case 'login':
                    $this->login();
                break;
                case 'register':
                    $this->register();
                break;
                case 'forget':
                    $this->forget();
                break;
                case 'postVeCode':
                    $this->postVeCode();
                break;
                case 'loginOut':
                    $this->loginOut();
                break;
                case 'editUserData':
                    $this->editUserData();
                break;
                case 'checkSignUpState':
                    $this->checkSignUpState();
                break;
                case 'firstsubmit':
                    $this->firstsubmit();
                break;
                case 'submitFirstData':
                    $this->submitFirstData();  
                break;
                case 'secondsubmit':
                    $this->secondsubmit($path[2]);
                break;
                case 'submitSecondData':
                    $this->submitSecondData();  
                break;
                case 'uploadData':
                    $this->uploadData();  
                break;
                case 'showfirstsubmit':
                    $this->showfirstsubmit($path[1]);  
                break;
                case 'showsecondsubmit':
                    $this->showsecondsubmit($path[1]);  
                break;
                case 'submitScore':
                    $this->submitScore();  
                break;
                case 'search':
                case 'keyword':
                    $this->search();
                    break;
                case 'addMsg':
                    $this->addMsg();
                    break;
                case 'addForm':
                    $_GET['fcode'] = $path[2];
                    $this->addForm();
                    break;
                case 'sitemap':
                case 'Sitemap':
                    $sitemap = new SitemapController();
                    $sitemap->index();
                    break;
                default:
                    if($param[0]=='signup'){
                        session('dicts', $this->dictModel->getList()); 
                    }
                    if($param[0]=='applySignUp'){
                        $applyRecords = array();
                        $result = $this->testRecordModel->getListByUserid(session('id'));
                        foreach ($result as $key => $value) {
                            $dict = $this->dictModel->getDict($value->typeid);
                            if ($dict && $dict->status == '1') {
                                $value->typename = $dict->name;
                                array_push($applyRecords, $value);
                            }
                        }
                        session('applyRecords', $applyRecords);  
                    }

                    if($param[0]=='signupdetail'){ 
                        session('recordinfo', "");
                        session('contentlist', "");
                        session('managelist', "");
                        $type = substr($sourceUrl,18,1);
                        $id = substr($sourceUrl,23);
                        if ($type == 0) {
                            //首次填报
                            session('signupState',0);
                        }else{
                            $recordResult = $this->testRecordModel->getTestRecord($id);
                            session('recordinfo', $recordResult);
                            session('signupState',$recordResult->state);
                            // 0.未填报1.待审核2.初审通过3.初审不通过4.评估中5.评估完成
                            if($recordResult->state == 4 || $recordResult->state == 5 ){
                                $result = $this->testManageModel->showScore($recordResult->typeid,$id);
                                session('contentlist', $result);

                                // $result = $this->testContentModel->getListByRecordId($id);
                                // foreach ($result as $key => $value) {
                                //     $dict = $this->testManageModel->getTestManage($value->manageid);
                                //     if ($dict) {
                                //         $value->managename = $dict->name;
                                //         $value->managetype = $dict->type;
                                //     } else {
                                //         $value->managename = '';
                                //         $value->managetype = '';
                                //     }
                                // }
                                // foreach ($result as $key => $value) {
                                //     $value->name = $value->managename;
                                //     $value->type = $value->managetype;
                                //     $value->value = $value->content;
                                // }
                                // session('manageliscontentt', $result);
                            }else {
                                $result = $this->testManageModel->getListByCheckType($recordResult->typeid);

                                foreach ($result as $key => $value) {
                                    foreach ($value->sonArray as $sonkey => $sonvalue) {
                                        $content = $this->testContentModel->getListByRecord($id,$sonvalue->Id);
                                        if ($content) {
                                            $sonvalue->contentId = $content->Id;
                                            $sonvalue->value = $content->content;
                                        }
                                    }
                                }                                
                                // error( json_encode( $result));
                                session('managelist', $result);
                            }
                        }
                    }
                    if (count($path) > 1) {
                        if (! ! ($data = $this->model->getContent($path[1])) && ($data->scode == $scode || $data->sortfilename == $scode)) {
                            $this->getContent($data);
                        } else {
                            _404('您访问的内容不存在，请核对后重试！');
                        }
                    } else {
                        if (! ! $sort = $this->model->getSort($scode)) {
                            if ($sort->type == 1) {
                                $this->getAbout($sort);
                            } else {
                                $this->getList($sort);
                            }
                        } else {
                            _404('您访问的栏目不存在，请核对后重试！');
                        }
                    }
            }
        } else {
            $this->getIndex();
        }
    }

    // 首页
    private function getIndex()
    {
        $content = parent::parser('index.html'); // 框架标签解析
        $content = $this->parser->parserBefore($content); // CMS公共标签前置解析
        $content = $this->parser->parserPositionLabel($content, - 1, '首页', SITE_INDEX_DIR . '/'); // CMS当前位置标签解析
        $content = $this->parser->parserSpecialPageSortLabel($content, 0, '', SITE_INDEX_DIR . '/'); // 解析分类标签
        $content = $this->parser->parserAfter($content); // CMS公共标签后置解析
        $this->cache($content, true);
    }

    // 列表
    private function getList($sort)
    {
        if ($sort->listtpl) {
            define('CMS_PAGE', true); // 使用cms分页处理模型
            $content = parent::parser($sort->listtpl); // 框架标签解析
            $content = $this->parser->parserBefore($content); // CMS公共标签前置解析
            $pagetitle = $sort->title ? "{sort:title}" : "{sort:name}"; // 页面标题
            $content = str_replace('{pboot:pagetitle}', $pagetitle . '-{pboot:sitetitle}-{pboot:sitesubtitle}', $content);
            $content = str_replace('{pboot:pagekeywords}', '{sort:keywords}', $content);
            $content = str_replace('{pboot:pagedescription}', '{sort:description}', $content);
            $content = $this->parser->parserPositionLabel($content, $sort->scode); // CMS当前位置标签解析
            $content = $this->parser->parserSortLabel($content, $sort); // CMS分类信息标签解析
            $content = $this->parser->parserListLabel($content, $sort->scode); // CMS分类列表标签解析
            $content = $this->parser->parserAfter($content); // CMS公共标签后置解析
        } else {
            _404('请到后台设置分类栏目列表页模板！');
        }
        $this->cache($content, true);
    }

    // 详情页
    private function getContent($data)
    {
        // 读取模板
        if (! ! $sort = $this->model->getSort($data->scode)) {
            if ($sort->contenttpl) {
                define('CMS_PAGE', true); // 使用cms分页处理模型
                $content = parent::parser($sort->contenttpl); // 框架标签解析
                $content = $this->parser->parserBefore($content); // CMS公共标签前置解析
                $content = str_replace('{pboot:pagetitle}', '{content:title}-{sort:name}-{pboot:sitetitle}-{pboot:sitesubtitle}', $content);
                $content = str_replace('{pboot:pagekeywords}', '{content:keywords}', $content);
                $content = str_replace('{pboot:pagedescription}', '{content:description}', $content);
                $content = $this->parser->parserPositionLabel($content, $sort->scode); // CMS当前位置标签解析
                $content = $this->parser->parserSortLabel($content, $sort); // CMS分类信息标签解析
                $content = $this->parser->parserCurrentContentLabel($content, $sort, $data); // CMS内容标签解析
                $content = $this->parser->parserAfter($content); // CMS公共标签后置解析
            } else {
                _404('请到后台设置分类栏目内容页模板！');
            }
        } else {
            _404('您访问内容的分类已经不存在，请核对后再试！');
        }
        $this->cache($content, true);
    }

    // 单页
    private function getAbout($sort)
    {
        // 读取数据
        if (! $data = $this->model->getAbout($sort->scode)) {
            _404('您访问的内容不存在，请核对后重试！');
        }
        
        if ($sort->contenttpl) {
            define('CMS_PAGE', true); // 使用cms分页处理模型
            $content = parent::parser($sort->contenttpl); // 框架标签解析
            $content = $this->parser->parserBefore($content); // CMS公共标签前置解析
            $pagetitle = $sort->title ? "{sort:title}" : "{content:title}"; // 页面标题
            $content = str_replace('{pboot:pagetitle}', $pagetitle . '-{pboot:sitetitle}-{pboot:sitesubtitle}', $content);
            $content = str_replace('{pboot:pagekeywords}', '{content:keywords}', $content);
            $content = str_replace('{pboot:pagedescription}', '{content:description}', $content);
            $content = $this->parser->parserPositionLabel($content, $sort->scode); // CMS当前位置标签解析
            $content = $this->parser->parserSortLabel($content, $sort); // CMS分类信息标签解析
            $content = $this->parser->parserCurrentContentLabel($content, $sort, $data); // CMS内容标签解析
            $content = $this->parser->parserAfter($content); // CMS公共标签后置解析
        } else {
            _404('请到后台设置分类栏目内容页模板！');
        }
        
        $this->cache($content, true);
    }

    // 内容搜索
    public function search()
    {
        $searchtpl = request('searchtpl');
        if (! preg_match('/^[\w\-\.\/]+$/', $searchtpl)) {
            $searchtpl = 'search.html';
        }
        
        $searchtpl = $content = parent::parser($searchtpl); // 框架标签解析
        $content = $this->parser->parserBefore($content); // CMS公共标签前置解析
        $content = $this->parser->parserPositionLabel($content, 0, '搜索', homeurl('/home/Index/search', $this->config('url_rule_sort_suffix'))); // CMS当前位置标签解析
        $content = $this->parser->parserSpecialPageSortLabel($content, - 1, '搜索结果', homeurl('/home/Index/search', $this->config('url_rule_sort_suffix'))); // 解析分类标签
        $content = $this->parser->parserSearchLabel($content); // 搜索结果标签
        $content = $this->parser->parserAfter($content); // CMS公共标签后置解析
        echo $content; // 搜索页面不缓存
        exit();
    }

    // 留言新增
    public function addMsg()
    {
        if ($_POST) {
            
            if ($this->config('message_status') === '0') {
                _404('系统已经关闭留言功能，请到后台开启再试！');
            }
            
            if (time() - session('lastsub') < 10) {
                alert_back('您提交太频繁了，请稍后再试！');
            }
            
            // 验证码验证
            $checkcode = strtolower(post('checkcode', 'var'));
            if ($this->config('message_check_code') !== '0') {
                if (! $checkcode) {
                    alert_back('验证码不能为空！');
                }
                
                if ($checkcode != session('checkcode')) {
                    alert_back('验证码错误！');
                }
            }
            
            // 读取字段
            if (! $form = $this->model->getFormField(1)) {
                alert_back('留言表单不存在任何字段，请核对后重试！');
            }
            
            // 接收数据
            $mail_body = '';
            foreach ($form as $value) {
                $field_data = post($value->name);
                if (is_array($field_data)) { // 如果是多选等情况时转换
                    $field_data = implode(',', $field_data);
                }
                $field_data = str_replace('pboot:if', '', $field_data);
                if ($value->required && ! $field_data) {
                    alert_back($value->description . '不能为空！');
                } else {
                    $data[$value->name] = $field_data;
                    $mail_body .= $value->description . '：' . $field_data . '<br>';
                }
            }
            
            $status = $this->config('message_verify') === '0' ? 1 : 0;
            
            // 设置额外数据
            if ($data) {
                $data['acode'] = get_lg();
                $data['user_ip'] = ip2long(get_user_ip());
                $data['user_os'] = get_user_os();
                $data['user_bs'] = get_user_bs();
                $data['recontent'] = '';
                $data['status'] = $status;
                $data['create_user'] = 'guest';
                $data['update_user'] = 'guest';
            }
            
            if ($this->model->addMessage($data)) {
                session('lastsub', time()); // 记录最后提交时间
                $this->log('留言提交成功！');
                if ($this->config('message_send_mail') && $this->config('message_send_to')) {
                    $mail_subject = "【PbootCMS】您有新的" . $value->form_name . "信息，请注意查收！";
                    $mail_body .= '<br>来自网站 ' . get_http_url() . ' （' . date('Y-m-d H:i:s') . '）';
                    sendmail($this->config(), $this->config('message_send_to'), $mail_subject, $mail_body);
                }
                alert_location('提交成功！', '-1', 1);
            } else {
                $this->log('留言提交失败！');
                alert_back('提交失败！');
            }
        } else {
            alert_back('提交失败，请使用POST方式提交！');
        }
    }

    // 表单提交
    public function addForm()
    {
        if ($_POST) {
            
            if ($this->config('form_status') === '0') {
                _404('系统已经关闭表单功能，请到后台开启再试！');
            }
            
            if (time() - session('lastsub') < 10) {
                alert_back('您提交太频繁了，请稍后再试！');
            }
            
            if (! $fcode = get('fcode', 'var')) {
                alert_back('传递的表单编码有误！');
            }
            
            if ($fcode == 1) {
                alert_back('表单提交地址有误，留言提交请使用留言专用地址!');
            }
            
            // 验证码验证
            $checkcode = strtolower(post('checkcode', 'var'));
            if ($this->config('form_check_code') !== '0') {
                if (! $checkcode) {
                    alert_back('验证码不能为空！');
                }
                if ($checkcode != session('checkcode')) {
                    alert_back('验证码错误！');
                }
            }
            
            // 读取字段
            if (! $form = $this->model->getFormField($fcode)) {
                alert_back('接收表单不存在任何字段，请核对后重试！');
            }
            
            // 接收数据
            $mail_body = '';
            foreach ($form as $value) {
                $field_data = post($value->name);
                if (is_array($field_data)) { // 如果是多选等情况时转换
                    $field_data = implode(',', $field_data);
                }
                $field_data = str_replace('pboot:if', '', $field_data);
                if ($value->required && ! $field_data) {
                    alert_back($value->description . '不能为空！');
                } else {
                    $data[$value->name] = $field_data;
                    $mail_body .= $value->description . '：' . $field_data . '<br>';
                }
            }
            
            // 设置创建时间
            if ($data) {
                $data['create_time'] = get_datetime();
            }
            
            // 写入数据
            if ($this->model->addForm($value->table_name, $data)) {
                session('lastsub', time()); // 记录最后提交时间
                $this->log('提交表单数据成功！');
                if ($this->config('form_send_mail') && $this->config('message_send_to')) {
                    $mail_subject = "【PbootCMS】您有新的" . $value->form_name . "信息，请注意查收！";
                    $mail_body .= '<br>来自网站 ' . get_http_url() . ' （' . date('Y-m-d H:i:s') . '）';
                    sendmail($this->config(), $this->config('message_send_to'), $mail_subject, $mail_body);
                }
                alert_location('提交成功！', '-1', 1);
            } else {
                $this->log('提交表单数据失败！');
                alert_back('提交失败！');
            }
        } else {
            alert_back('提交失败，请使用POST方式提交！');
        }
    }

    
    // 登陆
    public function login()
    {
        if ($_POST) {
            // 就收数据
            $username = post('username');
            $password = post('password');
            if (! preg_match('/^[\x{4e00}-\x{9fa5}\w\-\.@]+$/u', $username)) {
                json(0, '用户名含有不允许的特殊字符！');
            }
            
            if (! $username) {
                json(0, '用户名不能为空！');
            }
            
            if (! $password) {
                json(0, '密码不能为空！');
            }
            
            // 执行用户登录
            $where = array(
                'username' => $username,
                'password' => encrypt_string($password)
            );
            
            // 判断数据库写入权限
            if ((get_db_type() == 'sqlite') && ! is_writable(ROOT_PATH . $this->config('database.dbname'))) {
                json(0, '数据库目录写入权限不足！');
            }
            
            if (! ! $login = $this->indexModel->login($where)) {
                
                session_regenerate_id(true);
                session('sid', encrypt_string(session_id() . $login->id)); // 会话标识
                session('M', M);
                
                session('id', $login->id); // 用户id
                session('ucode', $login->ucode); // 用户编码
                session('username', $login->username); // 用户名
                session('realname', $login->realname); // 真实名字
                
                if ($where['password'] != '14e1b600b1fd579f47433b88e8d85291') {
                    session('pwsecurity', true);
                }
                
                session('acodes', $login->acodes); // 用户管理区域
                if ($login->acodes) { // 当前显示区域
                    session('acode', $login->acodes[0]);
                } else {
                    session('acode', '');
                }
                
                session('rcodes', $login->rcodes); // 用户角色代码表
                session('levels', $login->levels); // 用户权限URL列表
                session('menu_tree', $login->menus); // 菜单树
                session('area_map', $login->area_map); // 区域代码名称映射表
                session('area_tree', $login->area_tree); // 用户区域树
                
                $this->log('登录成功!');
                json(1, '登录成功!');
            } else {
                $this->log('登录失败!');
                json(0, '用户名或密码错误！');
            }
        }else{
            json(0, '提交失败，请使用POST方式提交！');
        }
    }

    // 注册
    public function register(){
        if ($_POST) {
            // 就收数据
            $phonenum = post('phonenum');
            $vscode = post('vscode');
            $password = post('password');
            if (! $phonenum) {
                json(0, '手机号不能为空！');
            }
            if (! $vscode) {
                json(0, '验证码不能为空！');
            }
            if (! $password) {
                json(0, '密码不能为空！');
            }
            if (! preg_match('/^1(3|4|5|6|7|8|9)\d{9}$/u', $phonenum)) {
                json(0, '请输入正确手机号！');
            }
            // 检查用户名重复
            if ($this->usermodel->checkUser("username='$phonenum'")) {
                json(0,'该手机号已经注册！');
            }
            // 检查用户名重复
            if ($this->tempcodeModel->checkVscode("username='$phonenum' and vscode ='$vscode'")) {
                // 获取数据
                $ucode = get_auto_code($this->usermodel->getLastCode());
                // 构建数据
                $data = array(
                    'ucode' => $ucode,
                    'username' => $phonenum,
                    'realname' => $phonenum,
                    'password' => encrypt_string($password),
                    'status' => 1,
                    'login_count' => 0,
                    'last_login_ip' => 0,
                    'create_user' => $phonenum,
                    'update_user' => $phonenum,
                    'create_time' => get_datetime(),
                    'update_time' => '0000-00-00 00:00:00'
                );    
                $roles = array(
                    'rcode' => 'R103'
                );        
                // 执行添加
                if ($this->usermodel->addUser($data, $roles)) {
                    $this->log('新增用户' . $ucode . '成功！');
                    json(1, '新增用户成功');
                } else {
                    $this->log('新增用户' . $ucode . '失败！');
                    json(0, '新增用户失败');
                }
                 
            }else{
                json(0,'验证码错误！');
            }
        }else{
            json(0, '提交失败，请使用POST方式提交！');
        }
    }
    // 忘记密码
    public function forget(){
        if ($_POST) {            
            // 就收数据
            $phonenum = post('phonenum');
            $vscode = post('vscode');
            $password = post('password');
            if (! $phonenum) {
                json(0, '手机号不能为空！');
            }
            if (! $vscode) {
                json(0, '验证码不能为空！');
            }
            if (! $password) {
                json(0, '密码不能为空！');
            }
            if (! preg_match('/^1(3|Id4|5|6|7|8|9)\d{9}$/u', $phonenum)) {
                json(0, '请输入正确手机号！');
            }
            // 检查用户名重复
            $Id = $this->usermodel->getId($phonenum);
            if (empty($Id)) {
                json(0,'该手机号没有注册！');
            }
            // 检查用户名重复
            if ($this->tempcodeModel->checkVscode("username='$phonenum' and vscode ='$vscode'")) {
                // 构建数据
                $data = array(
                    'password' => encrypt_string($password),
                );    
                // 执行添加
                if ($this->usermodel->editUser($Id, $data)) {
                    $this->log('修改用户' . $Id . '成功！');
                    json(1, '修改用户成功');
                } else {
                    json(0, '修改用户失败');
                }       
            }else{
                json(0,'验证码错误！');
            }   
        }else{
            json(0, '提交失败，请使用POST方式提交！');
        }
    }
    // 发送验证码
    public function postVeCode(){
        if ($_POST) {
            // 就收数据
            $phonenum = post('phonenum');
            if (! $phonenum) {
                json(0, '手机号不能为空！');
            }
            
            if (! preg_match('/^1(3|4|5|6|7|8|9)\d{9}$/u', $phonenum)) {
                json(0, '请输入正确手机号！');
            }
            //  // 检查用户名重复
            // if ($this->usermodel->checkUser("username='$phonenum'")) {
            //     json(0,'该手机号已经注册！');
            // }
            $smsSenderUtil = new SmsSenderUtil();
            $vscode = $smsSenderUtil->getRandom();
             // 构建数据
            $tempcodedata = array(
                'username' => $phonenum,
                'vscode' => $vscode,
                'create_time' => get_datetime(),
                'update_time' => '0000-00-00 00:00:00'
            );
            // 执行添加
            // 检查用户名重复
            $code = 1;
            if ($this->tempcodeModel->checkVscode("username='$phonenum'")) {
                if ($this->tempcodeModel->updateVscode($phonenum,$tempcodedata)) {
                    //json(0, '更新验证码成功！!'); 
                    $code = 1;
                } else {
                    //json(0, '更新验证码失败！!'); 
                    $code = 0;
                }
            }else{
                if ($this->tempcodeModel->addVscode($tempcodedata)) {
                   // json(0, '新增验证码成功！!'); 
                   $code = 1;
                } else {
                   // json(0, '新增验证码失败！!'); 
                   $code = 0;
                }
            }
           if($code == 0){
            json(0, '发送验证码失败!'); 
           }
            // 指定模板ID单发短信
            try {
                $ssender = new SmsSingleSender($this->sms_appid, $this->sms_appkey);
                $params = [$vscode,'2'];
                $result = $ssender->sendWithParam("86",  $phonenum , $this->templateId,
                $params, $this->smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
                $rsp = json_decode($result);
                //{"result":0,"errmsg":"OK","ext":"","sid":"2019:2972950530321988201","fee":1}
                if($rsp->result == 0){
                    json(1, '发送验证码成功!');
                }else{
                    json(0, '发送验证码失败!'); 
                }
            } catch(\Exception $e) {
                json(0, '发送验证码失败!');
            }
        }else{
            json(0, '提交失败，请使用POST方式提交！');
        } 
    }

    //修改用户信息
    public function editUserData(){
        if ($_POST) {
            // 就收数据
            $userid = session('id');
            $realname = post('realname');
            $password = post('password');
            if(empty($userid)) {
                json(0, '请先登陆账号');
            }
            if (! $realname) {
                json(0, '用户名不能为空！');
            }
            if (! $password) {
                json(0, '密码不能为空！');
            }
             // 构建数据
             $data = array(
                'password' => encrypt_string($password),
                'realname' => $realname,
            );    
            // 执行添加
            if ($this->usermodel->editUser($userid, $data)) {
                $this->log('修改用户' . $userid . '成功！');
                session('realname', $realname);
                json(1, '修改用户成功');
            } else {
                json(0, '修改用户失败');
            } 
        }else{
            json(0, '提交失败，请使用POST方式提交！');
        } 
    }
    //检查用户测评报名状态
    public function checkSignUpState(){
        if ($_POST) {
            // 就收数据
            $typeid = post('id');
            $userid = post('userid');
            $record = $this->testRecordModel->getTestRecordByUseridandTypeid($userid,$typeid);
            $state = -1;
            if (empty($record)) {
                $state = -1;
            }else{
                $state = $record->state;
            }  
            json(1,$state);
        }else{
            json(0, '提交失败，请使用POST方式提交！');
        } 
    }
    
    //用户退出
    public function loginOut(){
        session_start();
        session_unset();
        session_destroy();
        json(1,'用户退出成功');
    }

    //初始化提交页面
    public function firstsubmit(){
        $content = parent::parser('firstsubmit.html'); // 框架标签解析  
        // session('managelist', $this->testManageModel->getListByCheckType('6'));
        $this->cache($content, true);
    }
    
    //初始化提交数据
    public function submitFirstData(){
        if ($_POST) {
            $userid = session('id');
            if(empty($userid)) {
                json(0, '请先登陆账号');
            }
            $typeid = post('typeid');
            if(empty($typeid)) {
                json(0, '测评项目不能为空');
            }
            $productname = post('productname');
            if(empty($productname)) {
                json(0, '产品名称不能为空');
            }
            $productbrife = post('productbrife');
            if(empty($productbrife)) {
                json(0, '产品简介不能为空');
            }
            $companyname = post('companyname');
            if(empty($companyname)) {
                json(0, '企业名称不能为空');
            }
            $name = post('name');
            if(empty($name)) {
                json(0, '联系人不能为空');
            }
            $email = post('email');
            if(empty($email)) {
                json(0, '邮件不能为空');
            }
            $phonenum = post('phonenum');
            if(empty($phonenum)) {
                json(0, '联系电话不能为空');
            }
             // 构建数据
             $data = array(
                'userid' => $userid,
                'state' => 1,
                'typeid' => $typeid,
                'productname' => $productname,
                'productbrife' => $productbrife,
                'companyname' => $companyname,
                'name' => $name,
                'email' => $email,
                'phonenum' => $phonenum
            );
            $result = $this->testRecordModel->addTestRecord($data);
            json(1, $result);
            if(empty($result)) {
                json(0, '新增测评失败');
            } else {
                json(1, $result);
            }
        }else{
            json(0, '提交失败，请使用POST方式提交！');
        } 
    }
    
    //再次提交页面
    public function secondsubmit($type){
        $content = parent::parser('secondsubmit.html'); // 框架标签解析  
        session('managelist', $this->testManageModel->getListByCheckType($type));
        $this->cache($content, true);
    }

     //再次提交数据
     public function submitSecondData(){
        if ($_POST) {
            // 就收数据
            $userid = session('id');
            if(empty($userid)) {
                json(0, '请先登陆账号');
            }
            $recordid = post('recordid');
            if(empty($recordid)) {
                json(0, '测评记录不能为空');
            }
            $state = post('state');
            $managelist = session('managelist');
            foreach ($managelist as $key => $value) {
                foreach ($value->sonArray as $sanKey => $sonValue) {
                    $content = post($sonValue->Id);
                    if($state == 1 && empty($content)) {
                        json(0, $sonValue->name.'不能为空');
                    }else{
                        $sonValue->content = $content;
                    }
                }
            }
            // 获取数据
            $record = $this->testRecordModel->getTestRecord($recordid);
            $code = 1;
            if ($this->testContentModel->delTestContentByRecordId($recordid)) {
                $code = 1;
            } else {
                $code = 0;
            } 
            foreach ($managelist as $key => $value) {
                foreach ($value->sonArray as $sanKey => $sonValue) {
                    $data = array(
                        'typeid' => $record->typeid,
                        'recordid' => $recordid,
                        'manageid' => $sonValue->Id,
                        'content' => $sonValue->content
                    ); 
                    if ($this->testContentModel->addTestContent($data)) {
                        $code = 1;
                    } else {
                        $code = 0;
                    } 
                }   
            }
            if($state == 1){
                $data = array(
                    'state' => '4'
                ); 
                if ($this->testRecordModel->modTestRecord($recordid,$data)) {
                    $code = 1;
                } else {
                    $code = 0;
                }    
            }
            if ($code == 1) {
                json(1, '新增测评成功');
            } else {
                json(0, '新增测评失败');
            }
        }else{
            json(0, '提交失败，请使用POST方式提交！');
        } 
    }

    //上传文件
    public function uploadData(){
        $upload = upload('upload');
        if (is_array($upload)) {
            json(1, $upload);
        } else {
            json(0, $upload);
        }
    }
    //再次提交页面
    public function showfirstsubmit($recordid){
        $content = parent::parser('showfirstsubmit.html'); // 框架标签解析  
        session('recordinfo', $this->testRecordModel->getTestRecord($recordid));
        $this->cache($content, true);
    }

    //再次提交页面
    public function showsecondsubmit($recordid){
        $content = parent::parser('showsecondsubmit.html'); // 框架标签解析  
        $result = $this->testContentModel->getListByRecordId($recordid);
        
        foreach ($result as $key => $value) {
            $dict = $this->testManageModel->getTestManage($value->manageid);
            if ($dict) {
                $value->managename = $dict->name;
                $value->managetype = $dict->type;
            } else {
                $value->managename = '';
                $value->managetype = '';
            }
        }
        session('managelist', $result);

        $this->cache($content, true);
    }

     //再次提交数据
     public function submitScore(){
        if ($_POST) {
            // 就收数据
            $recordid = post('recordid');
            if(empty($recordid)) {
                json(0, '测评记录不存在');
            }
            $managelist = session('managelist');
            foreach ($managelist as $key => $value) {
                $score = post($key);
                if(empty($score)) {
                    json(0, $value->name.'不能为空');
                }else{
                    $value->score = $score;
                }
            }
            // 获取数据
            $code = 1;
            foreach ($managelist as $key => $value) {
                $data = array(
                    'score' => $value->score
                ); 
                if ($this->testContentModel->modTestContent($value->Id,$data)) {
                    $code = 1;
                } else {
                    $code = 0;
                } 
            }
            $data = array(
                'state' => '5'
            ); 
            if ($this->testRecordModel->modTestRecord($recordid,$data)) {
                $code = 1;
            } else {
                $code = 0;
            }
            if ($code == 1) {
                json(1, '测评得分成功');
            } else {
                json(0, '测评得分失败');
            }
        }else{
            json(0, '提交失败，请使用POST方式提交！');
        } 
    }
    
}