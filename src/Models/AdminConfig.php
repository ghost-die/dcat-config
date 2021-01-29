<?php

namespace Ghost\DcatConfig\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdminConfig extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'admin_config';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'options' => 'json',
    ];

    public function __construct(array $attributes = [])
    {
        $this->connection = config('database.connection') ?: config('database.default');

        parent::__construct($attributes);
    }

    //批量更新
    public function batchUpdate($multipleData = [])
    {
        try {
            if (empty($multipleData)) {
                throw new \Exception("数据不能为空");
            }
            $tableName = $this->getTable(); // 表名
            $firstRow = current($multipleData);

            $updateColumn = array_keys($firstRow);
            // 默认以id为条件更新，如果没有ID则以第一个字段为条件
            $referenceColumn = isset($firstRow['id']) ? 'id' : current($updateColumn);

            unset($updateColumn[0]);
            // 拼接sql语句
            $updateSql = "UPDATE ".$tableName." SET ";
            $sets = [];
            $bindings = [];
            foreach ($updateColumn as $uColumn) {
                $setSql = "`".$uColumn."` = CASE ";
                foreach ($multipleData as $data) {
                    $setSql .= "WHEN `".$referenceColumn."` = ? THEN ? ";
                    $bindings[] = $data[$referenceColumn];
                    $bindings[] = $data[$uColumn];
                }
                $setSql .= "ELSE `".$uColumn."` END ";
                $sets[] = $setSql;
            }
            $updateSql .= implode(', ', $sets);
            $whereIn = collect($multipleData)->pluck($referenceColumn)->values()->all();
            $bindings = array_merge($bindings, $whereIn);
            $whereIn = rtrim(str_repeat('?,', count($whereIn)), ',');
            $updateSql = rtrim($updateSql, ", ")." WHERE `".$referenceColumn."` IN (".$whereIn.")";

            // 传入预处理sql语句和对应绑定数据
            return DB::update($updateSql, $bindings);
        } catch (\Exception $e) {
            return false;
        }
    }
}