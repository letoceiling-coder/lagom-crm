<template>
    <div class="w-full px-3 sm:px-4 md:px-5 mt-3">
        <section v-if="banner && banner.is_active" class="relative w-full max-w-[1200px] mx-auto rounded-lg min-h-[350px] sm:min-h-[450px] md:min-h-[550px] lg:min-h-[650px] overflow-hidden shadow-sm">
            <!-- Background Image -->
            <div 
                class="absolute inset-0 bg-cover bg-center bg-no-repeat bg-[#6C7B6D]" 
                :style="{
                    backgroundImage: banner.background_image ? `url('${banner.background_image}')` : 'none',
                    backgroundSize: 'cover',
                    backgroundPosition: 'center'
                }"
            ></div>
            
            <!-- Content Overlay -->
            <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 h-full min-h-[350px] sm:min-h-[450px] md:min-h-[550px] lg:min-h-[650px] flex items-center">
                <div class="w-full max-w-2xl">
                    <div v-if="banner.heading_1 || banner.heading_2 || banner.description" class="space-y-2 sm:space-y-3 mb-6 sm:mb-8">
                        <h1 v-if="banner.heading_1" class="text-white text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-semibold leading-tight drop-shadow-lg">
                            {{ banner.heading_1 }}
                        </h1>
                        <h2 v-if="banner.heading_2" class="text-white text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-semibold leading-tight drop-shadow-lg">
                            {{ banner.heading_2 }}
                        </h2>
                        <p v-if="banner.description" class="text-white text-lg sm:text-xl md:text-2xl lg:text-3xl font-normal leading-relaxed whitespace-pre-line drop-shadow-md">
                            {{ banner.description }}
                        </p>
                    </div>
                    <component
                        :is="banner.button_type === 'method' ? 'button' : 'router-link'"
                        v-if="banner.button_text"
                        :to="banner.button_type === 'url' ? banner.button_value : undefined"
                        @click="handleButtonClick"
                        class="inline-block bg-[#657C6C] hover:bg-[#55695a] active:bg-[#48554a] text-white font-medium text-base sm:text-lg px-8 sm:px-10 md:px-12 py-3 sm:py-3.5 md:py-4 rounded-lg transition-all duration-200 transform hover:scale-[1.02] active:scale-100 shadow-lg"
                    >
                        {{ banner.button_text }}
                    </component>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
    name: 'HeroBanner',
    props: {
        slug: {
            type: String,
            default: 'home-banner',
        },
    },
    setup(props) {
        const banner = ref(null);
        const loading = ref(true);

        const fetchBanner = async () => {
            try {
                const response = await fetch(`/api/public/banners/${props.slug}`);
                if (response.ok) {
                    const data = await response.json();
                    if (data.data) {
                        banner.value = {
                            ...data.data,
                            background_image: data.data.background_image 
                                ? `/${data.data.background_image.replace(/^\//, '')}`
                                : null,
                        };
                    }
                }
            } catch (error) {
                console.error('Error fetching banner:', error);
            } finally {
                loading.value = false;
            }
        };

        const handleButtonClick = () => {
            if (banner.value.button_type === 'method') {
                // Здесь будет логика вызова метода/popup
                console.log('Method button clicked:', banner.value.button_value);
                // TODO: Реализовать вызов popup по методу
            }
        };

        onMounted(() => {
            fetchBanner();
        });

        return {
            banner,
            loading,
            handleButtonClick,
        };
    },
};
</script>

