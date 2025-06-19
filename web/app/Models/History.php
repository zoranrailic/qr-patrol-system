<?php

namespace App\Models;

use App\Models\Base\History as BaseHistory;

class History extends BaseHistory
{
	protected $fillable = [
		'name'
	];

	protected $casts = [
        'created_at'  => 'date:Y-m-d',
		'updated_at'  => 'date:Y-m-d',
    ];
}
