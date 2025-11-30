<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HowWorkBlockSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'is_active',
        'steps',
        'additional_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'steps' => 'array',
        'additional_settings' => 'array',
    ];

    /**
     * Получить настройки блока (singleton)
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'title' => 'Как это работает',
            'subtitle' => 'Простой процесс работы с нами',
            'is_active' => true,
            'steps' => [
                [
                    'title' => 'Шаг 1',
                    'description' => 'Описание первого шага',
                    'icon' => '1',
                ],
                [
                    'title' => 'Шаг 2',
                    'description' => 'Описание второго шага',
                    'icon' => '2',
                ],
                [
                    'title' => 'Шаг 3',
                    'description' => 'Описание третьего шага',
                    'icon' => '3',
                ],
            ],
        ]);
    }
}
