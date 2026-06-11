<?php

namespace App\DTOs\Image;

use App\Http\Requests\Image\ImageIndexRequest;

final readonly class ImageFilterDTO
{
    public function __construct(
        public int $perPage = 20,
        public string $sortBy = 'created_at',
        public string $sortDirection = 'desc',
        public ?string $originalName = null,
        public ?string $mimeType = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {}

    public static function fromRequest(ImageIndexRequest $request): self
    {
        return new self(
            perPage: (int) $request->validated('per_page', 20),
            sortBy: $request->validated('sort_by', 'created_at'),
            sortDirection: $request->validated('sort_direction', 'desc'),
            originalName: $request->validated('original_name'),
            mimeType: $request->validated('mime_type'),
            dateFrom: $request->validated('date_from'),
            dateTo: $request->validated('date_to'),
        );
    }
}
