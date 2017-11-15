<?php

namespace Unisharp\AuditTrail2;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $fillable = ['action', 'user', 'log', 'info', 'ip'];

    public function auditable()
    {
        return $this->morphTo();
    }
}
