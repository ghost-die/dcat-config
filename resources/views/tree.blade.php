<div class="card card-body">
    <div class="highlight bg-gray-light">
        @foreach($data as $k=>$v)
            <div class="p-1" data-toggle="collapse" href="#{{$k}}" aria-controls="{{$k}}">
                <span class="font-weight-bolder font-md-4">
                   {{ $k }}
                </span>
            </div>
            <div class="collapse show pl-4" id="{{$k}}">
                @foreach($v as $str )
                    <dl class="row">
                        <dt class="col-sm-4 text-right">{{$str['name']}} :</dt>
                        <dd class="col-sm-5">
                            <span class="badge badge-secondary dd-toggle" data-title='{{$str['key']}}'>
                                {{\Illuminate\Support\Str::limit("config('".$str['key']."')",25)}}
                            </span>
                        </dd>
                        <dd class="col-sm-3">
{{--                            edit--}}
                            <a class="text-right text-success copyable" title="已复制!" data-content='config("{{$str['key']}}")' href="#">
                                <i class="feather icon-copy"></i>
                            </a>

                            <a class="text-right text-success edit-form" href="{{ admin_url('config/'.$str['key'].'/edit') }}">
{{--                                <span class='btn btn-primary edit-form' data-url=''></span>--}}
                                <i class="feather icon-edit"></i>
                            </a>

{{--                            <span class='btn btn-primary edit-form' data-url="{{ admin_url('config/'.$str['key'].'/edit') }}" >--}}
{{--                                <i class="feather icon-edit"></i>--}}
{{--                            </span>--}}

                            <a class="text-right text-danger delete" data-url="{{ admin_url('config/'.$str['key']) }}" href="#">
                                <i class="feather icon-trash-2"></i>
                            </a>
                        </dd>
                    </dl>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
<script>
    Dcat.ready(function (){
        $('.copyable').off('click').on('click', function (e) {
            let content = $(this).data('content');
            let $temp = $('<input>');
            $("body").append($temp);
            $temp.val(content).select();
            document.execCommand("copy");
            $temp.remove();
            $(this).tooltip('show');
        });

        $('.delete').off('click').on('click', function (e) {

            let url = $(this).data('url');
            $.ajax(
                {
                    url: url,
                    dataType: 'json',
                    type:"post",
                    delay: 250,
                    data: {_method:'delete'},
                    success: function (response) {
                        Dcat.handleJsonResponse(response);
                        return false;
                    },
                }
            );
        });
    });

</script>