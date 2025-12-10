<script setup>
    import { ref, watch } from 'vue';
    import { router } from '@inertiajs/vue3';
    import { useGameStore } from '@/Stores/gameStore';

    const game = useGameStore();
    const formText = ref('');

    const startGame = () => {
        router.post(route('quiz.start'), { text: formText.value }, {
            onSuccess: () => formText.value = '',
            preserveScroll: true
        });
    };

    const validate = (correct) => {
        router.post(route('quiz.validate'), { correct }, { preserveScroll: true });
    };

    const handleTimeout = () => {
        if (game.remainingSeconds === 0 && ['active', 'buzzed'].includes(game.question?.status)) {
            router.post(route('quiz.timeout'), {}, { preserveScroll: true });
        }
    };

    // Watcher per il timer -> chiama handleTimeout quando il timer arriva a 0
    watch(() => game.remainingSeconds, (val) => {
        if (val === 0) handleTimeout();
    });
</script>

<template>
    
    <div class="p-6 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
        <h3 class="text-xs uppercase font-bold text-indigo-500 mb-2">Admin Console</h3>
        
        <div v-if="!game.question || ['closed', 'pending'].includes(game.question.status)" class="flex gap-2">
            <input v-model="formText" type="text" placeholder="Scrivi la domanda..." 
                   class="flex-1 rounded-md border-gray-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500">
            <button @click="startGame" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-500 transition">
                Lancia
            </button>
        </div>

        <div v-else-if="game.question.status === 'buzzed'" class="w-full">
            
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600 mb-4 shadow-sm relative overflow-hidden">
                <div class="text-xs text-gray-500 uppercase mb-1 flex justify-between">
                    <span>Risposta di {{ game.question.buzzed_user?.name }}:</span>
                    <span v-if="!game.question.answer" class="text-gray-800 dark:text-gray-200 font-mono">
                        {{ game.remainingSeconds }}s
                    </span>
                </div>

                <div v-if="game.question.answer" class="text-2xl font-bold text-gray-900 dark:text-white break-words">
                    {{ game.question.answer }}
                </div>
                
                <div v-else class="text-lg text-gray-400 italic flex items-center gap-2">
                    In attesa della risposta...
                </div>
            </div>

            <div class="flex gap-4" v-if="game.question.answer">
                <button @click="validate(true)" class="flex-1 bg-green-600 text-white py-3 rounded hover:bg-green-500 font-bold shadow">
                    CORRETTA (+1)
                </button>
                <button @click="validate(false)" class="flex-1 bg-red-600 text-white py-3 rounded hover:bg-red-500 font-bold shadow">
                    ERRATA (Elimina)
                </button>
            </div>
        </div>

        <div v-else class="text-xs text-gray-500 flex justify-between">
            <span>Partita in corso...</span>
            <span v-if="game.question?.status === 'active'">Timer: {{ game.remainingSeconds }}s</span>
        </div>
    </div>
</template>
