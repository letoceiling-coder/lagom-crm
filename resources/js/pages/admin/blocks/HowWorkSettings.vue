<template>
    <div class="how-work-settings-page space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-foreground">Настройки блока "Как это работает"</h1>
            <p class="text-muted-foreground mt-1">Управление блоком "Как это работает" на главной странице</p>
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
        <div v-if="!loading" class="space-y-6">
            <!-- General Settings -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <h2 class="text-xl font-semibold text-foreground">Общие настройки</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Заголовок блока
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            placeholder="Как это работает"
                            class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            Подзаголовок / Описание
                        </label>
                        <textarea
                            v-model="form.subtitle"
                            rows="3"
                            placeholder="Простой процесс работы с нами"
                            class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent resize-none"
                        ></textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="w-4 h-4 rounded border-border"
                        />
                        <label for="is_active" class="text-sm font-medium text-foreground">
                            Блок активен на главной странице
                        </label>
                    </div>
                </div>
            </div>

            <!-- Steps -->
            <div class="bg-card rounded-lg border border-border p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-foreground">Шаги работы</h2>
                    <button
                        @click="addStep"
                        class="px-4 py-2 bg-accent text-accent-foreground rounded-lg hover:bg-accent/90 transition-colors text-sm font-medium"
                    >
                        + Добавить шаг
                    </button>
                </div>

                <div v-if="form.steps && form.steps.length === 0" class="text-center py-8 text-muted-foreground">
                    <p>Нет шагов. Добавьте первый шаг.</p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="(step, index) in form.steps"
                        :key="index"
                        class="border border-border rounded-lg p-4 space-y-4"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-foreground">Шаг {{ index + 1 }}</h3>
                            <button
                                @click="removeStep(index)"
                                class="px-3 py-1 text-sm text-destructive hover:bg-destructive/10 rounded transition-colors"
                            >
                                Удалить
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Иконка / Номер
                                </label>
                                <input
                                    v-model="step.icon"
                                    type="text"
                                    placeholder="1, 2, 3 или эмодзи"
                                    class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                                />
                                <p class="text-xs text-muted-foreground mt-1">
                                    Если не указано, будет использован номер шага
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-foreground mb-2">
                                    Заголовок шага
                                </label>
                                <input
                                    v-model="step.title"
                                    type="text"
                                    placeholder="Название шага"
                                    class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">
                                Описание шага
                            </label>
                            <textarea
                                v-model="step.description"
                                rows="3"
                                placeholder="Подробное описание шага"
                                class="w-full px-4 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-accent resize-none"
                            ></textarea>
                        </div>

                        <!-- Кнопки перемещения -->
                        <div class="flex gap-2">
                            <button
                                v-if="index > 0"
                                @click="moveStep(index, 'up')"
                                class="px-3 py-1 text-sm border border-border hover:bg-accent/10 rounded transition-colors"
                                title="Переместить вверх"
                            >
                                ↑ Вверх
                            </button>
                            <button
                                v-if="index < form.steps.length - 1"
                                @click="moveStep(index, 'down')"
                                class="px-3 py-1 text-sm border border-border hover:bg-accent/10 rounded transition-colors"
                                title="Переместить вниз"
                            >
                                ↓ Вниз
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-border">
                <button
                    @click="saveSettings"
                    :disabled="saving"
                    class="px-6 py-2 bg-accent text-accent-foreground rounded-lg hover:bg-accent/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
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
    name: 'HowWorkSettings',
    setup() {
        const loading = ref(true);
        const saving = ref(false);
        const error = ref(null);
        const form = ref({
            title: '',
            subtitle: '',
            is_active: true,
            steps: [],
        });

        const fetchSettings = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await axios.get('/api/v1/how-work-block-settings');
                if (response.data && response.data.data) {
                    form.value = {
                        title: response.data.data.title || '',
                        subtitle: response.data.data.subtitle || '',
                        is_active: response.data.data.is_active !== false,
                        steps: response.data.data.steps || [],
                    };
                }
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка загрузки настроек';
                console.error('Error fetching settings:', err);
            } finally {
                loading.value = false;
            }
        };

        const addStep = () => {
            if (!form.value.steps) {
                form.value.steps = [];
            }
            form.value.steps.push({
                title: '',
                description: '',
                icon: '',
            });
        };

        const removeStep = (index) => {
            if (form.value.steps && form.value.steps.length > index) {
                form.value.steps.splice(index, 1);
            }
        };

        const moveStep = (index, direction) => {
            if (!form.value.steps || form.value.steps.length <= 1) return;
            
            const newIndex = direction === 'up' ? index - 1 : index + 1;
            if (newIndex >= 0 && newIndex < form.value.steps.length) {
                const step = form.value.steps[index];
                form.value.steps[index] = form.value.steps[newIndex];
                form.value.steps[newIndex] = step;
            }
        };

        const saveSettings = async () => {
            saving.value = true;
            error.value = null;
            try {
                const response = await axios.put('/api/v1/how-work-block-settings', form.value);
                
                await Swal.fire({
                    title: 'Настройки сохранены',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } catch (err) {
                error.value = err.response?.data?.message || 'Ошибка сохранения настроек';
                await Swal.fire({
                    title: 'Ошибка',
                    text: error.value,
                    icon: 'error',
                    confirmButtonText: 'ОК'
                });
            } finally {
                saving.value = false;
            }
        };

        onMounted(() => {
            fetchSettings();
        });

        return {
            loading,
            saving,
            error,
            form,
            addStep,
            removeStep,
            moveStep,
            saveSettings,
        };
    },
};
</script>

<style scoped>
/* Дополнительные стили при необходимости */
</style>

