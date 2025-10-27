<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityResource;
use App\Services\Interfaces\ActivityServiceInterface;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function __construct(
        protected ActivityServiceInterface $activityService
    ) {}

    public function index(Request $request)
    {
        $logName = $request->query('log_name');
        $event = $request->query('event');
        $perPage = (int) $request->query('per_page', 10);

        $activities = $this->activityService->getFiltered($logName, $event, $perPage);

        return ActivityResource::collection($activities);
    }
}
