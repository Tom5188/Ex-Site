<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Users;
class UpdateParent extends Command
{
	protected $signature = "update:parent";
	protected $description = "更新用户上级";
	public function __construct()
	{
		parent::__construct();
	}
	public function handle()
	{
		Users::where('type', 1)->chunk(10000, function ($users) {
			foreach ($users as $key => $user) {
				$parent = Users::where('origin_user_id', $user->origin_parent_id)->first();
				if (!$parent) {
					continue 1;
				}
				$user->parent_id = $parent->id;
				$user->save();
				$this->info('更新用户' . $user->id . '的上级完成');
			}
		});
		$this->info('全部执行完成');
	}
}