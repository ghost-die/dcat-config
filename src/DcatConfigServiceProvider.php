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

        $array = collect(admin_setting_array('ghost::admin_config'))->map(function ($value){

            return ['key'=>$value['key'],'value'=>$value['value']];
        })->toArray();

		foreach ($array as $config) {
            //$value = json_decode ( $config[ 'value' ] , true  );
            //if(json_last_error() !== JSON_ERROR_NONE){
            //    $value =  $config['value'];
            //}
            config([$config['key'] => $config['value']]);
		}
	}
	
	public function settingForm()
	{
		return new Setting($this);
	}
}
