<?php

namespace Codevia\PermissionMiddleware;

use Codevia\PermissionMiddleware\PermissionList;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpForbiddenException;
use Slim\Routing\RouteContext;

class PermissionMiddleware implements MiddlewareInterface
{
    public const SESSION_PERMISSION = 'middleware.permission';

    public function __construct(protected PermissionList $permissions)
    {
    }

    /**
     * Check a permission level against a permission mask.
     *
     * @param int $mask   The permission mask to check against.
     * @param int $level  The permission level to check.
     *
     * @return bool True if the permission level is allowed, false otherwise.
     */
    public static function checkMask(int $mask, int $level): bool
    {
        return ($mask & $level) === $level;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeContext = RouteContext::fromRequest($request);
        $callable = $routeContext->getRoute()->getCallable() ?? null;

        if (!is_array($callable)) {
            return $handler->handle($request);
        }

        $lastValue = $callable[count($callable) - 1];
        $permissionLevel = $_SESSION[self::SESSION_PERMISSION]
            ?? $this->permissions->getDefaultLevel();

        if (!is_int($lastValue)) {
            return $handler->handle($request);
        }

        $this->permissions->checkValidity();
        $isValid = $this->checkMask($permissionLevel, $lastValue);

        if (!$isValid) {
            throw new HttpForbiddenException($request);
        }

        $response = $handler->handle($request);
        return $response;
    }
}
