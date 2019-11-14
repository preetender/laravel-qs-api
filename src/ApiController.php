<?php

namespace Preetender\QueryString;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var Model|string
     */
    protected $model;

    /**
     * Iniciar uma nova instancia
     *
     * @var bool
     */
    protected $modelNewInstance = false;

    /**
     * @var string
     */
    protected $resource;

    /**
     * Listagem de registros
     *
     * @param Interceptor $intercptor
     * @return mixed
     */
    public function index(Interceptor $interceptor)
    {
        if (method_exists($this, 'beforeIndex')) {
            // Injetar antes do carregamento
            $this->beforeIndex($interceptor->getRequest());
        }

        abort_if(!$this->checkAttributeExists('model'), 500, 'Model não localizada.');

        $resource = $interceptor->watch(
            $this->model,
            $this->modelNewInstance
        );

        if ($this->checkAttributeExists('resource')) return $this->resource::collection($resource);

        return $resource;
    }
    /**
     * Visualizar registro.
     *
     * @param $id
     * @return mixed
     */
    public function show(Request $request, $id)
    {
        if (method_exists($this, 'beforeShow')) {
            // Injetar antes do carregamento
            $this->beforeShow($request);
        }

        abort_if(!$this->checkAttributeExists('model'), 500, 'Model não localizada.');

        $result = is_string($this->model) ? $this->model::findOrFail($id) : $this->model->first();

        if ($this->checkAttributeExists('resource')) return new $this->resource($result);

        return $result;
    }

    /**
     * Verificar se existe recurso para customizar coleção de dados.
     *
     * @param string $attribute
     * @return bool
     */
    private function checkAttributeExists(string $attribute): bool
    {
        return array_key_exists($attribute, get_object_vars($this)) && $this->{$attribute} !== null;
    }
}
