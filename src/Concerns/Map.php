<?php

namespace Preetender\Query\Concerns;

use Illuminate\Support\Str;
use InvalidArgumentException;

trait Map
{

    /**
     * Extrair parametros e montar query.
     *
     * @param $parameters
     * @return array
     */
    protected function prepareConditionals($parameters): array
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
    protected function extractArguments($arguments): array
    {
        $key = array_keys($arguments)[0];
        $values = array_values($arguments);

        if(!in_array($key, $this->eloquent->getFillable())) {
            throw new InvalidArgumentException("key $key not accept");
        }
        
        if (Str::contains($values[0], ',')) {
            $values = explode(',', str_replace(['[', ']'], '', $values[0]));
        }

        return [
            $key,
            $values,
        ];
    }
    /**
     * Remover caracteres inválidos para montar a sintaxe.
     *
     * @return string
     */
    protected function prepareRaw($expression): string
    {
        return str_replace(['[', ']', '__'], ['', '', ' '], $expression);
    }

}
