<div class="row mt-5">
    <div class="col-12 col-sm-3 col-md-6">
        <div class="info-box">
            <span class="info-box-icon text-bg-primary shadow-sm">
            <i class="bi bi-server"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Torrents</span>
                <span class="info-box-number">
                    {{ $torrentCount }} 
                </span>
            </div> 
        </div> 
    </div> 


    <div class="col-12 col-sm-3 col-md-6">
        <div class="info-box">
            <span class="info-box-icon text-bg-primary shadow-sm">
                <i class="bi bi-gear-fill"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Active Torrents</span>
                <span class="info-box-number">
                    {{ $torrentActive }} 
                </span>
            </div> 
        </div>
    </div> 

    <div class="col-12 col-sm-3 col-md-6">
        <div class="info-box">
            <span class="info-box-icon text-bg-danger shadow-sm">
                <i class="bi bi-people-fill"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Users</span>
                <span class="info-box-number">{{ $userCount }}</span> 
            </div> 
        </div> 
    </div> 

    <div class="col-12 col-sm-3 col-md-6">
        <div class="info-box">
            <span class="info-box-icon text-bg-success shadow-sm">
                <i class="bi bi-cart-fill"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Forum Topics</span>
                <span class="info-box-number">
                    {{ $forumTopicCount }} 
                </span>
            </div> 
        </div>
    </div> 


</div>
