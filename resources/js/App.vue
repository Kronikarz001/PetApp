<script setup lang="ts">
import { Toaster } from 'vue-sonner'
import AppButton from './components/AppButton.vue'
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-green-600 to-green-800 flex flex-col">
        <nav class="flex items-center justify-between px-8 py-4 bg-white/10 backdrop-blur">
            <router-link to="/pets" class="text-white text-xl font-bold tracking-wide">
                PetApp
            </router-link>
            <router-link to="/pets/create">
                <AppButton variant="primary">Dodaj zwierzę</AppButton>
            </router-link>
        </nav>

        <main class="flex-1 flex items-center justify-center p-8">
            <router-view />
        </main>

        <Toaster position="top-right" richColors />
    </div>
</template>
