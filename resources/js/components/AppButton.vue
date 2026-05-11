<script setup lang="ts">
interface Props {
    variant?: 'primary' | 'danger' | 'ghost' | 'outline'
    type?: 'button' | 'submit' | 'reset'
    disabled?: boolean
}

withDefaults(defineProps<Props>(), {
    variant:  'primary',
    type:     'button',
    disabled: false,
})

defineEmits<{
    click: []
}>()
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        :class="[
      'px-5 py-2 rounded-lg font-semibold text-sm transition disabled:opacity-50 disabled:cursor-not-allowed',
      {
        'bg-white text-green-700 hover:bg-green-50 border border-green-200': variant === 'primary',
        'bg-red-500 text-white hover:bg-red-600':                            variant === 'danger',
        'bg-white/20 text-white hover:bg-white/30':                          variant === 'ghost',
        'bg-transparent text-green-700 border border-green-700 hover:bg-green-50': variant === 'outline',
      },
    ]"
        @click="$emit('click')"
    >
        <slot />
    </button>
</template>
