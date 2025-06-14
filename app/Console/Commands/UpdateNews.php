<?php 

namespace App\Console\Commands;

use App\News;
use App\UserReal;
use Illuminate\Console\Command;
class UpdateNews extends Command
{
    protected $signature = "update_news";
    protected $description = "更新项目的新闻";
    public function __construct()
    {
        parent::__construct();
    }
    protected $searches = ["cfmcoin" => "toex"];
    public function handle()
    {
   
        $news_list = News::get();
        foreach ($news_list as $news) {
            foreach ($this->searches as $k => $v) {
      
                $news->content = str_replace($k, $v, $news->content);

                $news->title = str_replace($k, $v, $news->title);

                $news->keyword = str_replace($k, $v, $news->keyword);

                $news->abstract = str_replace($k, $v, $news->abstract);

                $news->thumbnail = str_replace($k, $v, $news->thumbnail);

                $news->cover = str_replace($k, $v, $news->cover);
            }
            $news->save();
        }
    }
}
?>