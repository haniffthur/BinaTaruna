@extends('layouts.app')
@section('title', 'Edit Kelas')
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('classes.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left fa-sm"></i> Kembali</a>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            @if ($errors->any())<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form action="{{ route('classes.update', $class->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group"><label>Nama Kelas</label><input type="text" name="name" class="form-control" value="{{ old('name', $class->name) }}" required></div>
                <div class="form-group"><label>Deskripsi</label><textarea name="description" class="form-control">{{ old('description', $class->description) }}</textarea></div>
                <div class="form-group"><label>Harga</label><input type="number" name="price" class="form-control" value="{{ old('price', $class->price) }}" required min="0"></div>
                <button type="submit" class="btn btn-primary">Update Kelas</button>
            </form>
        </div>
    </div>
@endsection