<?php

namespace App\Http\Controllers\Admin;

use App\Currency;
use App\CurrencyMatch;
use App\MarketHour;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use App\MyQuotation as NeedleModel;

class MyQuotation extends \App\Http\Controllers\Admin\Controller
{
    //
    public function index(Request $request)
    {   
        
        $bases=DB::table("currency_matches")->where("market_from",1)->select("currency_id")->get();
        $bases=json_decode(json_encode($bases),true);
        foreach ($bases as $k=>$v){
            $one=DB::table("currency")->where("id",$v["currency_id"])->select("id","name")->first();
            $bases[$k]["name"]=$one->name;
        }
        $base=$request->get("base",$bases[0]["name"]);
        
        $news = NeedleModel::where("base",$base)->orderBy('id', 'desc')->paginate(10);
        $news->appends(['base'=>$base])->render();
        $data = [
            'currencys' => $currencys = CurrencyMatch::where('market_from', 3)->get(),
            'news' => $news
        ];
        $res=[
            'data' => $data,
            "bases"=>$bases,
            "base"=>$base
        ];
        //$res->appends(['base'=>$base])->render();
       
        return view('admin.needle.quotation', $res);
    }

    public function lists(Request $request)
    {

        $param = [
            'index' => 'market.quotation',
            'type' => 'doc',//$type,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['base' => $request->get('currency')]],
                        ],
                    ],
                ],
                'sort' => [
                    'itime' => ['order' => 'asc']
                ],
                'size' => $request->get('limit'),
                'from' => ($request->get('page') - 1) * $request->get('limit')
            ],
        ];
        $esclient = MarketHour::getEsearchClient();
        $result = $esclient->search($param);
        $total = 0;
        $data = [];
        if (isset($result['hits'])) {
            $data = array_column($result['hits']['hits'], '_source');
            $total = $result['hits']['total']['value'];
        }
        $res = ['code' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $data];
        return $res;
    }

    public function delete(Request $request)
    {
        $param = [
            'index' => 'market.quotation',
            'type' => 'doc',//$type,
        ];
    }

    public function reset(Request $request)
    {
        $param = [
            'index' => 'market.quotation',
        ];
        $eclient = MarketHour::getEsearchClient();
        $eclient->indices()->delete($param);

        foreach (['5min', '15min', '30min', '60min', '1day', '1week', '1mon'] as $v) {
            try {
                $param = [
                    'index' => 'market.kline.' . $v,
                ];
                $eclient = MarketHour::getEsearchClient();
                $eclient->indices()->delete($param);
            } catch (\Exception $e) {

            }

        }
        return ['code' => 1];
    }

    public static function needleList($num = 0, $name)
    {
        $esclient = MarketHour::getEsearchClient();

        $param = [
            'index' => 'market.quotation',
            'type' => 'doc',//$type,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['base' => $_POST]],
                            ['match' => ['base-currency' => $base_currency]],
                            ['match' => ['quote-currency' => $quote_currency]],
                        ],
                        'filter' => [
                            'range' => [
                                'id' => [
                                    'gte' => $from,
                                    'lte' => $to,
                                ],
                            ],
                        ],
                    ],
                ],
                'sort' => [
                ],
                'size' => 20,
                'from' => 20
            ],
        ];
        $result = $esclient->search($param);
        if (isset($result['hits'])) {
            $data = [];
//            foreach($result['hits']['hits'] as $val)
//            {
//                $val['_source'];
//            }
            $data = array_column($result['hits']['hits'], '_source');

            return $data;
        } else {
            return [];
        }
//        $news_query = NeedleModel::where(function ($query) use ($cId) {
//            $cId > 0 && $query->where('id', $cId);
//        })->orderBy('id', 'desc');
        $news = $num != 0 ? $news_query->paginate($num) : $news_query->get();
        return $news;
    }
}
