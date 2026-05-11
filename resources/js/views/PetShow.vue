<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAPI } from '@/composables/useAPI'
import { usePets } from '@/features/pets'
import type { Pet } from '@/types'
import AppCard   from '@/components/AppCard.vue'
import AppBadge  from '@/components/AppBadge.vue'
import AppAlert  from '@/components/AppAlert.vue'
import AppButton from '@/components/AppButton.vue'

const route  = useRoute()
const router = useRouter()
const id     = route.params.id as string

const { data: pet, pending, error, execute } = useAPI<Pet>(`/api/pets/${id}`)
const { deletePet } = usePets()

const validPhotoUrls = computed(() =>
  pet.value?.photoUrls?.filter(url =>
    url && url.startsWith('http') && /\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i.test(url)
  ) ?? []
)

const onDelete = async (): Promise<void> => {
  if (!confirm(`Usunąć ${pet.value?.name}?`)) return
  await deletePet(Number(id))
  router.push('/pets')
}

onMounted(() => execute())
</script>

<template>
  <div class="w-full max-w-lg">
    <div v-if="pending" class="flex justify-center py-12">
      <div class="w-10 h-10 border-4 border-white border-t-transparent rounded-full animate-spin" />
    </div>

    <AppAlert v-else-if="error" :message="error" type="error" />

    <AppCard v-else-if="pet" :title="pet.name">
      <dl class="space-y-4 text-sm">
        <div class="flex justify-between">
          <dt class="text-gray-500">ID</dt>
          <dd class="font-medium text-green-900">{{ pet.id }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-gray-500">Status</dt>
          <dd><AppBadge :status="pet.status" /></dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-gray-500">Kategoria</dt>
          <dd class="font-medium text-green-900">{{ pet.category?.name ?? '—' }}</dd>
        </div>
        <div v-if="validPhotoUrls.length" class="flex flex-col gap-2">
          <dt class="text-gray-500">Zdjęcia</dt>
          <dd v-for="url in validPhotoUrls" :key="url">
            <img :src="url" :alt="pet.name" class="rounded-lg max-h-48 object-cover" />
          </dd>
        </div>
      </dl>

      <div class="flex gap-3 mt-8">
        <router-link :to="`/pets/${pet.id}/edit`">
          <AppButton variant="primary">Edytuj</AppButton>
        </router-link>
        <AppButton variant="danger" @click="onDelete">Usuń</AppButton>
        <router-link to="/pets">
          <AppButton variant="ghost">Wróć</AppButton>
        </router-link>
      </div>
    </AppCard>
  </div>
</template>
