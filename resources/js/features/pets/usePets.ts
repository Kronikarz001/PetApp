import { toast } from 'vue-sonner'
import type { PetPayload } from '@/types'

const BASE_URL = '/api/pets'

export const usePets = () => {
    async function addPet(payload: PetPayload): Promise<void> {
        return toast.promise(
            fetch(BASE_URL, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body:    JSON.stringify(payload),
            }).then(async (res) => {
                if (!res.ok) {
                    const json = await res.json()
                    throw new Error(json.message ?? 'Błąd podczas dodawania zwierzęcia')
                }
                return res.json()
            }),
            {
                loading: 'Dodawanie...',
                success: 'Zwierzę zostało dodane',
                error:   (err: Error) => err.message,
            },
        ) as Promise<void>
    }

    async function editPet(id: number, payload: Partial<PetPayload>): Promise<void> {
        return toast.promise(
            fetch(`${BASE_URL}/${id}`, {
                method:  'PUT',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body:    JSON.stringify({ ...payload, id }),
            }).then(async (res) => {
                if (!res.ok) {
                    const json = await res.json()
                    throw new Error(json.message ?? 'Błąd podczas zapisywania zmian')
                }
            }),
            {
                loading: 'Zapisywanie...',
                success: 'Zmiany zostały zapisane',
                error:   (err: Error) => err.message,
            },
        ) as Promise<void>
    }

    async function deletePet(id: number): Promise<void> {
        return toast.promise(
            fetch(`${BASE_URL}/${id}`, {
                method:  'DELETE',
                headers: { Accept: 'application/json' },
            }).then(async (res) => {
                if (!res.ok && res.status !== 204) {
                    const json = await res.json()
                    throw new Error(json.message ?? 'Błąd podczas usuwania zwierzęcia')
                }
            }),
            {
                loading: 'Usuwanie...',
                success: 'Zwierzę zostało usunięte',
                error:   (err: Error) => err.message,
            },
        ) as Promise<void>
    }

    return {
        addPet,
        editPet,
        deletePet,
    }
}
