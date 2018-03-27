<?php

namespace Unisharp\AuditTrail2\Traits;

use Unisharp\AuditTrail2\Audit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Config;

trait Auditable
{
    public static function bootAuditable()
    {
        if (Config::get('audit.auto')) {
            if (isset(static::$auditAuto) && !static::$auditAuto) {
                return;
            }

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

    public function audit($action, $log = null)
    {
        $info = array_intersect_key($this->toArray(), array_flip($this->getInfo()));

        $this->audits()->create([
            'action' => $action,
            'user' => Auth::user() ? Auth::user()->{Config::get('audit.user')} : null,
            'log' => $log,
            'info' => $info,
            'ip' => Request::ip() ?: '127.0.0.1'
        ]);

        return $this;
    }

    public function getInfo()
    {
        return isset($this->auditInfo) ? (array) $this->auditInfo : [];
    }
}
