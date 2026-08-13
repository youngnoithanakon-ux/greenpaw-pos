<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemConfig extends Model
{
    protected $fillable = ['key', 'value', 'label', 'group'];

    // ---- Static Helpers ----

    /**
     * ดึงค่า config โดย key (cache 10 นาที)
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("sysconfig.{$key}", 600, function () use ($key, $default) {
            $row = static::where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }

    /**
     * บันทึก/อัพเดตค่า config แล้วล้าง cache
     */
    public static function set(string $key, mixed $value, ?string $label = null, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'label' => $label, 'group' => $group]
        );
        Cache::forget("sysconfig.{$key}");
    }

    /**
     * ดึงหลาย key พร้อมกัน → ['key' => value, ...]
     */
    public static function many(array $keys): array
    {
        return static::whereIn('key', $keys)
            ->pluck('value', 'key')
            ->toArray();
    }
}
