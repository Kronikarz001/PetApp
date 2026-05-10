<?php

namespace App\Dtos;

/**
 * Summary of PetDto
 */
readonly class PetDto
{
    /**
     * @param int|null $id
     * @param string $name
     * @param string $status
     * @param array $photoUrls
     * @param array $tags
     * @param array|null $category
     */
    public function __construct(
        public ?int $id,
        public string $name,
        public string $status,
        public array $photoUrls = [],
        public array $tags = [],
        public ?array $category = null,
    ) {}

    /**
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            status: $data['status'],
            photoUrls: $data['photoUrls'] ?? [],
            tags: $data['tags'] ?? [],
            category: $data['category'] ?? null,
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'photoUrls' => $this->photoUrls,
            'tags' => $this->tags,
            'category' => $this->category,
        ];
    }
}
