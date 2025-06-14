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

    'accepted'             => 'Le :attribute doit être accepté.',
	'active_url'           => 'Le :attribute n\'est pas une URL valide.',
	'after'                => 'Le :attribute doit être une date postérieure au :date.',
	'after_or_equal'       => 'Le :attribute doit être une date postérieure ou égale au :date.',
	'alpha'                => 'Le :attribute ne peut contenir que des lettres.',
	'alpha_dash'           => 'Le :attribute ne peut contenir que des lettres, des chiffres et des tirets.',
	'alpha_num'            => 'Le :attribute ne peut contenir que des lettres et des chiffres.',
	'array'                => 'Le :attribute doit être un tableau.',
	'before'               => 'Le :attribute doit être une date antérieure au :date.',
	'before_or_equal'      => 'Le :attribute doit être une date antérieure ou égale au :date.',
	'between'              => [
	    'numeric' => 'Le :attribute doit être compris entre :min et :max.',
	    'file'    => 'Le :attribute doit être compris entre :min et :max kilo-octets.',
	    'string'  => 'Le :attribute doit être compris entre :min et :max caractères.',
	    'array'   => 'Le :attribute doit contenir entre :min et :max éléments.',
	],
	'boolean'              => 'Le champ :attribute doit être vrai ou faux.',
	'confirmed'            => 'La confirmation de :attribute ne correspond pas.',
	'date'                 => 'Le :attribute n\'est pas une date valide.',
	'date_format'          => 'Le :attribute ne correspond pas au format :format.',
	'different'            => 'Le :attribute et le :other doivent être différents.',
	'digits'               => 'Le :attribute doit être de :digits chiffres.',
	'digits_between'       => 'Le :attribute doit être compris entre :min et :max chiffres.',
	'dimensions'           => 'Le :attribute a des dimensions d\'image non valides.',
	'distinct'             => 'Le champ :attribute a une valeur en double.',
	'email'                => 'Le :attribute doit être une adresse e-mail valide.',
	'exists'               => 'Le :attribute sélectionné n\'est pas valide.',
	'file'                 => 'Le :attribute doit être un fichier.',
	'filled'               => 'Le champ :attribute doit avoir une valeur.',
	'image'                => 'Le :attribute doit être une image.',
	'in'                   => 'Le :attribute sélectionné n\'est pas valide.',
	'in_array'             => 'Le champ :attribute n\'existe pas dans :other.',
	'integer'              => 'Le :attribute doit être un entier.',
	'ip'                   => 'Le :attribute doit être une adresse IP valide.',
	'ipv4'                 => 'Le :attribute doit être une adresse IPv4 valide.',
	'ipv6'                 => 'Le :attribute doit être une adresse IPv6 valide.',
	'json'                 => 'Le :attribute doit être une chaîne JSON valide.',
	'max'                  => [
	    'numeric' => 'Le :attribute ne peut pas être supérieur à :max.',
	    'file'    => 'Le :attribute ne peut pas être supérieur à :max kilo-octets.',
	    'string'  => 'Le :attribute ne peut pas être supérieur à :max caractères.',
	    'array'   => 'Le :attribute ne peut pas avoir plus de :max éléments.',
	],
	'mimes'                => 'Le :attribute doit être un fichier de type : :values.',
	'mimetypes'            => 'Le :attribute doit être un fichier de type : :values.',
	'min'                  => [
	    'numeric' => 'Le :attribute doit être au moins de :min.',
	    'file'    => 'Le :attribute doit être au moins de :min kilo-octets.',
	    'string'  => 'Le :attribute doit comporter au moins :min caractères.',
	    'array'   => 'Le :attribute doit contenir au moins :min éléments.',
	],
	'not_in'               => 'Le :attribute sélectionné n\'est pas valide.',
	'numeric'              => 'Le :attribute doit être un nombre.',
	'present'              => 'Le champ :attribute doit être présent.',
	'regex'                => 'Le format de :attribute n\'est pas valide.',
	'required'             => 'Le champ :attribute est obligatoire.',
	'required_if'          => 'Le champ :attribute est obligatoire lorsque :other est :value.',
	'required_unless'      => 'Le champ :attribute est obligatoire sauf si :other est dans :values.',
	'required_with'        => 'Le champ :attribute est obligatoire lorsque :values est présent.',
	'required_with_all'    => 'Le champ :attribute est obligatoire lorsque :values est présent.',
	'required_without'     => 'Le champ :attribute est obligatoire lorsque :values n\'est pas présent.',
	'required_without_all' => 'Le champ :attribute est obligatoire lorsqu\'aucun de :values n\'est présent.',
	'same'                 => 'Le :attribute et le :other doivent correspondre.',
	'size'                 => [
	    'numeric' => 'Le :attribute doit être de :size.',
	    'file'    => 'Le :attribute doit être de :size kilo-octets.',
	    'string'  => 'Le :attribute doit être de :size caractères.',
	    'array'   => 'Le :attribute doit contenir :size éléments.',
	],
	'string'               => 'Le :attribute doit être une chaîne de caractères.',
	'timezone'             => 'Le :attribute doit être une zone valide.',
	'unique'               => 'Le :attribute a déjà été pris.',
	'uploaded'             => 'Le téléchargement de :attribute a échoué.',
	'url'                  => 'Le format de :attribute n\'est pas valide.',

	'custom' => [
	    'attribute-name' => [
	        'rule-name' => 'message-personnalisé',
	    ],
	],

	'attributes' => [],

];
