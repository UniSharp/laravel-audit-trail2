<?php

namespace Unisharp\AuditTrail2;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $fillable = ['action', 'user', 'log', 'info', 'ip'];

    protected $casts = [
        'info' => 'array',
    ];

    public function auditable()
    {
        return $this->morphTo();
    }
}
