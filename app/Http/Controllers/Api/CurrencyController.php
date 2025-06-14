<?php

namespace App\Http\Controllers\Api;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Utils\RPC;
use App\Currency;
use App\TransactionComplete;
use App\Users;
use App\MarketHour;
use App\CurrencyQuotation;
use App\AreaCode;
use App\UsersWallet;
class CurrencyController extends Controller
{
    public function area_code()
    {
        $LHaaruJ = AreaCode::get()->toArray();
        return $this->success($LHaaruJ);
    }
    public function rangeNew(){
        $hot = DB::table('currency_quotation')
            ->join('currency', 'currency_quotation.currency_id', '=', 'currency.id')
            ->where('currency.is_display', 1)
            ->where('currency.is_legal', 1)
            ->orderBy("currency_quotation.volume","desc")
            ///->where('currency_quotation.change','>', 0)
            ->limit(6)
            ->get();
            
        $increase = DB::table('currency_quotation')
            ->join('currency', 'currency_quotation.currency_id', '=', 'currency.id')
            ->where('currency.is_display', 1)
            ->where('currency.is_legal', 1)
            //->where('currency_quotation.change','>', 0)
            ->orderBy("currency_quotation.change","desc")
            ->limit(6)
            ->get();
            $currency['hot'] = $hot;
             $currency['increase'] = $increase;
        return $this->success($currency);
    }
    public function lists()
    {
        $CnrtCHJ = Currency::where('is_display', 1)->orderBy('sort', 'asc')->get()->toArray();
        $ZwZsbXQ = array();
        foreach ($CnrtCHJ as $LkZuCYJ) {
            if ($LkZuCYJ['is_legal']) {
                array_push($ZwZsbXQ, $LkZuCYJ);
            }
        }
        return $this->success(array('currency' => $CnrtCHJ, 'legal' => $ZwZsbXQ));
    }
    public function lever()
    {
        $ORXHJHJ = Currency::where('is_display', 1)->orderBy('sort', 'asc')->get()->toArray();
        $GDKcLQQ = array();
        foreach ($ORXHJHJ as $tvYImDv) {
            if ($tvYImDv['is_lever']) {
                array_push($GDKcLQQ, $tvYImDv);
            }
        }
        $QQXtbYJ = strtotime(date('Y-m-d'));
        foreach ($GDKcLQQ as $lZliuuJ) {
            $NCAnfzQ = array();
            foreach ($ORXHJHJ as $wlhJtsJ) {
                if ($wlhJtsJ['id'] != $lZliuuJ['id']) {
                    $kbWrlVv = 0;
                    $qpXftSJ = 0;
                    $ilDGdcQ = '';
                    $DpAftsJ = '';
                    $VVYVhSv = 0.0;
                    $ilDGdcQ = TransactionComplete::orderBy('create_time', 'desc')->where('currency', $wlhJtsJ['id'])->where('legal', $lZliuuJ['id'])->first();
                    $DpAftsJ = TransactionComplete::orderBy('create_time', 'desc')->where('create_time', '<', $QQXtbYJ)->where('currency', $wlhJtsJ['id'])->where('legal', $lZliuuJ['id'])->first();
                    !empty($ilDGdcQ) && ($kbWrlVv = $ilDGdcQ->price);
                    !empty($DpAftsJ) && ($qpXftSJ = $DpAftsJ->price);
                    if (empty($kbWrlVv)) {
                        if ($qpXftSJ) {
                            $VVYVhSv = -100.0;
                        }
                    } else {
                        if ($qpXftSJ) {
                            $VVYVhSv = ($kbWrlVv - $qpXftSJ) / $qpXftSJ;
                        } else {
                            $VVYVhSv = 100.0;
                        }
                    }
                    array_push($NCAnfzQ, array('id' => $wlhJtsJ['id'], 'name' => $wlhJtsJ['name'], 'last_price' => $kbWrlVv, 'proportion' => $VVYVhSv, 'yesterday_last_price' => $qpXftSJ));
                }
            }
            $lZliuuJ['quotation'] = $NCAnfzQ;
        }
        return $this->success($GDKcLQQ);
    }
    public function TradeMarket(Request $request)
    {
        $quo = Currency::find($request->input('legal_id'))->name;
        $base = Currency::find($request->input('currency_id'))->name;


        $symbol = strtolower($base . $quo);
//        var_dump($base,$quo);
//        die;
        $url = "https://api.huobi.pro/market/history/trade?symbol={$symbol}&size=20";
        $res = json_decode(file_get_contents($url), true);


        $rsp = [];
        if (isset($res['data'])) {

            foreach ($res['data'] as $val) {
                if (count($rsp) >= 20) {
                    break;
                }
                array_walk($val['data'], function (&$v) use (& $rsp) {

                    $v['time'] = date('H:i:s', intVal($v['ts'] / 1000));
                    if (count($rsp) >= 20) {

                    } else {
                        $rsp[] = $v;
                    }
                });
            }
        }

//        var_dump($rsp);
        return $this->success($rsp);
    }
    public function quotation_tian()
    {
        $BrsWBjv = Currency::where('is_display', 1)->orderBy('sort', 'asc')->get()->toArray();
        $mOnknFv = array();
        foreach ($BrsWBjv as $qMSPJTQ) {
            if ($qMSPJTQ['is_legal']) {
                array_push($mOnknFv, $qMSPJTQ);
            }
        }
        $fjllgVJ = strtotime(date('Y-m-d'));
        foreach ($mOnknFv as $zlqdSQv) {
            $XSjKELv = array();
            foreach ($BrsWBjv as $NAaKMjv => $UNHGjLv) {
                $zlqdSQv['quotation'] = CurrencyQuotation::orderBy('add_time', 'desc')->where('legal_id', $zlqdSQv['id'])->get()->toArray();
            }
        }
        return $this->success($mOnknFv);
    }
    public function newTimeshars(Request $request)
    {
        echo 123;
        exit;
        $iriUDGJ = $request->get('symbol');
        $VmCMUNQ = $request->get('period');
        $biRnHJv = $request->get('from', null);
        $cAMDfuJ = $request->get('to', null);
        $iriUDGJ = strtoupper($iriUDGJ);
        $KuPpbTJ = ['1min' => 5, '5min' => 6, '15min' => 1, '30min' => 7, '60min' => 2, '1D' => 4, '1W' => 8, '1M' => 9, '1day' => 4, '1week' => 8, '1mon' => 9, '1year' => 10];
        $lKNcYmQ = array_keys($KuPpbTJ);
        $fVvRJYJ = array_values($KuPpbTJ);
        if ($biRnHJv == null || $cAMDfuJ == null) {
            return ['code' => -1, 'msg' => 'error: start time or end time must be filled in', 'data' => null];
        }
        if ($biRnHJv > $cAMDfuJ) {
            return ['code' => -1, 'msg' => 'error: start time should not exceed the end time.', 'data' => null];
        }
        if ($iriUDGJ == '' || stripos($iriUDGJ, '/') === false) {
            return ['code' => -1, 'msg' => 'error: symbol invalid', 'data' => null];
        }
        if ($VmCMUNQ == '' || !in_array($VmCMUNQ, $lKNcYmQ)) {
            return ['code' => -1, 'msg' => 'error: period invalid', 'data' => null];
        }
        $adlYLXQ = strtotime(date('Y-m-d H:i'));
        if ($VmCMUNQ == '1min' && $cAMDfuJ >= $adlYLXQ) {
            $cAMDfuJ = $adlYLXQ - 1;
        }
        $mjWkkrv = $KuPpbTJ[$VmCMUNQ];
        $iriUDGJ = explode('/', $iriUDGJ);
        list($HSQfSkJ, $JnRWhhQ) = $iriUDGJ;
        $HSQfSkJ = Currency::where('name', $HSQfSkJ)->where('is_display', 1)->first();
        $JnRWhhQ = Currency::where('name', $JnRWhhQ)->where('is_display', 1)->where('is_legal', 1)->first();
        if (!$HSQfSkJ || !$JnRWhhQ) {
            return ['code' => -1, 'msg' => 'error: symbol not exist', 'data' => null];
        }
        $jqbYHhJ = $JnRWhhQ->id;
        $ElYLALJ = $HSQfSkJ->id;
        $fFVCeHJ = MarketHour::orderBy('day_time', 'asc')->where('currency_id', $ElYLALJ)->where('legal_id', $jqbYHhJ)->where('type', $mjWkkrv)->where('day_time', '>=', $biRnHJv)->where('day_time', '<=', $cAMDfuJ)->get();
        $rXDaOwJ = array();
        if ($fFVCeHJ) {
            foreach ($fFVCeHJ as $qhNdsMJ => $ncfMSiJ) {
                $KuIMXuv = array('open' => $ncfMSiJ->start_price, 'close' => $ncfMSiJ->end_price, 'high' => $ncfMSiJ->highest, 'low' => $ncfMSiJ->mminimum, 'volume' => $ncfMSiJ->number, 'time' => $ncfMSiJ->day_time * 1000);
                array_push($rXDaOwJ, $KuIMXuv);
            }
        } else {
            foreach ($fFVCeHJ as $qhNdsMJ => $ncfMSiJ) {
                $KuIMXuv = null;
                array_push($rXDaOwJ, $KuIMXuv);
            }
        }
        return ['code' => 1, 'msg' => 'success:)', 'data' => $rXDaOwJ];
    }
    public function klineMarket(Request $request)
    {
        $ZKtQpVv = $request->input('symbol');
        $IlcOTQQ = $request->input('period');
        $skXnrZv = $request->input('from', null);
        $lhpKpPv = $request->input('to', null);
        $ZKtQpVv = strtoupper($ZKtQpVv);
        $wjttOOQ = [];
        $RLgCtMv = ['1min' => '1min', '5min' => '5min', '15min' => '15min', '30min' => '30min', '60min' => '60min', '1H' => '60min', '1D' => '1day', '1W' => '1week', '1M' => '1mon', '1Y' => '1year', '1day' => '1day', '1week' => '1week', '1mon' => '1mon', '1year' => '1year'];
        if ($skXnrZv == null || $lhpKpPv == null) {
            return ['code' => -1, 'msg' => 'error: from time or to time must be filled in', 'data' => $wjttOOQ];
        }
        if ($skXnrZv > $lhpKpPv) {
            return ['code' => -1, 'msg' => 'error: from time should not exceed the to time.', 'data' => $wjttOOQ];
        }
        $nKJlPuQ = array_keys($RLgCtMv);
        if ($IlcOTQQ == '' || !in_array($IlcOTQQ, $nKJlPuQ)) {
            return ['code' => -1, 'msg' => 'error: period invalid', 'data' => $wjttOOQ];
        }
        if ($ZKtQpVv == '' || stripos($ZKtQpVv, '/') === false) {
            return ['code' => -1, 'msg' => 'error: symbol invalid', 'data' => $wjttOOQ];
        }
        $IlcOTQQ = $RLgCtMv[$IlcOTQQ];
        list($djNwDwJ, $QhRIIIv) = explode('/', $ZKtQpVv);
        $bUjuraQ = Currency::where('name', $djNwDwJ)->where('is_display', 1)->first();
        $IJLSyCQ = Currency::where('name', $QhRIIIv)->where('is_display', 1)->where('is_legal', 1)->first();
        if (!$bUjuraQ || !$IJLSyCQ) {
            return ['code' => -1, 'msg' => 'error: symbol not exist', 'data' => null];
        }
        $wjttOOQ = MarketHour::getEsearchMarket($djNwDwJ, $QhRIIIv, $IlcOTQQ, $skXnrZv, $lhpKpPv);
        $wjttOOQ = array_map(function ($value) {
            $value['time'] = $value['id'] * 1000;
            $value['volume'] = $value['amount'] ?? 0;
            return $value;
        }, $wjttOOQ);
        return ['code' => 1, 'msg' => 'success', 'data' => $wjttOOQ];
    }
    public function newQuotation()
    {
        $BmnYXrJ = Currency::with('quotation')->whereHas('quotation', function ($query) {
            $query->where('is_display', 1);
        })->where('is_display', 1)->where('is_legal', 1)->orderBy('sort','asc')->get();
        return $this->success($BmnYXrJ);
    }
    public function dealInfo()
    {
        $TAXjNyv = Input::get('legal_id');
        $XhwvDSQ = Input::get('currency_id');
        if (empty($TAXjNyv) || empty($XhwvDSQ)) {
            return $this->error('参数错误');
        }
        $bltKpAQ = Currency::where('is_display', 1)->where('id', $TAXjNyv)->where('is_legal', 1)->first();
        $AmPMnfQ = Currency::where('is_display', 1)->where('id', $XhwvDSQ)->first();
        if (empty($bltKpAQ) || empty($AmPMnfQ)) {
            return $this->error('币未找到');
        }
        $gEUGyVJ = Input::get('type', '1');
        $PWRLYvQ = 60;
        switch ($gEUGyVJ) {
            case 2:
                $PWRLYvQ = 900;
                break 1;
            case 3:
                $PWRLYvQ = 3600;
                break 1;
            case 4:
                $PWRLYvQ = 14400;
                break 1;
            case 5:
                $PWRLYvQ = 86400;
                break 1;
            default:
                $PWRLYvQ = 60;
        }
        $GlYqXKv = time();
        $qTstiMQ = 0;
        $dViLeaQ = TransactionComplete::orderBy('create_time', 'desc')->where('currency', $XhwvDSQ)->where('legal', $TAXjNyv)->first();
        $dViLeaQ && ($qTstiMQ = $dViLeaQ->price);
        $aRpAMwv = TransactionComplete::getQuotation($TAXjNyv, $XhwvDSQ, $GlYqXKv - $PWRLYvQ, $GlYqXKv);
        $vaTCiiv = array();
        for ($LVnvAcQ = 0; $LVnvAcQ < 10; $LVnvAcQ++) {
            $KXhrmpJ = $GlYqXKv - $LVnvAcQ * $PWRLYvQ;
            $yrQDxmQ = $KXhrmpJ - $PWRLYvQ;
            $PtbcDYQ = array();
            $PtbcDYQ = $aRpAMwv = TransactionComplete::getQuotation($TAXjNyv, $XhwvDSQ, $yrQDxmQ, $KXhrmpJ);
            array_push($vaTCiiv, $PtbcDYQ);
        }
        return $this->success(array('legal' => $bltKpAQ, 'currency' => $AmPMnfQ, 'last_price' => $qTstiMQ, 'now_quotation' => $aRpAMwv, 'quotation' => $vaTCiiv));
    }
    public function userCurrencyList()
    {
        $user_id = Users::getUserId();
        $XOylMHJ = Currency::where('is_display', 1)->orderBy('sort', 'desc')->get();
        $XOylMHJ = $XOylMHJ->filter(function ($item, $key) {
            $fRtZmfQ = array_sum([$item->is_legal, $item->is_lever, $item->is_match, $item->is_micro]);
            return $fRtZmfQ > 1;
        })->values();
        $XOylMHJ->transform(function ($item, $key) use($user_id) {
            $VxDXWbJ = UsersWallet::where('user_id', $user_id)->where('currency', $item->id)->first();
            $item->setVisible(['id', 'name', 'is_legal', 'is_lever', 'is_match', 'is_micro', 'wallet']);
            return $item->setAttribute('wallet', $VxDXWbJ);
        });
        return $this->success($XOylMHJ);
    }
}