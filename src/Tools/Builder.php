<?php

namespace Ghost\DcatConfig\Tools;

use Dcat\Admin\Form;
use Dcat\Admin\Widgets\Form as WidgetsForm;
use Ghost\DcatConfig\DcatConfigServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class Builder
{
    /**
     * @var \Dcat\Admin\Form
     */
    protected $form;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $model;

    protected $data;

    protected $option = [
        'text' => '文本',
        'select' => '下拉选框单选',
        'multipleSelect' => '下拉选框多选',
        'listbox' => '多选盒',
        'textarea' => '长文本',
        'radio' => '单选',
        'checkbox' => '多选',
        'email' => '邮箱',
        'password' => '密码',
        'url' => '链接',
        'ip' => 'IP',
        'mobile' => '手机',
        'color' => '颜色选择器',
        'time' => '时间',
        'date' => '日期',
        'datetime' => '时间日期',
        'file' => '文件上传',
        'image' => '图片上传',
        'multipleImage' => '多图上传',
        'multipleFile' => '多文件上传',
        'editor' => '富文本编辑器',
        //'markdown' => 'Markdown',
        'number' => '数字',
        'rate' => '费率',
        'arrays'=>"数组"
    ];

    protected $input;

    public function __construct($form)
    {
        $this->form = $form;
        $this->form->disableDeleteButton();
        $this->form->disableViewButton();
        $this->form->disableCreatingCheck();
        $this->form->disableListButton();
        $this->form->disableEditingCheck();
        $this->form->disableViewCheck();
        $this->model = $this->all();
    }

    /**
     * @return $this
     */
    public function form()
    {
        $this->data = $this->model->map(function ($model) {
            [$key, $value] = explode('.', $model['key'], 2);
            $model['tab'] = $key;
            $model['key'] = str_replace(".", '-', $value);
            return $model;
        })->groupBy('tab');

        $this->form->title(DcatConfigServiceProvider::trans('dcat-config.builder.config'));
        $this->form->action(admin_url('config.do'));
        $tab = collect($this->config('tab'))->pluck('value', 'key')->toArray();
        $this->data = $this->data->toArray();
        $this->data = collect(array_merge($tab, $this->data));

        $this->data->each(function ($value, $item) use ($tab) {
            $this->form->tab($tab[$item], function () use ($value, $item) {
                if (is_array($value)) {
                    collect($value)->each(function ($model) {
                        Field::make($model,$this->form)->{$model['element']}();
                    });
                }
            });
        });

        $this->form->tools([new Create()]);

        return $this;
    }


    ///**
    // * @return mixed
    // */
    //public function getInput()
    //{
    //    return $this->input;
    //}

    /**
     * @param $str
     *
     * @return false|string[]
     */
    protected function textToArray($str)
    {
        return explode("\n", str_replace("\r\n", "\n", $str));
    }

    /**
     * @param $str
     * @return string
     */
    //protected function trim($str): string
    //{
    //    return trim(strip_tags(str_replace(["\n", "\t", "\r", " ", "&nbsp;"], '', htmlspecialchars_decode($str))));
    //}

    /**
     * @param $id
     * @return $this
     */
    public function putEdit($id)
    {
        $request = request();

        $option = $request->get('option');
        $rule = $request->get('rule');

        if ($option) {
            $option = collect($this->textToArray($option))->map(function ($value, $k) {
                if (false === strpos($value, ':')) {
                    $attr = ['key' => $k, 'value' => $value];
                } else {
                    [$v1, $v2] = explode(':', $value);
                    $attr = ['key' => $v1, 'value' => $v2];
                }

                return $attr;
            });
        }
        if ($rule) {
            $rule = $this->textToArray($rule);
        }

        $options = [
            'rule'=>$rule,
            'option'=>$option,
        ];

        $data = [

            'key'=>$id,
            'value' => null,
            'name' => $request->get('name'),
            'help' => $request->get('help'),
            'element' => $request->get('element'),
            'options' => $options
        ];

        $this->model = $this->model->map(function ($value)use ($data){

            if ($value['key']===$data['key']){
                $value['value'] = '';
                $value['name'] = $data['name'];
                $value['help'] = $data['help'];
                $value['element'] = $data['element'];
                $value['options'] = $data['options'];
            }
            return $value;
        });
        $this->save();
        return $this;

    }

    /**
     * @return $this
     */
    public function update(): Builder
    {
        $request = \request()->except('_token');
        $i = 0;
        $update = [];
        foreach ($request as $key => $value) {

            $update[$i]['key'] = str_replace("-", '.', $key);
            if (is_array($value)) {

                $update[$i]['value'] = collect($value)->map(function ($v){

                    if (isset($v['_remove_'])){
                        if ((int)$v['_remove_'] ===1){
                            return 0;
                        }
                        unset($v['_remove_']);
                    }
                    return $v;

                })->filter(function ($v){
                    return $v !== null;
                })->values();
            } else {
                $update[$i]['value'] = $value;
            }
            $i++;
        }
        $update = collect($update)->pluck('value','key');
        $this->model  = $this->model->map(function ($value)use ($update){
            $value['value'] = $update[$value['key']];
            return $value;
        });

        $this->save();
        Artisan::call('config:clear');

        return $this;
    }

    /**
     * @return $this
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store()
    {
        $tab = request()->get('tab');
        $attribute = request()->get('attribute');


        $attribute = collect($attribute)->map(function ($data) {

            if ($data['option']) {
                $data['option'] = collect($this->textToArray($data['option']))->map(function ($value, $k) {
                    if (false === strpos($value, ':')) {
                        $attr = ['key' => $k, 'value' => $value];
                    } else {
                        [$v1, $v2] = explode(':', $value);
                        $attr = ['key' => $v1, 'value' => $v2];
                    }

                    return $attr;
                });
            }
            if ($data['rule']) {
                $data['rule'] = $this->textToArray($data['rule']);
            }

            return $data;
        })->values();
        $data = [];
        $attribute->each(function ($value, $item) use (&$data, $tab) {

            if (null !== $value['key']) {
                $data[$item]['key'] = $tab.'.'.$value['key'];
                $data[$item]['name'] = $value['name'];
                $data[$item]['value'] = '';
                $data[$item]['help'] = $value['help'];
                $data[$item]['element'] = $value['element'];
                $data[$item]['options'] = [
                    'option' => $value['option']??[],
                    'rule' => $value['rule']??[],
                ];
                $data[$item]['order'] = $this->order() + $item + 1;
            }
        });
        $rules = [];
        $message = [];
        foreach ($data as $key => $val) {
            $rules[$key.'.key'] = 'required|unique:admin_config,key|regex:/^[a-zA-Z_\.0-9]+$/';
            $rules[$key.'.name'] = 'required';
            $rules[$key.'.element'] = 'required';

            $message[$key.'.key.required'] = DcatConfigServiceProvider::trans('dcat-config.builder.key').' 不能为空';
            $message[$key.'.key.unique'] = DcatConfigServiceProvider::trans('dcat-config.builder.key').' 已存在';
            $message[$key.'.key.regex'] = DcatConfigServiceProvider::trans('dcat-config.builder.key').' 只能包含字母数字';

            $message[$key.'.name.required'] = DcatConfigServiceProvider::trans('dcat-config.builder.name').' 不能为空';
            $message[$key.'.element.required'] = DcatConfigServiceProvider::trans('dcat-config.builder.element').' 不能为空';
        }

        Validator::make($data, $rules, $message)->validate();
        $this->model = $this->model->merge($data);
        $this->save();
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function edit($id)
    {

        $tab = collect($this->config('tab'))->pluck('value', 'key');

        $this->model = $this->model->where('key',$id)->first();

        if (null === $this->model){
            return $this;
        }

        $this->form->hidden('_method')->value('put');
        //$this->form->edit($id);
        $this->form->select('tab', DcatConfigServiceProvider::trans('dcat-config.builder.groups'))
            ->options($tab)
            ->value(function (){
               return substr($this->model['key'], 0, strpos($this->model['key'],'.'));
            })
            ->disable()
            ->default($tab->keys()->first());

        $this->form->text('key', DcatConfigServiceProvider::trans('dcat-config.builder.key'))
            ->required()
            ->value(substr($this->model['key'],strrpos($this->model['key'],".")+1))
            ->disable();
        $this->form->text('name', DcatConfigServiceProvider::trans('dcat-config.builder.name'))
            ->required()->value($this->model['name']);
        $this->form->select('element', DcatConfigServiceProvider::trans('dcat-config.builder.element'))->required()->when([
            'select',
            'multipleSelect',
            'listbox',
            'radio',
            'checkbox',
        ], function ( $form) {
            $form->textarea('option', DcatConfigServiceProvider::trans('dcat-config.builder.option'))
                ->value(function (){

                    $d= collect($this->model['options']['option'])->pluck('value','key')->toArray();

                    $text = '';
                    foreach ($d as $k=>$v){

                        $text .= $k.':'.$v."\r\n";
                    }
                    return $text;
                })
                ->placeholder("例如:\r\nkey1:value1\r\nkey2:value2");
        })->options($this->option)->value($this->model['element'])->default('text');
        $this->form->textarea('rule', DcatConfigServiceProvider::trans('dcat-config.builder.rule'))->customFormat(function (){


            $d = $this->model['options']['rule'];
            $text = '';
            foreach ((array)$d as $k=>$v){

                $text .= $v."\r\n";
            }
            return $text;
        });
        $this->form->text('help', DcatConfigServiceProvider::trans('dcat-config.builder.help'));

        return $this;
    }

    protected function order()
    {
        $res = collect($this->model)->last();
        return $res?$res['order']:0;
    }

    /**
     * @return $this
     */
    public function create()
    {

        $tab = collect($this->config('tab'))->pluck('value', 'key');
        $this->form->action(admin_url('config/addo'));
        $this->form->select('tab', DcatConfigServiceProvider::trans('dcat-config.builder.groups'))->options($tab)->default($tab->keys()->first());
        $this->form->array('attribute', DcatConfigServiceProvider::trans('dcat-config.builder.attribute'), function (
            WidgetsForm $form
        ) {
            $form->text('key', DcatConfigServiceProvider::trans('dcat-config.builder.key'))
                ->required()->help('请输入字母/数字/点/下划线');
            $form->text('name', DcatConfigServiceProvider::trans('dcat-config.builder.name'))->required();
            $form->select('element', DcatConfigServiceProvider::trans('dcat-config.builder.element'))->required()->when([
                'select',
                'multipleSelect',
                'listbox',
                'radio',
                'checkbox',
            ], function (WidgetsForm $form) {
                $form->textarea('option', DcatConfigServiceProvider::trans('dcat-config.builder.option'))->placeholder("例如:\r\nkey1:value1\r\nkey2:value2");
            })->options($this->option)->default('text');
            $form->textarea('rule', DcatConfigServiceProvider::trans('dcat-config.builder.rule'))->placeholder("例如:\r\nrequired\r\n");
            $form->text('help', DcatConfigServiceProvider::trans('dcat-config.builder.help'));
        });

        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function destroy($id)
    {
        $this->model = $this->model->map(function ($value) use ($id){
            if ($value['key'] === $id){
                return [];
            }
            return $value;
        })->filter();

        $this->save();
        return $this;
    }

    /**
     * @return \Dcat\Admin\Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    protected function config($key, $default = null)
    {
        return DcatConfigServiceProvider::setting($key, $default);
    }


    /**
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return collect(admin_setting_array("ghost::admin_config"));
    }

    /**
     * @return \Dcat\Admin\Support\Setting|mixed
     */
    public function save()
    {
        return admin_setting(["ghost::admin_config"=>$this->model]);
    }
}
