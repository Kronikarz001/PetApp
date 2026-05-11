<script setup lang="ts">
import { computed } from 'vue'
import type { PetStatus } from '@/types'

interface Props {
    status: PetStatus | string
}

const props = defineProps<Props>()

const colorClass = computed(() => ({
    'bg-green-100 text-green-700':   props.status === 'available',
    'bg-yellow-100 text-yellow-700': props.status === 'pending',
    'bg-red-100 text-red-700':       props.status === 'sold',
    'bg-gray-100 text-gray-700':     !['available', 'pending', 'sold'].includes(props.status),
}))
</script>

<template>
  <span :class="['px-2 py-1 rounded-full text-xs font-semibold', colorClass]">
    {{ status }}
  </span>
</template>
