var checkPaypal = false, formPaypal = false, checkMercado = false, formMercado = false, carrito = null;
$(document).ready(function (){
    getCartProductsView();

    $(document).on('click','.remove-product', function () {
        var current = $(this).parent('.product-controls').find('.cart-count').text();
        var price = $(this).parents('tr').find('.price-product').val();
        current = Number(current) - 1;
        if(current === 0){
            $(this).parents('tr').remove();
            checkRowsProducts();
        }else{
            var total = Number(current) * Number(price);
            $(this).parent('.product-controls').find('.cart-count').text(current);
            $(this).parents('tr').find('.total-row').text(formatterDolar.format(total));
            $(this).parents('tr').find('.total-row-input').val(total);
        }
        calculateTotal();
    });

    $(document).on('click','.add-product', function () {
        var current = $(this).parent('.product-controls').find('.cart-count').text();
        var price = $(this).parents('tr').find('.price-product').val();
        current = Number(current) + 1;
        var total = Number(current) * Number(price);
        $(this).parent('.product-controls').find('.cart-count').text(current);
        $(this).parents('tr').find('.total-row').text(formatterDolar.format(total));
        $(this).parents('tr').find('.total-row-input').val(total);
        calculateTotal();
    });

    $(document).on('click','.btn-remove-product', function () {
        $(this).parents('tr').remove();
        checkRowsProducts();
        calculateTotal();
    });

    $('#remove-all-products').click(function () {
        removeAllProducts();
        $('#table-body').find('tr').remove();
        checkRowsProducts();
    });

    $('#terminosPayPal').change(function () {
        checkPaypal = $(this).is(':checked');
        if (checkPaypal){
            $('#text-terms-paypal').css('display', 'none');
        }else{
            $('#text-terms-paypal').css('display', 'block');
        }
        checkFormPaypal();
    });

    $('#form-paypal input').change(function () {
        formPaypal = true;
        $('#form-paypal input').each(function () {
            if ($(this).val() == ""){
                formPaypal = false;
            }
        });
        if(formPaypal){
            $('#text-input-paypal').css('display', 'none');
        }else{
            $('#text-input-paypal').css('display', 'block');
        }
        checkFormPaypal();
    });

    $('#terminosMP').change(function () {
        checkMercado = $(this).is(':checked');
        if (checkMercado){
            $('#text-terms-mp').css('display', 'none');
        }else{
            $('#text-terms-mp').css('display', 'block');
        }
        checkFormMercado();
    });

    $('#form-mp input').change(function () {
        formMercado = true;
        $('#form-mp input').each(function () {
            if ($(this).val() == ""){
                formMercado = false;
            }
        });
        if(formMercado){
            $('#text-input-mp').css('display', 'none');
        }else{
            $('#text-input-mp').css('display', 'block');
        }
        checkFormMercado();
    });

    $('.btn-modal-paypal').click(function () {
        var session = Cookies.get('session');
        var parameters = [];
        parameters['url'] = "api/cart/products";
        parameters['type'] = "get";
        parameters['dataType'] = "json";
        parameters['data'] = {
            sessionCookie: session
        };
        parameters['success'] = function (response) {
            let products = [];
            let total = 0;
            response = JSON.parse(response);
            $.each(response.cart, function (i,product) {
                let price = product.newPrice;
                price = Number((price).toFixed(2));
                products.push({
                    name: product.name,
                    unit_amount: {
                        currency_code: "MXN",
                        value: price
                    },
                    quantity: product.cartCount
                });
                total += price * product.cartCount;
            });
            if(total < 3000){
                total += 300;
                products.push({
                    name: "Costo de envio",
                    unit_amount: {
                        currency_code: "MXN",
                        value: "300.00"
                    },
                    quantity: "1"
                });
            }
            createPaypalButton(products, total);
        };
        ajaxCall(parameters);
    });

    $('#btn-mercado-pago').click(function () {
        var session = Cookies.get('session');
        var parameters = [];
        var form = JSON.stringify({
            firstName: $('#name-mp').val(),
            lastName: $('#lastname-mp').val(),
            email: $('#email-mp').val(),
            phone: $('#phone-mp').val(),
            state: $('#state-mp').val(),
            city: $('#city-mp').val(),
            zip: $('#zip-mp').val(),
            suburb: $('#suburb-mp').val(),
            address: $('#address-mp').val()
        });
        parameters['url'] = "api/checkout/mercadopago";
        parameters['type'] = "post";
        parameters['dataType'] = "json";
        parameters['data'] = {
            sessionCookie: session,
            form: form
        };
        parameters['success'] = function (response) {
            console.log(response);
            if(response.state == "success"){
                window.location = response.data;
            }else{
                alert("Ocurrio un error al generar el link de pago, comunicate con nostros para más información");
            }
        };
        ajaxCall(parameters);
    });

});

function checkFormPaypal() {
    if(formPaypal && checkPaypal){
        $('#form-incomplete').css('display','none');
        $('#form-complete').css('display','block');
    }else{
        $('#form-incomplete').css('display','block');
        $('#form-complete').css('display','none');
    }
}

function checkFormMercado() {
    if(formMercado && checkMercado){
        $('#form-incomplete-mp').css('display','none');
        $('#form-complete-mp').css('display','block');
    }else{
        $('#form-incomplete-mp').css('display','block');
        $('#form-complete-mp').css('display','none');
    }
}

function checkRowsProducts() {
    if(!$('#table-body').find('tr').length > 0){
        $('#cart-content').removeClass('d-lg-block').addClass('d-none');
        $('#no-cart-content').removeClass('d-none').addClass('d-block');
    }
}

function calculateTotal() {
    var total = 0;
    $('.total-row-input').each(function () {
        total = Number(total) + Number($(this).val());
    });
    $('#total-final').text(formatterDolar.format(total));
}

function getCartProductsView(){
    var session = Cookies.get('session');
    if(session !== undefined && session !==''){
        var parameters = [];
        parameters['url'] = "api/cart/products";
        parameters['type'] = "get";
        parameters['dataType'] = "json";
        parameters['data'] = {
            sessionCookie: session
        };
        parameters['success'] = function (response) {
            response = JSON.parse(response);

            $.each(response.cart, function (i, e) {
                var item = '<tr>' +
                    '<td><img style="width: 80px;height: 80px;"' +
                    '                     src="'+e.images[0].medium+'">' +
                    '</td>' +
                    '<td data-title="Nombre"><a style="font-weight: 500;color: #000000" ' +
                    'href="catalogo/'+e.brand.toLowerCase()+'/'+e.productType.toLowerCase()+'-'+e.brand.toLowerCase()+'-'+e.mpn.toLowerCase()+'">'+e.name+'</a></td>' +
                    '<td data-title="Precio"> '+formatterDolar.format(e.newPrice)+' <input class="price-product" type="hidden" value="'+e.newPrice+'"></td>' +
                    '<td data-title="Cantidad">' +
                    '    <div class="product-controls">' +
                    '        <input type="hidden" class="inventory" value="'+e.inventory+'">' +
                    '        <button class="btn remove-product" ' +
                    'onclick="decreaseCartProduct(\''+e.productType+'\', \''+e.brand+'\', \''+e.mpn+'\', -1)"><i class="material-icons">remove</i></button>' +
                    '        <span class="cart-count">'+e.cartCount+'</span>' +
                    '        <button class="btn add-product" ' +
                    'onclick="addCartProduct(\''+e.productType+'\', \''+e.brand+'\', \''+e.mpn+'\', 1)"><i class="material-icons">add</i></button>' +
                    '    </div>' +
                    '</td>' +
                    '<td data-title="Total"><span class="total-row">'+formatterDolar.format((e.cartCount * e.newPrice))+'</span> <input class="total-row-input" type="hidden" value="'+(e.cartCount * e.newPrice)+'"></td>' +
                    '<td data-title="          " class="text-center">' +
                    '    <button title="Borrar" class="btn btn-remove-product btn-secondary btn-circle"' +
                    'onclick="removeCartProduct(\''+e.name+'\')">X</button>' +
                    '</td>' +
                    '</tr>';
                $('#table-body').append(item);
            });
            checkRowsProducts();
            calculateTotal();
        };
        ajaxCall(parameters);
    }
}

function createPaypalButton(products, total) {
    paypal.Buttons({
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [
                    {
                        reference_id: "PAGO-JD",
                        description: "Compra en linea JarDepot",
                        soft_descriptor: "Compra en JarDepot",
                        amount: {
                            currency_code: "MXN",
                            value: total,
                            breakdown: {
                                item_total: {
                                    currency_code: "MXN",
                                    value: total
                                }
                            }
                        },
                        items:products
                    }
                ]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                const name = details.purchase_units[0].shipping.name.full_name;
                const address = Object.values(details.purchase_units[0].shipping.address).toString();
                const clientForm = JSON.stringify({
                    firstName:$('#name-paypal').val(),
                    lastName:$('#lastname-paypal').val(),
                    email:$('#email-paypal').val(),
                    phone:$('#phone-paypal').val()
                });
                const session = Cookies.get('session');
                window.location = 'confirmacion/paypal/'+data.orderID+'?form='+clientForm+'&address='+address+'&name='+name+'&session='+session;
                return true;
            });
        }
    }).render('#paypal-button-container');
}
