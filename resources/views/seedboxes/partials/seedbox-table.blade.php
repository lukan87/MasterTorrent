@php
    $isDev = auth()->user()->user_class == \App\Models\UserClass::WEB_DEVELOPER;
@endphp

{{-- Desktop Table --}}
<div class="d-none d-md-block">
    <table class="table align-middle text-light" style="background-color:#2b2b2b; border-radius:12px; overflow:hidden;">
        <thead style="background-color:#3a3a3a;">
            <tr>
                @if($isDev) <th>User</th> @endif
                <th>Name</th>
                <th>Address</th>
                <th>Auth Type</th>
                @if($isDev) <th>Username</th> <th>Password</th> @endif
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($boxes as $box)
                <tr @if(isset($highlightOwner) && $box->user_id == auth()->id()) style="background-color:#333;" @endif class="table-hover-dark">
                    @if($isDev)<td>{{ $box->user->name }}</td>@endif
                    <td>
                        <a href="{{ route('seedboxes.torrents', $box) }}" class="text-decoration-none text-light fw-bold">
                            {{ $box->name }}
                        </a>
                        @if($box->user_id == auth()->id())
                            <span class="badge bg-info text-dark ms-1">Your Seedbox</span>
                        @endif
                    </td>
                    <td>{{ $box->address }}</td>
                    <td>
                        @if($box->auth_type === 'basic')
                            <span class="badge bg-primary">Basic</span>
                        @else
                            <span class="badge bg-warning text-dark">Digest</span>
                        @endif
                    </td>
                    @if($isDev)
                        <td>{{ $box->username }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <input type="password" id="password-{{ $box->id }}" value="{{ $box->password }}" readonly class="form-control form-control-sm" style="width:auto;">
                                <button type="button" class="btn btn-sm btn-secondary btn-hover" onclick="togglePassword({{ $box->id }})">Show</button>
                            </div>
                        </td>
                    @endif
                    <td>
                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                            @if($isDev || $box->user_id == auth()->id())
                                <a href="{{ route('seedboxes.edit', $box) }}" class="btn btn-sm btn-warning btn-hover">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form action="{{ route('seedboxes.destroy', $box) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger btn-hover" onclick="return confirm('Are you sure?')">
                                        <i class="bi bi-trash-fill"></i> Delete
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('seedboxes.test', $box) }}" class="btn btn-sm btn-info btn-hover">
                                <i class="bi bi-wifi"></i> Test
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Mobile Cards --}}
<div class="d-md-none">
    @foreach($boxes as $box)
        <div class="card mb-3 shadow-sm" style="background-color:#2b2b2b; color:#f0f0f0; border-radius:12px;">
            <div class="card-body">
                <h5 class="card-title fw-bold">
                    <a href="{{ route('seedboxes.torrents', $box) }}" class="text-decoration-none text-light">
                        {{ $box->name }}
                    </a>
                    @if($box->user_id == auth()->id())
                        <span class="badge bg-info text-dark ms-1">Your Seedbox</span>
                    @endif
                </h5>
                @if($isDev)<p><strong>User:</strong> {{ $box->user->name }}</p>@endif
                <p><strong>Address:</strong> {{ $box->address }}</p>
                <p><strong>Auth:</strong> 
                    @if($box->auth_type === 'basic') <span class="badge bg-primary">Basic</span>
                    @else <span class="badge bg-warning text-dark">Digest</span> @endif
                </p>
                @if($isDev)
                    <p><strong>Username:</strong> {{ $box->username }}</p>
                    <p class="d-flex align-items-center gap-1">
                        <strong>Password:</strong>
                        <input type="password" id="password-{{ $box->id }}" value="{{ $box->password }}" readonly class="form-control form-control-sm" style="width:auto;">
                        <button type="button" class="btn btn-sm btn-secondary btn-hover" onclick="togglePassword({{ $box->id }})">Show</button>
                    </p>
                @endif
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @if($isDev || $box->user_id == auth()->id())
                        <a href="{{ route('seedboxes.edit', $box) }}" class="btn btn-sm btn-warning btn-hover w-100"><i class="bi bi-pencil-square"></i> Edit</a>
                        <form action="{{ route('seedboxes.destroy', $box) }}" method="POST" class="w-100">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger btn-hover w-100" onclick="return confirm('Are you sure?')">
                                <i class="bi bi-trash-fill"></i> Delete
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('seedboxes.test', $box) }}" class="btn btn-sm btn-info btn-hover w-100"><i class="bi bi-wifi"></i> Test</a>
                </div>
            </div>
        </div>
    @endforeach
</div>
