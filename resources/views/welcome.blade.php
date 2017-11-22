<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
</head>

<body screen_capture_injected="true">
    <div id="main">
        <div class="spc-container">
            <h1 style="color:#17526e;"> Pizza Order </h1>
            <form id="quoteform" class="fixed-total simple-price-calc">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <h2> Product Type</h2>
                <fieldset>
                    <legend>Personal Information</legend>
                    <h4>Name: </h4>
                    <input name="name" type="text" id="txtName" data-label="Name">
                    <h4>Email: </h4>
                    <input name="email"  type="text" id="txtEmail" data-label="Email">
                    <h4>Phone: </h4>
                    <input name="phone"  type="text" id="txtPhone" data-label="Phone">

                    <h4>(Google Address - Australia only)</h4>
                    <h4>Street Address:</h4>
                    <input type="text" id="txtStreet" value="Your Address">
                    <h4>Suburb</h4>
                    <input type="text" id="txtSuburb" value="Your Address">
                    <h4>State</h4>
                    <input type="text" id="txtState" value="Your Address">
                    <h4>Postcode</h4>
                    <p>
                        <input type="text" id="txtPostcode" value="Your Address">
                    </p>
                    <p>&nbsp; </p>
                </fieldset>
                <fieldset>
                    <legend>Product Information</legend>
                    <select data-total="0" id="ddlProduct">
                        <option> Choose a Type </option>
                        <option data-label="Veg Pizza" value="19.95">Veg Pizza ($19.95)</option>
                        <option data-label="Italian Pizza" value="23.95">Italian Pizza ($23.95)</option>
                        <option data-label="Supreme Pizza" value="29.95">Supreme Pizza ($29.95)</option>
                    </select>
                    <br>
                    <h2> Pizza Upgrades </h2>
                    <input type="checkbox" name="feat1" id="chkDoubleCheese" value="10" data-label="Feature 1" data-total="0"> Double Cheese ($10)
                    <input type="checkbox" name="feat2" id="chkDoubleVeggies" value="15" data-label="Feature 2" data-total="0"> Double the veggies ($15)
                    <input type="checkbox" name="feat3" id="chkExtraSauce" value="5" data-label="Feature 3" data-total="0"> Extra Peri Peri Sauce ($5)
                    <br>
                    <h4>Delivery or Pickup</h4>
                    <input type="radio" name="css" id="radPickup" value="0" data-label="Option Declined" data-total="0" checked="checked"> Pickup ($0)
                    <input type="radio" name="css" id="radDelivered" value="10" data-label="Option Selected" data-total="0"> Delivered ($10)
                    <br>
                    <h2>Quantity</h2>
                    <input type="text" id="txtQuantity" data-label="Quantity">
                    <br>
                    <button id="submit" onclick="return false;">Submit</button>
                </fieldset>
                <div id="sidebar">
                    <div id="simple-price-total">
                        <h3 style="margin:0;">Total: </h3>
                        <label id="simple-price-total-num"> $0.00 </label>
                    </div>
                    <div id="simple-price-details">
                        <h3>Order Details:</h3>
                        <p id="parOrderDetails"></p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script>
    var totalPrice = 0;
    var orderDetails = new Array()

    $(document).ready(function() {
        //set initial state.

        

        var previousProduct;
        var previousPrice;
        $('#ddlProduct').focus(function () {
            // Store the current value on focus, before it changes
            var productName = $('select option:selected').data('label')
            var price = parseInt($('select option:selected').val());
            
        }).change(function() {
            // Do soomething with the previous value after the change

            if(previousProduct != undefined)
            {
                delete orderDetails[previousProduct + previousPrice];
            }

            var productName = $('select option:selected').data('label')
            var price = parseInt($('select option:selected').val());
            if(productName != undefined)
            {
                var data = {description : productName, price : price}
                orderDetails[productName + price] = data

                previousProduct = productName;
                previousPrice = price;
            }

            updateInfo()
        });
        
        $('#chkDoubleCheese').change(function() {
            var price = parseInt($('#chkDoubleCheese').val())
            var description = "Double Cheese"
            if(this.checked) 
            {
                var data = {description : description, price : price}
                orderDetails[description + price] = data
            }
            else
            {
                delete orderDetails[description + price];
            }  
            updateInfo()
        });

        $('#chkDoubleVeggies').change(function() {
            var price = parseInt($('#chkDoubleVeggies').val());
            var description = "Double the veggies"
            
            if(this.checked) 
            {
                var data = {description : description, price : price}
                orderDetails[description + price] = data
            }
            else
            {
                delete orderDetails[description + price];
            }  
            updateInfo()
        });

        $('#chkExtraSauce').change(function() {
            var price = parseInt($('#chkExtraSauce').val());
            var description = "Extra Peri Peri Sauce"
            
            if(this.checked) 
            {
                var data = {description : description, price : price}
                orderDetails[description + price] = data
            }
            else
            {
                delete orderDetails[description + price];
            }  
            updateInfo()
        });



        deliveryPrice = parseInt($('#radDelivered').val());
        deliveryDescription = "Delivered"
        
        pickupPrice = parseInt($('#radPickup').val());
        pickupDescription = "Pickup"
        
        $('#radPickup').change(function() {
            var data = {description : pickupDescription, price : pickupPrice}
            orderDetails[pickupDescription + pickupPrice] = data

            delete orderDetails[deliveryDescription + deliveryPrice];
            updateInfo()    
        });

        $('#radDelivered').change(function() {
            var data = {description : deliveryDescription, price : deliveryPrice}
            orderDetails[deliveryDescription + deliveryPrice] = data

            delete orderDetails[pickupDescription + pickupPrice];
            updateInfo()   
        });

        $("#txtQuantity").keyup(function()
        {
            if($.isNumeric(this.value))
            {
                $("#simple-price-total-num").text("$" + getselectedPrice()  * parseInt(this.value));
            }
        });

        $('#submit').click(function () {
            var token = $("input[name='_token']").val();

            var name = $("#txtName").val();
            var email = $("#txtEmail").val();
            var phone = $("#txtPhone").val();
            var street = $("#txtStreet").val();
            var suburb = $("#txtSuburb").val();
            var state = $("#txtState").val();
            var postcode = $("#txtPostcode").val();
            var product = $('#ddlProduct :selected').data('label')

            var doubleCheese = $('#chkDoubleCheese').is(":checked")
            var doubleVeggies = $('#chkDoubleVeggies').is(":checked")
            var extraSauce = $('#chkExtraSauce').is(":checked")

            var val = $('input[name=css]:checked').val();
                
            
            //console.log(getFormDataToObject($('#quoteform')))
            $.ajax({
                url: "{{ route('store') }}",
                method: 'POST',
                data: { _token: token, name: name, email: email, phone: phone, street: street, suburb: suburb, state: state, 
                        postcode: postcode, product: product, doubleCheese: doubleCheese , doubleVeggies: doubleVeggies, 
                        extraSauce: extraSauce, extraSauce: extraSauce},
                success: function (data) {
                    
                }
            });
        });
    });

    function updateInfo()
    {
        totalPrice = 0
        $("#parOrderDetails").html("")
        Object.keys(orderDetails).forEach(key => {
            var description = orderDetails[key]['description'] + " : $" + orderDetails[key]['price']
            $("#parOrderDetails").append(description + "<br>" ) ;

            totalPrice += parseInt(orderDetails[key]['price'])

            var quantity = $("#txtQuantity").val()
            if(quantity != undefined)
            {
                if($.isNumeric(quantity))
                {
                    totalPrice *= quantity
                }
            }
        });

        $("#simple-price-total-num").text("$" + totalPrice);
    }

    function getselectedPrice()
    {
        var total = 0
        Object.keys(orderDetails).forEach(key => {
            total += parseInt(orderDetails[key]['price'])
        });

        return total;
    }

    // Converting form data into array
    function getFormDataToObject(form){
        var un_array = form.serializeArray();
        var _array = {};
        $.map(un_array, function(n, i){
            if(n.name.indexOf('[') > -1 ){
                var array = n.name.match(/\[(.*?)\]/);
                var key = n.name.replace(array[1],"").replace('[',"").replace(']',"");
                if(!_array[key]){
                    _array[key] = {};
                }
                _array[key][array[1]] = n['value'];
            }else{
                _array[n['name']] = n['value'];
            }
        });
        return _array;
    }
</script>

</html>