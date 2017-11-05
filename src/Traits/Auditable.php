<?php

namespace Unisharp\AuditTrail2\Traits;

use Unisharp\AuditTrail2\Audit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($auditable) {
            $auditable->audit('CREATE');
        });

        static::updated(function ($auditable) {
            $auditable->audit('UPDATE');
        });

        static::deleted(function ($auditable) {
            $auditable->audit('DELETE');
        });
    }

    public function audits()
    {
        return $this->morphMany(Audit::class, 'auditable');
    }

    public function getCreatorAttribute()
    {
        return $this->audits->where('action', 'CREATE')->first();
    }

    public function getLastModifierAttribute()
    {
        return $this->audits->where('action', 'UPDATE')->reverse()->first()
            ?? $this->audits->where('action', 'CREATE')->first();
    }

    protected function audit($action)
    {
        $this->audits()->create([
            'action' => $action,
            'user' => Auth::user() ? Auth::user()->name : null,
            'ip' => Request::ip(),
        ]);

        return $this;
    }
}
