<?php

if (!function_exists('jsonResponse')) {
    /**
     * Retorna uma resposta JSON padronizada
     *
     * @param mixed $resource Dados para retornar
     * @param int $code Código HTTP
     * @param string $status Status da resposta (SUCCESS|ERROR)
     * @param int|null $page Página atual (para paginação)
     * @param int|null $total Total de registros
     * @return \Illuminate\Http\JsonResponse
     */
    function jsonResponse($resource, int $code = 200, string $status = 'SUCCESS', int $page = null, int $total = null)
    {
        $response = [
            'status' => $status,
            'code' => $code,
            'data' => $resource,
        ];

        if ($page !== null) {
            $response['page'] = $page;
        }

        if ($total !== null) {
            $response['total'] = $total;
        }

        return response()->json($response, $code);
    }
}

if (!function_exists('beginTransaction')) {
    /**
     * Inicia uma transação no banco de dados
     *
     * @param string $connection Nome da conexão
     * @return void
     */
    function beginTransaction(string $connection = 'mysql')
    {
        \Illuminate\Support\Facades\DB::connection($connection)->beginTransaction();
    }
}

if (!function_exists('commit')) {
    /**
     * Confirma uma transação no banco de dados
     *
     * @param string $connection Nome da conexão
     * @return void
     */
    function commit(string $connection = 'mysql')
    {
        \Illuminate\Support\Facades\DB::connection($connection)->commit();
    }
}

if (!function_exists('rollback')) {
    /**
     * Desfaz uma transação no banco de dados
     *
     * @param string $connection Nome da conexão
     * @return void
     */
    function rollback(string $connection = 'mysql')
    {
        \Illuminate\Support\Facades\DB::connection($connection)->rollBack();
    }
}

if (!function_exists('throwException')) {
    /**
     * Lança uma exceção padronizada com log e resposta JSON
     *
     * @param \Throwable $th Exception capturada
     * @param string $acao Descrição da ação que falhou
     * @param string|null $route Rota para redirect (opcional)
     * @param mixed $model Model relacionado (opcional)
     * @param mixed $to Destinatário (opcional)
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    function throwException(\Throwable $th, string $acao, string $route = null, $model = null, $to = null)
    {
        \Illuminate\Support\Facades\Log::error("Erro ao {$acao}: " . $th->getMessage(), [
            'file' => $th->getFile(),
            'line' => $th->getLine(),
            'trace' => $th->getTraceAsString(),
        ]);

        $message = "Erro ao {$acao}. " . $th->getMessage();

        if (request()->expectsJson()) {
            return jsonResponse(['message' => $message], 500, 'ERROR');
        }

        return redirect($route ?? back())
            ->with('error', $message);
    }
}

if (!function_exists('problemDetails')) {
    /**
     * Retorna uma resposta RFC 7807 Problem Details
     *
     * @param string $type URI que identifica o tipo do erro
     * @param string $title Sumário legível do problema
     * @param int $status HTTP status code
     * @param string $detail Explicação detalhada do erro
     * @param string|null $instance URI da instância específica do problema
     * @param array $extensions Extensões adicionais
     * @return \Illuminate\Http\JsonResponse
     */
    function problemDetails(
        string $type,
        string $title,
        int $status,
        string $detail,
        ?string $instance = null,
        array $extensions = []
    ) {
        $problem = [
            'type' => $type,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
        ];

        if ($instance !== null) {
            $problem['instance'] = $instance;
        }

        // Adicionar extensões customizadas
        foreach ($extensions as $key => $value) {
            $problem[$key] = $value;
        }

        return response()->json($problem, $status, [
            'Content-Type' => 'application/problem+json',
        ]);
    }
}

if (!function_exists('apiResponse')) {
    /**
     * Retorna uma resposta API padronizada
     *
     * @param mixed $data Dados para retornar
     * @param int $code HTTP status code
     * @return \Illuminate\Http\JsonResponse
     */
    function apiResponse($data, int $code = 200)
    {
        return response()->json([
            'data' => $data,
            'code' => $code,
        ], $code);
    }
}

if (!function_exists('preventN1Query')) {
    /**
     * Habilita prevenção de N+1 queries (Laravel 11/12)
     *
     * @return void
     */
    function preventN1Query()
    {
        if (class_exists(\Illuminate\Database\Eloquent\Model::class)) {
            \Illuminate\Database\Eloquent\Model::preventLazyLoading(!app()->isProduction());
        }
    }
}
