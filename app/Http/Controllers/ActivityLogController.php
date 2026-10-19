<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');
        
        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by action
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }
        
        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        
        $logs = $query->orderBy('created_at', 'desc')->paginate(50);
        
        // Get unique actions for filter
        $actions = ActivityLog::distinct()->pluck('action');
        
        return view('activity-logs.index', compact('logs', 'actions'));
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user');
        
        return view('activity-logs.show', compact('activityLog'));
    }

    public function userActivity(Request $request, $userId)
    {
        $logs = ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return view('activity-logs.user-activity', compact('logs'));
    }

    public function export(Request $request)
    {
        $query = ActivityLog::with('user');
        
        if ($request->has('start_date') && $request->start_date) {
            $query->where('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        
        $logs = $query->orderBy('created_at', 'desc')->get();
        
        return view('activity-logs.export', compact('logs'));
    }
}