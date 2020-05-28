<?php

namespace WebDevEtc\BlogEtc\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use WebDevEtc\BlogEtc\Exceptions\UploadedPhotoNotFoundException;
use WebDevEtc\BlogEtc\Models\UploadedPhoto;

class UploadedPhotosRepository
{
    /**
     * @var UploadedPhoto
     */
    private $model;

    /**
     * Constructor.
     */
    public function __construct(UploadedPhoto $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new Uploaded Photo row in the database.
     */
    public function create(array $attributes): UploadedPhoto
    {
        return $this->query()->create($attributes);
    }

    /**
     * Return new instance of the Query Builder for this model.
     */
    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Delete a uploaded photo from the database.
     */
    public function delete(int $uploadedPhotoID): ?bool
    {
        $uploadedPhoto = $this->find($uploadedPhotoID);

        return $uploadedPhoto->delete();
    }

    /**
     * Find a blog etc uploaded photo by ID.
     *
     * If cannot find, throw exception.
     */
    public function find(int $uploadedPhotoID): UploadedPhoto
    {
        try {
            return $this->query()->findOrFail($uploadedPhotoID);
        } catch (ModelNotFoundException $e) {
            throw new UploadedPhotoNotFoundException('Unable to find Uploaded Photo with ID: '.$uploadedPhotoID);
        }
    }
}
