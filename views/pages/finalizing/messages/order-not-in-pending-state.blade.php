@extends('templates.default.views.layouts.default')
@inject('productService', 'App\Services\ProductService')

@section('title', 'خطای تایید')

@section('content')
    <div class="container flex flex-col items-center justify-center my-auto">
        <img src="{{ template_asset('assets/img/payment-failed.svg') }}" class="w-24 h-24 mb-4">
        <h2>خطای تایید</h2>
        <div>سفارش مورد نظر در وضعیت انتظار جهت تایید پرداخت قرار ندارد.</div>
    </div>
@endsection
