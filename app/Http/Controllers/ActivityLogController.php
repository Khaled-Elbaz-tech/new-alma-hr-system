<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
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

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        // Get unique actions + model types + users for filter dropdowns
        $actions = ActivityLog::distinct()->pluck('action');
        $modelTypes = ActivityLog::distinct()->pluck('model_type')->filter();
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('activity-logs.index', compact('logs', 'actions', 'modelTypes', 'users'));
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user');

        return view('activity-logs.show', compact('activityLog'));
    }

    public function userActivity(Request $request, $userId)
    {
        $query = ActivityLog::where('user_id', $userId);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('start_date') && strtotime($request->start_date)) {
            $query->where('created_at', '>=', date('Y-m-d', strtotime($request->start_date)));
        }
        if ($request->filled('end_date') && strtotime($request->end_date)) {
            $query->where('created_at', '<=', date('Y-m-d', strtotime($request->end_date)) . ' 23:59:59');
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        return view('activity-logs.user-activity', compact('logs', 'userId'));
    }

    // WF-17 (ActivityLog part): the previous export() method is REMOVED,
    // not routed. Reasons: it had no route and no UI link, and the view it
    // returned (activity-logs.export) does not exist, so wiring a route to
    // it would have 500'd on first use — routing it would have meant
    // inventing a new export surface. The read-only index (paginated,
    // filtered) plus the show view cover audit viewing. If a log
    // export is wanted later, it should be added deliberately with its own
    // route, super_admin gate, and view — not by resurrecting this method.
}
