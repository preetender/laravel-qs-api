<?php

namespace App\Base\Support\Api;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Http\Resources\MissingValue;

class ApiResource extends Resource
{
    /**
     * Verificar se existe chave na query de consulta
     *
     * @param $param
     * @param $value
     * @return MissingValue|mixed
     */
    public function whenQueryParam($param, $value)
    {
        list($method, $key) = explode(',', $param);

        // maps
        $query = request()->query();

        $keys = 0;
        $tests = 10;
        do {

            if ($keys === 0 && isset($query[$method]) && array_key_exists($key, $query[$method])) {
                return value($value);
                break;
            }

            $identified = "$method:$keys";

            if (isset($query[$identified]) && array_key_exists($key, $query[$identified])) {
                return value($value);
                break;
            }
            // next
            $keys++;
        } while ($keys <= $tests);

        return new MissingValue;
    }
}
