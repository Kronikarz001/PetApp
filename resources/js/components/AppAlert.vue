<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  message?: string
  type?: 'error' | 'success'
}

const props = withDefaults(defineProps<Props>(), {
  message: '',
  type:    'error',
})

const colorClass = computed(() => ({
  'bg-red-100 text-red-700':     props.type === 'error',
  'bg-green-100 text-green-700': props.type === 'success',
}))
</script>

<template>
  <div v-if="message" :class="['px-4 py-3 rounded-lg text-sm font-medium', colorClass]">
    {{ message }}
  </div>
</template>
