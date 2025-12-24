<?php

namespace Gomaa\Base\Base\Repositories;

use Gomaa\Base\Base\Models\BaseAuthModel;
use Gomaa\Base\Base\Models\BaseModel;
use Spatie\QueryBuilder\QueryBuilder;

class BaseApiRepository extends BaseRepository implements RepositoryInterface
{
   /**
     * @var BaseModel|BaseAuthModel
     */
    protected BaseModel|BaseAuthModel $model;

    /**
     * Get all with Base filter.
     *
     * @param array $attributes
     * @return mixed
     */
    public function getAll(array $attributes)
    {
        // Select
        $query = $this->model->select("*");
        $QueryBuilder = QueryBuilder::for(
            $query
        )
            ->allowedFilters($this->model->getAllowedFilters())
            ->allowedFields($this->model::getAllowedFields())
            ->allowedIncludes($this->model::getAllowedIncludes())
            ->allowedSorts($this->model::getAllowedSorts())
            ->defaultSort($this->model::getDefaultSort())
            ->with($this->model::getDefaultIncludedRelations())
            ->with($this->model::getDefaultIncludedRelationsCount());

        return $this->result($attributes, $QueryBuilder, $this->model->getTable());
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return mixed
     */
    public function getById(int $id)
    {
        return $this->model->where('id', $id)->first();
    }

    /**
     * Save new data
     *
     * @param array $data
     * @param bool $createdBy
     * @return mixed
     */
    public function save(array $data, bool $createdBy = false)
    {
        if ($createdBy)
            $this->createdBy($data);
        return $this->model->create($data);
    }

    /**
     * Update by id
     *
     * @param int $id
     * @param array $data
     * @param bool $updatedBy
     * @param bool $restore
     * @return mixed
     */
    public function updateById(int $id, array $data, bool $updatedBy = false, bool $restore = false)
    {
        if ($updatedBy)
            $this->updatedBy($data);

        if ($restore)
            $item = $this->model->withTrashed()->find($id);
        else
            $item = $this->model->find($id);

        if ($item) {
            if ($restore) $item->restore();
            $item->update($data);
        }
        return $item;
    }

    /**
     * Delete by id
     *
     * @param int $id
     * @param bool $deletedBy
     * @return bool
     */
    public function deleteById(int $id, bool $deletedBy = false): bool
    {
        if ($object = $this->model->find($id)) {
            $object->delete();
            // determine if a given Models instance has been soft deleted, use the trashed method:
            if (!$this->model->find($id))
                return true;
        }
        return false;
    }



    public function AES_Encode($plain_text, $key = 'MunjzJan2017')
    {
        return base64_encode(openssl_encrypt($plain_text, 'aes-256-cbc', $key, true, str_repeat(chr(0), 16)));
    }

    public function AES_Decode($base64_text, $key = 'MunjzJan2017')
    {
        return openssl_decrypt(base64_decode($base64_text), 'aes-256-cbc', $key, true, str_repeat(chr(0), 16));
    }
}
