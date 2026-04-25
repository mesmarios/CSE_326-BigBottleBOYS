<?php
declare(strict_types=1);

function normalizeAppRole(?string $role): string
{
    $normalized = strtolower(trim((string)$role));

    return match ($normalized) {
        'admin' => 'admin',
        'hr_manager' => 'hr_manager',
        'evaluator' => 'evaluator',
        'specialist' => 'specialist',
        'candidate', 'user' => 'candidate',
        default => 'candidate',
    };
}

function appRoleLabel(?string $role): string
{
    return match (normalizeAppRole($role)) {
        'admin' => 'Admin',
        'hr_manager' => 'HR Manager',
        'evaluator' => 'Evaluator',
        'specialist' => 'Specialist',
        default => 'Candidate',
    };
}

function normalizeRequestedModule(?string $module): ?string
{
    $normalized = strtolower(trim((string)$module));

    return in_array($normalized, ['admin', 'recruitment', 'enrollment'], true)
        ? $normalized
        : null;
}

function moduleLabel(string $module): string
{
    return match ($module) {
        'admin' => 'Admin Module',
        'enrollment' => 'Enrollment Module',
        default => 'Recruitment Module',
    };
}

function moduleDashboardPath(string $module): string
{
    return match ($module) {
        'admin' => 'modules/admin/index.php',
        'enrollment' => 'modules/enrollmentModule/index.php',
        default => 'modules/recruitmentModule/index.php',
    };
}

function modulesForRole(?string $role): array
{
    return match (normalizeAppRole($role)) {
        'admin' => ['admin', 'enrollment'],
        'hr_manager' => ['recruitment', 'enrollment'],
        'evaluator' => ['recruitment'],
        'specialist' => ['enrollment'],
        default => ['recruitment'],
    };
}

function roleCanAccessModule(?string $role, string $module): bool
{
    return in_array($module, modulesForRole($role), true);
}

function defaultModuleForRole(?string $role): string
{
    return match (normalizeAppRole($role)) {
        'admin' => 'admin',
        'specialist' => 'enrollment',
        default => 'recruitment',
    };
}

function defaultDashboardPathForRole(?string $role): string
{
    return moduleDashboardPath(defaultModuleForRole($role));
}

function resolveDashboardPathForRole(?string $role, ?string $requestedModule = null): string
{
    $normalizedModule = normalizeRequestedModule($requestedModule);

    if ($normalizedModule !== null && roleCanAccessModule($role, $normalizedModule)) {
        return moduleDashboardPath($normalizedModule);
    }

    return defaultDashboardPathForRole($role);
}

function roleHasEnrollmentManagerAccess(?string $role): bool
{
    return in_array(normalizeAppRole($role), ['admin', 'hr_manager'], true);
}

