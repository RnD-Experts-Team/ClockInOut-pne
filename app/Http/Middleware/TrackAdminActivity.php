<?php

namespace App\Http\Middleware;

use App\Models\AdminActivityLog;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackAdminActivity
{
    /**
     * Handle an incoming request and track admin activity.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track for authenticated users
        if (!Auth::check()) {
            return $response;
        }

        $user = Auth::user();

        // Only track admin activities (you can customize this condition)
        if (!$this->shouldTrackUser($user)) {
            return $response;
        }
        // Only track certain HTTP methods
        if (!$this->shouldTrackMethod($request->method())) {
            return $response;
        }

        // Skip tracking for certain routes/patterns
        if ($this->shouldSkipRoute($request)) {
            return $response;
        }

        try {
            $this->logAdminActivity($request, $response, $user);
        } catch (\Exception $e) {
            // Don't break the request if logging fails
            Log::error('TrackAdminActivity middleware error: ' . $e->getMessage());
        }

        return $response;
    }

    /**
     * Determine if we should track this user's activity
     */
    private function shouldTrackUser(User $user): bool
    {
        // Track if user is admin or has admin role
        return $user->is_admin ||
            $user->role === 'admin' ||
            in_array($user->email, config('app.admin_emails', []));

    }

    /**
     * Determine if we should track this HTTP method
     */
    private function shouldTrackMethod(string $method): bool
    {
        return in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE']);
    }

    /**
     * Determine if we should skip tracking for this route
     */
    private function shouldSkipRoute(Request $request): bool
    {
        $skipPatterns = [
            'api/health',
            'api/ping',
            'logout',
            'password/confirm',
            '_debugbar',
            'telescope',
            'horizon',
            'admin-activity/api', // Don't track admin activity API calls
            'notifications/mark-read', // Skip notification marking
        ];

        $path = $request->path();

        foreach ($skipPatterns as $pattern) {
            if (Str::contains($path, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log the admin activity
     */
    /**
     * Log the admin activity
     */
    /**
     * Log the admin activity
     */
    private function logAdminActivity(Request $request, Response $response, User $user): void
    {
        $actionType = $this->determineActionType($request);
        $description = $this->generateDescription($request, $actionType);
        $affectedModel = $this->extractAffectedModel($request);

        AdminActivityLog::create([
            'admin_user_id' => $user->id,
            'action_type' => $actionType,
            'description' => $description,
            'model_type' => $affectedModel['type'] ?? 'System', // ✅ Default to 'System' if null
            'model_id' => $affectedModel['id']   ?? 1 ,              // ✅ Can be null
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
            'field_name' => null,
            'old_value' => null,
            'new_value' => null,
        ]);
    }

    /**
     * Determine the action type based on the request
     */
    private function determineActionType(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        // Specific action mappings
        $actionMap = [
            // User management
            'users' => 'user_management',
            'roles' => 'role_management',
            'permissions' => 'permission_management',

            // Content management
            'reminders' => 'reminder_management',
            'calendar' => 'calendar_management',
            'maintenance' => 'maintenance_management',
            'leases' => 'lease_management',
            'apartments' => 'apartment_management',
            'payments' => 'payment_management',

            // System operations
            'settings' => 'system_settings',
            'logs' => 'log_management',
            'backups' => 'backup_management',
            'cache' => 'cache_management',

            // Reports
            'reports' => 'report_generation',
            'analytics' => 'analytics_access',
            'exports' => 'data_export',
        ];

        // Check for specific action in path
        foreach ($actionMap as $pathPattern => $actionType) {
            if (Str::contains($path, $pathPattern)) {
                return $this->getMethodSpecificAction($actionType, $method, $path);
            }
        }

        // Generic actions based on HTTP method
        return match($method) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'unknown',
        };
    }

    /**
     * Get method-specific action type
     */
    private function getMethodSpecificAction(string $baseAction, string $method, string $path): string
    {
        $suffix = match($method) {
            'POST' => Str::contains($path, 'bulk') ? '_bulk_create' : '_create',
            'PUT', 'PATCH' => '_update',
            'DELETE' => '_delete',
            default => '',
        };

        // Special cases
        if (Str::contains($path, 'restore')) return $baseAction . '_restore';
        if (Str::contains($path, 'activate')) return $baseAction . '_activate';
        if (Str::contains($path, 'deactivate')) return $baseAction . '_deactivate';
        if (Str::contains($path, 'approve')) return $baseAction . '_approve';
        if (Str::contains($path, 'reject')) return $baseAction . '_reject';
        if (Str::contains($path, 'assign')) return $baseAction . '_assign';

        return $baseAction . $suffix;
    }

    /**
     * Generate human-readable description
     */
    private function generateDescription(Request $request, string $actionType): string
    {
        $method = $request->method();
        $path = $request->path();
        $segments = $request->segments();

        // Extract resource name and ID if available
        $resource = $segments[0] ?? 'resource';
        $resourceId = null;

        // Look for numeric ID in URL segments
        foreach ($segments as $segment) {
            if (is_numeric($segment)) {
                $resourceId = $segment;
                break;
            }
        }

        // Get specific fields that were changed
        $changedFields = $this->getChangedFields($request);
        $fieldsDescription = $changedFields ? ' (Fields: ' . implode(', ', $changedFields) . ')' : '';

        // Generate description based on action
        $descriptions = [
            'POST' => "Created new " . Str::singular($resource) . ($resourceId ? " #{$resourceId}" : ''),
            'PUT' => "Updated " . Str::singular($resource) . ($resourceId ? " #{$resourceId}" : '') . $fieldsDescription,
            'PATCH' => "Modified " . Str::singular($resource) . ($resourceId ? " #{$resourceId}" : '') . $fieldsDescription,
            'DELETE' => "Deleted " . Str::singular($resource) . ($resourceId ? " #{$resourceId}" : ''),
        ];

        $description = $descriptions[$method] ?? "Performed {$actionType} action";

        // Add specific action details
        if (Str::contains($path, 'bulk')) {
            $description = "Bulk operation: " . $description;
        }

        return $description;
    }

    /**
     * Extract affected model information
     */
    private function extractAffectedModel(Request $request): array
    {
        $segments = $request->segments();

        // Map URL segments to model classes
        $modelMap = [
            'users' => 'User',
            'reminders' => 'CalendarReminder',
            'calendar' => 'CalendarEvent',
            'maintenance' => 'MaintenanceRequest',
            'leases' => 'Lease',
            'apartment-leases' => 'ApartmentLease',
            'apartments' => 'Apartment',
            'payments' => 'Payment',
            'roles' => 'Role',
            'permissions' => 'Permission',
        ];

        $resource = $segments[0] ?? null;
        $modelType = $modelMap[$resource] ?? null;

        // Extract ID from URL
        $modelId = null;
        foreach ($segments as $segment) {
            if (is_numeric($segment)) {
                $modelId = (int) $segment;
                break;
            }
        }

        // Try to extract from route parameters
        if (!$modelId && $request->route()) {
            $parameters = $request->route()->parameters();
            foreach ($parameters as $key => $value) {
                if (is_numeric($value)) {
                    $modelId = (int) $value;
                    break;
                }
            }
        }

        return [
            'type' => $modelType,
            'id' => $modelId,
        ];
    }

    /**
     * Get fields that were changed in the request
     */
    private function getChangedFields(Request $request): array
    {
        $sensitiveFields = [
            'password', 'password_confirmation', 'token', 'api_key',
            'secret', 'private_key', '_token', 'remember_token'
        ];

        $allData = $request->except($sensitiveFields);
        $changedFields = [];

        // Only track fields that have actual values
        foreach ($allData as $key => $value) {
            if (!empty($value) && !is_array($value)) {
                $changedFields[] = $key;
            }
        }

        return array_slice($changedFields, 0, 10); // Limit to 10 fields
    }

    /**
     * Sanitize request data for logging
     */
    private function sanitizeRequestData(Request $request): ?string
    {
        $sensitiveFields = [
            'password', 'password_confirmation', 'current_password',
            'new_password', 'token', 'api_key', 'secret', 'private_key',
            '_token', 'remember_token', 'credit_card', 'ssn', 'social_security'
        ];

        $data = $request->except($sensitiveFields);

        // Remove empty values and limit size
        $data = array_filter($data, function($value) {
            return !empty($value);
        });

        // Truncate large values
        foreach ($data as $key => $value) {
            if (is_string($value) && strlen($value) > 500) {
                $data[$key] = substr($value, 0, 500) . '...';
            }
        }

        $jsonData = json_encode($data);

        // Limit total size
        return strlen($jsonData) > 2000 ? substr($jsonData, 0, 2000) . '...' : $jsonData;
    }
}
