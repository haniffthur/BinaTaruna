@extends('layouts.app')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail User</h1>
        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <!-- User Detail Card -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Informasi Lengkap User</h6>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Nama</dt>
                <dd class="col-sm-9">: {{ $user->name }}</dd>

                <dt class="col-sm-3">Nik</dt>
                <dd class="col-sm-9">: {{ $user->nik }}</dd>

                <dt class="col-sm-3">Username</dt>
                <dd class="col-sm-9">: {{ $user->username }}</dd>

                <dt class="col-sm-3">No. Telepon</dt>
                <dd class="col-sm-9">: {{ $user->no_telp }}</dd>

                <dt class="col-sm-3">Alamat</dt>
                <dd class="col-sm-9">: {{ $user->alamat }}</dd>

                <dt class="col-sm-3">Jenis Kelamin</dt>
                <dd class="col-sm-9">: {{ ucfirst($user->jenis_kelamin) }}</dd>

                <dt class="col-sm-3">Role</dt>
                <dd class="col-sm-9">: {{ ucfirst($user->role) }}</dd>

                <dt class="col-sm-3">Dibuat Pada</dt>
                <dd class="col-sm-9">: {{ optional($user->created_at)->format('d M Y H:i') }}</dd>

                <dt class="col-sm-3">Diperbarui Terakhir</dt>
                <dd class="col-sm-9">: {{ optional($user->updated_at)->format('d M Y H:i') }}</dd>
            </dl>
        </div>
    </div>
@endsection
