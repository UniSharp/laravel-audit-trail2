<?php

namespace Tests;

use Unisharp\AuditTrail2\Audit;
use Unisharp\AuditTrail2\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Capsule\Manager as Capsule;

class AuditableTest extends TestCase
{
    protected $user = 'Albert Seafood';
    protected $ip = '127.0.0.1';

    protected $table;
    protected $model;

    public function setUp()
    {
        parent::setUp();

        Schema::create($this->table = str_random(), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Auth::shouldReceive('user')->andReturn((object) ['name' => $this->user]);
        Request::shouldReceive('ip')->andReturn($this->ip);

        Config::shouldReceive('get')->andReturnUsing(function ($key, $default = null) {
            $configs = [
                'audit.user' => 'name'
            ];
            if (array_key_exists($key, $configs)) {
                return $configs[$key];
            }
            return $default;
        });

        $this->model = new class extends Model {
            use Auditable;

            protected $fillable = ['name'];
        };

        $this->model->setTable($this->table);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testCreateAudit()
    {
        $this->model->save();

        $result =  [
            'auditable_type' => get_class($this->model),
            'auditable_id' => $this->model->id,
            'action' => 'CREATE',
            'user' => $this->user,
            'ip' => $this->ip,
        ];
        $audit = Capsule::table('audits')->first();

        foreach ($result as $key => $value) {
            $this->assertEquals($audit->{$key}, $value);
        }
    }

    public function testUpdateAudit()
    {
        $this->model->save();
        $this->model->update(['name' => 'Albert Seafood']);

        $result =  [
            'auditable_type' => get_class($this->model),
            'auditable_id' => $this->model->id,
            'action' => 'UPDATE',
            'user' => $this->user,
            'ip' => $this->ip,
        ];
        $audit = Capsule::table('audits')->find(2);

        foreach ($result as $key => $value) {
            $this->assertEquals($audit->{$key}, $value);
        }
    }

    public function testDeleteAudit()
    {
        $this->model->save();
        $this->model->delete();

        $result =  [
            'auditable_type' => get_class($this->model),
            'auditable_id' => $this->model->id,
            'action' => 'DELETE',
            'user' => $this->user,
            'ip' => $this->ip,
        ];
        $audit = Capsule::table('audits')->find(2);

        foreach ($result as $key => $value) {
            $this->assertEquals($audit->{$key}, $value);
        }
    }

    public function testLastModifier()
    {
        $this->model->save();
        $this->model->update(['name' => 'Albert Seafood']);
        $this->model->update(['name' => 'Albert Seafood']);
        $this->model->update(['name' => 'Albert Seafood']);

        $this->assertEquals(
            Audit::whereAction('UPDATE')->orderBy('created_at', 'desc')->first(),
            $this->model->last_modifier
        );
    }

    public function testCreator()
    {
        $this->model->save();

        $this->assertEquals(
            Audit::whereAction('CREATE')->first(),
            $this->model->creator
        );
    }

    public function testLog()
    {
        $this->model->save();

        $this->model->audit('LOG', $log = 'Add log');

        $this->assertEquals(
            Audit::whereAction('LOG')->first()->log,
            $log
        );
    }
}
