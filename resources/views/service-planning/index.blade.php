@extends('layouts.app')

@section('title', __('app.service_planning'))

@section('content')
@include('service-planning._matrix')
@endsection
