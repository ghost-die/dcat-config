<?php

namespace Ghost\DcatConfig\Tools;

use Illuminate\Support\Str;

class Field
{
    protected $model;

    protected $form;

    public function __construct($model, $form)
    {
        $this->model = $model;
        $this->form = $form;
    }

    public static function make($model, $form)
    {
        return new self($model, $form);
    }

    /**
     * @param \Dcat\Admin\Form\Field $field
     * @param $rule
     * @return \Dcat\Admin\Form\Field
     */
    public function rule(\Dcat\Admin\Form\Field $field, $rules)
    {
        foreach ((array)$rules as $rule){

            if ($rule === 'required')
            {
                $field->required();
            }
            if (Str::contains($rule,'min:')){
                [$r,$num] = explode(':',$rule);
                $field->minLength($num);
            }

            if (Str::contains($rule,'max:')){
                [$r,$num] = explode(':',$rule);
                $field->maxLength($num);
            }
        }
        return $field;
    }

    public function text()
    {


        $field = $this->form->text($this->model['tab'].'-'.$this->model['key'], $this->model['name']);

        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function select()
    {
        $field = $this->form->select($this->model['tab'].'-'.$this->model['key'], $this->model['name'])->options($this->option());
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function multipleSelect()
    {
        $field = $this->form->multipleSelect($this->model['tab'].'-'.$this->model['key'], $this->model['name'])->options($this->option());
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function listbox()
    {
        $field = $this->form->listbox($this->model['tab'].'-'.$this->model['key'], $this->model['name'])->options($this->option());
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function textarea()
    {
        $field = $this->form->textarea($this->model['tab'].'-'.$this->model['key'], $this->model['name']);
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function radio()
    {
        $field = $this->form->radio($this->model['tab'].'-'.$this->model['key'], $this->model['name'])->options($this->option());
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function checkbox()
    {
        $field = $this->form->checkbox($this->model['tab'].'-'.$this->model['key'], $this->model['name'])->options($this->option());
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function email()
    {
        $field = $this->form->email($this->model['tab'].'-'.$this->model['key'], $this->model['name']);
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function password()
    {
        $field = $this->form->password($this->model['tab'].'-'.$this->model['key'], $this->model['name']);
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function url()
    {
        $field = $this->form->url($this->model['tab'].'-'.$this->model['key'], $this->model['name']);
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function ip()
    {
        $field = $this->form->ip($this->model['tab'].'-'.$this->model['key'], $this->model['name']);
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function mobile()
    {
        $field = $this->form->mobile($this->model['tab'].'-'.$this->model['key'], $this->model['name']);
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function color()
    {
        $field = $this->form->color($this->model['tab'].'-'.$this->model['key'], $this->model['name']);
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function time()
    {
        $field = $this->form->time($this->model['tab'].'-'.$this->model['key'], $this->model['name']);
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function date()
    {
        $field = $this->form->date($this->model['tab'].'-'.$this->model['key'], $this->model['name']);
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function datetime()
    {
        $field = $this->form->datetime($this->model['tab'].'-'.$this->model['key'], $this->model['name']);
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function file()
    {
        $field = $this->form->file($this->model['tab'].'-'.$this->model['key'], $this->model['name'])->autoUpload()->url(admin_url('files'));
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function image()
    {
        $field = $this->form->image($this->model['tab'].'-'.$this->model['key'], $this->model['name'])->autoUpload()->url(admin_url('files'));
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function multipleFile()
    {
        $field = $this->form->multipleFile($this->model['tab'].'-'.$this->model['key'], $this->model['name'])->autoUpload()->url(admin_url('files'));

        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function multipleImage()
    {
        $field = $this->form->multipleImage($this->model['tab'].'-'.$this->model['key'], $this->model['name'])->autoUpload()->url(admin_url('files'));
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function editor()
    {
        $field = $this->form->editor($this->model['tab'].'-'.$this->model['key'], $this->model['name']);
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    //protected function markdown()
    //{
    //    $field = $this->form->markdown($this->model['tab'].'.'.$this->model['key'], $this->model['name']);
    //    //$field = $this->rule($field, $this->model['options']['rule']);
    //
    //    return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '');
    //}

    public function map()
    {
        $field = $this->form->map($this->model['tab'].'-'.$this->model['key'], $this->model['name']);
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function number()
    {
        $field = $this->form->number($this->model['tab'].'-'.$this->model['key'], $this->model['name']);
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function rate()
    {
        $field = $this->form->rate($this->model['tab'].'-'.$this->model['key'], $this->model['name']);
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function tags()
    {
        $field = $this->form->tags($this->model['tab'].'-'.$this->model['key'], $this->model['name'])->options($this->option());
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function arrays()
    {
        $field = $this->form->array($this->model['tab'].'-'.$this->model['key'], $this->model['name'], function ($form
        ) {
            $form->text('key');
            $form->text('value');
        });
        $field = $this->rule($field, $this->model['options']['rule']);

        return $field->help($this->model['help'], $this->model['help'] ? 'feather icon-help-circle' : '')->value($this->model['value']);
    }

    public function option()
    {

        return collect($this->model['options']['option'])->pluck('value', 'key')->toArray();
    }
}