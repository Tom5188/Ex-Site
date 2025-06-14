<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\{
    MailMessage,
    Users,
    UserChat
};
use App\Utils\Hash;

class MailMessageController extends Controller
{
    public function index()
    {
        return view("admin.message.index");
    }
    
    public function list(Request $request){
        $limit = $request->get('limit');
        $start_time = strtotime($request->get('start_time', 0));
        $end_time = strtotime($request->get('end_time', 0));
        
        $list = MailMessage::where(function ($query) use ($start_time) {
            if (!empty($start_time)) {
                 $query->where('create_time', '>=', $start_time);
            }
        })->where(function ($query) use ($end_time) {
            if ($end_time != '') {
                $query->where('create_time', '<=', $end_time);
            }
        })->orderBy('id', 'desc')->paginate($limit);
        
        return $this->layuiData($list);
    }
    
    public function add(){
        $user_list = Users::all();
        return view("admin.message.add", [
            'user_list' => $user_list
        ]);
    }
    
    public function postAdd(Request $request){
        $userIds = Input::get("userIds");
        $title = Input::get("title");
        $content = Input::get("content");
        $abstract = Input::get("abstract");
        
        if (empty($title) || empty($content)) return $this->error("参数错误");

        
        DB::beginTransaction();
        $mailMessage = new MailMessage();
        try {
            
            $mailMessage->user_ids = $userIds ?? '';
            $mailMessage->title = $title ?? '';
            $mailMessage->content = $content ?? '';
            $mailMessage->status = 1;
            $mailMessage->abstract = $abstract;
            $mailMessage->create_time = time();
            $mailMessage->save();
            
            DB::commit();
            $send = ['type' => 'mail_message', 'period' => true];
            UserChat::sendChat($send);
            return $this->success('保存发送成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    
    public function post_add(Request $request){
        $userIds = Input::get("userIds");
        $title = Input::get("title");
        $content = Input::get("content");
        
        if (empty($title) || empty($content)) return $this->error("参数错误");

        
        DB::beginTransaction();
        $mailMessage = new MailMessage();
        try {
            
            $mailMessage->user_ids = $userIds ?? '';
            $mailMessage->title = $title ?? '';
            $mailMessage->content = $content ?? '';
            $mailMessage->status = 0;
            $mailMessage->abstract = $abstract;
            $mailMessage->create_time = time();
            $mailMessage->save();
            
            DB::commit();
            return $this->success('保存成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    
    public function del(Request $request)
    {
        $id = $request->get('id');
        $userreal = MailMessage::find($id);
        if (empty($userreal)) {
            $this->error("信息未找到");
        }
        try {

            $userreal->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }
    
    public function fs(Request $request){
        $id = $request->get('id');
        $userreal = MailMessage::find($id);
        if (empty($userreal)) {
            $this->error("信息未找到");
        }
        try {
            $userreal->status = 1;
            $userreal->save();
            return $this->success('发送成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }
    
    
}
