<div class="row mt-5">
    <div class="col-12 col-sm-4 col-md-4">
        <div class="info-box">
            <span class="info-box-icon text-bg-primary shadow-sm">
                <i class="bi bi-gear-fill"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Torrents</span>
                <span class="info-box-number">
                    {{ $torrentCount }} <!-- Display the count of torrents -->
                </span>
            </div> <!-- /.info-box-content -->
        </div> <!-- /.info-box -->
    </div> <!-- /.col -->

    <div class="col-12 col-sm-4 col-md-4">
        <div class="info-box">
            <span class="info-box-icon text-bg-danger shadow-sm">
                <i class="bi bi-people-fill"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Users</span>
                <span class="info-box-number">{{ $userCount }}</span> <!-- Display the count of users -->
            </div> <!-- /.info-box-content -->
        </div> <!-- /.info-box -->
    </div> <!-- /.col -->

    <div class="col-12 col-sm-4 col-md-4">
        <div class="info-box">
            <span class="info-box-icon text-bg-success shadow-sm">
                <i class="bi bi-cart-fill"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Forum Topics</span>
                <span class="info-box-number">
                    {{ $forumTopicCount }} <!-- Display the count of forum topics -->
                </span>
            </div> <!-- /.info-box-content -->
        </div> <!-- /.info-box -->
    </div> <!-- /.col -->


</div>
