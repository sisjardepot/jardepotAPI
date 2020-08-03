@extends('pages')


@section('metaData')
    <title>Products</title>
    {{--<meta title="{{ ucfirst($titleweb)}}"/>
    <meta name="description" content="{{$metadesc}}">
    <meta name="keywords" content="{{$keywords}}">--}}
@endsection

@section('specificCSS')
    <!-- Page CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/pages/products.css')}}">
    <!-- Components CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/components/sidebar.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/components/breadcrumb.css')}}">
    <!-- Scrollbar Custom CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/components/jquery.mCustomScrollbar.min.css')}}">
@endsection

@section('content')
    @component('components.breadcrumb')
    @endcomponent

    <div class="wrapper">
        <!-- Sidebar  -->
        <nav id="sidebar">
            @component('components.sidebar')
                @slot('id', 'Movil')
                @slot('sections', $sidebar)
            @endcomponent
        </nav>

        <!-- Page Content  -->
        <div id="content" class="row">
            <div class="border shadow bg-white rounded d-none d-lg-block col-lg-3 p-0" style="max-width: 21%">
                @component('components.sidebar')
                    @slot('id', 'Desktop')
                    @slot('sections', $sidebar)
                @endcomponent
            </div>

            <div id="content-products-principal" class="col-lg-10 col-md-12" style="">
                <div id="list-products-sections">
                    <div class="row border shadow bg-white rounded">
                        <h1 class="m-2 text-muted" style="font-size: 28px;">Aspersoras</h1>
                    </div>
                    <div class="row border shadow rounded bg-dark my-2 text-white px-2 d-flex justify-content-between align-items-center">
                        <div class="d-flex justify-content-center align-items-center">
                            <p>Ordenar por:</p>
                            <select class="form-control">
                                <option value="relevancia">Relevancia</option>
                                <option value="menor-precio">Menor Precio</option>
                                <option value="mayor-precio">Mayor Precio</option>
                            </select>
                        </div>
                        <button type="button" id="sidebarCollapse" class="btn bg-color-jd btn-sm d-flex align-items-center justify-content-center d-lg-none d-flex">
                            <i class="material-icons mr-2">menu</i>
                            <span>Filtros y secciones</span>
                        </button>
                        <div>
                            <div class="dropdown">
                                <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Mostrar 8
                                </a>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                    <a class="dropdown-item" href="#">8</a>
                                    <a class="dropdown-item" href="#">12</a>
                                    <a class="dropdown-item" href="#">16</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @foreach($products as $key => $item)
                            <div class="card shadow-sm product-item col-md-3 p-0 mt-2" style="border-radius: 5px;overflow: hidden;">
                            <a href="">
                                @if(isset($item['discount']))
                                    <div class="ribbon ribbon-top-right" style="display: block"><span>Oferta</span></div>
                                @endif
                                <div class="product-image" style="height: 205px">
                                    <img style="max-width: 80%"
                                         src="{{asset($item['images'][0]['medium'])}}"
                                         title="{{$item['name']}}" alt="{{$item['name']}}">
                                </div>
                                <img class="free-delivery-recom" src="{{asset('assets/images/otros/gratis.png')}}"
                                     title="Envío gratis Jardepot" alt="Envío gratis Jardepot">
                            </a>
                            <div class="d-flex align-items-center flex-column">
                                <p class="text-muted text-center" style="font-weight: 500; font-size: 18px;">{{$item['name']}}</p>
                                <p class="old-price">{{$item['name']}}</p>
                                <p class="new-price">$3,359.00</p>
                                <button class="btn btn-buy d-flex justify-content-center align-items-center">
                                    <i class="material-icons" style="font-size: 16px;">shopping_cart</i> Comprar
                                </button>
                                <p class="envio-volada d-flex justify-content-center align-items-center my-2">
                                    <i class="material-icons" style="font-size: 16px;">flash_on</i>Envio de volada
                                </p>
                                <p class="little-letters">*Envio gratis a partir de $3,000 de compra</p>
                                <p class="little-letters">*Consulte condiciones.</p>
                                <p class="product-description py-2 text-center" data-toggle="tooltip" data-placement="bottom"
                                   title="Aspersora Agrícola ECHO SHP-800-2, 22.8CC, 25L, 2 Salidas Fumigadora de alta presión de doble lanza para duplicar la eficiencia.">
                                    Aspersora Agrícola ECHO SHP-800-2, 22.8CC, 25L, 2 Salidas Fumigadora de alta presión de do...
                                </p>
                            </div>
                            <hr>
                            <div class="d-flex align-items-center flex-column">
                                <button type="button" class="btn-add-cart d-flex justify-content-center align-items-center" style="font-size: 14px;">
                                    <i class="material-icons fn-color-jd">shopping_cart</i>
                                    <span class="text-muted" style="font-size: 14px; font-weight: 500;">Agregar al carrito</span>
                                </button>
                                <p class="little-letters text-center">*Sujeto a existencias.</p>
                                <p class="little-letters text-center">*Precios sujetos a cambio sin previo aviso.</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div id="list-products-search" class="row">
                </div>
                <div id="form-search" class="d-none">
                    <div class="row border shadow bg-white rounded">
                        <div class="col-12 mb-3 d-flex justify-content-between" style="border-bottom: 1px solid rgba(204,204,204,.6);">
                            <h1 class="m-2 text-muted" style="font-size: 28px;">Buscaste: { { Palabra } } </h1>
                            <button type="button" id="" class="sidebarCollapse btn bg-color-jd btn-sm d-flex align-items-center justify-content-center d-lg-none d-flex">
                                <i class="material-icons mr-2">menu</i>
                                <span>Filtros y secciones</span>
                            </button>
                        </div>
                        <div class="col-6 d-flex flex-column justify-content-center align-items-center">
                            <h3>¿No encontraste tu producto?</h3>
                            <h5>Comunícate al:</h5>
                            <h4><i class="material-icons">phone</i> 800 212 9225</h4>
                            <h4>
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                                     focusable="false" width="20px" height="20px" style="-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);"
                                     preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                                    <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21c5.46 0 9.91-4.45 9.91-9.91c0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.24 8.23c-1.48 0-2.93-.39-4.19-1.15l-.3-.17l-3.12.82l.83-3.04l-.2-.32a8.188 8.188 0 0 1-1.26-4.38c.01-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.16 0-.43.06-.66.31c-.22.25-.87.86-.87 2.07c0 1.22.89 2.39 1 2.56c.14.17 1.76 2.67 4.25 3.73c.59.27 1.05.42 1.41.53c.59.19 1.13.16 1.56.1c.48-.07 1.46-.6 1.67-1.18c.21-.58.21-1.07.15-1.18c-.07-.1-.23-.16-.48-.27c-.25-.14-1.47-.74-1.69-.82c-.23-.08-.37-.12-.56.12c-.16.25-.64.81-.78.97c-.15.17-.29.19-.53.07c-.26-.13-1.06-.39-2-1.23c-.74-.66-1.23-1.47-1.38-1.72c-.12-.24-.01-.39.11-.5c.11-.11.27-.29.37-.44c.13-.14.17-.25.25-.41c.08-.17.04-.31-.02-.43c-.06-.11-.56-1.35-.77-1.84c-.2-.48-.4-.42-.56-.43c-.14 0-.3-.01-.47-.01z" fill="#000"/><rect x="0" y="0" width="24" height="24" fill="rgba(0, 0, 0, 0)" />
                                </svg>
                                55 5185 7805
                            </h4>
                            <h4 class="my-5">Horario de atención: 9am a 6pm</h4>
                        </div>
                        <div class="col-6">
                            <div class="row">
                                <div class="col-12">
                                    <h3>Nosotros te llamamos, ingresa tus datos.</h3>
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control" id="name" placeholder="Nombre Completo*">
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control" id="phone" placeholder="Teléfono *">
                                </div>
                                <div class="col-12 my-3">
                                    <textarea class="form-control" id="coments" placeholder="Comentarios" style="resize: none;"></textarea>
                                </div>
                                <div class="col-12 text-center mb-3">
                                    <button class="btn bg-color-jd" type="button"> Enviar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('components.caruselCanales')
                @include('components.infoCompra')
            </div>
        </div>
    </div>
@endsection

@section('specificJS')
    <div class="overlay"></div>
    <!-- jQuery Custom Scroller CDN -->
    <script src="{{asset('assets/js/components/jquery.mCustomScrollbar.concat.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/pages/products.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/components/sidebar.js')}}"></script>
@endsection