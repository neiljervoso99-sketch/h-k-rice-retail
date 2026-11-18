@extends('admin.layouts.app')

@section('title', 'Add Supplier')

@section('content')
<style>
    .supplier-form .field { margin-bottom: 14px; }
</style>
<div class="page-heading">
    <h1>Add Supplier</h1>
    <a class="button" href="{{ route('admin.suppliers.index') }}">Back to Suppliers</a>
</div>

@if ($errors->any())
    <div class="notice error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('admin.suppliers.store') }}" class="panel supplier-form">
    @csrf
    @include('admin.suppliers.form')
    <button class="button" type="submit">Create Supplier</button>
</form>
@endsection
