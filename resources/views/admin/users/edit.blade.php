@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Edit User</h1>

        <!-- Edit User Form -->
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <!-- Name Field -->
            <div class="col-md-4 mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email Field -->
            <div class="col-md-4 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <!-- Profile Image Field -->
            <div class="col-md-4 mb-3">
                <label for="profile_image" class="form-label">Profile Image URL</label>
                <input type="url" name="profile_image" id="profile_image" class="form-control" value="{{ old('profile_image', $user->profile_image) }}">
            </div>
        </div>
    </div>
</div>




            <!-- Yes/No Toggles -->
            @if (auth()->id() !== $user->id)
    <div class="card mb-3">
        <div class="card-header">
            User Settings
        </div>
        <div class="card-body">
            <div class="row">
                @foreach (['enabled', 'downloadpos', 'uploadpos', 'donor'] as $field)
                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ ucfirst($field) }}</label>
                        <div class="d-flex align-items-center">
                            <!-- Yes Option -->
                            <label class="form-check-label me-3">
                                <input type="radio"
                                       name="{{ $field }}"
                                       value="yes"
                                       class="form-check-input"
                                       {{ old($field, $user->$field) === 'yes' ? 'checked' : '' }}>
                                Yes
                            </label>

                            <!-- No Option -->
                            <label class="form-check-label">
                                <input type="radio"
                                       name="{{ $field }}"
                                       value="no"
                                       class="form-check-input"
                                       {{ old($field, $user->$field) === 'no' ? 'checked' : '' }}>
                                No
                            </label>
                        </div>
                        @error($field)
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                @foreach (['is_immune', 'is_freeleech'] as $field)
                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ ucfirst(str_replace('_', ' ', $field)) }}</label>
                        <div class="d-flex align-items-center">
                            <!-- 1 Option -->
                            <label class="form-check-label me-3">
                                <input type="radio"
                                       name="{{ $field }}"
                                       value="1"
                                       class="form-check-input"
                                       {{ old($field, $user->$field) == 1 ? 'checked' : '' }}>
                                Yes
                            </label>

                            <!-- 0 Option -->
                            <label class="form-check-label">
                                <input type="radio"
                                       name="{{ $field }}"
                                       value="0"
                                       class="form-check-input"
                                       {{ old($field, $user->$field) == 0 ? 'checked' : '' }}>
                                No
                            </label>
                        </div>
                        @error($field)
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif


 <!-- User Role -->
 @if (auth()->user()->user_class >= \App\Models\UserClass::MODERATOR && auth()->id() !== $user->id)
                <div class="mb-3">
                    <label for="user_class" class="form-label">User Role</label>
                    <select name="user_class" id="user_class" class="form-control">
                        @foreach (App\Models\UserClass::getClasses() as $classValue => $className)
                            <option value="{{ $classValue }}" {{ $user->user_class == $classValue ? 'selected' : '' }}>
                                {{ $className }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif




            <!-- VIP Section -->
            @if ($user->vip_until)
                <div class="mb-3 mt-3">
                    <label for="vip_until" class="form-label">VIP Until</label>
                    <p>{{ $user->vip_until }}</p>
                </div>
            @endif

            @if (auth()->user()->user_class === \App\Models\UserClass::OWNER && $user->user_class <= \App\Models\UserClass::VIP)
                <div class="mb-3">
                    <label for="vip_until" class="form-label">Set VIP Duration</label>
                    <select id="vip_until" name="vip_until" class="form-control">
                        <option value="">-- Select Duration --</option>
                        <option value="4 weeks" {{ old('vip_until') == '4 weeks' ? 'selected' : '' }}>4 Weeks</option>
                        <option value="6 weeks" {{ old('vip_until') == '6 weeks' ? 'selected' : '' }}>6 Weeks</option>
                        <option value="8 weeks" {{ old('vip_until') == '8 weeks' ? 'selected' : '' }}>8 Weeks</option>
                        <option value="10 weeks" {{ old('vip_until') == '10 weeks' ? 'selected' : '' }}>10 Weeks</option>
                        <option value="12 weeks" {{ old('vip_until') == '12 weeks' ? 'selected' : '' }}>12 Weeks</option>
                        <option value="remove" {{ old('vip_until') == 'remove' ? 'selected' : '' }}>Remove VIP</option>
                    </select>
                </div>
            @endif

            <!-- Upload and Download -->
            <div class="row">
    <div class="col-md-6 mb-3">
        <label for="uploaded" class="form-label">Uploaded (GB)</label>
        <div class="input-group">
            <button type="button" class="btn btn-outline-secondary" onclick="adjustValue('uploaded', -1)">-</button>
            <input type="number" step="1" name="uploaded" id="uploaded" class="form-control"
                   value="{{ old('uploaded', floor($user->uploaded / (1024 ** 3))) }}">
            <button type="button" class="btn btn-outline-secondary" onclick="adjustValue('uploaded', 1)">+</button>
        </div>
        <small class="text-muted">Adjust the value in GB (e.g., 1 for 1 GB).</small>
        @error('uploaded')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="downloaded" class="form-label">Downloaded (GB)</label>
        <div class="input-group">
            <button type="button" class="btn btn-outline-secondary" onclick="adjustValue('downloaded', -1)">-</button>
            <input type="number" step="1" name="downloaded" id="downloaded" class="form-control"
                   value="{{ old('downloaded', floor($user->downloaded / (1024 ** 3))) }}">
            <button type="button" class="btn btn-outline-secondary" onclick="adjustValue('downloaded', 1)">+</button>
        </div>
        <small class="text-muted">Adjust the value in GB (e.g., 1 for 1 GB).</small>
        @error('downloaded')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>



            <!-- User Info -->
            <div class="mb-3">
                <label for="info" class="form-label">User Info</label>
                <textarea name="info" id="info" class="form-control" rows="6">{{ old('info', $user->info) }}</textarea>
            </div>

            <!-- Buttons -->
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back to Users List</a>
        </form>
    </div>

    <script>
    function adjustValue(fieldId, increment) {
        const inputField = document.getElementById(fieldId);
        const currentValue = parseInt(inputField.value, 10) || 0; // Use parseInt for integers
        const newValue = currentValue + increment;

        // Ensure the value doesn't go below 0
        inputField.value = Math.max(newValue, 0); // No decimals
    }
</script>


@endsection
