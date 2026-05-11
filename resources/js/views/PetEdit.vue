<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAPI } from '@/composables/useAPI'
import { usePets } from '@/features/pets'
import type { Pet, PetPayload, PetStatus } from '@/types'
import AppCard   from '@/components/AppCard.vue'
import AppInput  from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'
import AppAlert  from '@/components/AppAlert.vue'

const route  = useRoute()
const router = useRouter()
const id     = route.params.id as string

const { data: pet, pending, error, execute } = useAPI<Pet>(`/api/pets/${id}`)
const { editPet } = usePets()
const saving = ref(false)

const form = reactive({
    name:     '',
    status:   'available' as PetStatus,
    category: '',
    photoUrl: '',
})

const fillForm = (data: Pet): void => {
    form.name     = data.name ?? ''
    form.status   = data.status ?? 'available'
    form.category = data.category?.name ?? ''
    form.photoUrl = data.photoUrls?.[0] ?? ''
}

const buildPayload = (): PetPayload => ({
    name:      form.name,
    status:    form.status,
    photoUrls: form.photoUrl ? [form.photoUrl] : [],
    category:  form.category ? { name: form.category } : null,
})

const onSubmit = async (): Promise<void> => {
    saving.value = true
    try {
        await editPet(Number(id), buildPayload())
        router.push(`/pets/${id}`)
    } finally {
        saving.value = false
    }
}

onMounted(async () => {
    await execute()
    if (pet.value) fillForm(pet.value)
})
</script>

<template>
    <div class="w-full max-w-lg">
        <div v-if="pending" class="flex justify-center py-12">
            <div class="w-10 h-10 border-4 border-white border-t-transparent rounded-full animate-spin" />
        </div>

        <AppCard v-else :title="`Edytuj: ${form.name}`">
            <AppAlert :message="error ?? ''" type="error" class="mb-4" />

            <form class="space-y-4" @submit.prevent="onSubmit">
                <AppInput v-model="form.name" label="Nazwa" :required="true" />

                <AppInput type="select" v-model="form.status" label="Status" :required="true">
                    <option value="available">Available</option>
                    <option value="pending">Pending</option>
                    <option value="sold">Sold</option>
                </AppInput>

                <AppInput v-model="form.category" label="Kategoria" />

                <AppInput v-model="form.photoUrl" label="URL zdjęcia" />

                <div class="flex gap-3 pt-2">
                    <AppButton type="submit" variant="primary" :disabled="saving">
                        {{ saving ? 'Zapisywanie...' : 'Zapisz' }}
                    </AppButton>
                    <router-link :to="`/pets/${id}`">
                        <AppButton variant="ghost">Anuluj</AppButton>
                    </router-link>
                </div>
            </form>
        </AppCard>
    </div>
</template>
