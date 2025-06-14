@extends('admin._layoutNew')

@section('page-head')
    <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
@endsection

@section('page-content')

<style>
    .el-pagination{
        display: flex;
        align-items: center;
    }
    .layui-text ul{
        padding: 5px 0 5px 5px;
    }
</style>
    <div id="app" v-cloak>
        <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
            <legend>用户邀请关系图</legend>
        </fieldset>

{{--        <div style="display: inline-block; padding: 10px; overflow: auto;">--}}
{{--            <ul id="demo2"></ul>--}}
{{--        </div>--}}

        <el-table
                :data="tableData"
                style="width: 100%"
                row-key="id"
                border
                lazy
                :load="load"
                :tree-props="{children: 'children', hasChildren: 'hasChildren'}">
            <el-table-column
                    prop="account_number"
                    label="账号"
                    width="300">
            </el-table-column>
        </el-table>

        <div style="margin-top: 20px">
            <el-pagination
                    background
                    @current-change="handleCurrentChange"
                    :current-page="currentPage"
                    layout="prev, pager, next"
                    :total="total">
            </el-pagination>
        </div>

    </div>

@endsection


@section('scripts')

    <script src="https://cdn.bootcss.com/vue/2.6.11/vue.min.js"></script>

    <script src="https://unpkg.com/element-ui/lib/index.js"></script>
    <script>
        var vue = new Vue({
            el: "#app",
            data() {
                return {
                    total:10,
                    currentPage:1,
                    tableData: []
                }
            },
            mounted() {
                this.list()
            },
            methods: {
                list(){
                    let that = this;
                    $.ajax({
                        url:'{{url('admin/invite/getTop')}}' + '?page=' + this.currentPage,
                        type:'get',
                        dataType:'json',
                        success:function (res) {
                            let data = res.message;
                            let tableData = data.data ? data.data : [];
                            that.tableData = tableData;
                            that.total = data.total;
                        }
                     });
                },
                // 初始页currentPage、初始每页数据数pagesize和数据data
                handleCurrentChange: function(currentPage){
                    this.currentPage = currentPage;
                    this.list();
                },

                load(tree, treeNode, resolve) {
                    console.log(tree)
                    let pid = tree.id;
                    $.ajax({
                        url:'{{url('admin/invite/getSon')}}' + '?pid=' + pid,
                        type:'get',
                        dataType:'json',
                        success:function (res) {
                            let data = res.message;
                            resolve(data)
                        }
                    });
                }

            }
        })
    </script>

@endsection






{{-- @section('scripts')--}}
    <script>

    window.onload = function() {

            layui.use(['element', 'layer', 'table','tree'], function () {
                var element = layui.element;
                var layer = layui.layer;
                var table = layui.table;
                var $ = layui.$;

                $.ajax({

                        url:'{{url('admin/invite/getTree')}}',
                        type:'get',
                        dataType:'json',
                        success:function (res) {



                              layui.tree({
                                elem: '#demo2' //指定元素
                                ,nodes: res.data
                              });




                            }
                     });



                    });
                }
            </script>

{{--@endsection--}}