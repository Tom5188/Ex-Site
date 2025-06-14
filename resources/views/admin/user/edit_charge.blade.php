@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">充值信息</label>
            <div class="layui-input-block">
               <table class="layui-table">
                <tbody>
                    <tr>
                        <td>
                            账户名：{{$charge_info->account_name}}
                        </td>
                        <td>
                            币种：{{$charge_info->currency_name}}
                        </td>
                    </tr>
                     <tr>
                        <td>
                            充值数量：{{$charge_info->amount}}
                        </td>
                        <td>
                            @if($charge_info->type == 1 )
                                充值方式：银行卡
                            @endif
                            @if($charge_info->type == 0 )
                                充值方式：在线充值
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>
                            费率：{{$charge_info->give}}
                        </td>
                         <td>
                            申请时间：{{$charge_info -> created_at}}
                        </td>
                    </tr>
                    @if($charge_info->type == 0 )
                        <tr>
                            <td>
                                类型：{{$charge_info->sub_type}}
                            </td>
                             <td>
                                地址：{{$charge_info -> address}}
                            </td>
                        </tr>
                    @endif
                     @if($charge_info->type == 1 )
                         <tr>
                            <td>
                                账户名称：{{$charge_info->bank_user_name}}
                            </td>
                             <td>
                                IBAN：{{$charge_info -> iban}}
                            </td>
                        </tr>
                         <tr>
                            <td>
                                收款人国家/地区：{{$charge_info->beneficiary_country}}
                            </td>
                             <td>
                                银行编码（BIC/SWIFT)：{{$charge_info -> bank_code}}
                            </td>
                        </tr>
                         <tr>
                            <td>
                                银行名称：{{$charge_info->bank_name}}
                            </td>
                             <td>
                                银行地址：{{$charge_info -> bank_address}}
                            </td>
                        </tr>
                     @endif
                      <tr>
                        <td colspan="2">
                            备注：{{$charge_info->desc}}
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
        
        
    </form>

@endsection

@section('scripts')
    

@endsection