<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramSettings;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TelegramSettingsController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Получить настройки Telegram
     */
    public function show()
    {
        $settings = TelegramSettings::getSettings();
        $botInfo = null;
        
        // Пытаемся получить информацию о боте, если токен указан
        if ($settings->bot_token) {
            try {
                $telegramService = new TelegramService();
                $botInfo = $telegramService->getBotInfo($settings->bot_token);
            } catch (\Exception $e) {
                // Игнорируем ошибки получения информации о боте
            }
        }
        
        return response()->json([
            'data' => [
                'settings' => $settings,
                'bot_info' => $botInfo,
            ],
        ]);
    }

    /**
     * Обновить настройки Telegram
     */
    public function update(Request $request)
    {
        $settings = TelegramSettings::getSettings();

        $validator = Validator::make($request->all(), [
            'bot_token' => 'nullable|string|max:255',
            'bot_name' => 'nullable|string|max:255',
            'chat_id' => 'nullable|string|max:255',
            'webhook_url' => 'nullable|url|max:500',
            'is_enabled' => 'nullable|boolean',
            'send_notifications' => 'nullable|boolean',
            'send_errors' => 'nullable|boolean',
            'parse_mode' => 'nullable|in:HTML,Markdown,MarkdownV2',
            'disable_notification' => 'nullable|boolean',
            'reply_to_message_id' => 'nullable|integer',
            'disable_web_page_preview' => 'nullable|boolean',
            'additional_settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings->update($request->only([
            'bot_token',
            'bot_name',
            'chat_id',
            'webhook_url',
            'is_enabled',
            'send_notifications',
            'send_errors',
            'parse_mode',
            'disable_notification',
            'reply_to_message_id',
            'disable_web_page_preview',
            'additional_settings',
        ]));

        // Если включен и указан webhook_url, настраиваем webhook
        if ($settings->is_enabled && $settings->webhook_url && $settings->bot_token) {
            try {
                $telegramService = new TelegramService();
                $telegramService->setWebhook($settings->webhook_url);
            } catch (\Exception $e) {
                // Логируем ошибку, но не прерываем сохранение настроек
                \Log::error('Failed to set webhook', ['error' => $e->getMessage()]);
            }
        }

        // Получаем информацию о боте после обновления
        $botInfo = null;
        if ($settings->bot_token) {
            try {
                $telegramService = new TelegramService();
                $botInfo = $telegramService->getBotInfo($settings->bot_token);
            } catch (\Exception $e) {
                // Игнорируем ошибку
            }
        }

        return response()->json([
            'message' => 'Настройки Telegram успешно обновлены',
            'data' => [
                'settings' => $settings->fresh(),
                'bot_info' => $botInfo,
            ],
        ]);
    }

    /**
     * Проверить соединение с ботом
     */
    public function testConnection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bot_token' => 'required|string',
            'chat_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $telegramService = new TelegramService();
            $testMessage = '🧪 Тестовое сообщение от ' . config('app.name') . "\n\nВремя: " . now()->format('d.m.Y H:i:s');
            
            $success = $telegramService->sendMessage($testMessage, $request->chat_id, [
                'parse_mode' => 'HTML',
            ], $request->bot_token);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Тестовое сообщение успешно отправлено',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Не удалось отправить тестовое сообщение',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отправке тестового сообщения: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить информацию о webhook
     */
    public function getWebhookInfo()
    {
        $settings = TelegramSettings::getSettings();
        
        if (!$settings->bot_token) {
            return response()->json([
                'success' => false,
                'message' => 'Токен бота не указан',
            ], 400);
        }

        try {
            $telegramService = new TelegramService();
            $webhookInfo = $telegramService->getWebhookInfo();
            
            return response()->json([
                'success' => true,
                'data' => $webhookInfo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка получения информации о webhook: ' . $e->getMessage(),
            ], 500);
        }
    }
}
