<script setup lang="ts">
interface Props {
    modelValue?: string
    label?: string
    type?: string
    placeholder?: string
    required?: boolean
    error?: string
}

withDefaults(defineProps<Props>(), {
    modelValue:  '',
    label:       '',
    type:        'text',
    placeholder: '',
    required:    false,
    error:       '',
})

defineEmits<{
    'update:modelValue': [value: string]
}>()
</script>

<template>
    <div class="flex flex-col gap-1">
        <label v-if="label" class="text-sm font-medium text-green-900">
            {{ label }} <span v-if="required" class="text-red-500">*</span>
        </label>

        <input
            v-if="type !== 'select'"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :class="[
        'w-full px-4 py-2 rounded-lg border bg-white text-sm focus:outline-none focus:ring-2 focus:ring-green-400',
        error ? 'border-red-400' : 'border-green-200',
      ]"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />

        <select
            v-else
            :value="modelValue"
            :class="[
        'w-full px-4 py-2 rounded-lg border bg-white text-sm focus:outline-none focus:ring-2 focus:ring-green-400',
        error ? 'border-red-400' : 'border-green-200',
      ]"
            @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        >
            <slot />
        </select>

        <span v-if="error" class="text-xs text-red-500">{{ error }}</span>
    </div>
</template>
