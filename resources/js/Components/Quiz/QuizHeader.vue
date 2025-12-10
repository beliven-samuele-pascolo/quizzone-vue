<script setup>
import { useGameStore } from '@/Stores/gameStore';
const game = useGameStore();
</script>

<template>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
            Quizzone
            <span v-if="game.isAdmin" class="text-xs bg-indigo-500 text-white px-2 py-1 rounded ml-2">CONDUTTORE</span>
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