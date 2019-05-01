<?php

namespace Preetender\QueryString;

use Illuminate\Http\Request;
use ReflectionClass;

final class Interceptor
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var
     */
    protected $eloquent;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Mapear query string.
     *
     * @param $model
     * @param $newInstance
     * @return mixed
     */
    public function watch($model, $newInstance = false)
    {
        $this->eloquent = $newInstance ? (new ReflectionClass($model))->newInstance() : $model;

        $parameters = $this->request->all();

        if (sizeof($parameters) > 0) {
            $params = [];

            //
            // Gerar atributos enumerados
            //
            for ($i = 1; $i <= 10; $i++) {
                $params[] = ":$i";
            }

            foreach ($parameters as $method => $arguments) {
                $method = str_replace($params, '', $method);

                if (method_exists($this, $method)) {
                    $arguments = is_string($arguments) ? [$arguments] : $this->extractArguments($arguments);
                    call_user_func_array([$this, $method], $arguments);
                }
            }
        }

        return $this->request->has('paginate') ? $this->eloquent->paginate($this->request->paginate) : $this->eloquent->get();
    }
    /**
     * @param $value
     */
    private function select($value)
    {
        $fields = explode(',', $value);
        $this->eloquent = $this->eloquent->select(...$fields);
    }

    /**
     * values: value,operator
     *
     * @param $column
     * @param $values
     */
    private function whereTime($column, $values)
    {
        $params = $this->prepareConditionals($values);

        $this->eloquent = $this->eloquent->whereTime($column, ...$params);
    }

    /**
     * @param $column
     * @param $values
     */
    private function whereDate($column, $values)
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->whereDate($column, ...$params);
    }
    /**
     * @param $column
     * @param $values
     */
    private function whereYear($column, $values)
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->whereYear($column, ...$params);
    }

    /**
     * @param $column
     * @param $values
     */
    private function whereMonth($column, $values)
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->whereMonth($column, ...$params);
    }

    /**
     * @param $column
     * @param $values
     */
    private function whereDay($column, $values)
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->whereDay($column, ...$params);
    }

    /**
     * @param $column
     * @param $values
     */
    private function whereIn($column, $values)
    {
        $this->eloquent = $this->eloquent->whereIn($column, $values);
    }

    /**
     * @param $column
     * @param $values
     */
    private function whereNotIn($column, $values)
    {
        $this->eloquent = $this->eloquent->whereNotIn($column, $values);
    }

    /**
     * @param $column
     * @param $values
     */
    private function whereBetween($column, $values)
    {
        $this->eloquent = $this->eloquent->whereBetween($column, $values);
    }

    /**
     * @param $column
     * @param $values
     */
    private function whereNotBetween($column, $values)
    {
        $this->eloquent = $this->eloquent->whereNotBetween($column, $values);
    }

    /**
     * @param $column
     * @param $values
     */
    private function where($column, $values)
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->where($column, ...$params);
    }

    /**
     * @param $column
     * @param $values
     */
    private function orWhere($column, $values)
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->orWhere($column, ...$params);
    }

    /**
     * @param $value
     */
    private function whereNull($value)
    {
        $this->eloquent = $this->eloquent->whereNull($value);
    }

    /**
     * @param $value
     */
    private function whereNotNull($value)
    {
        $this->eloquent = $this->eloquent->whereNotNull($value);
    }

    /**
     * @param $value
     */
    private function limit($value)
    {
        $this->eloquent = $this->eloquent->limit($value);
    }

    /**
     * @param $value
     */
    private function offset($value)
    {
        $this->eloquent = $this->eloquent->offset($value);
    }

    /**
     * @param $column
     * @param $values
     */
    private function orderBy($column, $values)
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->orderBy($column, $values[0]);
    }

    /**
     * @param $action
     * @param null $value
     * @return void
     */
    private function scope($action, $value = null): void
    {
        $method = str_start(studly_case($action), 'scope');
        dd($method);
        if (method_exists($this->eloquent, $method)) {
            $this->eloquent = $this->eloquent->{$action}(isset($value) && $value[0] !== null ? $value[0] : true);
        }
    }

    /**
     * @param $relation
     * @param $values
     */
    private function with($relation, $values)
    {
        $keys = implode(',', $values);
        $this->eloquent = $this->eloquent->with("{$relation}:$keys");
    }

    /**
     * Executar ações na eloquente
     *
     * @return void
     */
    private function bind(string $call): void
    {
        $this->eloquent = $this->eloquent->{$call}();
    }

    /**
     * Extrair parametros e montar query.
     *
     * @param $parameters
     * @return array
     */
    private function prepareConditionals($parameters)
    {
        $params = [];
        switch (count($parameters)) {
            case 1:
                array_push($params, '=', $parameters[0]);
                break;
            case 2:
                array_push($params, $parameters[0], $parameters[1]);
                break;
        }
        return $params;
    }

    /**
     * Extrair argumentos do parametro.
     *
     * @param $arguments
     * @return array
     */
    private function extractArguments($arguments): array
    {
        $key = array_keys($arguments)[0];
        $values = array_values($arguments);
        if (str_contains($values[0], ',')) {
            $values = explode(',', str_replace(['(', ')'], '', $values[0]));
        }

        return [
            $key,
            $values,
        ];
    }
}
