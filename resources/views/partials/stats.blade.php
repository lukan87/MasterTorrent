<div class="container mt-5">
    <div class="row">

        <!-- Torrents Box -->
        <div class="col-12 col-sm-4 col-md-6 col-lg-4 mb-4">
            <div class="info-box shadow-md rounded-5">
                <span class="info-box-icon bg-primary text-white rounded-circle">
                    <i class="bi bi-server"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase fw-bold">Torrents</span>
                    <span class="info-box-number fs-5">{{ $torrentCount }}</span>
                </div>
            </div>
        </div>

        <!-- Active Torrents Box -->
        <div class="col-12 col-sm-4 col-md-6 col-lg-4 mb-4">
            <div class="info-box shadow-md rounded-5">
                <span class="info-box-icon bg-info text-white rounded-circle">
                    <i class="bi bi-gear-fill"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase fw-bold">Active Torrents</span>
                    <span class="info-box-number fs-5">{{ $torrentActive }}</span>
                </div>
            </div>
        </div>

        <!-- Users Box -->
        <div class="col-12 col-sm-4 col-md-6 col-lg-4 mb-4">
            <div class="info-box shadow-md rounded-5">
                <span class="info-box-icon bg-danger text-white rounded-circle">
                    <i class="bi bi-people-fill"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase fw-bold">Users</span>
                    <span class="info-box-number fs-5">{{ $userCount }}</span>
                </div>
            </div>
        </div>

        <!-- Forum Topics Box -->
        <div class="col-12 col-sm-4 col-md-6 col-lg-4 mb-4">
            <div class="info-box shadow-md rounded-5">
                <span class="info-box-icon bg-success text-white rounded-circle">
                    <i class="bi bi-cart-fill"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase fw-bold">Forum Topics</span>
                    <span class="info-box-number fs-5">{{ $forumTopicCount }}</span>
                </div>
            </div>
        </div>

        <!-- Active Seeders Box -->
        <div class="col-12 col-sm-4 col-md-6 col-lg-4 mb-4">
            <div class="info-box shadow-md rounded-5">
                <span class="info-box-icon bg-warning text-white rounded-circle">
                    <i class="bi bi-cloud-upload-fill"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase fw-bold">Active Seeders</span>
                    <span class="info-box-number fs-5">{{ $uniqueSeeders }}</span>
                </div>
            </div>
        </div>

        <!-- Active Leechers Box -->
        <div class="col-12 col-sm-4 col-md-6 col-lg-4 mb-4">
            <div class="info-box shadow-md rounded-5">
                <span class="info-box-icon bg-secondary text-white rounded-circle">
                    <i class="bi bi-cloud-download-fill"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase fw-bold">Active Leechers</span>
                    <span class="info-box-number fs-5">{{ $uniqueLeechers }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
