<?php

namespace Hageman\Wics\ServiceLayer;

use Exception;
use Hageman\Wics\ServiceLayer\Collections\ModelCollection;
use Hageman\Wics\ServiceLayer\Models\ServiceLayerModel;
use Illuminate\Support\Arr;

class HttpQueryBuilder
{
    /**
     * @var ServiceLayerModel $model
     */
    protected ServiceLayerModel $model;

    /**
     * @var array $queryParameters
     */
    protected array $queryParameters = [];

    /**
     * Returns data resolved from the ServiceLayer.
     *
     * @param int $page
     * @param int $pageSize
     *
     * @return array|mixed
     */
    protected function retrievePaginated(int $page = 1, int $pageSize = 10): mixed
    {
        $queryParameters = implode('&', $this->queryParameters + ["page=$page","pageSize=$pageSize"]);

        $response = ServiceLayer::get($this->model->getEndpoint() . '?' . $queryParameters);

        return $response->success ? $response->data : [];
    }

    /**
     * Constructs a new instance.
     *
     * @param class-string<ServiceLayerModel> $modelName
     */
    public function __construct(
        string $modelName,
    ) {
        $this->model = new $modelName();
    }

    /**
     * Alias for paginate() -> retrieve all collections.
     * Returns a model collection resolved from the ServiceLayer.
     *
     * @return ModelCollection
     */
    public function all(): ModelCollection
    {
        return $this->paginate(1, -1);
    }

    /**
     * Alias for paginate() -> retrieve array of collections.
     * Returns a model collection resolved from the ServiceLayer.
     *
     * @return ModelCollection
     */
    public function list(): ModelCollection
    {
        return $this->paginate();
    }

    /**
     * Alias for paginate() -> retrieve first collection.
     * Returns a model resolved from the ServiceLayer.
     *
     * @return null|ServiceLayerModel
     */
    public function first(): ?ServiceLayerModel
    {
        return $this->paginate(1, 1)?->first();
    }

    /**
     * Applies a query filter to- and returns current instance.
     *
     * @param string $field
     * @param string|null $operator
     * @param mixed|null $value
     *
     * @return HttpQueryBuilder
     *
     * @throws Exception
     */
    public function where(string $field, string|null $operator = null, mixed $value = null): HttpQueryBuilder
    {
        if (empty($field)) throw new Exception("Field parameter value can't be empty.");;

        if (is_null($operator)) throw new Exception("Missing second argument as value to filter against.");

        if (empty($value)) {
            $value = $operator;
            $operator = null;
        }

        $operator = match ($operator) {
            '=', 'eq' => '[eq]',
            '!=', 'neq' => '[neq]',
            'gt', '>' => '[gt]',
            'lt', '<' => '[lt]',
            'gte', '>=' => '[gte]',
            'lte', '<=' => '[lte]',
            default => '',
        };

        $this->queryParameters[] = "$field$operator=$value";

        return $this;
    }

    /**
     * Returns a model collection resolved from the ServiceLayer.
     *
     * @param int $page
     * @param int $pageSize
     *
     * @return ModelCollection
     */
    public function paginate(int $page = 1, int $pageSize = 10): ModelCollection
    {
        $modelCollection = new ModelCollection();

        $retrieveAll = $pageSize < 1;

        if($retrieveAll) $pageSize = 100;

        $page--;

        while(++$page) {
            $collection = $this->retrievePaginated($page, $pageSize);

            foreach($collection as $attributes) {
                $model = new $this->model($attributes);

                $model->newlyCreated = false;

                $modelCollection->add($model);
            }

            if(count($collection) < $pageSize || !$retrieveAll) break;
        }

        return $modelCollection;
    }

    /**
     * Resolves model collection from the ServiceLayer and updates each model with the provided attributes to the ServiceLayer.
     * Returns the affected amount of models.
     *
     * @param array $attributes
     *
     * @return int
     */
    public function update(array $attributes): int
    {
        $affected = 0;

        if(!method_exists($this->model, 'save')) return $affected;

        foreach($this->all() as $model) {
            foreach (Arr::dot($attributes) as $attribute => $value) {
                $model->$attribute = $value;
            }

            if($model->save()) $affected++;
        }

        return $affected;
    }
}