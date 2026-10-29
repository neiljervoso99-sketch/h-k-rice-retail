<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class AuditAdminActions
{
    public function handle(Request $request, callable $next): Response
    {
        $routeName = $request->route()?->getName() ?? 'admin.request';
        $profileMutation = in_array($routeName, [
            'admin.profile.update',
            'admin.profile.password.update',
        ], true);

        try {
            $response = $next($request);

            if (!$profileMutation) {
                $this->recordRequest($request, $routeName, $response->getStatusCode());
            }

            return $response;
        } catch (Throwable $exception) {
            if (!$request->isMethodSafe()) {
                $statusCode = match (true) {
                    $exception instanceof ValidationException => 422,
                    $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                    default => 500,
                };

                $this->recordRequest($request, $routeName, $statusCode);
            }

            throw $exception;
        }
    }

    private function recordRequest(Request $request, string $routeName, int $statusCode): void
    {
        ActivityLog::record($request, $routeName, [
            'method' => $request->method(),
            'route_parameters' => $this->routeParameters($request),
            'input' => $this->safeInput($request->input()),
            'uploads' => $this->uploadDetails($request),
            'status_code' => $statusCode,
        ]);
    }

    private function routeParameters(Request $request): array
    {
        $parameters = [];

        foreach ($request->route()?->parameters() ?? [] as $key => $value) {
            if ($value instanceof Model) {
                $parameters[$key] = $value->getKey();
            } elseif (is_scalar($value) || $value === null) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }

    private function safeInput(array $input, int $depth = 0): array
    {
        $safe = [];

        foreach (array_slice($input, 0, 50, true) as $key => $value) {
            if (preg_match('/password|token|secret|authorization|cookie|email|phone|address|customer|account|card|bank|cvv|pin|proof|document|signature|search|query|term/i', (string) $key)) {
                continue;
            }

            if (in_array($key, ['_method', '_token'], true)) {
                continue;
            }

            if (is_array($value) && $depth < 3) {
                $safe[$key] = $this->safeInput($value, $depth + 1);
            } elseif (is_string($value)) {
                $safe[$key] = mb_substr($value, 0, 500);
            } elseif (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
                $safe[$key] = $value;
            }
        }

        return $safe;
    }

    private function uploadDetails(Request $request): array
    {
        $uploads = [];

        foreach ($request->allFiles() as $key => $file) {
            foreach (is_array($file) ? $file : [$file] as $uploadedFile) {
                if ($uploadedFile->isValid()) {
                    $uploads[$key][] = [
                        'name' => mb_substr($uploadedFile->getClientOriginalName(), 0, 255),
                        'mime_type' => $uploadedFile->getClientMimeType(),
                        'size' => $uploadedFile->getSize(),
                    ];
                }
            }
        }

        return $uploads;
    }
}