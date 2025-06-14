<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute 을(를) 수락해야 합니다.',
	'active_url'           => ':attribute 은(는) 올바른 URL이 아닙니다.',
	'after'                => ':attribute 은(는) :date 이후 날짜여야 합니다.',
	'after_or_equal'       => ':attribute 은(는) :date 이후 또는 같은 날짜여야 합니다.',
	'alpha'                => ':attribute 은(는) 문자만 포함할 수 있습니다.',
	'alpha_dash'           => ':attribute 은(는) 문자, 숫자, 대시만 포함할 수 있습니다.',
	'alpha_num'            => ':attribute 은(는) 문자와 숫자만 포함할 수 있습니다.',
	'array'                => ':attribute 은(는) 배열이어야 합니다.',
	'before'               => ':attribute 은(는) :date 이전 날짜여야 합니다.',
	'before_or_equal'      => ':attribute 은(는) :date 이전 또는 같은 날짜여야 합니다.',
	'between'              => [
	    'numeric' => ':attribute 은(는) :min 에서 :max 사이여야 합니다.',
	    'file'    => ':attribute 은(는) :min 에서 :max 킬로바이트 사이여야 합니다.',
	    'string'  => ':attribute 은(는) :min 에서 :max 문자 사이여야 합니다.',
	    'array'   => ':attribute 은(는) :min 에서 :max 항목 사이여야 합니다.',
	],
	'boolean'              => ':attribute 필드는 true 또는 false 여야 합니다.',
	'confirmed'            => ':attribute 확인이 일치하지 않습니다.',
	'date'                 => ':attribute 은(는) 올바른 날짜가 아닙니다.',
	'date_format'          => ':attribute 이(가) :format 형식과 일치하지 않습니다.',
	'different'            => ':attribute 와(과) :other 은(는) 달라야 합니다.',
	'digits'               => ':attribute 은(는) :digits 자리여야 합니다.',
	'digits_between'       => ':attribute 은(는) :min 에서 :max 자리 사이여야 합니다.',
	'dimensions'           => ':attribute 의 이미지 크기가 유효하지 않습니다.',
	'distinct'             => ':attribute 필드에 중복된 값이 있습니다.',
	'email'                => ':attribute 은(는) 유효한 이메일 주소여야 합니다.',
	'exists'               => '선택된 :attribute 이(가) 유효하지 않습니다.',
	'file'                 => ':attribute 은(는) 파일이어야 합니다.',
	'filled'               => ':attribute 필드에 값이 있어야 합니다.',
	'image'                => ':attribute 은(는) 이미지여야 합니다.',
	'in'                   => '선택된 :attribute 이(가) 유효하지 않습니다.',
	'in_array'             => ':attribute 필드는 :other 에 존재하지 않습니다.',
	'integer'              => ':attribute 은(는) 정수여야 합니다.',
	'ip'                   => ':attribute 은(는) 유효한 IP 주소여야 합니다.',
	'ipv4'                 => ':attribute 은(는) 유효한 IPv4 주소여야 합니다.',
	'ipv6'                 => ':attribute 은(는) 유효한 IPv6 주소여야 합니다.',
	'json'                 => ':attribute 은(는) 유효한 JSON 문자열이어야 합니다.',
	'max'                  => [
	    'numeric' => ':attribute 은(는) :max 보다 클 수 없습니다.',
	    'file'    => ':attribute 은(는) :max 킬로바이트를 초과할 수 없습니다.',
	    'string'  => ':attribute 은(는) :max 자를 초과할 수 없습니다.',
	    'array'   => ':attribute 은(는) :max 항목을 초과할 수 없습니다.',
	],
	'mimes'                => ':attribute 은(는) :values 유형의 파일이어야 합니다.',
	'mimetypes'            => ':attribute 은(는) :values 유형의 파일이어야 합니다.',
	'min'                  => [
	    'numeric' => ':attribute 은(는) 최소한 :min 이어야 합니다.',
	    'file'    => ':attribute 은(는) 최소한 :min 킬로바이트이어야 합니다.',
	    'string'  => ':attribute 은(는) 최소한 :min 자이어야 합니다.',
	    'array'   => ':attribute 은(는) 최소한 :min 항목이어야 합니다.',
	],
	'not_in'               => '선택된 :attribute 이(가) 유효하지 않습니다.',
	'numeric'              => ':attribute 은(는) 숫자여야 합니다.',
	'present'              => ':attribute 필드가 있어야 합니다.',
	'regex'                => ':attribute 형식이 유효하지 않습니다.',
	'required'             => ':attribute 필드는 필수입니다.',
	'required_if'          => ':other 이(가) :value 일 때 :attribute 필드는 필수입니다.',
	'required_unless'      => ':other 이(가) :values 에 없으면 :attribute 필드는 필수입니다.',
	'required_with'        => ':values 이(가) 있을 때 :attribute 필드는 필수입니다.',
	'required_with_all'    => ':values 이(가) 있을 때 :attribute 필드는 필수입니다.',
	'required_without'     => ':values 이(가) 없을 때 :attribute 필드는 필수입니다.',
	'required_without_all' => ':values 이(가) 모두 없을 때 :attribute 필드는 필수입니다.',
	'same'                 => ':attribute 와(과) :other 은(는) 일치해야 합니다.',
	'size'                 => [
	    'numeric' => ':attribute 은(는) :size (이)여야 합니다.',
	    'file'    => ':attribute 은(는) :size 킬로바이트여야 합니다.',
	    'string'  => ':attribute 은(는) :size 자이어야 합니다.',
	    'array'   => ':attribute 은(는) :size 항목을 포함해야 합니다.',
	],
	'string'               => ':attribute 은(는) 문자열이어야 합니다.',
	'timezone'             => ':attribute 은(는) 유효한 시간대여야 합니다.',
	'unique'               => ':attribute 은(는) 이미 사용 중입니다.',
	'uploaded'             => ':attribute 을(를) 업로드하지 못했습니다.',
	'url'                  => ':attribute 형식이 유효하지 않습니다.',

	'custom' => [
	    'attribute-name' => [
	        'rule-name' => '맞춤 메시지',
	    ],
	],

	'attributes' => [],

];
