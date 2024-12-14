<div class="container">
    <!-- Tabs -->
    <ul class="nav nav-tabs" id="leaderboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="uploaders-tab" data-bs-toggle="tab" href="#top-uploaders" role="tab" aria-controls="top-uploaders" aria-selected="true">Top Uploaders</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="downloaders-tab" data-bs-toggle="tab" href="#top-downloaders" role="tab" aria-controls="top-downloaders" aria-selected="false">Top Downloaders</a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="leaderboardTabsContent">
        <!-- Top Uploaders Tab -->
        <div class="tab-pane fade show active" id="top-uploaders" role="tabpanel" aria-labelledby="uploaders-tab">
            <table class="table mt-4">
                <thead>
                    <tr>
                        <th scope="col">Rank</th>
                        <th scope="col">User</th>
                        <th scope="col">Uploaded</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topUploaders as $index => $user)
                        <tr>
                            <th scope="row">{{ $index + 1 }}</th>
                            <td>{{ $user->name }}</td>
                            <td>{{ \App\Helpers\FormatHelper::formatSize($user->uploaded) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Top Downloaders Tab -->
        <div class="tab-pane fade" id="top-downloaders" role="tabpanel" aria-labelledby="downloaders-tab">
            <table class="table mt-4">
                <thead>
                    <tr>
                        <th scope="col">Rank</th>
                        <th scope="col">User</th>
                        <th scope="col">Downloaded</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topDownloaders as $index => $user)
                        <tr>
                            <th scope="row">{{ $index + 1 }}</th>
                            <td>{{ $user->name }}</td>
                            <td>{{ \App\Helpers\FormatHelper::formatSize($user->downloaded) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>