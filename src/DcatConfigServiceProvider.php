<?php

namespace Ghost\DcatConfig;

use Dcat\Admin\Extend\ServiceProvider;
use Dcat\Admin\Admin;
use Ghost\DcatConfig\Models\AdminConfig;
use Illuminate\Support\Facades\Schema;

class DcatConfigServiceProvider extends ServiceProvider
{
	
	
	// 定义菜单
	protected $menu = [
		[
			'title' => 'Config',
			'uri'   => 'config',
			'icon'  => 'fa-toggle-off', // 图标可以留空
		],
	];

	public function init()
	{
		parent::init();
		if (Schema::hasTable('admin_config')) {
			$this->load();
		}
	}
	
	public function load()
	{
		foreach (AdminConfig::all(['key', 'value']) as $config) {
			config([$config['key'] => $config['value']]);
		}
	}
	
	public function settingForm()
	{
		return new Setting($this);
	}
}
