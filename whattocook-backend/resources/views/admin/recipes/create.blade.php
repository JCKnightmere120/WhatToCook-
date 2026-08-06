@extends('admin.layout')

@section('title', 'Add recipe')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Add recipe</h1>
            <p class="subheading">Recipes saved here will become available in the Ionic app.</p>
        </div>
        <a class="button secondary" href="{{ route('admin.recipes.index') }}">Back to recipes</a>
    </div>

    @include('admin.recipes.form', ['action' => route('admin.recipes.store'), 'method' => 'POST', 'submitLabel' => 'Create recipe'])
@endsection
