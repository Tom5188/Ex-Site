<?php
namespace App\Http\Middleware;

use Closure;
use App\Setting;

class EnableCrossRequestMiddleware{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next){
        
        $response = $next($request);
        $origin = $request->server('HTTP_ORIGIN') ? $request->server('HTTP_ORIGIN') : '';
        
        $origin_string = Setting::getValueByKey('site_url');
        // 假设取出的内容是： "ggace.cc\njjace.cc"
        
        // 1. 按换行 / 空格 / 逗号分割
        $domains = preg_split('/[\r\n\s,]+/', trim($origin_string), -1, PREG_SPLIT_NO_EMPTY);
        
        // 2. 生成带前缀的数组
        $allow_origin = [];
        foreach ($domains as $domain) {
            $allow_origin[] = 'https://' . $domain;
            $allow_origin[] = 'https://www.' . $domain;
        }
        
        // 3. 去重（如果有的话）
        $allow_origin = array_unique($allow_origin);
        
        if (in_array($origin, $allow_origin)) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN, lang');
            $response->header('Access-Control-Expose-Headers', 'Authorization, authenticated');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS');
            $response->header('Access-Control-Allow-Credentials', 'true');
        }
        
        return $response;
    }
}
