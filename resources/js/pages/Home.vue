<template>
    <div class="home-page">
        <HeroBanner />
        <Decisions />
        
        <!-- Main Content -->
        <div class="container mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            <div class="text-center">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-4">Добро пожаловать</h2>
                <p class="text-lg text-gray-600 mb-8">Это публичная главная страница</p>
            </div>
        </div>
    </div>
</template>

<script>
import HeroBanner from '../components/public/HeroBanner.vue';
import Decisions from '../components/public/Decisions.vue';

export default {
    name: 'Home',
    components: {
        HeroBanner,
        Decisions,
    },
};
</script>

