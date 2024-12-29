@extends('layouts.app')

@section('content')
    <div class="mt-5">
        <div class="row">
            <div class="col-md-6">
                <!-- Card for System Information -->
                <div class="card">
                    <div class="card-header">
                        <h3>System Information</h3>
                    </div>
                    <div class="card-body">

                        <!-- Table for displaying system info -->
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th>PHP Version</th>
                                    <td>{{ $phpVersion }}</td>
                                </tr>
                                <tr>
                                    <th>Operating System</th>
                                    <td>{{ $os }}</td>
                                </tr>
                                <tr>
                                    <th>Disk Storage</th>
                                    <td>{{ number_format($storage / 1073741824, 2) }} GB free of {{ number_format($diskTotal / 1073741824, 2) }} GB</td>
                                </tr>
                                <tr>
                                    <th>Cache Status</th>
                                    <td><p>{{ $cacheStatus }}</p></td>
                                </tr>
                                <tr>
                                    <th>CPU Load (1 min / 5 min / 15 min)</th>
                                    <td><p>{{ implode(' / ', $cpuLoad) }}</p></td>
                                </tr>
                                <!-- Display System Uptime -->
                                 <tr>
                                    <th>System Uptime:</th>
                                    <th><p>{{ $uptime }}</p></th>
                                 </tr>
                                <tr>
                                    <th>Ram Usage</th>
                                    <td><p>{{ $ramUsage['total'] }} MB, Used: {{ $ramUsage['used'] }} MB, Free: {{ $ramUsage['free'] }} MB</p></td>
                                </tr>
                                <tr>
                                    <th>Backup scheduled</th>
                                    <td><p>On the 1st of each month</p></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>



                <!-- Actions Section -->
                <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Actions</h4>
                    </div>
                    <div class="card-body d-flex justify-content-start gap-3">
    <!-- Clear Cache Button -->
    <form action="{{ route('admin.systemInfo.clearCache') }}" method="POST" class="mb-3 mr-2">
        @csrf
        <button type="submit" class="btn btn-danger btn-sm">Clear Cache</button>
    </form>

    <!-- Clear Views Button -->
    <form action="{{ route('admin.systemInfo.clearViews') }}" method="POST" class="mb-3 mr-2">
        @csrf
        <button type="submit" class="btn btn-warning btn-sm">Clear Views</button>
    </form>

    <!-- Clear Routes Button -->
    <form action="{{ route('admin.systemInfo.clearRoutes') }}" method="POST" class="mb-3 mr-2">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm">Clear Routes</button>
    </form>


<!-- <form action="{{ route('admin.systemInfo.backup') }}" method="POST" class="mb-3 mr-2">
    @csrf
    <button type="submit" class="btn btn-success btn-sm">Backup All Data</button>
</form> -->


    <a href="{{ route('admin.systemInfo.showRoutes') }}" class="btn btn-info btn-sm mb-3 mr-2">Show Routes</a>
</div>


                </div>


            </div>
        </div>
        <!-- <div class="card mt-5">
    <div class="card-header">
        <h4>Scheduled Tasks</h4>
    </div>
    <div class="card-body">
        <p><strong>Database Backup:</strong> {{ $backupSchedule }}</p>
    </div>
</div> -->
    </div>
@endsection
