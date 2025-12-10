<script setup>
import { useGameStore } from '@/Stores/gameStore';
const game = useGameStore();
</script>

<template>
    <div class="w-full text-center">
        <h2 class="text-3xl font-black text-gray-800 dark:text-white mb-8">
            Gioco terminato!
            <p class="p-4 text-green-500" v-show="game.winner.id === game.currentUser.id">Hai vinto!</p>
        </h2>
    </div>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center space-x-12">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
            Risultati
        </h2>
        
        <div class="flex gap-4 overflow-x-auto">
            <div v-for="player in game.players" :key="player.id" 
                    class="flex flex-col items-center transition-all"
                    :class="{ 'opacity-40 grayscale': player.banned }">
                <span class="text-xs font-bold uppercase text-gray-500">{{ player.name }}</span>
                <span class="font-mono text-xl font-bold text-gray-700 dark:text-gray-300">
                    {{ player.score }}
                </span>
            </div>
        </div>
    </div>
</template>