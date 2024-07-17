@extends('templates.default.views.layouts.default')
@inject('productService', 'App\Services\ProductService')

@section('title', 'تراکنش یافت نشد')

@section('content')
    <div class="container flex flex-col items-center justify-center my-auto">
        <img src="{{ template_asset('assets/img/payment-failed.svg') }}" class="w-24 h-24 mb-4">
        <h2>تراکنش یافت شد</h2>
        <div>تراکنش مورد نظر برای تایید پرداخت یافت نشد.</div>
    </div>
@endsection
