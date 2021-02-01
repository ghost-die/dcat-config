<?php

namespace Ghost\DcatConfig\Http\Controllers;

use Dcat\Admin\Traits\HasUploadedFile;

class FileController
{
    use HasUploadedFile;

    public function handle()
    {
        $disk = $this->disk();

        // 判断是否是删除文件请求
        if ($this->isDeleteRequest()) {
            // 删除文件并响应
            return $this->deleteFileAndResponse($disk);
        }

        // 获取上传的文件
        $file = $this->file();
        //upload_failed
        if (! $file) {
            return $this->responseErrorMessage(trans('admin.upload_failed'));
        }

        // 获取上传的字段名称
        $column = $this->uploader()->upload_column;

        $unique = md5_file($file);

        $dir = 'images';
        $newName = $column.$unique.".".$file->getClientOriginalExtension();

        $result = $disk->putFileAs($dir, $file, $newName);

        $path = "{$dir}/$newName";

        return $result ? $this->responseUploaded($path, $disk->url($path)) : $this->responseErrorMessage(trans('admin.upload_failed'));
    }
}