<template>
    <div class="settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки</h1>
            <p class="text-muted-foreground mt-1">Настройки системы</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Telegram Settings Card -->
            <router-link
                to="/admin/settings/telegram"
                class="bg-card rounded-lg border border-border p-6 hover:border-accent transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.173 1.861-.921 6.664-.921 6.664s-.518 2.952-2.069 3.456c-1.556.501-3.896-.979-4.883-1.515-.987-.535-7.311-2.847-7.311-2.847S1.499 13.826 2.726 13.55c1.227-.276 5.334-.706 5.334-.706s1.406.133 1.891-.267c.485-.4.299-1.269.299-1.269s.454-3.023.73-4.436c.278-1.413 1.87-1.304 1.87-1.304s4.526.354 7.532.798c.974.148 1.14.667 1.14.667z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">Telegram бот</h2>
                        <p class="text-sm text-muted-foreground">Настройка уведомлений</p>
                    </div>
                </div>
                <p class="text-sm text-muted-foreground">
                    Настройка интеграции с Telegram для отправки уведомлений и ошибок
                </p>
            </router-link>

            <!-- Placeholder for future settings -->
            <div class="bg-card rounded-lg border border-border p-6 opacity-50">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-muted rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">Дополнительно</h2>
                        <p class="text-sm text-muted-foreground">Скоро</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Settings',
};
</script>

