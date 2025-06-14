@extends('layouts.app')
@section('title', 'Tambah Tiket Baru')
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left fa-sm"></i> Kembali</a>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            @if ($errors->any())<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form action="{{ route('tickets.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Nama Tiket</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="form-control">{{ old('description') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="price">Harga</label>
                    <input type="number" id="price" name="price" class="form-control" value="{{ old('price') }}" required min="0">
                </div>
                <button type="submit" class="btn btn-primary">Simpan Tiket</button>
            </form>
        </div>
    </div>
@endsection