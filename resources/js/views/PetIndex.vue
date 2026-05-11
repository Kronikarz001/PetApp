<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { usePaginatedAPI } from '@/composables/useAPI'
import { usePets } from '@/features/pets'
import type { Pet, PetStatus } from '@/types'
import AppBadge from '@/components/AppBadge.vue'
import AppAlert from '@/components/AppAlert.vue'
import AppInput from '@/components/AppInput.vue'

const status    = ref<PetStatus>('available')
const { deletePet } = usePets()
const { data: pets, pagination, pending, error, execute } = usePaginatedAPI<Pet>(
    `/api/pets?status=${status.value}`,
)

const fetchPets = async (page: number = 1): Promise<void> => {
    await execute(page)
}

const onStatusChange = async (): Promise<void> => {
    await fetchPets(1)
}

const onDelete = async (id: number, name: string): Promise<void> => {
    if (!confirm(`Usunąć ${name}?`)) return
    await deletePet(id)
    await fetchPets()
}

onMounted(() => fetchPets())
</script>

<template>
    <div class="w-full max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-white">Lista zwierząt</h1>
            <AppInput type="select" v-model="status" @update:modelValue="onStatusChange">
                <option value="available">Available</option>
                <option value="pending">Pending</option>
                <option value="sold">Sold</option>
            </AppInput>
        </div>

        <AppAlert :message="error ?? ''" type="error" class="mb-4" />

        <div v-if="pending" class="flex justify-center py-12">
            <div class="w-10 h-10 border-4 border-white border-t-transparent rounded-full animate-spin" />
        </div>

        <div v-else-if="pets.length" class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-green-700 text-white">
                <tr>
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">Nazwa</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Kategoria</th>
                    <th class="px-4 py-3 text-left">Akcje</th>
                </tr>
                </thead>
                <tbody>
                <tr
                    v-for="pet in pets"
                    :key="pet.id ?? 0"
                    class="border-t border-green-50 hover:bg-green-50 transition"
                >
                    <td class="px-4 py-3 text-gray-500">{{ pet.id }}</td>
                    <td class="px-4 py-3 font-medium text-green-900">{{ pet.name }}</td>
                    <td class="px-4 py-3"><AppBadge :status="pet.status" /></td>
                    <td class="px-4 py-3 text-gray-600">{{ pet.category?.name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-3">
                            <router-link :to="`/pets/${pet.id}`" class="text-green-600 hover:underline font-medium text-xs">
                                Szczegóły
                            </router-link>
                            <router-link :to="`/pets/${pet.id}/edit`" class="text-yellow-600 hover:underline font-medium text-xs">
                                Edytuj
                            </router-link>
                            <button
                                class="text-red-500 hover:underline font-medium text-xs"
                                @click="onDelete(pet.id!, pet.name)"
                            >
                                Usuń
                            </button>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>

            <div v-if="pagination.last_page > 1" class="flex justify-center gap-2 p-4 border-t border-green-50">
                <button
                    v-for="page in pagination.last_page"
                    :key="page"
                    :class="[
            'w-8 h-8 rounded-full text-sm font-medium transition',
            page === pagination.current_page
              ? 'bg-green-700 text-white'
              : 'bg-green-100 text-green-700 hover:bg-green-200',
          ]"
                    @click="fetchPets(page)"
                >
                    {{ page }}
                </button>
            </div>
        </div>

        <div v-else class="text-center text-white/70 py-12">
            Brak zwierząt ze statusem <strong>{{ status }}</strong>.
        </div>
    </div>
</template>
