<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class SystemLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:admin_access');
    }

    /**
     * Display a listing of system logs
     */
    public function index(Request $request)
    {
        $query = SystemLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('action', 'LIKE', "%{$search}%")
                  ->orWhere('module', 'LIKE', "%{$search}%")
                  ->orWhere('ip_address', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20)->withQueryString();

        // Get data for filters
        $users = User::select('id', 'name')->orderBy('name')->get();
        
        $actions = SystemLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $modules = SystemLog::select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        // Statistics
        $stats = [
            'total_logs' => SystemLog::count(),
            'logs_today' => SystemLog::whereDate('created_at', today())->count(),
            'logs_this_week' => SystemLog::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'logs_this_month' => SystemLog::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'unique_users_today' => SystemLog::whereDate('created_at', today())
                ->distinct('user_id')
                ->count('user_id'),
            'most_active_user' => SystemLog::select('user_id', DB::raw('COUNT(*) as log_count'))
                ->with('user:id,name')
                ->groupBy('user_id')
                ->orderBy('log_count', 'desc')
                ->first(),
        ];

        return Inertia::render('SystemLogs/Index', [
            'logs' => $logs,
            'users' => $users,
            'actions' => $actions,
            'modules' => $modules,
            'filters' => $request->only(['search', 'action', 'module', 'user_id', 'date_from', 'date_to']),
            'stats' => $stats
        ]);
    }

    /**
     * Show detailed view of a log entry
     */
    public function show(SystemLog $systemLog)
    {
        $systemLog->load('user', 'loggable');

        return Inertia::render('SystemLogs/Show', [
            'log' => $systemLog
        ]);
    }

    /**
     * Export logs to CSV
     */
    public function export(Request $request)
    {
        $query = SystemLog::with('user')
            ->orderBy('created_at', 'desc');

        // Apply the same filters as in index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('action', 'LIKE', "%{$search}%")
                  ->orWhere('module', 'LIKE', "%{$search}%")
                  ->orWhere('ip_address', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        $filename = 'system_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'ID',
                'Fecha',
                'Usuario',
                'Acción',
                'Módulo',
                'Descripción',
                'IP',
                'User Agent',
                'Valores Antiguos',
                'Valores Nuevos'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user ? $log->user->name : 'Sistema',
                    $log->action_label,
                    $log->module_label,
                    $log->description,
                    $log->ip_address,
                    $log->user_agent,
                    $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE) : '',
                    $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get logs summary for dashboard
     */
    public function dashboard()
    {
        // Activity by day (last 30 days)
        $dailyActivity = SystemLog::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Activity by action
        $actionStats = SystemLog::select('action', DB::raw('COUNT(*) as count'))
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Activity by module
        $moduleStats = SystemLog::select('module', DB::raw('COUNT(*) as count'))
            ->groupBy('module')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Most active users
        $topUsers = SystemLog::select('user_id', DB::raw('COUNT(*) as activity_count'))
            ->with('user:id,name')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderBy('activity_count', 'desc')
            ->limit(10)
            ->get();

        // Recent critical actions
        $criticalActions = SystemLog::with('user:id,name')
            ->whereIn('action', ['delete', 'force_delete', 'restore'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'daily_activity' => $dailyActivity,
            'action_stats' => $actionStats,
            'module_stats' => $moduleStats,
            'top_users' => $topUsers,
            'critical_actions' => $criticalActions
        ]);
    }

    /**
     * Clean old logs (keep only last 90 days)
     */
    public function cleanup()
    {
        $deleted = SystemLog::where('created_at', '<', now()->subDays(90))->delete();

        return response()->json([
            'message' => "Se eliminaron {$deleted} registros de más de 90 días.",
            'deleted_count' => $deleted
        ]);
    }
}