<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { usePets } from '@/features/pets'
import type { PetPayload, PetStatus } from '@/types'
import AppCard   from '@/components/AppCard.vue'
import AppInput  from '@/components/AppInput.vue'
import AppButton from '@/components/AppButton.vue'

const router  = useRouter()
const { addPet } = usePets()
const loading = ref(false)

const form = reactive({
    name:     '',
    status:   'available' as PetStatus,
    category: '',
    photoUrl: '',
})

const buildPayload = (): PetPayload => ({
    name:      form.name,
    status:    form.status,
    photoUrls: form.photoUrl ? [form.photoUrl] : [],
    category:  form.category ? { name: form.category } : null,
})

const onSubmit = async (): Promise<void> => {
    loading.value = true
    try {
        await addPet(buildPayload())
        router.push('/pets')
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="w-full max-w-lg">
        <AppCard title="Dodaj nowe zwierzę">
            <form class="space-y-4" @submit.prevent="onSubmit">
                <AppInput v-model="form.name" label="Nazwa" placeholder="np. Reksio" :required="true" />

                <AppInput type="select" v-model="form.status" label="Status" :required="true">
                    <option value="available">Available</option>
                    <option value="pending">Pending</option>
                    <option value="sold">Sold</option>
                </AppInput>

                <AppInput v-model="form.category" label="Kategoria" placeholder="np. Dogs, Cats..." />

                <AppInput v-model="form.photoUrl" label="URL zdjęcia" placeholder="https://..." />

                <div class="flex gap-3 pt-2">
                    <AppButton type="submit" variant="primary" :disabled="loading">
                        {{ loading ? 'Dodawanie...' : 'Dodaj' }}
                    </AppButton>
                    <router-link to="/pets">
                        <AppButton variant="ghost">Anuluj</AppButton>
                    </router-link>
                </div>
            </form>
        </AppCard>
    </div>
</template>
