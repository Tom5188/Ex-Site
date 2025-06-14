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

    'accepted'             => ':attribute ต้องได้รับการยอมรับ',
	'active_url'           => ':attribute ไม่ใช่ URL ที่ถูกต้อง',
	'after'                => ':attribute ต้องเป็นวันที่หลังจาก :date',
	'after_or_equal'       => ':attribute ต้องเป็นวันที่หลังหรือเท่ากับ :date',
	'alpha'                => ':attribute อาจมีเฉพาะตัวอักษรเท่านั้น',
	'alpha_dash'           => ':attribute อาจมีเฉพาะตัวอักษร ตัวเลข และขีดกลางเท่านั้น',
	'alpha_num'            => ':attribute อาจมีเฉพาะตัวอักษรและตัวเลขเท่านั้น',
	'array'                => ':attribute ต้องเป็นอาร์เรย์',
	'before'               => ':attribute ต้องเป็นวันที่ก่อน :date',
	'before_or_equal'      => ':attribute ต้องเป็นวันที่ก่อนหรือเท่ากับ :date',
	'between'              => [
	    'numeric' => ':attribute ต้องอยู่ระหว่าง :min และ :max',
	    'file'    => ':attribute ต้องอยู่ระหว่าง :min และ :max กิโลไบต์',
	    'string'  => ':attribute ต้องอยู่ระหว่าง :min และ :max ตัวอักษร',
	    'array'   => ':attribute ต้องมีระหว่าง :min และ :max รายการ',
	],
	'boolean'              => ':attribute ฟิลด์ต้องเป็นจริงหรือเท็จ',
	'confirmed'            => ':attribute การยืนยันไม่ตรงกัน',
	'date'                 => ':attribute ไม่ใช่วันที่ที่ถูกต้อง',
	'date_format'          => ':attribute ไม่ตรงกับรูปแบบ :format',
	'different'            => ':attribute และ :other ต้องแตกต่างกัน',
	'digits'               => ':attribute ต้องเป็น :digits หลัก',
	'digits_between'       => ':attribute ต้องอยู่ระหว่าง :min และ :max หลัก',
	'dimensions'           => ':attribute มีขนาดรูปภาพที่ไม่ถูกต้อง',
	'distinct'             => ':attribute ฟิลด์มีค่าที่ซ้ำกัน',
	'email'                => ':attribute ต้องเป็นที่อยู่อีเมลที่ถูกต้อง',
	'exists'               => ':attribute ที่เลือกไม่ถูกต้อง',
	'file'                 => ':attribute ต้องเป็นไฟล์',
	'filled'               => ':attribute ฟิลด์ต้องมีค่า',
	'image'                => ':attribute ต้องเป็นรูปภาพ',
	'in'                   => ':attribute ที่เลือกไม่ถูกต้อง',
	'in_array'             => ':attribute ฟิลด์ไม่มีอยู่ใน :other',
	'integer'              => ':attribute ต้องเป็นจำนวนเต็ม',
	'ip'                   => ':attribute ต้องเป็นที่อยู่ IP ที่ถูกต้อง',
	'ipv4'                 => ':attribute ต้องเป็นที่อยู่ IPv4 ที่ถูกต้อง',
	'ipv6'                 => ':attribute ต้องเป็นที่อยู่ IPv6 ที่ถูกต้อง',
	'json'                 => ':attribute ต้องเป็นสตริง JSON ที่ถูกต้อง',
	'max'                  => [
	    'numeric' => ':attribute อาจไม่มากกว่า :max',
	    'file'    => ':attribute อาจไม่มากกว่า :max กิโลไบต์',
	    'string'  => ':attribute อาจไม่มากกว่า :max ตัวอักษร',
	    'array'   => ':attribute อาจไม่มีมากกว่า :max รายการ',
	],
	'mimes'                => ':attribute ต้องเป็นไฟล์ประเภท: :values',
	'mimetypes'            => ':attribute ต้องเป็นไฟล์ประเภท: :values',
	'min'                  => [
	    'numeric' => ':attribute ต้องมีอย่างน้อย :min',
	    'file'    => ':attribute ต้องมีอย่างน้อย :min กิโลไบต์',
	    'string'  => ':attribute ต้องมีอย่างน้อย :min ตัวอักษร',
	    'array'   => ':attribute ต้องมีอย่างน้อย :min รายการ',
	],
	'not_in'               => ':attribute ที่เลือกไม่ถูกต้อง',
	'numeric'              => ':attribute ต้องเป็นตัวเลข',
	'present'              => ':attribute ฟิลด์ต้องมีอยู่',
	'regex'                => ':attribute รูปแบบไม่ถูกต้อง',
	'required'             => ':attribute ฟิลด์เป็นสิ่งจำเป็น',
	'required_if'          => ':attribute ฟิลด์เป็นสิ่งจำเป็นเมื่อ :other เป็น :value',
	'required_unless'      => ':attribute ฟิลด์เป็นสิ่งจำเป็นเว้นแต่ :other จะอยู่ใน :values',
	'required_with'        => ':attribute ฟิลด์เป็นสิ่งจำเป็นเมื่อ :values มีอยู่',
	'required_with_all'    => ':attribute ฟิลด์เป็นสิ่งจำเป็นเมื่อ :values มีอยู่',
	'required_without'     => ':attribute ฟิลด์เป็นสิ่งจำเป็นเมื่อ :values ไม่มีอยู่',
	'required_without_all' => ':attribute ฟิลด์เป็นสิ่งจำเป็นเมื่อไม่มี :values',
	'same'                 => ':attribute และ :other ต้องตรงกัน',
	'size'                 => [
	    'numeric' => ':attribute ต้องเป็น :size',
	    'file'    => ':attribute ต้องเป็น :size กิโลไบต์',
	    'string'  => ':attribute ต้องเป็น :size ตัวอักษร',
	    'array'   => ':attribute ต้องประกอบด้วย :size รายการ',
	],
	'string'               => ':attribute ต้องเป็นสตริง',
	'timezone'             => ':attribute ต้องเป็นเขตที่ถูกต้อง',
	'unique'               => ':attribute ถูกใช้ไปแล้ว',
	'uploaded'             => ':attribute อัปโหลดไม่สำเร็จ',
	'url'                  => ':attribute รูปแบบไม่ถูกต้อง',

	'custom' => [
	    'attribute-name' => [
	        'rule-name' => 'ข้อความกำหนดเอง',
	    ],
	],

	'attributes' => [],

];
