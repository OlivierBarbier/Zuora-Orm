<?php

namespace OlivierBarbier\Zorm;

use Illuminate\Support\Collection;

/**
 */
class Builder
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var \OlivierBarbier\Zorm\Base
     */
    protected $model;

    /**
     * @var array
     */
    protected $wheres = [];

    /**
     * @var \Zuora_API
     */
    protected $zuora;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param \OlivierBarbier\Zorm\Base
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @param array $columns
     *
     * @return string
     */
    public function selectQuery($columns = ['*'])
    {
        $fields = $this->model->queryFields();

        if ($columns[0] != '*') {
            $fields = array_unique(array_merge($columns, ['Id']));
        }

        return 'select '.implode(', ', $fields)
            .' from '
            .$this->model->getClassNameWithoutNamespace().' ';
    }

    /**
     * @return string
     */
    public function whereQuery()
    {
        return implode(' AND ', $this->wheres);
    }

    /**
     * @param string
     * @param string
     * @param string
     *
     * @return \OlivierBarbier\Zorm\Base
     */
    public function where($attribute, $operator, $value)
    {
        $where = $attribute.$operator;

        if ($value == 'true' || $value == 'false') {
            $where .= "$value";
        } else {
            $where .= "'$value'";
        }

        $this->wheres[] = $where;

        return $this;
    }

    /**
     * @param array columns
     * @param bool $matchAll
     *
     * @return \Illuminate\Support\Collection
     */
    public function get($columns = ['*'], $matchAll = false)
    {
        $zoql = $this->toZoql($columns);

        $this->wheres = [];

        $result = $this->doQuery($zoql);

        $all = [];

        do {
            foreach ($result->records as $record) {
                $all[] = $record;
            }
        } while ($matchAll && !$result->done && ($result = $this->doQueryMore($result)));

        $cn = get_class($this->model);

        return $this->fillMany($all, new $cn());
    }

    /**
     * @param array $columns
     *
     * @return string
     */
    public function toZoql($columns = ['*'])
    {
        $select = $this->selectQuery($columns);
        $where = $this->whereQuery();

        if (empty($where)) {
            return $this->selectQuery($columns);
        }

        return $this->selectQuery($columns).' WHERE '.$this->whereQuery();
    }

    /**
     * @param string $zoql
     *
     * @return stdClass
     */
    public function doQuery($zoql)
    {
        $result = $this->zuora()->query($zoql)->result;

        return $this->fixRecordsType($result);
    }

    /**
     * @param stdClass $result
     *
     * @return stdClass
     */
    public function doQueryMore($result)
    {
        $result = $this->zuora()->queryMore($result->queryLocator)->result;

        return $this->fixRecordsType($result);
    }

    /**
     * @param stdClass $result
     *
     * @return stdClass
     */
    protected function fixRecordsType($result)
    {
        if (isset($result->records) && !is_array($result->records)) {
            $result->records = [$result->records];
        } elseif (!isset($result->records)) {
            $result->records = [];
        }

        return $result;
    }

    /**
     * @return \Zuora_API
     */
    public function zuora()
    {
        if (is_null($this->zuora)) {
            $instance = \Zuora_API::getInstance((object) [
                'wsdl'      => $this->config['wsdl'],
            ]);

            $instance->setLocation($this->config['endpoint']);

            if (!isset($instance->signedIn) || !$instance->signedIn) {
                $instance->signedIn = $instance->login($this->config['user'], $this->config['password']);
            }

            $this->zuora = $instance;
        }

        return $this->zuora;
    }

    /**
     * @param array                    $records
     * @param \Menus1001\Zupquent\Base $repo
     *
     * @return \Illuminate\Support\Collection
     */
    public function fillMany($records, $repo)
    {
        $relations = [];

        foreach ($records as $record) {
            $relations[] = clone $repo->fill($record);
        }

        return new Collection($relations);
    }
}
