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
            $value = json_decode ( $config[ 'value' ] , true  );
            if(json_last_error() !== JSON_ERROR_NONE){
                $value =  $config['value'];
            }
            config([$config['key'] => $value]);
		}
	}
	
	public function settingForm()
	{
		return new Setting($this);
	}
}
