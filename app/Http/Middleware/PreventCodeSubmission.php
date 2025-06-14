<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventCodeSubmission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 获取所有请求参数
        $parameters = $request->all();

        // 定义用于检测代码的正则表达式
        $codePattern = '/[<>$()&*#]+|<\?php|<script|<\/script>|<style|<\/style>|<\w+>|<\/\w+>/i';

        // 循环遍历所有参数并进行验证
        foreach ($parameters as $key => $value) {
            // 检查参数是否匹配正则表达式（即是否包含代码）
            if (preg_match($codePattern, $value)) {
                return response()->json(['type' => 'error', 'message' => 'PARAMETER CONTAINS FORBIDDEN CODE!']);
            }
        }

        // 如果所有参数都通过验证，继续处理请求
        return $next($request);
    }
}
