<?php

namespace App\Contracts;

use App\DTOs\Image\ImageFilterDTO;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ImageServiceInterface
{
    public function upload(UploadedFile $uploadedFile, User $user): Image;

    public function listForUser(User $user, ImageFilterDTO $filters): LengthAwarePaginator;

    public function delete(Image $image): void;
}
