@extends('layouts.bms-master')

@section('title', 'Quản Trị Vận Hành Tòa Nhà | Office BMS')

@section('sidebar')
  @include('components.sidebars.admin-sidebar')
@endsection

@section('portal-content')
  @include('bms.tabs.dashboard')
@endsection
