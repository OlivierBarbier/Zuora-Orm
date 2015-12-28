<?php

namespace OlivierBarbier\Zorm;

/*
 * @package OlivierBarbier\Zorm
 */
class UnsupportedCallException extends \Exception
{
}

/**
 */
abstract class Base
{
    /**
     * @var array
     */
    protected $blackList = [];

    /**
     * @var array
     */
    protected $fields = ['Id' => null];

    /**
     * @var \OlivierBarbier\Zorm\Builder
     */
    protected $queryBuilder;

    /**
     * @var array
     */
    protected $supportedCalls = [
        'create', 'query',
        'update', 'delete',
        'subscribe',
    ];

    /**
     * @var array
     */
    protected static $config;

    /**
     */
    public function __construct($config = null)
    {
        if (is_null($config)) {
            self::$config = require __DIR__ . '/../' . 'config/credentials.php';
        }

        $this->queryBuilder = $this->newQueryBuilder();

        $this->boot();
    }

    /**
     */
    protected function boot()
    {
        $xml = simplexml_load_file(static::$config['wsdl']);

        $array = $xml->xpath($this->getXpath());

        foreach ($array as $element) {
            $name = (string) $element['name'];

            if (false !== strpos($name, '__c')) {
                continue;
            }

            if (false !== in_array($name, $this->blackList)) {
                continue;
            }

            $this->fields[$name] = null;
        }
    }

    /**
     * @param string $methpd
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, $this->supportedCalls)) {
            return call_user_func_array(array($this->zuora(), $method), $parameters);
        }

        $namespace = 'OlivierBarbier\Zorm\Zobject\\';

        $name = rtrim(ucfirst($method), 's');

        $className = $namespace.$name;

        if (!class_exists($className)) {
            throw new UnsupportedCallException();
        }

        $columns = ['*'];
        $matchAll = true;
        $returnBuilder = true;

        if ($name != ucfirst($method)) {
            return $this->findSons($name, $className, $columns, $matchAll, $returnBuilder);
        }

        return $this->findFather($name, $className, $columns, $matchAll, $returnBuilder);
    }

    /**
     * @return \Menus1001\Factories\ZuoraApiFactory | \Zuora_API
     */
    public function zuora()
    {
        return $this->queryBuilder->zuora();
    }

    /**
     * @param string $id
     * @param array  $columns
     * @param bool   $matchAll
     *
     * @return \OlivierBarbier\Zorm\Base | null
     */
    public static function find($id, $columns = ['*'], $matchAll = false)
    {
        $instance = new static;

        return $instance->newQueryBuilder()->where('id', '=', $id)->get($columns, $matchAll)->first();
    }

    /**
     * @param string $fatherName
     * @param string $fatherClassName
     * @param array  $columns
     * @param bool   $matchAll
     * @param bool   $returnBuilder
     *
     * @return \OlivierBarbier\Zorm\Base | null
     */
    protected function findFather($fatherName, $fatherClassName, $columns, $matchAll, $returnBuilder)
    {
        $father = new $fatherClassName();

        if ($returnBuilder) {
            return $father->newQueryBuilder()->where('id', '=', $this->{$fatherName.'Id'});
        }

        return $father->find($this->{$fatherName.'Id'}, $columns, $matchAll);
    }

    /**
     * @param string $sonName
     * @param string $sonClassName
     * @param array  $columns
     * @param bool   $matchAll
     * @param bool   $returnBuilder
     *
     * @return \Illuminate\Support\Collection
     */
    protected function findSons($sonName, $sonClassName, $columns, $matchAll, $returnBuilder)
    {
        $son = new $sonClassName();

        $parentName = $this->getClassNameWithoutNamespace();

        $builder = $son->newQueryBuilder()
            ->where("{$parentName}Id", '=', $this->Id)
        ;

        if ($returnBuilder) {
            return $builder;
        }

        return $builder->get($columns, $matchAll);
    }

    /**
     * @param array $columns
     *
     * @return \OlivierBarbier\Zorm\Base
     */
    public function refresh($columns = ['*'])
    {
        return $this->fill($this->find($this->Id, $columns)->castToZuora());
    }

    /**
     * @param array $columns
     *
     * @return \Illuminate\Support\Collection
     */
    public function all($columns = ['*'])
    {
        return $this->newQueryBuilder()->get($columns, true);
    }

    /**
     * @param array $columns
     *
     * @return \OlivierBarbier\Zorm\Base | null
     */
    public function first($columns = ['*'])
    {
        return $this->newQueryBuilder()->get($columns, false)->first();
    }

    /**
     * @param Zuora_Object $zaccount
     *
     * @return \OlivierBarbier\Zorm\Base
     */
    public function fill($zaccount)
    {
        foreach ($this->fields as $field => &$fieldValue) {
            if (isset($zaccount->$field)) {
                $fieldValue = $zaccount->$field;
            }
        }

        return $this;
    }

    /**
     * @param string key
     *
     * @return mixed
     */
    public function __get($key)
    {
        $namespace = 'OlivierBarbier\Zorm\Zobject\\';

        $name = rtrim(ucfirst($key), 's');

        $className = $namespace.$name;

        if (!class_exists($className)) {
            return $this->fields[$key];
        }

        if ($name != ucfirst($key)) {
            return $this->__call($key, [])->get();
        }

        return $this->__call($key, [])->get()->first();
    }

    /**
     * @param string key
     * @param string value
     */
    public function __set($key, $value)
    {
        $this->fields[$key] = $value;
    }

    public static function create($columns)
    {
        $instance = new static;

        foreach($columns as $k => $v)
        {
            $instance->$k = $v;
        }

        $create = $instance->zuora()->create([$instance->castToZuora()]);

        $instance->throwExceptionOnError($create);

        return $instance->find($create->result->Id);
    }

    public function save($columns = ['*'])
    {
        if ($columns[0] != '*') {
            $columns = array_merge($columns, ['Id']);
        }

        $zObject = $this->castToZuora($columns);

        $update = $this->zuora()->update([$zObject]);

        $this->throwExceptionOnError($update);

        return $update;
    }

    public function delete()
    {
        $delete = $this->zuora()->delete(
            $this->getClassNameWithoutNamespace(),
            [$this->Id]
        );

        $this->throwExceptionOnError($delete);

        return $delete;
    }

    protected function throwExceptionOnError($response)
    {
        if (!$response->result->Success) {
            $errors = isset($response->result->Errors) ? $response->result->Errors : $response->result->errors;

            throw new \Exception(
                $errors->Message
            );
        }
    }

    /**
     * @return array
     */
    public function fields()
    {
        return $this->fields;
    }

    public function doQuery($zoql)
    {
        return $this->queryBuilder->doQuery($zoql);
    }

    /**
     * @param string $attribute
     * @param string $operator
     * @param string $value
     *
     * @return \OlivierBarbier\Zorm\Builder
     */
    public static function where($attribute, $operator, $value)
    {
        $instance = new static;

        return $instance->queryBuilder->where($attribute, $operator, $value);
    }

    /**
     * @param array $columns
     *
     * @return \Illuminate\Support\Collection
     */
    public function get($columns = ['*'])
    {
        return $this->queryBuilder->get($columns);
    }

    /**
     * @param array $columns
     *
     * @return string
     */
    public function toZoql($columns = ['*'])
    {
        return $this->queryBuilder->toZoql($columns);
    }

    /**
     * @return \OlivierBarbier\Zorm\Builder
     */
    public function newQueryBuilder()
    {
        $builder = new Builder(static::$config);

        $builder->setModel($this);

        return $builder;
    }

    /**
     * @param array $columns
     *
     * @return string
     */
    protected function selectQuery($columns = ['*'])
    {
        return $this->queryBuilder->selectQuery($columns);
    }

    /**
     * @return array
     */
    public function queryFields()
    {
        return array_diff(array_keys($this->fields), $this->blackList);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->Id;
    }

    /**
     * @return string
     */
    public function getClassNameWithoutNamespace()
    {
        $explode = explode('\\', get_class($this));

        return end($explode);
    }

    /**
     * @return string
     */
    protected function getXpath()
    {
        return '//xs:complexType[@name="'.
            $this->getClassNameWithoutNamespace().
            '"]/xs:complexContent/xs:extension/xs:sequence/xs:element/@name';
    }

    /**
     * @param array $columns
     *
     * @return \Zuora_Object
     */
    public function castToZuora($columns = ['*'])
    {
        $class = 'Zuora_'.$this->getClassNameWithoutNamespace();

        $zaccount = new $class();

        foreach ($this->fields as $field => $fieldValue) {
            if ($columns[0] != '*') {
                if (!in_array($field, $columns)) {
                    continue;
                }
            }

            $zaccount->$field = $fieldValue;
        }

        return $zaccount;
    }
}
