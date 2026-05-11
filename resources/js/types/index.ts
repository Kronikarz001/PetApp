import { createRouter, createWebHistory } from 'vue-router'
import PetIndex  from '@/views/PetIndex.vue'
import PetShow   from '@/views/PetShow.vue'
import PetCreate from '@/views/PetCreate.vue'
import PetEdit   from '@/views/PetEdit.vue'

const routes = [
    { path: '/',              redirect: '/pets' },
    { path: '/pets',          name: 'pets.index',  component: PetIndex  },
    { path: '/pets/create',   name: 'pets.create', component: PetCreate },
    { path: '/pets/:id',      name: 'pets.show',   component: PetShow   },
    { path: '/pets/:id/edit', name: 'pets.edit',   component: PetEdit   },
]

export default createRouter({
    history: createWebHistory(),
    routes,
})
