<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value'])]
class Pengaturan extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'settings';

    public $timestamps = false;

    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::remember("setting.{$key}", now()->addHours(1), function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting.{$key}");
    }
}
