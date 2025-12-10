<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head, router } from '@inertiajs/vue3';
    import { onMounted, onUnmounted } from 'vue';
    import { useGameStore } from '@/Stores/gameStore';

    import QuizHeader from '@/Components/Quiz/QuizHeader.vue';
    import QuizWinner from '@/Components/Quiz/QuizWinner.vue';
    import QuizActiveGame from '@/Components/Quiz/QuizActiveGame.vue';
    import QuizAdminPanel from '@/Components/Quiz/QuizAdminPanel.vue';

    const props = defineProps({ 
        auth: Object, 
        question: Object, 
        game_winner: Object, 
        players: Array 
    });

    // Inizializzazione Pinia
    const game = useGameStore();
    game.initGame(props);

    // listen WebSocket
    onMounted(() => {
        if (window.Echo) {
            window.Echo.channel('quiz-channel')
                .listen('.game.updated', (payload) => game.handleSocketEvent(payload));
        }
    });

    // unlisten WebSocket
    onUnmounted(() => {
        clearInterval(game.timerInterval);
        window.Echo.leave('quiz-channel');
    });

    const startNewGame = () => {
        router.post(route('quiz.reset'), {}, { preserveScroll: true });
    };
</script>

<template>
    <Head title="Quizzone" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700 relative min-h-[500px] flex flex-col">
                    
                    <div v-if="game.isAdmin" class="bg-indigo-50 dark:bg-indigo-900/30 border-b border-indigo-100 dark:border-indigo-800 p-2 flex justify-center">
                        <button @click="startNewGame"
                            class="flex items-center gap-2 px-4 py-1 text-xs font-bold text-white bg-indigo-600 rounded-full hover:bg-indigo-500 shadow transition-transform active:scale-95"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            NUOVO GIOCO
                        </button>
                    </div>

                    <QuizHeader />

                    <div class="flex-1 p-8 flex flex-col justify-center items-center text-center relative">
                        <QuizWinner v-if="game.winner" />
                        <QuizActiveGame v-else />
                    </div>

                    <QuizAdminPanel v-if="game.isAdmin && !game.winner" />

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>