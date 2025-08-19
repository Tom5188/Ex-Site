<?php

namespace App\Http\Controllers\Admin;

use App\Agent;
use App\AgentMoneylog;
use App\Setting;
use App\AccountLog;
use App\Transaction;
use App\TransactionComplete;
use App\TransactionIn;
use App\TransactionOut;
use App\Users;
use App\Currency;
use App\LeverTransaction;
use App\TransactionOrder;
use App\TransactionOrdercopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
class TransactionController extends Controller
{
	public function index()
	{
		$_var_0 = Currency::all();
		return view("admin.transaction.index", ["currency" => $_var_0]);
	}
	public function lists()
	{
		$_var_1 = Input::get("limit", 10);
		$_var_2 = Input::get("account_number", "");
		$_var_3 = Input::get("type", "");
		$_var_4 = Input::get("currency", "");
		$_var_5 = Input::get("status", "");
		$_var_6 = new Transaction();
		if (!empty($_var_2)) {
			$users = Users::where("account_number", "like", "%" . $_var_2 . "%")->get()->pluck("id");
			$_var_6 = $_var_6->where(function ($query) use($users) {
				$query->whereIn("from_user_id", $users);
			});
		}
		if (!empty($_var_3)) {
			$_var_6 = $_var_6->where("type", "=", $_var_3);
		}
		if (!empty($_var_4)) {
			$_var_6 = $_var_6->where("currency", $_var_4);
		}
		if (!empty($_var_5)) {
			$_var_6 = $_var_6->where("status", $_var_5);
		}
		$_var_7 = $_var_6->orderBy("id", "desc")->paginate($_var_1);
		return response()->json(["code" => 0, "data" => $_var_7->items(), "count" => $_var_7->total()]);
	}
	public function completeIndex()
	{
		return view("admin.transaction.complete");
	}
	public function inIndex()
	{
		return view("admin.transaction.in");
	}
	public function outIndex()
	{
		return view("admin.transaction.out");
	}
	public function cnyIndex()
	{
		return view("admin.transaction.cny");
	}
	public function completeList(Request $request)
	{
		$_var_8 = $request->get("limit", 10);
		$_var_9 = $request->get("account_number", "");
		$_var_10 = new TransactionComplete();
		if (!empty($_var_9)) {
			$_var_11 = Users::where("account_number", "like", "%" . $_var_9 . "%")->get()->pluck("id");
			$_var_10 = $_var_10->whereIn("user_id", $_var_11);
		}
		$_var_10 = $_var_10->orderBy("id", "desc")->paginate($_var_8);
		return $this->layuiData($_var_10);
	}
	public function inList(Request $request)
	{
		$_var_12 = $request->get("limit", 10);
		$_var_13 = $request->get("account_number", "");
		$_var_14 = new TransactionIn();
		if (!empty($_var_13)) {
			$_var_15 = Users::where("account_number", "like", "%" . $_var_13 . "%")->get()->pluck("id");
			$_var_14 = $_var_14->whereIn("user_id", $_var_15);
		}
		$_var_14 = $_var_14->orderBy("id", "desc")->paginate($_var_12);
		return $this->layuiData($_var_14);
	}
	public function outList(Request $request)
	{
		$_var_16 = $request->get("limit", 10);
		$_var_17 = $request->get("account_number", "");
		$_var_18 = new TransactionOut();
		if (!empty($_var_17)) {
			$_var_19 = Users::where("account_number", "like", "%" . $_var_17 . "%")->get()->pluck("id");
			$_var_18 = $_var_18->whereIn("user_id", $_var_19);
		}
		$_var_18 = $_var_18->orderBy("id", "desc")->paginate($_var_16);
		return $this->layuiData($_var_18);
	}
	public function cnyList(Request $request)
	{
		$_var_20 = $request->get("limit", 10);
		$_var_21 = $request->get("account_number", "");
		$_var_22 = new AccountLog();
		if (!empty($_var_21)) {
			$_var_23 = Users::where("account_number", "like", "%" . $_var_21 . "%")->get()->pluck("id");
			$_var_22 = $_var_22->whereIn("user_id", $_var_23);
		}
		$_var_24 = array(13, 14, 15, 20, 22, 24);
		$_var_22 = $_var_22->whereIn("type", $_var_24)->orderBy("id", "desc")->paginate($_var_20);
		return $this->layuiData($_var_22);
	}
	public function Leverdeals_show()
	{
		return view("admin.leverdeals.list");
	}
	public function Leverdeals(Request $request)
	{
		$_var_25 = $request->input("limit", 20);
		$id = $request->input("id", 0);
		$username = $request->input("phone", "");
		$status = $request->input("status", 10);
		$type = $request->input("type", 0);
		$legal_id = $request->input("legal_id", -1);
		$start = $request->input("start", "");
		$end = $request->input("end", "");
		$_var_26 = LeverTransaction::whereHas("user", function ($query) use($username) {
			$username != "" && $query->where("account_number", $username)->orWhere("user_id", $username);
		})->where(function ($query) use($id, $status, $type, $legal_id) {
			$id != 0 && $query->where("id", $id);
			$legal_id != -1 && $query->where("legal", $legal_id);
			$status != 10 && in_array($status, [LeverTransaction::ENTRUST, LeverTransaction::BUY, LeverTransaction::CLOSED, LeverTransaction::CANCEL, LeverTransaction::CLOSING]) && $query->where("status", $status);
			$type > 0 && in_array($type, [1, 2]) && $query->where("type", $type);
		})->where(function ($query) use($start, $end) {
			!empty($start) && $query->where("create_time", ">=", strtotime($start . " 0:0:0"));
			!empty($end) && $query->where("create_time", "<=", strtotime($end . " 23:59:59"));
		});
		$_var_27 = clone $_var_26;
		$_var_28 = $_var_27->select([DB::raw("SUM((CASE `type` WHEN 1 THEN `update_price` - `price` WHEN 2 THEN `price` - `update_price` END) * `number`* `multiple`) AS `balance1`"), DB::raw("sum(origin_caution_money) as balance2"), DB::raw("sum(trade_fee) as balance3")])->first();
		$_var_28 && ($_var_28 = $_var_28->setAppends([]));
		$_var_29 = $_var_26->orderBy("id", "desc")->paginate($_var_25);
		$_var_30 = $_var_29->getCollection();
		$_var_30->transform(function ($item, $key) {
			$item->setAppends(["symbol", "account_number", "profits", "time"]);
			return $item;
		});
		return $this->layuiData($_var_29, ["total" => $_var_28]);
	}
	public function csv(Request $request)
	{
		$_var_31 = $request->input("id", 0);
		$_var_32 = $request->input("phone", "");
		$_var_33 = $request->input("status", 10);
		$_var_34 = $request->input("type", 0);
		$_var_35 = $request->input("start", "");
		$_var_36 = $request->input("end", "");
		$_var_37 = [];
		if ($_var_31 > 0) {
			$_var_37[] = ["lever_transaction.id", "=", $_var_31];
		}
		if (!empty($_var_32)) {
			$_var_38 = DB::table("users")->where("account_number", $_var_32)->first();
			if ($_var_38 !== null) {
				$_var_37[] = ["lever_transaction.user_id", "=", $_var_38->id];
			}
		}
		if ($_var_33 != 10 && in_array($_var_33, [LeverTransaction::ENTRUST, LeverTransaction::BUY, LeverTransaction::CLOSED, LeverTransaction::CANCEL, LeverTransaction::CLOSING])) {
			$_var_37[] = ["lever_transaction.status", "=", $_var_33];
		}
		if ($_var_34 > 0 && in_array($_var_34, [1, 2])) {
			$_var_37[] = ["type", "=", $_var_34];
		}
		if (!empty($_var_35) && !empty($_var_36)) {
			$_var_37[] = ["lever_transaction.create_time", ">", strtotime($_var_35 . " 0:0:0")];
			$_var_37[] = ["lever_transaction.create_time", "<", strtotime($_var_36 . " 23:59:59")];
		}
		$_var_39 = TransactionOrdercopy::leftjoin("users", "lever_transaction.user_id", "=", "users.id")->select("lever_transaction.*", "users.phone")->whereIn("lever_transaction.status", [LeverTransaction::ENTRUST, LeverTransaction::BUY, LeverTransaction::CLOSED, LeverTransaction::CANCEL, LeverTransaction::CLOSING])->where($_var_37)->get();
		foreach ($_var_39 as $_var_40 => $_var_41) {
			$_var_39[$_var_40]["create_time"] = date("Y-m-d H:i:s", $_var_41->create_time);
			$_var_39[$_var_40]["transaction_time"] = date("Y-m-d H:i:s", substr($_var_41->transaction_time, 0, strpos($_var_41->transaction_time, ".")));
			$_var_39[$_var_40]["update_time"] = date("Y-m-d H:i:s", substr($_var_41->update_time, 0, strpos($_var_41->update_time, ".")));
			$_var_39[$_var_40]["handle_time"] = date("Y-m-d H:i:s", substr($_var_41->handle_time, 0, strpos($_var_41->handle_time, ".")));
			$_var_39[$_var_40]["complete_time"] = date("Y-m-d H:i:s", substr($_var_41->complete_time, 0, strpos($_var_41->complete_time, ".")));
		}
		$data = $_var_39;
		return Excel::create("杠杆交易", function ($excel) use($data) {
			$excel->sheet("杠杆交易", function ($sheet) use($data) {
				$sheet->cell("A1", function ($cell) {
					$cell->setValue("ID");
				});
				$sheet->cell("B1", function ($cell) {
					$cell->setValue("用户名");
				});
				$sheet->cell("C1", function ($cell) {
					$cell->setValue("交易手续费");
				});
				$sheet->cell("D1", function ($cell) {
					$cell->setValue("隔夜费金额");
				});
				$sheet->cell("E1", function ($cell) {
					$cell->setValue("交易类型");
				});
				$sheet->cell("F1", function ($cell) {
					$cell->setValue("当前状态");
				});
				$sheet->cell("G1", function ($cell) {
					$cell->setValue("原始价格");
				});
				$sheet->cell("H1", function ($cell) {
					$cell->setValue("开仓价格");
				});
				$sheet->cell("I1", function ($cell) {
					$cell->setValue("当前价格");
				});
				$sheet->cell("J1", function ($cell) {
					$cell->setValue("手数");
				});
				$sheet->cell("K1", function ($cell) {
					$cell->setValue("倍数");
				});
				$sheet->cell("L1", function ($cell) {
					$cell->setValue("初始保证金");
				});
				$sheet->cell("M1", function ($cell) {
					$cell->setValue("当前可用保证金");
				});
				$sheet->cell("N1", function ($cell) {
					$cell->setValue("创建时间");
				});
				$sheet->cell("O1", function ($cell) {
					$cell->setValue("价格刷新时间");
				});
				$sheet->cell("P1", function ($cell) {
					$cell->setValue("平仓时间");
				});
				$sheet->cell("Q1", function ($cell) {
					$cell->setValue("完成时间");
				});
				if (!empty($data)) {
					foreach ($data as $_var_42 => $_var_43) {
						if ($_var_43["type"] == 1) {
							$_var_43["type"] = "买入";
						} else {
							$_var_43["type"] = "卖出";
						}
						if ($_var_43["status"] == 0) {
							$_var_43["status"] = "挂单中";
						} elseif ($_var_43["status"] == 1) {
							$_var_43["status"] = "交易中";
						} elseif ($_var_43["status"] == 2) {
							$_var_43["status"] = "平仓中";
						} elseif ($_var_43["status"] == 3) {
							$_var_43["status"] = "已平仓";
						} elseif ($_var_43["status"] == 4) {
							$_var_43["status"] = "已撤单";
						}
						$_var_44 = $_var_42 + 2;
						$sheet->cell("A" . $_var_44, $_var_43["id"]);
						$sheet->cell("B" . $_var_44, $_var_43["phone"]);
						$sheet->cell("C" . $_var_44, $_var_43["trade_fee"]);
						$sheet->cell("D" . $_var_44, $_var_43["overnight_money"]);
						$sheet->cell("E" . $_var_44, $_var_43["type"]);
						$sheet->cell("F" . $_var_44, $_var_43["status"]);
						$sheet->cell("G" . $_var_44, $_var_43["origin_price"]);
						$sheet->cell("H" . $_var_44, $_var_43["price"]);
						$sheet->cell("I" . $_var_44, $_var_43["update_price"]);
						$sheet->cell("J" . $_var_44, $_var_43["share"]);
						$sheet->cell("K" . $_var_44, $_var_43["multiple"]);
						$sheet->cell("L" . $_var_44, $_var_43["origin_caution_money"]);
						$sheet->cell("M" . $_var_44, $_var_43["caution_money"]);
						$sheet->cell("N" . $_var_44, $_var_43["create_time"]);
						$sheet->cell("O" . $_var_44, $_var_43["update_time"]);
						$sheet->cell("P" . $_var_44, $_var_43["handle_time"]);
						$sheet->cell("Q" . $_var_44, $_var_43["complete_time"]);
					}
				}
			});
		})->download("xlsx");
	}
}