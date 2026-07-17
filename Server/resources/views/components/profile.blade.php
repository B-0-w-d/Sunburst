<div class="modal-window">
    <!-- Header stays at the top -->
    <div class="modal-header">
        <h2 class="modal-title">Edit Your Profile</h2>
        <button type="button" class="modal-close-btn" onclick="closeModal()">&times;</button>
    </div>

    <!-- Form wraps the scrollable body -->
    <form action="{{ route('profile.update') }}" method="POST" style="display: flex; flex-direction: column; flex: 1; min-height: 0;">
        @csrf
        @method('PUT')

        <div class="modal-body">
            @if ($errors->any())
                <div style="color: red; margin-bottom: 10px;">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="form-group">
                <label>DISPLAY NAME</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
            </div>

            <div class="form-group">
                <label>EMAIL ADDRESS</label>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
            </div>

            <div class="form-group">
                <label>BIRTHDAY</label>
                <input type="date" name="birthday" value="{{ old('birthday', auth()->user()->birthday) }}">
            </div>

            <div class="form-group">
                <label>ROLE</label>
                <select name="role" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="admin" {{ auth()->user()->role == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="member" {{ auth()->user()->role == 'member' ? 'selected' : '' }}>Member</option>
                </select>
            </div>

            <div class="form-group">
                <label>INSTRUMENTS</label>
                <input type="text" name="instrument" value="{{ is_array(auth()->user()->instrument) ? implode(', ', auth()->user()->instrument) : auth()->user()->instrument }}">
            </div>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #e2e8f0;">

            <div class="form-group">
                <label>NEW PASSWORD</label>
                <input type="password" name="password" placeholder="Leave blank to keep current">
            </div>

            <div class="form-group">
                <label>CONFIRM PASSWORD</label>
                <input type="password" name="password_confirmation">
            </div>
        </div>

        <!-- Footer stays at the bottom -->
        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; flex-shrink: 0;">
            <button type="button" onclick="closeModal()" style="padding: 10px 20px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 6px; cursor: pointer;">Cancel</button>
            <button type="submit" style="padding: 10px 40px; background: #cc0000; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">Save Changes</button>
        </div>
    </form>
</div>
