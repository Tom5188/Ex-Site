<style>
    .layui-side-menu{
        background-color: #fff;
    }
</style>
<div class="layui-side layui-side-menu">
    <div class="layui-side-scroll">
        <div class="layui-logo" lay-href="/manages/index">
            <span>内容管理系统</span>
        </div>
        <ul class="layui-nav layui-nav-tree" lay-shrink="all" id="LAY-system-side-menu" lay-filter="layadmin-system-side-menu">

            @foreach($menus as $vo)
                @if($vo['menustatus']==1)
                    <li data-name="{{@$vo['name']}}" class="layui-nav-item">
                        <a href="javascript:;" lay-tips="{{@$vo['title']}}" lay-direction="2">
                            <i class="layui-icon {{@$vo['icon']}}"></i>
                            <cite>{{@$vo['title']}}</cite>
                        </a>
                        @if(isset($vo['children']))
                            <dl class="layui-nav-child">
                                @foreach($vo['children'] as $v)

                                    @if($v['menustatus']==1)
                                        <dd data-name="console">
                                            <a lay-href="{{@$v['url']}}">{{@$v['title']}}</a>
                                        </dd>
                                    @endif

                                @endforeach
                            </dl>
                        @endif
                    </li>
                @endif
            @endforeach

        </ul>
    </div>
</div>
