<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use RuntimeException;

class FatwaIntegrityAuditLog extends Model
{
    use HasFactory;

    protected $table = 'fatwa_integrity_audit_logs';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'changed_fields'            => 'array',
        'pre_restoration_snapshot'  => 'array',
        'post_restoration_snapshot' => 'array',
        'source_record_info'        => 'array',
        'created_at'                => 'datetime',
    ];

    /**
     * Enforce strict audit log immutability:
     * Never allow updates or deletions through Eloquent.
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function () {
            throw new RuntimeException("Fatwa integrity audit logs are strictly immutable and cannot be updated.");
        });

        static::deleting(function () {
            throw new RuntimeException("Fatwa integrity audit logs are permanent audit records and cannot be deleted.");
        });
    }

    // Relationships
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function record()
    {
        return $this->belongsTo(QuestionAndAnswer::class, 'record_id');
    }

    public function backup()
    {
        return $this->belongsTo(FatwaIntegrityBackup::class, 'backup_id');
    }
}

