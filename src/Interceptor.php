<?php

namespace Preetender\Query;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Preetender\Query\Concerns\Map;
use ReflectionClass;

final class Interceptor
{
    use Map;

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
     * Obtem instancia da requisição atual.
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
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

            // Gerar atributos enumerados
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
     * @return void
     */
    private function select($value): void
    {
        $fields = explode(',', $value);
        $this->eloquent = $this->eloquent->select(...$fields);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function where($column, $values): void
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->where($column, ...$params);
    }

    /**
     * @param $column
     * @return void
     */
    private function whereNull($column): void
    {
        $this->eloquent = $this->eloquent->whereNull($column);
    }

    /**
     * @param $column
     * @return void
     */
    private function whereNotNull($column): void
    {
        $this->eloquent = $this->eloquent->whereNotNull($column);
    }

    /**
     * values: value,operator
     *
     * @param $column
     * @param $values
     * @return void
     */
    private function whereTime($column, $values): void
    {
        $params = $this->prepareConditionals($values);

        $this->eloquent = $this->eloquent->whereTime($column, ...$params);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function whereDate($column, $values): void
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->whereDate($column, ...$params);
    }
    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function whereYear($column, $values): void
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->whereYear($column, ...$params);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function whereMonth($column, $values): void
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->whereMonth($column, ...$params);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function whereDay($column, $values): void
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->whereDay($column, ...$params);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function whereIn($column, $values): void
    {
        $this->eloquent = $this->eloquent->whereIn($column, $values);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function whereNotIn($column, $values): void
    {
        $this->eloquent = $this->eloquent->whereNotIn($column, $values);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function whereBetween($column, $values): void
    {
        $this->eloquent = $this->eloquent->whereBetween($column, $values);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function whereNotBetween($column, $values): void
    {
        $this->eloquent = $this->eloquent->whereNotBetween($column, $values);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function orWhere($column, $values): void
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->orWhere($column, ...$params);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function orWhereIn($column, $values): void
    {
        $this->eloquent = $this->eloquent->orWhereIn($column, $values);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function orWhereNotIn($column, $values): void
    {
        $this->eloquent = $this->eloquent->orWhereNotIn($column, $values);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function orWhereBetween($column, $values): void
    {
        $this->eloquent = $this->eloquent->orWhereBetween($column, $values);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function orWhereNotBetween($column, $values): void
    {
        $this->eloquent = $this->eloquent->orWhereNotBetween($column, $values);
    }
    /**
     * @param $column
     * @return void
     */
    private function orWhereNull($column): void
    {
        $this->eloquent = $this->eloquent->orWhereNull($column);
    }

    /**
     * @param $column
     * @return void
     */
    private function orWhereNotNull($column): void
    {
        $this->eloquent = $this->eloquent->orWhereNotNull($column);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function having($column, $values): void
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->having($column, ...$params);
    }

    /**
     * @param $value
     * @return void
     */
    private function limit($value): void
    {
        $this->eloquent = $this->eloquent->limit($value);
    }

    /**
     * @param $value
     * @return void
     */
    private function offset($value): void
    {
        $this->eloquent = $this->eloquent->offset($value);
    }

    /**
     * alias offset
     *
     * @param $value
     * @return void
     */
    private function skip($value): void
    {
        $this->eloquent = $this->eloquent->skip($value);
    }

    /**
     * alias offset
     *
     * @param $value
     * @return void
     */
    private function take($value): void
    {
        $this->eloquent = $this->eloquent->take($value);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function orderBy($column, $values): void
    {
        $params = $this->prepareConditionals($values);
        $this->eloquent = $this->eloquent->orderBy($column, $values[0]);
    }

    /**
     * @param $column
     * @param $values
     * @return void
     */
    private function groupBy($columns): void
    {
        $this->eloquent = $this->eloquent->groupBy($columns);
    }

    /**
     * Raw expressions
     *
     * @return void
     */
    private function selectRaw(): void
    {
        $expression = $this->prepareRaw(func_get_arg(0));
        $values = func_num_args() > 1 ? func_get_arg(1) ?? [] : [];

        if (func_num_args() === 1) {
            $this->eloquent = $this->eloquent->selectRaw($expression);
        } else {
            // selectRaw[expression]=variables
            // selectRaw[sum(?) as soma]=total
            $this->eloquent = $this->eloquent->selectRaw($expression, $values);
        }
    }

    /**
     * Raw expressions
     *
     * @return void
     */
    private function havingRaw(): void
    {
        $expression = $this->prepareRaw(func_get_arg(0));
        $values = func_num_args() > 1 ? func_get_arg(1) ?? [] : [];

        if (func_num_args() === 1) {
            $this->eloquent = $this->eloquent->havingRaw($expression);
        } else {
            // havingRaw[expression]=variables
            // havingRaw[id > ?]=10
            // havingRaw[count(id) > ?]=10
            // havingRaw[sum(id) > ?]=10
            $this->eloquent = $this->eloquent->havingRaw($expression, $values);
        }
    }

    /**
     * Raw expressions
     *
     * @return void
     */
    private function orderByRaw(): void
    {
        $expression = $this->prepareRaw(func_get_arg(0));
        $this->eloquent = $this->eloquent->orderByRaw($expression);
    }

    /**
     * Raw expressions
     *
     * @return void
     */
    private function whereRaw(): void
    {
        $expression = $this->prepareRaw(func_get_arg(0));
        $values = func_num_args() > 1 ? func_get_arg(1) ?? [] : [];

        if (func_num_args() === 1) {
            $this->eloquent = $this->eloquent->whereRaw($expression);
        } else {
            // whereRaw[expression]=variables
            // whereRaw[id > IF(...,?,..)]=10
            $this->eloquent = $this->eloquent->whereRaw($expression, $values);
        }
    }

    /**
     * @param $action
     * @param null $value
     * @return void
     */
    private function scope($action, $value = null): void
    {
        $method = Str::start(Str::studly_case($action), 'scope');

        if (method_exists($this->eloquent, $method)) {
            $this->eloquent = $this->eloquent->{$action}(isset($value) && $value[0] !== null ? $value[0] : true);
        }
    }

    /**
     * @param $relation
     * @param $values
     * @return void
     */
    private function with($relation, $values): void
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
}
