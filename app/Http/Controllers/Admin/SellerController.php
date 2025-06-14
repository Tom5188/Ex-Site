<?php

namespace App\Http\Controllers\Admin;

use App\Bank;
use App\Currency;
use App\LegalDeal;
use App\LegalDealSend;
use App\Seller;
use App\UserReal;
use App\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class SellerController extends Controller
{
	public function index()
	{
		return view("admin.seller.index");
	}
	public function status(Request $request)
	{
		$EWLCuXv = $request->get("id", 0);
		$VslANFv = Seller::find($EWLCuXv);
		if (empty($VslANFv)) {
			return $this->error("参数错误");
		}
		if ($VslANFv->status == 1) {
			$VslANFv->status = 0;
		} else {
			$VslANFv->status = 1;
		}
		try {
			$VslANFv->save();
			return $this->success("操作成功");
		} catch (\Exception $MBiuMjv) {
			return $this->error($MBiuMjv->getMessage());
		}
	}
	public function lists(Request $request)
	{
		$mOViITQ = $request->get("limit", 10);
		$PVVHhIv = $request->get("account_number", "");
		$lSZwmpQ = new Seller();
		if (!empty($PVVHhIv)) {
			$wBUciPQ = Users::where("account_number", "like", "%" . $PVVHhIv . "%")->get()->pluck("id");
			$lSZwmpQ = $lSZwmpQ->whereIn("user_id", $wBUciPQ);
		}
		$lSZwmpQ = $lSZwmpQ->orderBy("id", "desc")->paginate($mOViITQ);
		return $this->layuiData($lSZwmpQ);
	}
	public function add(Request $request)
	{
		$wzCTTIQ = $request->get("id", 0);
		if (empty($wzCTTIQ)) {
			$bSHyBhv = new Seller();
			$bSHyBhv->create_time = time();
		} else {
			$bSHyBhv = Seller::find($wzCTTIQ);
		}
		$sYOdeDQ = Bank::all();
		$ZlIsRIv = Currency::where("is_legal", 1)->orderBy("id", "desc")->get();
		return view("admin.seller.add")->with(["result" => $bSHyBhv, "banks" => $sYOdeDQ, "currencies" => $ZlIsRIv]);
	}
	public function postAdd(Request $request)
	{
		$sYwmMfv = $request->get("id", 0);
		$ElrRcJv = $request->get("account_number", "");
		$lhxBpkJ = $request->get("name", "");
		$lHYNMJv = $request->get("mobile", "");
		$GlYdHdJ = $request->get("currency_id", "");
		$LIltKOQ = $request->get("wechat_nickname", "");
		$ZpUjYWv = $request->get("wechat_account", "");
		$UrCMwzJ = $request->get("ali_nickname", "");
		$yBzscSQ = $request->get("ali_account", "");
		$jUDYFGv = $request->get("bank_account", "");
		$iElciLQ = $request->get("bank_address", "");
		$ZivQULJ = $request->get("bank_name", "");
		$LlQbUxJ = ["required" => ":attribute 为必填字段"];
		$NhGlCFQ = Validator::make($request->all(), ["account_number" => "required", "name" => "required", "mobile" => "required", "currency_id" => "required", "wechat_account" => "required", "ali_account" => "required", "bank_account" => "required", "bank_address" => "required", "bank_name" => "required"], $LlQbUxJ);
		if ($NhGlCFQ->fails()) {
			return $this->error($NhGlCFQ->errors()->first());
		}
		$xDIOduJ = Users::where("account_number", $ElrRcJv)->first();
		if (empty($xDIOduJ)) {
			return $this->error("找不到此交易账号的用户");
		}
		$WgatXjQ = UserReal::where("user_id", $xDIOduJ->id)->where("review_status", 2)->first();
		if (empty($WgatXjQ)) {
			return $this->error("此用户还未通过实名认证");
		}
		$RXmFpAQ = Currency::find($GlYdHdJ);
		if (empty($RXmFpAQ)) {
			return $this->error("币种不存在");
		}
		if (empty($RXmFpAQ->is_legal)) {
			return $this->error("该币不是法币");
		}
		$YCUGrXv = Seller::where("name", $lhxBpkJ)->where("user_id", "!=", $xDIOduJ->id)->where("currency_id", $GlYdHdJ)->first();
		if (empty($sYwmMfv) && !empty($YCUGrXv)) {
			return $this->error("此法币 " . $lhxBpkJ . " 商家名称已存在");
		}
		$FrqBwlQ = Seller::where("user_id", $xDIOduJ->id)->where("currency_id", $GlYdHdJ)->first();
		if (!empty($FrqBwlQ) && empty($sYwmMfv)) {
			return $this->error("此用户已是此法币商家");
		}
		if (empty($sYwmMfv)) {
			$ctlmbJQ = new Seller();
			$ctlmbJQ->create_time = time();
		} else {
			$ctlmbJQ = Seller::find($sYwmMfv);
		}
		$JYlcgxv = Bank::where("name", $ZivQULJ)->first();
		if ($JYlcgxv) {
			$GzlYeZJ = $JYlcgxv->id;
		} else {
			$AMBtlKv = new Bank();
			$GzlYeZJ = $AMBtlKv->insertGetId(["name" => $ZivQULJ]);
		}
		$ctlmbJQ->user_id = $xDIOduJ->id;
		$ctlmbJQ->name = $lhxBpkJ;
		$ctlmbJQ->mobile = $lHYNMJv;
		$ctlmbJQ->currency_id = $GlYdHdJ;
		$ctlmbJQ->wechat_nickname = $LIltKOQ;
		$ctlmbJQ->wechat_account = $ZpUjYWv;
		$ctlmbJQ->ali_nickname = $UrCMwzJ;
		$ctlmbJQ->ali_account = $yBzscSQ;
		$ctlmbJQ->bank_id = intval($GzlYeZJ);
		$ctlmbJQ->bank_account = $jUDYFGv;
		$ctlmbJQ->bank_address = $iElciLQ;
		$ctlmbJQ->status = 1;
		try {
			$ctlmbJQ->save();
			return $this->success("操作成功");
		} catch (\Exception $OLncirv) {
			return $this->error($OLncirv->getMessage());
		}
	}
	public function delete(Request $request)
	{
		$CMGNquQ = $request->get("id", 0);
		$zULfAdQ = Seller::find($CMGNquQ);
		if (empty($zULfAdQ)) {
			return $this->error("无此用户");
		}
		try {
			$zULfAdQ->delete();
			return $this->success("删除成功");
		} catch (\Exception $nXnYPCJ) {
			return $this->error($nXnYPCJ->getMessage());
		}
	}
	public function sendBack(Request $request)
	{
		$nffuTUJ = $request->get("id", 0);
		if (empty($nffuTUJ)) {
			return $this->error("参数错误");
		}
		try {
			DB::beginTransaction();
			$EazxunJ = LegalDealSend::lockForUpdate()->find($nffuTUJ);
			if (empty($EazxunJ)) {
				DB::rollback();
				return $this->error("无此记录");
			}
			if ($EazxunJ->is_shelves != 2) {
				throw new \Exception("必须下架后才可以撤销");
			}
			if ($EazxunJ->is_done != 0) {
				throw new \Exception("当前发布状态无法撤销");
			}
			if (bc_comp($EazxunJ->surplus_number) <= 0) {
				throw new \Exception("当前发布剩余数量不足,无法撤销");
			}
			LegalDealSend::sendBack($nffuTUJ);
			DB::commit();
			return $this->success("发布撤回成功,此发布改变为已完成状态");
		} catch (\Exception $sCvTfzJ) {
			DB::rollback();
			return $this->error($sCvTfzJ->getMessage());
		}
	}
	public function sendDel(Request $request)
	{
		$PWlKXSv = $request->get("id", 0);
		if (empty($PWlKXSv)) {
			return $this->error("参数错误");
		}
		$HyztDwQ = LegalDealSend::isHasIncompleteness($PWlKXSv);
		if ($HyztDwQ) {
			return $this->error("该发布信息下有未完成订单，请先运行撤回发布再来删除");
		}
		DB::beginTransaction();
		try {
			$XNldeIJ = LegalDealSend::lockForUpdate()->find($PWlKXSv);
			if (empty($XNldeIJ)) {
				return $this->error("无此记录");
			}
			$UZfwGZQ = LegalDeal::where("legal_deal_send_id", $PWlKXSv)->get();
			if (!empty($UZfwGZQ)) {
				foreach ($UZfwGZQ as $XilJuhv) {
					$XilJuhv->delete();
				}
			}
			if ($XNldeIJ->type == "sell") {
				if ($XNldeIJ->surplus_number > 0) {
					$GhxsTBJ = Seller::lockForUpdate()->find($XNldeIJ->seller_id);
					if (!empty($GhxsTBJ)) {
						$cTAvPXv = $GhxsTBJ->user_id;
						$egjCciv = UsersWallet::lockForUpdate()->where("user_id", $cTAvPXv)->where("currency", $GhxsTBJ->currency_id)->first();
						$NelPScQ = change_wallet_balance($egjCciv, 1, -$XNldeIJ->surplus_number, AccountLog::SELLER_BACK_SEND, "系统删除", true);
						if ($NelPScQ !== true) {
							throw new \Exception("删除失败:减少冻结资金失败");
						}
						$uhhTkFv = change_wallet_balance($egjCciv, 1, $XNldeIJ->surplus_number, AccountLog::SELLER_BACK_SEND, "系统删除");
						if ($uhhTkFv !== true) {
							throw new \Exception("删除失败:增加余额失败");
						}
						$GhxsTBJ->lock_seller_balance = bc_sub($GhxsTBJ->lock_seller_balance, $XNldeIJ->surplus_number, 5);
						$GhxsTBJ->save();
					}
				}
			}
			$XNldeIJ->delete();
			DB::commit();
			return $this->success("删除成功");
		} catch (\Exception $fnqRLxJ) {
			DB::rollback();
			return $this->error($fnqRLxJ->getMessage());
		}
	}
	public function is_shelves(Request $request)
	{
		$PkBDliJ = $request->get("id", 0);
		$XAqRljJ = $request->get("is_shelves", 1);
		if (empty($PkBDliJ)) {
			return $this->error("参数错误");
		}
		$iZKliEv = LegalDealSend::find($PkBDliJ);
		if (empty($iZKliEv)) {
			return $this->error("无此记录");
		}
		if (empty($iZKliEv->is_shelves)) {
			$iZKliEv->is_shelves = 1;
			$iZKliEv->save();
		}
		DB::beginTransaction();
		try {
			if ($iZKliEv->is_shelves == 1) {
				$iZKliEv->is_shelves = 2;
			} elseif ($iZKliEv->is_shelves == 2) {
				$iZKliEv->is_shelves = 1;
			}
			$iZKliEv->save();
			DB::commit();
			return $this->success("操作成功");
		} catch (\Exception $BIZQdDJ) {
			DB::rollback();
			return $this->error($BIZQdDJ->getMessage());
		}
	}
}