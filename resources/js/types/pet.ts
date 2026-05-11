export interface Pet {
    id: number | null
    name: string
    status: 'available' | 'pending' | 'sold'
    photoUrls: string[]
    tags: Tag[]
    category: Category | null
}

export interface Category {
    id?: number
    name: string
}

export interface Tag {
    id?: number
    name: string
}

export type PetStatus = 'available' | 'pending' | 'sold'

export interface PetPayload {
    name: string
    status: PetStatus
    photoUrls: string[]
    category: Category | null
}
