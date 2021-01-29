<?php

namespace Ghost\DcatConfig\Http\Controllers;


use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Ghost\DcatConfig\DcatConfigServiceProvider;
use Ghost\DcatConfig\Models\AdminConfig;
use Ghost\DcatConfig\Tools\Builder;
use Illuminate\Routing\Controller;
use Dcat\Admin\Layout\Row;

class DcatConfigController extends Controller
{
	
	protected $title  ;
	protected $description;
	
	protected $breadcrumb;
	
	
	protected function title()
	{
		return $this->title ?: DcatConfigServiceProvider::trans('dcat-config.title');
	}
	
	protected function breadcrumb()
	{
		return $this->breadcrumb ?:[
			'text' =>  DcatConfigServiceProvider::trans('dcat-config.title'),
			'url' => admin_url ('config'),
			//			        'icon'  => 'fa-toggle-off', // 图标可以留空
		];
	}
	
	
	protected function description()
	{
		return $this->description ?: DcatConfigServiceProvider::trans('dcat-config.description');
	}
	
	public function index(Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description())
	        ->breadcrumb(
		       $this->breadcrumb()
	        )
            ->body(
	            function (Row $row) {
		            $row->column(8,$this->form ());
		            $row->column(4,$this->grid  ());
	            }
            );
    }
	
	
	
	public function destroy($id){
		
		
		$form = Form::make(new AdminConfig());
		
		return $form->destroy($id);
	}
	/**
	 * @return mixed
	 */
	public function update()
	{

		$configModel= new AdminConfig();
		$form = Form::make();
		return (new Builder($form,$configModel))->update ()->getForm ()
			->response()
			->refresh()
			->success(trans('admin.save_succeeded'));
	}
	
	/**
	 * @param Content $content
	 *
	 * @return Content
	 */
    public function add(Content $content)
    {
	    return $content
		    ->title($this->title)
		    ->description($this->description)
		    ->breadcrumb(
			    $this->breadcrumb()
		    )
		    ->body($this->create());
    }
	
	
	/**
	 * @return mixed
	 */
    public function addo()
    {
	    return (new Builder(Form::make(),new AdminConfig()))->store ()->getForm ()
		    ->response()
		    ->redirect(admin_url ('config'))
		    ->success(trans('admin.save_succeeded'));
    }
	
	/**
	 * @return null
	 */
	protected function form()
	{
		$configModel= new AdminConfig();
		$form = Form::make();
		return (new Builder($form,$configModel))->form ()->getForm ();
	}
	
	/**
	 * @return null
	 */
	protected function create()
	{
		return (new Builder(Form::make()))->create ()->getForm ();
	}
	
	/**
	 * @return Grid
	 */
	protected function grid()
	{
		return Grid::make(AdminConfig::query ()->orderByDesc ('created_at'), function (Grid $grid) {
			$grid->column('key',DcatConfigServiceProvider::trans('dcat-config.grid.key'))->display (function ($key){
				return "config($key)";
			})->copyable ();
			
			$grid->column('name',DcatConfigServiceProvider::trans('dcat-config.grid.name'));
			
			$grid->quickSearch (['key','name'])->placeholder (DcatConfigServiceProvider::trans('dcat-config.grid.key').DIRECTORY_SEPARATOR.DcatConfigServiceProvider::trans('dcat-config.grid.name'));
			$grid->disableRefreshButton ();
			$grid->disableViewButton ();
			$grid->disableEditButton ();
			$grid->disableCreateButton ();
		});
	}
}