<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\News;
use App\Setting;
use App\NewsCategory;

class NewsController extends Controller
{

    public function kefu(Request $request)
    {
       
        $mobile=Setting::getValueByKey("zxkf_url","");
        return $this->success($mobile);
    }
    public function get(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        
        $news = News::find($id);
        
        return $this->success($news);
    }

    public function getUserAgreement(Request $request)
    {
        $lang = $request->get('lang', '') ?: session()->get('lang');
        $lang == '' && $lang = 'en';

        $news = News::where('c_id', 41)->where('lang', $lang)->first();
        if (!$news) {
            $news = News::where('c_id', 41)->where('lang', 'en')->first();
        }

        return $this->success($news);
    }

    public function getFAQ(Request $request)
    {

        $lang = $request->get('lang', '') ?: session()->get('lang');
        $lang == '' && $lang = 'en';

        $news = News::where('c_id', 18)->where('lang', $lang)->first();
        if (!$news) {
            $news = News::where('c_id', 18)->where('lang', 'en')->first();
        }

        return $this->success($news);
    }

    public function getContactUs(Request $request)
    {

        $lang = $request->get('lang', '') ?: session()->get('lang');
        $lang == '' && $lang = 'en';

        $news = News::where('c_id', 33)->where('lang', $lang)->first();
        if (!$news) {
            $news = News::where('c_id', 33)->where('lang', 'en')->first();
        }

        return $this->success($news);
    }
    
    public function general(Request $request)
    {
        $lang = $request->get('lang', '') ?: session()->get('lang');
        $lang == '' && $lang = 'en';
        $c_id = $request->get('c_id', '');
        $news = News::where('c_id', $c_id)->where('lang', $lang)->first();
        return $this->success($news);
    }

    public function getAboutUs(Request $request)
    {

        $lang = $request->get('lang', '') ?: session()->get('lang');
        $lang == '' && $lang = 'en';

        $news = News::where('c_id', 19)->where('lang', $lang)->first();
        if (!$news) {
            $news = News::where('c_id', 19)->where('lang', 'en')->first();
        }

        return $this->success($news);
    }

    public function getOperationalCompliance(Request $request)
    {

        $lang = $request->get('lang', '') ?: session()->get('lang');
        $lang == '' && $lang = 'en';

        $news = News::where('c_id', 34)->where('lang', $lang)->first();
        if (!$news) {
            $news = News::where('c_id', 34)->where('lang', 'en')->first();
        }

        return $this->success($news);
    }
    
     public function getAgreement(Request $request)
    {

        $lang = $request->get('lang', '') ?: session()->get('lang');
        $lang == '' && $lang = 'en';

        $news = News::where('c_id', 35)->where('lang', $lang)->first();
        if (!$news) {
            $news = News::where('c_id', 35)->where('lang', 'en')->first();
        }

        return $this->success($news);
    }

    //帮助中心,新闻分类
    public function getCategory()
    {
        $results = NewsCategory::where('is_show', 1)->orderBy('sorts')->get(['id', 'name'])->toArray();
        return $this->success($results);
    }

    //推荐新闻
    public function recommend()
    {
        $results = News::where('recommend', 1)->orderBy('id', 'desc')->get(['id', 'title', 'c_id'])->toArray();
        return $this->success($results);
    }

    // 获取分类下的文章
    public function getArticle(Request $request)
    {
        $limit = $request->get('limit', 15);
        $page = $request->get('page', 1);
        $category_id = $request->get('c_id');
        $lang = $request->get('lang', '') ?: session()->get('lang');
        $lang == '' && $lang = 'zh';
        if (empty($category_id)) {
            $article = News::where('lang', $lang)
                ->orderBy('sorts', 'desc')
                ->orderBy('id', 'desc')
                ->paginate($limit);
        } else {
            $article = News::where('lang', $lang)
                ->where('c_id', $category_id)
                ->orderBy('sorts', 'desc')
                ->orderBy('id', 'desc')
                ->paginate($limit, ['*'], 'page', $page);
        }
        //dd($article);
        foreach ($article->items() as &$value) {
            unset($value->recommend);
            unset($value->display);
            unset($value->discuss);
            unset($value->author);
            unset($value->audit);
            unset($value->browse_grant);
            unset($value->keyword);
            unset($value->views);
            unset($value->update_time);
        }
        $version = Setting::where('key','version')->limit(1)->first();
        $version['value'] = explode(",",$version['value']);
        
        return $this->success(array(
            'version'=>$version,
            "list" => $article->items(), 'count' => $article->total(),
            "page" => $page, "limit" => $limit
        ));
    }

    //获取返佣规则新闻
    public function getInviteReturn()
    {

        $c_id = 23;//返佣类型
        $news = News::where('c_id', $c_id)->orderBy('id', 'desc')->first();
        if (empty($news)) {
            return $this->error('新闻不存在');
        }
        $data['news'] = $news;
        //相关新闻
        $article = News::where('c_id', $c_id)->where('id', '<>', $news->id)->orderBy('id', 'desc')->get(['id', 'c_id', 'title'])->toArray();

        $data['relation_news'] = $article;
        
        return $this->success($data);
    }

    //获取返佣规则新闻
    public function getIndexPopup(Request $request)
    {
        $c_id = 32;//首页弹窗类型

        $lang = $request->get('lang', '');
        $lang == '' && $lang = 'zh';
        $news = News::where('c_id', $c_id)
            ->where('lang',$lang)
            ->orderBy('id', 'desc')
            ->first();
        return $this->success($news);
    }

}
