<template>
    <section v-if="settings && settings.is_active" class="w-full px-3 sm:px-4 md:px-5 py-8 sm:py-12 md:py-16 lg:py-20">
        <div class="w-full max-w-[1200px] mx-auto">
            <!-- Заголовок и подзаголовок -->
            <div v-if="settings.title || settings.subtitle" class="text-center mb-8 sm:mb-12 md:mb-16">
                <h2 v-if="settings.title" class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-foreground mb-4 sm:mb-6">
                    {{ settings.title }}
                </h2>
                <p v-if="settings.subtitle" class="text-base sm:text-lg md:text-xl text-muted-foreground max-w-3xl mx-auto">
                    {{ settings.subtitle }}
                </p>
            </div>

            <!-- Шаги -->
            <div v-if="settings.steps && settings.steps.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 md:gap-10">
                <div
                    v-for="(step, index) in settings.steps"
                    :key="index"
                    class="relative bg-card border border-border rounded-lg p-6 sm:p-8 hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
                >
                    <!-- Номер шага / Иконка -->
                    <div class="flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-accent text-accent-foreground font-bold text-xl sm:text-2xl mb-4 sm:mb-6">
                        <span v-if="step.icon">{{ step.icon }}</span>
                        <span v-else>{{ index + 1 }}</span>
                    </div>

                    <!-- Заголовок шага -->
                    <h3 v-if="step.title" class="text-lg sm:text-xl md:text-2xl font-semibold text-foreground mb-3 sm:mb-4">
                        {{ step.title }}
                    </h3>

                    <!-- Описание шага -->
                    <p v-if="step.description" class="text-sm sm:text-base text-muted-foreground leading-relaxed">
                        {{ step.description }}
                    </p>
                </div>
            </div>

            <!-- Пустое состояние -->
            <div v-else class="text-center py-12 text-muted-foreground">
                <p>Шаги не настроены. Добавьте шаги в настройках блока.</p>
            </div>
        </div>
    </section>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
    name: 'HowWork',
    setup() {
        const settings = ref(null);
        const loading = ref(true);

        const fetchSettings = async () => {
            try {
                const response = await fetch('/api/public/how-work-block/settings');
                if (response.ok) {
                    const data = await response.json();
                    if (data.data) {
                        settings.value = data.data;
                    }
                }
            } catch (error) {
                console.error('Error fetching HowWork block settings:', error);
            } finally {
                loading.value = false;
            }
        };

        onMounted(() => {
            fetchSettings();
        });

        return {
            settings,
            loading,
        };
    },
};
</script>

<style scoped>
/* Дополнительные стили при необходимости */
</style>

