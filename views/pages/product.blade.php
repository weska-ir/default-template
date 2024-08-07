@extends('templates.default.views.layouts.default')
@inject('productService', 'App\Services\ProductService')
@inject('schemaService', 'App\Services\SchemaService')

@section('title', $product->title)
@section('description', $product->content->summary)

@push('meta-tags')
    <meta name="product_id" content="{{ $product->id }}">
    <meta name="product_name" content="{{ $product->title }}">
    <meta property="og:image" content="{{ $product->image->url['medium'] }}">
    <meta name="product_price" content="{{ $product->final_price }}">
    <meta name="product_old_price" content="{{ $product->price }}">
    <meta name="availability" content="{{ $product->is_quantity_unlimited || $product->quantity > 0 ? 'instock' : 'outofstock' }}">
@endpush

@push('head-scripts')
    <script type="application/ld+json">
        <?php
            echo $schemaService->toJSON([
                '@content' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $product->title,
                'description' => $product->content->summary,
                'mpn' => $product->sku,
                'sku' => $product->sku,
                'image' => $product->images->map(fn ($image) => $image->url['medium']),
                'category' => $product->categories->map(fn ($category) => $category->url),
                'offer' => [
                    '@type' => 'Offer',
                    'availability' => $product->quantity > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                    'price' => $product->final_price,
                    'priceCurrency' => 'IRR',
                ],
            ]);
        ?>
    </script>
    <script type="application/ld+json">
        <?php
            echo $schemaService->toJSON([
                '@content' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $product->categories->map(function ($category, $index) {
                    return [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $category->name,
                        'item' => $category->url,
                    ];
                })
            ]);
        ?>
    </script>
@endpush

@section('raw-content')
    @include('templates.default.views.components.product.breadcrumb')
@endsection

@section('content')
    <div class="container">
        <div class="flex flex-col lg:flex-row lg:space-x-8 rtl:space-x-reverse mt-3">
            <!-- Images -->
            <div class="w-full lg:w-[400px] space-y-3">
                <div class="rounded-lg overflow-hidden">
                    @if ($product->image)
                        <img src="{{ $product->image->url['medium'] }}" data-role="product-big-image" class="w-full" alt="{{ $product->title }}">
                    @else
                        <div class="bg-neutral-100 flex items-center justify-center aspect-square">
                            بدون عکس
                        </div>
                    @endif
                </div>

                @if (! $product->images->isEmpty())
                    <div class="flex border border-neutral-200 p-3 rounded-lg space-x-3 rtl:space-x-reverse overflow-x-auto hide-scrollbar md:show-scrollbar">
                        @foreach ($product->images as $image)
                            <img
                                src="{{ $image->url['thumbnail'] }}"
                                data-original-image-url="{{ $image->url['original'] }}"
                                data-medium-image-url="{{ $image->url['medium'] }}"
                                data-image-id="{{ $image->id }}"
                                data-role="product-tiny-image"
                                class="object-cover w-20 h-20 cursor-pointer" alt="{{ $product->title }}">
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Details -->
            <div class="flex-1 mt-10 lg:mt-0">
                <div class="flex flex-col lg:flex-row justify-center lg:justify-between lg:items-center">
                    <h1 class="text-2xl font-medium">{{ $product->title }}</h1>

                    <div class="flex items-center justify-end space-x-4 space-x-reverse mt-3 lg:mt-0 mr-5">
                        @if ($product->is_shipping_free)
                            <span class="badge bg-red-400 text-white">ارسال رایگان</span>
                        @endif

                        <!-- Like Button -->
                        @include('templates.default.views.components.product.like-button', [
                            'id' => $product->id,
                            'status' => $product->is_liked,
                        ])
                    </div>
                </div>
                <div class="text-neutral-400 text-lg mt-2">
                    <span>کد محصول:</span>
                    <span class="text-neutral-700">{{ $product->sku }}</span>
                </div>
                
                <!-- Brands -->
                @if (! $product->brands->isEmpty())
                    <div class="text-neutral-400 text-lg mt-2">
                        <span>برند:</span>
                        <span>
                            @foreach ($product->brands as $index => $brand)
                                <a class="text-neutral-700" href="{{ route('client.brands.brand', $brand) }}">{{ $brand->name }}</a>@if ($index + 1 < $product->brands->count())<span>،</span>@endif
                            @endforeach
                        </span>
                    </div>
                @endif

                <!-- Description -->
                @if ($product->content->description)
                    <section class="font-light mt-5">
                        <div class="text-neutral-600 [&>ul]:ps-5 [&>ul>li]:list-disc">
                            {!! $product->content->description !!}
                        </div>
                    </section>
                @endif

                <!-- Combinations -->
                @include('templates.default.views.components.product.combinations')

                <!-- Price -->
                <div data-role="price-container" class="mt-8 hidden">
                    <span data-role="final-price" class="h2 text-green-500">{{ number_format($product->final_price) }} {{ productCurrency()->label() }}</span>

                    @if ($product->discount > 0)
                        <span data-role="raw-price" class="h3 line-through text-neutral-400 ms-2">{{ number_format($product->price) }} {{ productCurrency()->label() }}</span>
                    @endif
                </div>

                <div class="mt-7">
                    <!-- Add to cart -->
                    @include('templates.default.views.components.product.add-to-cart')

                    @if ($product->points > 0)
                        <div data-role="points" class="font-light text-sm text-green-700 mt-3 hidden">
                            با خرید این محصول <span class="font-bold">{{ number_format($product->points) }} امتیاز</span> دریافت کنید.
                        </div>
                    @endif

                    @if (! $product->is_quantity_unlimited && settingService('product')['show_quantity']['status'])
                        <div data-role="quantity-container" class="text-danger mt-5 hidden">
                            تنها <span data-role="quantity">{{ $product->quantity }}</span> عدد باقی مانده است.
                        </div>
                    @endif

                    <div data-role="unavailable" class="font-medium text-danger mt-8 hidden">
                        متاسفانه محصول مورد نظر موجود نمی باشد.
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-5">

        <div class="space-y-10">
            <!-- Content Tabs -->
            @include('templates.default.views.components.product.content-tabs')

            <!-- Comments -->
            @include('templates.default.views.components.product.comments')
        </div>

        <!-- Related Products -->
        @include('templates.default.views.components.product.related-products')
    </div>
@endsection

@push('bottom-scripts')
    <link rel="stylesheet" href="{{ template_asset('/assets/packages/swiper/swiper-bundle.min.css') }}" />
    <script src="{{ template_asset('/assets/packages/swiper/swiper-bundle.min.js') }}"></script>
    <script>
        ready(function () {
            selectCombination('{{ $product->combinations[0]->uid }}');
        });

        $('img[data-role="product-tiny-image"]').click(function () {
            const originalUrl = $(this).data('original-image-url');

            $('img[data-role="product-big-image"]').attr('src', originalUrl);
        });

        var swiper = new Swiper("#related-products", {
            slidesPerView: "auto",
            spaceBetween: 20,
            // breakpoints: {
            //     // when window width is >= 320px
            //     420: {
            //         slidesPerView: 2,
            //         spaceBetween: 20
            //     },
            //     // when window width is >= 480px
            //     600: {
            //         slidesPerView: 3,
            //         spaceBetween: 20
            //     },
            //     // when window width is >= 640px
            //     800: {
            //         slidesPerView: 4,
            //         spaceBetween: 20
            //     },
            //     // when window width is >= 640px
            //     1100: {
            //         slidesPerView: 5,
            //         spaceBetween: 20
            //     },
            // },
            freeMode: true,
        });
    </script>
@endpush
