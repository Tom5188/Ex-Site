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

    'accepted'             => ':attribute を承認する必要があります。',
	'active_url'           => ':attribute は有効なURLではありません。',
	'after'                => ':attribute は :date より後の日付である必要があります。',
	'after_or_equal'       => ':attribute は :date 以降の日付である必要があります。',
	'alpha'                => ':attribute は文字のみを含むことができます。',
	'alpha_dash'           => ':attribute は文字、数字、ダッシュのみを含むことができます。',
	'alpha_num'            => ':attribute は文字と数字のみを含むことができます。',
	'array'                => ':attribute は配列である必要があります。',
	'before'               => ':attribute は :date より前の日付である必要があります。',
	'before_or_equal'      => ':attribute は :date 以前の日付である必要があります。',
	'between'              => [
	    'numeric' => ':attribute は :min から :max の間でなければなりません。',
	    'file'    => ':attribute は :min から :max キロバイトの間でなければなりません。',
	    'string'  => ':attribute は :min から :max 文字の間でなければなりません。',
	    'array'   => ':attribute は :min から :max 項目の間でなければなりません。',
	],
	'boolean'              => ':attribute フィールドは true または false でなければなりません。',
	'confirmed'            => ':attribute 確認が一致しません。',
	'date'                 => ':attribute は有効な日付ではありません。',
	'date_format'          => ':attribute は :format 形式と一致しません。',
	'different'            => ':attribute と :other は異なる必要があります。',
	'digits'               => ':attribute は :digits 桁でなければなりません。',
	'digits_between'       => ':attribute は :min から :max 桁の間でなければなりません。',
	'dimensions'           => ':attribute は無効な画像サイズです。',
	'distinct'             => ':attribute フィールドに重複する値があります。',
	'email'                => ':attribute は有効なメールアドレスである必要があります。',
	'exists'               => '選択された :attribute は無効です。',
	'file'                 => ':attribute はファイルである必要があります。',
	'filled'               => ':attribute フィールドには値が必要です。',
	'image'                => ':attribute は画像である必要があります。',
	'in'                   => '選択された :attribute は無効です。',
	'in_array'             => ':attribute フィールドは :other に存在しません。',
	'integer'              => ':attribute は整数である必要があります。',
	'ip'                   => ':attribute は有効なIPアドレスである必要があります。',
	'ipv4'                 => ':attribute は有効なIPv4アドレスである必要があります。',
	'ipv6'                 => ':attribute は有効なIPv6アドレスである必要があります。',
	'json'                 => ':attribute は有効なJSON文字列である必要があります。',
	'max'                  => [
	    'numeric' => ':attribute は :max を超えてはなりません。',
	    'file'    => ':attribute は :max キロバイトを超えてはなりません。',
	    'string'  => ':attribute は :max 文字を超えてはなりません。',
	    'array'   => ':attribute は :max 項目を超えてはなりません。',
	],
	'mimes'                => ':attribute は次のタイプのファイルでなければなりません: :values。',
	'mimetypes'            => ':attribute は次のタイプのファイルでなければなりません: :values。',
	'min'                  => [
	    'numeric' => ':attribute は少なくとも :min である必要があります。',
	    'file'    => ':attribute は少なくとも :min キロバイトである必要があります。',
	    'string'  => ':attribute は少なくとも :min 文字である必要があります。',
	    'array'   => ':attribute には少なくとも :min 項目が必要です。',
	],
	'not_in'               => '選択された :attribute は無効です。',
	'numeric'              => ':attribute は数字である必要があります。',
	'present'              => ':attribute フィールドが存在する必要があります。',
	'regex'                => ':attribute 形式は無効です。',
	'required'             => ':attribute フィールドは必須です。',
	'required_if'          => ':attribute フィールドは、:other が :value の場合に必須です。',
	'required_unless'      => ':attribute フィールドは、:other が :values にない限り必須です。',
	'required_with'        => ':attribute フィールドは、:values が存在する場合に必須です。',
	'required_with_all'    => ':attribute フィールドは、:values が存在する場合に必須です。',
	'required_without'     => ':attribute フィールドは、:values が存在しない場合に必須です。',
	'required_without_all' => ':attribute フィールドは、:values が一つも存在しない場合に必須です。',
	'same'                 => ':attribute と :other は一致する必要があります。',
	'size'                 => [
	    'numeric' => ':attribute は :size でなければなりません。',
	    'file'    => ':attribute は :size キロバイトでなければなりません。',
	    'string'  => ':attribute は :size 文字でなければなりません。',
	    'array'   => ':attribute には :size 項目が含まれている必要があります。',
	],
	'string'               => ':attribute は文字列である必要があります。',
	'timezone'             => ':attribute は有効なタイムゾーンである必要があります。',
	'unique'               => ':attribute は既に存在しています。',
	'uploaded'             => ':attribute のアップロードに失敗しました。',
	'url'                  => ':attribute の形式は無効です。',

	'custom' => [
	    'attribute-name' => [
	        'rule-name' => 'カスタムメッセージ',
	    ],
	],

	'attributes' => [],

];
