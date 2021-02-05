<?php

namespace Ghost\DcatConfig\Tools;

use Dcat\Admin\Form\Field;
use Dcat\Admin\Widgets\Form as WidgetsForm;
use Ghost\DcatConfig\DcatConfigServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class Builder
{
    /**
     * @var \Dcat\Admin\Form
     */
    protected $form;

    /**
     * @var Model
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

    public function __construct($form, $model = null)
    {
        $this->form = $form;

        $this->form->disableDeleteButton();
        $this->form->disableViewButton();
        $this->form->disableCreatingCheck();
        $this->form->disableListButton();
        $this->form->disableEditingCheck();
        $this->form->disableViewCheck();
        $this->model = $model;
    }

    public function form()
    {
        $this->data = $this->model::query()->orderBy('order')->orderBy('created_at')->get()->each(function ($model) {
            [$key, $value] = explode('.', $model['key'], 2);
            $model['tab'] = $key;
            $model['key'] = preg_replace('/\./','-',$value);

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

                        $this->{$model['element']}($model);
                    });
                }
            });
        });

        $this->form->tools([new Create()]);

        return $this;
    }

    /**
     * @param \Dcat\Admin\Form\Field $field
     * @param $rule
     * @return \Dcat\Admin\Form\Field
     */
    public function rule(Field $field, $rule)
    {
        if ($rule && in_array('required', $rule, true)) {
            $field->required();
        }

        return $field;
    }

    protected function text($model)
    {

        $field = $this->form->text($model['tab'].'-'.$model['key'], $model['name']);

        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function select($model)
    {
        $field = $this->form->select($model['tab'].'-'.$model['key'], $model['name'])->options($this->option($model));
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function multipleSelect($model)
    {
        $field = $this->form->multipleSelect($model['tab'].'-'.$model['key'], $model['name'])->options($this->option($model));
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value(json_decode($model['value']));
    }

    protected function listbox($model)
    {
        $field = $this->form->listbox($model['tab'].'-'.$model['key'], $model['name'])->options($this->option($model));
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value(json_decode($model['value']));
    }

    protected function textarea($model)
    {
        $field = $this->form->textarea($model['tab'].'-'.$model['key'], $model['name']);
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function radio($model)
    {
        $field = $this->form->radio($model['tab'].'-'.$model['key'], $model['name'])->options($this->option($model));
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function checkbox($model)
    {
        $field = $this->form->checkbox($model['tab'].'-'.$model['key'], $model['name'])->options($this->option($model));
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }



    protected function email($model)
    {
        $field = $this->form->email($model['tab'].'-'.$model['key'], $model['name']);
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function password($model)
    {
        $field = $this->form->password($model['tab'].'-'.$model['key'], $model['name']);
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function url($model)
    {
        $field = $this->form->url($model['tab'].'-'.$model['key'], $model['name']);
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function ip($model)
    {
        $field = $this->form->ip($model['tab'].'-'.$model['key'], $model['name']);
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function mobile($model)
    {
        $field = $this->form->mobile($model['tab'].'-'.$model['key'], $model['name']);
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function color($model)
    {
        $field = $this->form->color($model['tab'].'-'.$model['key'], $model['name']);
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function time($model)
    {
        $field = $this->form->time($model['tab'].'-'.$model['key'], $model['name']);
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function date($model)
    {
        $field = $this->form->date($model['tab'].'-'.$model['key'], $model['name']);
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function datetime($model)
    {
        $field = $this->form->datetime($model['tab'].'-'.$model['key'], $model['name']);
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function file($model)
    {
        $field = $this->form->file($model['tab'].'-'.$model['key'], $model['name'])->autoUpload()->url(admin_url('files'));
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function image($model)
    {
        $field = $this->form->image($model['tab'].'-'.$model['key'], $model['name'])->autoUpload()->url(admin_url('files'));
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function multipleFile($model)
    {
        $field = $this->form->multipleFile($model['tab'].'-'.$model['key'], $model['name'])->autoUpload()->url(admin_url('files'));

        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function multipleImage($model)
    {
        $field = $this->form->multipleImage($model['tab'].'-'.$model['key'], $model['name'])->autoUpload()->url(admin_url('files'));
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function editor($model)
    {
        $field = $this->form->editor($model['tab'].'-'.$model['key'], $model['name']);
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    //protected function markdown($model)
    //{
    //    $field = $this->form->markdown($model['tab'].'.'.$model['key'], $model['name']);
    //    //$field = $this->rule($field, $model['options']['rule']);
    //
    //    return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '');
    //}

    protected function map($model)
    {
        $field = $this->form->map($model['tab'].'-'.$model['key'], $model['name']);
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function number($model)
    {
        $field = $this->form->number($model['tab'].'-'.$model['key'], $model['name']);
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function rate($model)
    {
        $field = $this->form->rate($model['tab'].'-'.$model['key'], $model['name']);
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function tags($model)
    {
        $field = $this->form->tags($model['tab'].'-'.$model['key'], $model['name'])->options($this->option($model));
        $field = $this->rule($field, $model['options']['rule']);

        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }

    protected function arrays($model)
    {
        $field = $this->form->array($model['tab'].'-'.$model['key'], $model['name'],function ($form){
            $form->text('key');
            $form->text('value');
        });
        $field = $this->rule($field, $model['options']['rule']);
        return $field->help($model['help'], $model['help'] ? 'feather icon-help-circle' : '')->value($model['value']);
    }


    public function option($model)
    {

        return collect($model['options']['option'])->pluck('value', 'key')->toArray();
    }

    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

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
    protected function trim($str): string
    {
        return trim(strip_tags(str_replace(["\n", "\t", "\r", " ", "&nbsp;"], '', htmlspecialchars_decode($str))));
    }


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
            //'key'=>$tab.".".$key,
            'value' => null,
            'name' => $request->get('name'),
            'help' => $request->get('help'),
            'element' => $request->get('element'),
            'options' => $options
        ];

        $this->model = $this->model::query()->find($id);

        $this->model->forceFill($data)->save();

        return $this;

    }
    /**
     * @return $this
     */
    public function update(): Builder
    {
        $request = \request()->except('_token');
        $i = 0;


        //dd($request);
        //$res = Arr::dot($request);


        $data = [];
        //foreach ($request as $k => $v) {
        //
        //    $str = strrchr($k, '.');
        //    $key = substr($k, 0, strlen($k) - strlen($str));
        //    if (is_numeric(str_replace('.', '', $str))) {
        //        $data[$key][] = $v;
        //    } else {
        //        $data[$k] = $v;
        //    }
        //}

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


                })->filter()->values()->toJson();
            } else {
                $update[$i]['value'] = $value;
            }
            $i++;
        }

        $this->model->batchUpdate($update);

        Artisan::call('config:clear');

        return $this;
    }

    /**
     * @return $this
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

        $time = Carbon::now();
        $attribute->each(function ($value, $item) use (&$data, $tab, $time) {

            if (null !== $value['key']) {
                $data[$item]['key'] = $tab.'.'.$value['key'];
                $data[$item]['name'] = $value['name'];
                $data[$item]['help'] = $value['help'];
                $data[$item]['element'] = $value['element'];
                $data[$item]['options'] = collect([
                    'option' => $value['option'],
                    'rule' => $value['rule'],
                ])->toJson();
                $data[$item]['order'] = $this->order() + $item + 1;
                $data[$item]['created_at'] = $time;
                $data[$item]['updated_at'] = $time;
            }
        });

        $rules = [];
        $message = [];
        foreach ($data as $key => $val) {
            $rules[$key.'.key'] = 'required|unique:admin_config,key|regex:/^[a-zA-Z_\.0-9]+$/';
            $rules[$key.'.name'] = 'required';
            $rules[$key.'.element'] = 'required';

            $message[$key.'.key.required'] = ':attribute 不能为空';
            $message[$key.'.key.unique'] = ':attribute 已存在';
            $message[$key.'.key.regex'] = ':attribute 只能包含字母数字';

            $message[$key.'.key.name'] = ':attribute 不能为空';
            $message[$key.'.key.element'] = ':attribute 不能为空';
        }

        Validator::make($data, $rules, $message)->validate();

        $this->model::query()->insert($data);

        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function edit($id)
    {

        $tab = collect($this->config('tab'))->pluck('value', 'key');

        $this->form->edit($id);
        $this->form->select('tab', DcatConfigServiceProvider::trans('dcat-config.builder.groups'))
            ->options($tab)
            ->customFormat(function (){
               return substr($this->key, 0, strpos($this->key,'.'));
            })
            ->disable()
            ->default($tab->keys()->first());

        $this->form->text('key', DcatConfigServiceProvider::trans('dcat-config.builder.key'))->required()->customFormat(function (){

            return substr($this->key,strrpos($this->key,".")+1);
        })->disable();
        $this->form->text('name', DcatConfigServiceProvider::trans('dcat-config.builder.name'))->required();
        $this->form->select('element', DcatConfigServiceProvider::trans('dcat-config.builder.element'))->required()->when([
            'select',
            'multipleSelect',
            'listbox',
            'radio',
            'checkbox',
        ], function ( $form) {
            $form->textarea('option', DcatConfigServiceProvider::trans('dcat-config.builder.option'))
                ->customFormat(function (){

                    $d= collect($this->options['option'])->pluck('value','key')->toArray();

                    $text = '';
                    foreach ($d as $k=>$v){

                        $text .= $k.':'.$v."\r\n";
                    }
                    return $text;
                })
                ->placeholder("例如:\r\nkey1:value1\r\nkey2:value2");
        })->options($this->option)->default('text');
        $this->form->textarea('rule', DcatConfigServiceProvider::trans('dcat-config.builder.rule'))->customFormat(function (){


            $d = $this->options['rule'];
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
        return $this->model::query()->limit(1)->orderByDesc('order')->value('order');
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
        $this->model::destroy($id);
        return $this;
    }
    /**
     * @return \Dcat\Admin\Form
     */
    public function getForm(): \Dcat\Admin\Form
    {
        return $this->form;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    protected function config($key, $default = null)
    {
        return DcatConfigServiceProvider::setting($key, $default);
    }
}
