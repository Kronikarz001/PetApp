<script setup lang="ts">
interface Props {
    title?: string
}

withDefaults(defineProps<Props>(), {
    title: '',
})
</script>

<template>
    <div class="bg-white rounded-2xl shadow-lg p-8 w-full">
        <h2 v-if="title" class="text-xl font-bold text-green-800 mb-6">{{ title }}</h2>
        <slot />
    </div>
</template>
