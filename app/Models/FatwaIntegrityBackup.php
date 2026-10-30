<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use RuntimeException;

class FatwaIntegrityBackup extends Model
{
    use HasFactory;

    protected $table = 'fatwa_integrity_backups';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'full_row_snapshot'     => 'array',
        'references_snapshot'   => 'array',
        'original_is_public'    => 'boolean',
        'original_published_at' => 'datetime',
        'original_created_at'   => 'datetime',
        'original_updated_at'   => 'datetime',
        'created_at'            => 'datetime',
    ];

    /**
     * Enforce strict database immutability:
     * Never allow updates or deletions through Eloquent.
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function () {
            throw new RuntimeException("Fatwa integrity backups are strictly immutable and cannot be updated.");
        });

        static::deleting(function () {
            throw new RuntimeException("Fatwa integrity backups are permanent audit records and cannot be deleted.");
        });
    }

    /**
     * Produce a deterministic, sorted canonical JSON string for hashing.
     */
    public static function canonicalSerialize(array $data): string
    {
        ksort($data);
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = self::canonicalSerializeArrayRecursive($v);
            }
        }
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private static function canonicalSerializeArrayRecursive(array $arr): array
    {
        ksort($arr);
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr[$k] = self::canonicalSerializeArrayRecursive($v);
            }
        }
        return $arr;
    }

    /**
     * Calculate SHA-256 integrity hash from canonical row representation.
     */
    public static function computeIntegrityHash(array $rowData): string
    {
        return hash('sha256', self::canonicalSerialize($rowData));
    }

    /**
     * Verify whether the stored hash matches the canonical hash of the snapshot.
     */
    public function verifyHash(): bool
    {
        if (empty($this->full_row_snapshot) || empty($this->content_sha256)) {
            return false;
        }

        $expectedHash = self::computeIntegrityHash($this->full_row_snapshot);
        return hash_equals($this->content_sha256, $expectedHash);
    }

    // Relationships
    public function record()
    {
        return $this->belongsTo(QuestionAndAnswer::class, 'record_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }
}

