<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Generator Owners Management</h4>
        <div>
            <a href="{{ route('users.create') }}" class="btn btn-success me-2">
                <i class="bi bi-plus-circle"></i> Add Generator Owner
            </a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-dark">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Phone Numbers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="{{ $user->trashed() ? 'table-secondary' : '' }}">
                                <td>{{ $user->name }}</td>
                                <td>
                                    @forelse($user->phoneNumbers as $phone)
                                        <div>{{ $phone->phone_number }}</div>
                                    @empty
                                        <div class="text-muted">No phone numbers</div>
                                    @endforelse
                                </td>
                                <td class="text-center">
    <div class="d-flex justify-content-center flex-wrap gap-1">
        @if($user->trashed())
            <form action="{{ route('users.restore', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore this user?')">
                @csrf
                <button class="btn btn-sm btn-success d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                </button>
            </form>
        @else
            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Soft delete this user?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-warning d-flex align-items-center gap-1">
                    <i class="bi bi-trash"></i> Soft Delete
                </button>
            </form>
        @endif

        <form action="{{ route('users.forceDelete', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this user? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                <i class="bi bi-x-circle"></i> Force Delete
            </button>
        </form>
    </div>
</td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-muted">No generator owners found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
