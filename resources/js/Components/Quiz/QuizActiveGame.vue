<script setup>
    import { router } from '@inertiajs/vue3';
    import { ref } from 'vue';
    import { useGameStore } from '@/Stores/gameStore';

    const game = useGameStore();
    const answerText = ref('');
    const attemptBuzz = () => {
        answerText.value = '';
        router.post(route('quiz.buzz'), {}, { preserveScroll: true });
    };

    const sendAnswer = () => {
        if (!answerText.value) return;
        router.post(route('quiz.answer'), { answer: answerText.value }, {
            preserveScroll: true,
            onSuccess: () => answerText.value = ''
        });
    };
</script>

<template>
    <div class="w-full text-center">
        
        <div v-if="!game.question || ['pending', 'closed'].includes(game.question.status)"
            class="text-gray-500 italic text-xl">
            In attesa della domanda...
        </div>

        <div v-else>
            <h2 class="text-3xl font-black text-gray-800 dark:text-white mb-8">
                {{ game.question.body }}
            </h2>

            <div v-if="game.question.status === 'active'">
                <div class="absolute top-4 right-4 font-mono text-2xl font-bold text-gray-300">
                    {{ game.remainingSeconds }}s
                </div>

                <div v-if="!game.isAdmin" class="flex justify-center">
                    <button v-if="!game.isBanned" @click="attemptBuzz" class="bg-red-600 border-8 border-red-800 text-white shadow-2xl hover:scale-105 hover:bg-red-500 active:scale-95 transition-all flex flex-col items-center justify-center group px-8 py-6 rounded-full">
                        PRENOTA
                    </button>
                    <div v-else class="text-red-500 font-bold text-xl p-4 border border-red-500 rounded bg-red-500/10 inline-block">
                        SEI STATO ELIMINATO
                    </div>
                </div>
                <div v-else class="text-gray-400">
                    In attesa che un giocatore prenoti la risposta...
                </div>
            </div>

            <div v-if="game.question.status ==='buzzed'" class="space-y-6">
            
                <div v-if="game.isMyTurn">
                    <div v-if="!game.hasAnswered">
                        <h3 class="text-xl text-indigo-500 font-bold mb-4">TOCCA A TE! SCRIVI LA RISPOSTA!</h3>
                        <div class="text-4xl font-mono text-gray-800 dark:text-gray-200 font-bold mb-4">
                            {{ game.remainingSeconds }}s
                        </div>
                        
                        <div class="flex gap-2 justify-center max-w-md mx-auto">
                            <input 
                                v-model="answerText" 
                                @keyup.enter="sendAnswer"
                                type="text" 
                                class="flex-1 rounded-lg border-2 border-indigo-500 text-lg p-2 text-black"
                                placeholder="La tua risposta..." 
                                autofocus
                            />
                            <button @click="sendAnswer" class="bg-indigo-600 text-white px-6 rounded-lg font-bold">
                                INVIA
                            </button>
                        </div>
                    </div>
                    <div v-else class="text-xl text-gray-500 animate-pulse">
                        Risposta inviata. In attesa del conduttore...
                    </div>
                </div>

                <div v-else>
                    <h3 class="text-2xl font-bold text-yellow-500">
                        {{ game.question.buzzed_user?.name }} si è prenotato!
                        <p v-if="!game.hasAnswered" class="text-gray-400">Sta scrivendo la risposta... ({{ game.remainingSeconds }}s)</p>
                        <p v-else class="text-indigo-400 font-bold text-xl">Ha risposto! In attesa del conduttore</p>
                    </h3>
                </div>

            </div>
        </div>
    </div>
</template>