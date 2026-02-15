@extends('layouts.app')

@section('title', $board->display_name)

@section('content')
    @include('boards._kanban', ['embedded' => false])
@endsection
