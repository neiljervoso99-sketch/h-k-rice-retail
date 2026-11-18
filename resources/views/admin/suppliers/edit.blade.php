@extends('admin.layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<style>
    .supplier-form .field { margin-bottom: 14px; }
</style>
<div class="page-heading">
    <h1>Edit Supplier</h1>
    <a class="button" href="{{ route('admin.suppliers.index') }}">Back to Suppliers</a>
</div>

@if ($errors->any())
    <div class="notice error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('admin.suppliers.update', $supplier) }}" class="panel supplier-form">
    @csrf
    @method('PUT')
    @include('admin.suppliers.form')
    <button class="button" type="submit">Save Changes</button>
</form>
@endsection
