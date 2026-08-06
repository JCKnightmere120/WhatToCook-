@extends('admin.layout')

@section('title', 'Edit recipe')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit recipe</h1>
            <p class="subheading">{{ $recipe->name }}</p>
        </div>
        <a class="button secondary" href="{{ route('admin.recipes.index') }}">Back to recipes</a>
    </div>

    @include('admin.recipes.form', ['action' => route('admin.recipes.update', $recipe), 'method' => 'PUT', 'submitLabel' => 'Save changes'])
@endsection
