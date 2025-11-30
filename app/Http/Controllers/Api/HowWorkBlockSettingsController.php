<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HowWorkBlockSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HowWorkBlockSettingsController extends Controller
{
    /**
     * Получить настройки блока
     */
    public function show()
    {
        $settings = HowWorkBlockSettings::getSettings();
        
        return response()->json([
            'data' => $settings,
        ]);
    }

    /**
     * Обновить настройки блока
     */
    public function update(Request $request)
    {
        $settings = HowWorkBlockSettings::getSettings();

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'steps' => 'nullable|array',
            'steps.*.title' => 'required_with:steps|string|max:255',
            'steps.*.description' => 'required_with:steps|string|max:1000',
            'steps.*.icon' => 'nullable|string|max:255',
            'additional_settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings->update($request->only([
            'title',
            'subtitle',
            'is_active',
            'steps',
            'additional_settings',
        ]));

        return response()->json([
            'message' => 'Настройки блока успешно обновлены',
            'data' => $settings,
        ]);
    }
}
