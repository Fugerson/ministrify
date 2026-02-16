@extends('public.layout')

@section('title', $church->name)

@section('content')
@foreach($enabledSections as $section)
    @include('public.sections.' . Str::replace('_', '-', $section['id']), ['church' => $church])
@endforeach

@include('public.sections.cta', ['church' => $church])

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
