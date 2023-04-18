@extends('errors::illustrated-layout')

@section('title', __($exception->getMessage() ?: 'Forbidden'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'Forbidden'))
