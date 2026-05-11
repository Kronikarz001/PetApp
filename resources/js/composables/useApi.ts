import { ref } from 'vue'
import type { Ref } from 'vue'
import type { PaginatedResponse } from '@/types'

interface UseAPIReturn<T> {
  data: Ref<T | null>
  pending: Ref<boolean>
  error: Ref<string | null>
  execute: () => Promise<void>
}

export function useAPI<T>(url: string): UseAPIReturn<T> {
  const data    = ref<T | null>(null) as Ref<T | null>
  const pending = ref(false)
  const error   = ref<string | null>(null)

  const execute = async (): Promise<void> => {
    pending.value = true
    error.value   = null

    try {
      const res  = await fetch(url, {
        headers: { Accept: 'application/json' },
      })
      const json = await res.json()

      if (!res.ok) {
        error.value = json.message ?? `Błąd HTTP ${res.status}`
        return
      }

      data.value = json as T
    } catch {
      error.value = 'Nie można połączyć się z API.'
    } finally {
      pending.value = false
    }
  }

  return { data, pending, error, execute }
}

interface UsePaginatedAPIReturn<T> {
  data: Ref<T[]>
  pagination: Ref<Omit<PaginatedResponse<T>, 'data'>>
  pending: Ref<boolean>
  error: Ref<string | null>
  execute: (page?: number) => Promise<void>
}

export function usePaginatedAPI<T>(url: string): UsePaginatedAPIReturn<T> {
  const data       = ref<T[]>([]) as Ref<T[]>
  const pending    = ref(false)
  const error      = ref<string | null>(null)
  const pagination = ref({
    current_page: 1,
    last_page:    1,
    per_page:     15,
    total:        0,
  })

  const execute = async (page: number = 1): Promise<void> => {
    pending.value = true
    error.value   = null

    try {
      const separator = url.includes('?') ? '&' : '?'
      const res       = await fetch(`${url}${separator}page=${page}`, {
        headers: { Accept: 'application/json' },
      })
      const json = await res.json()

      if (!res.ok) {
        error.value = json.message ?? `Błąd HTTP ${res.status}`
        return
      }

      data.value       = json.data as T[]
      pagination.value = {
        current_page: json.current_page,
        last_page:    json.last_page,
        per_page:     json.per_page,
        total:        json.total,
      }
    } catch {
      error.value = 'Nie można połączyć się z API.'
    } finally {
      pending.value = false
    }
  }

  return { data, pagination, pending, error, execute }
}
