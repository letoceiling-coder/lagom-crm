<template>
    <div class="telegram-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки Telegram бота</h1>
            <p class="text-muted-foreground mt-1">Настройка интеграции с Telegram для отправки уведомлений и ошибок</p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <p class="text-muted-foreground">Загрузка настроек...</p>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
            <p class="text-destructive">{{ error }}</p>
        </div>

        <!-- Settings Form -->
        <div v-if="!loading && settings" class="bg-card rounded-lg border border-border p-6 space-y-6">
            <!-- Bot Information -->
            <div class="space-y-4">
                <h2 class="text-xl font-semibold text-foreground">Информация о боте</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Токен бота *
                        </label>
                        <input
                            type="password"
                            v-model="settings.bot_token"
                            placeholder="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                        <p class="text-xs text-muted-foreground mt-1">
                            Получите токен у @BotFather в Telegram
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Имя бота
                        </label>
                        <input
                            type="text"
                            v-model="settings.bot_name"
                            placeholder="Admin Bot"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        ID чата для уведомлений *
                    </label>
                    <input
                        type="text"
                        v-model="settings.chat_id"
                        placeholder="123456789 или @channel_username"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                    />
                    <p class="text-xs text-muted-foreground mt-1">
                        ID группы, канала или личного чата. Для получения ID отправьте боту /start и используйте @userinfobot
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">
                        Webhook URL
                    </label>
                    <input
                        type="url"
                        v-model="settings.webhook_url"
                        placeholder="https://yourdomain.com/api/telegram/webhook"
                        class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                    />
                    <p class="text-xs text-muted-foreground mt-1">
                        URL для получения обновлений от Telegram (опционально)
                    </p>
                </div>

                <!-- Bot Info Display -->
                <div v-if="botInfo" class="p-4 bg-muted/50 rounded-lg">
                    <h3 class="font-semibold text-foreground mb-2">Информация о боте:</h3>
                    <p class="text-sm text-foreground">
                        <strong>Имя:</strong> {{ botInfo.first_name }} {{ botInfo.last_name || '' }}<br>
                        <strong>Username:</strong> @{{ botInfo.username }}<br>
                        <strong>ID:</strong> {{ botInfo.id }}
                    </p>
                </div>
            </div>

            <!-- General Settings -->
            <div class="space-y-4 border-t border-border pt-6">
                <h2 class="text-xl font-semibold text-foreground">Общие настройки</h2>
                
                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="settings.is_enabled"
                            class="w-4 h-4 rounded border-border"
                        />
                        <span class="text-sm font-medium text-foreground">Включить бота</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="settings.send_notifications"
                            :disabled="!settings.is_enabled"
                            class="w-4 h-4 rounded border-border"
                        />
                        <span class="text-sm font-medium text-foreground">Отправлять уведомления</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="settings.send_errors"
                            :disabled="!settings.is_enabled"
                            class="w-4 h-4 rounded border-border"
                        />
                        <span class="text-sm font-medium text-foreground">Отправлять критические ошибки</span>
                    </label>
                </div>
            </div>

            <!-- Message Settings -->
            <div class="space-y-4 border-t border-border pt-6">
                <h2 class="text-xl font-semibold text-foreground">Настройки сообщений</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Режим парсинга
                        </label>
                        <select
                            v-model="settings.parse_mode"
                            class="w-full h-10 px-3 border border-border rounded bg-background text-sm"
                        >
                            <option value="HTML">HTML</option>
                            <option value="Markdown">Markdown</option>
                            <option value="MarkdownV2">MarkdownV2</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="settings.disable_notification"
                            class="w-4 h-4 rounded border-border"
                        />
                        <span class="text-sm font-medium text-foreground">Отправлять без звука</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="settings.disable_web_page_preview"
                            class="w-4 h-4 rounded border-border"
                        />
                        <span class="text-sm font-medium text-foreground">Отключить превью ссылок</span>
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between gap-4 border-t border-border pt-6">
                <button
                    @click="testConnection"
                    :disabled="testing || !settings.bot_token || !settings.chat_id"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
                >
                    {{ testing ? 'Отправка...' : 'Отправить тестовое сообщение' }}
                </button>

                <button
                    @click="saveSettings"
                    :disabled="saving"
                    class="px-6 py-2 bg-accent text-accent-foreground rounded hover:bg-accent/90 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
                >
                    {{ saving ? 'Сохранение...' : 'Сохранить настройки' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'TelegramSettings',
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const testing = ref(false);
        const error = ref(null);
        const settings = ref(null);
        const botInfo = ref(null);

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            
            try {
                const response = await axios.get('/api/v1/telegram-settings');
                settings.value = response.data.data.settings;
                botInfo.value = response.data.data.bot_info;
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
                console.error('Error fetching Telegram settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const saveSettings = async () => {
            saving.value = true;
            error.value = null;
            
            try {
                const response = await axios.put('/api/v1/telegram-settings', settings.value);
                
                if (response.data.data.bot_info) {
                    botInfo.value = response.data.data.bot_info;
                }
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    text: 'Настройки успешно сохранены',
                    timer: 2000,
                    showConfirmButton: false,
                });
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка сохранения настроек';
                await Swal.fire({
                    icon: 'error',
                    title: 'Ошибка',
                    text: error.value,
                });
            } finally {
                saving.value = false;
            }
        };

        const testConnection = async () => {
            testing.value = true;
            
            try {
                const response = await axios.post('/api/v1/telegram-settings/test', {
                    bot_token: settings.value.bot_token,
                    chat_id: settings.value.chat_id,
                });
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Успешно!',
                    text: 'Тестовое сообщение отправлено',
                    timer: 2000,
                    showConfirmButton: false,
                });
            } catch (err) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Ошибка',
                    text: err.response?.data?.message || 'Не удалось отправить тестовое сообщение',
                });
            } finally {
                testing.value = false;
            }
        };

        onMounted(() => {
            fetchSettings();
        });

        return {
            loading,
            saving,
            testing,
            error,
            settings,
            botInfo,
            saveSettings,
            testConnection,
        };
    },
};
</script>

