;( function( $, window, document, undefined ) {

    // The actual plugin constructor
    function StoreFinder ( element, options ) {
        this.element = element;
        this.settings = $.extend({},StoreFinder.Default, options );
        this.initialize();
    }

    StoreFinder.Default = {
        customer : {
            shipping_address : "",
            lat_shipping : "",
            lng_shipping : ""
        },
        stores : [],
        store_products : [],
        productIds : [],
        defaultStore: null,
        selectedStore: null,
        orderby : {
            type: 'qty',
            sort: 'asc'
        },
        displaytype : 'short',
        api_key : "AIzaSyDszlbWP-zggO-J_Pk4go5tdaMIjZUCAio",
        DistanceMatrix : {
            url : "https://maps.googleapis.com/maps/api/distancematrix/json",
            mode : null, //driving||walking||bicycling||transit
            language : null,
            avoid : null, //tolls|highways|ferries|indoor
            units : null, //units=metric || units=imperial
            traffic_model : null, //best_guess (default)||pessimistic ||optimistic
            transit_mode : null //bus||subway||train||tram||rail (mode is transit)
        },
        Geocoding : {
            url : "https://maps.googleapis.com/maps/api/geocode/json"
        },
        MapEmbed :{
            url : "https://www.google.com/maps/embed/v1/directions",
            mode : null, //driving||walking||bicycling||transit
            language : null,
            avoid : null, //tolls|highways|ferries|indoor
            units : null //units=metric || units=imperial
        },
        DisplaySetting :{
            showdescription : true,
            showname : 1,
            showaddress : 1,
            showstocking : 2,
            instock_label : 'In Stock',
            item_label  : 'Items',
            number_item_display : 1,
        },
        mapElement : '#map-embed',
        mapContainer : '#map-container',
        storesFinderDetail : '#stores-finder-details',
        storeDetail : '.store-details',
        storesElement : '.info-stores',
        storeElement : '.info-store',
        storeElementName : 'info-store',
        backToolbar : '.back-toolbar',
        selectedId : null,
        overlayElement : '.map-overlay',
        
    };

    StoreFinder.prototype.initialize = function() {
        var self = this;

        this.initializeLocationCustomer();
        this.initializeStoreEnable();

        setTimeout(
            function () {
                self.selectStore('default');
                //this.showDirectionsToStore();
                //Thiet lap event

                self.showDirectionsToStore();
                self.onListenControlStore();
            },1000
        );
        this.displayMap();


    };

    StoreFinder.prototype.initializeLocationCustomer = function () {
        var self = this;
        if(!this.settings.customer.lat_shipping||!this.settings.customer.lat_shipping){
            var address = this.settings.customer.shipping_address;
            if(address){
                this.setLocationCustomerFromAddress(address);
            }else{
                this.setLocationCustomerFromGeolocation();
            }
        }
    };

    StoreFinder.prototype.setLocationCustomerFromGeolocation = function () {
        var self = this;
        navigator.geolocation.getCurrentPosition(function(position) {
            var geolocation = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            self.settings.customer.lat_shipping = position.coords.latitude;
            self.settings.customer.lng_shipping = position.coords.longitude;
        });
    };

    StoreFinder.prototype.setLocationCustomerFromAddress = function (address) {
        var self = this,
            api_key = this.settings.api_key;
        if(api_key){
            var urlGeocoding = this.settings.Geocoding.url;
            $.ajax({
                url: urlGeocoding,
                data: {
                    address: address,
                    key : api_key
                },
                type: 'GET',
                error: function() {

                },
                success: function(response) {
                    if(response.status=="OK"){
                        var infoAddress = response.results[0];
                        self.settings.customer.shipping_address = infoAddress.formatted_address;
                        self.settings.customer.lat_shipping = infoAddress.geometry.location.lat;
                        self.settings.customer.lng_shipping = infoAddress.geometry.location.lng;
                    }
                }
            });
        }
    };

    StoreFinder.prototype.setLocationCustomer = function (lat,lng) {
        this.settings.customer.lat_shipping = lat;
        this.settings.customer.lng_shipping = lng;
    };

    StoreFinder.prototype.onChangeLocationCustomer = function (lat,lng) {
        this.setLocationCustomer(lat,lng);
        this.showDirectionsToStore();
        var oderby_type = this.settings.orderby.type;
        if(oderby_type == 'distance'){
            this.sortStores();
            this.showStores();
        }
    };

    StoreFinder.prototype.initializeStoreEnable = function () {
        if(this.settings.productIds){
            this.setStoresEnable();
        }
        this.sortStores();
        this.showStores();
        this.setDefaultStore();
    };
    StoreFinder.prototype.setDefaultStore = function () {
        if(this.settings.stores){
            this.settings.defaultStore = this.settings.stores[0];
        }
    };
    StoreFinder.prototype.sortStores = function() {
        var self = this;
        var oderby_type = this.settings.orderby.type;
        if(oderby_type == 'distance'){
            setTimeout(
                function () {
                    self.sortStoresByDistance();
                },300
            );

        }
        if(oderby_type == 'qty'){
            this.sortStoresByQty();
        }

    };

    StoreFinder.prototype.sortStoresByDistance = function () {

        var self = this;
        var urlDistanceMatrix = this.settings.DistanceMatrix.url,
            api_key = this.settings.api_key,
            stores = this.settings.stores,
            origins = this.settings.customer.lat_shipping+','+this.settings.customer.lng_shipping,
            destinations = '';
            count = 0;
        stores.forEach(function(store,index) {
            if(index){
                destinations += '|';
            }
            destinations += store['lat']+','+store['lng'];
        });
        $.ajax({
            url: urlDistanceMatrix,
            data: {
                origins: origins,
                destinations: destinations,
                key : api_key
            },
            type: 'GET',
            error: function() {

            },
            success: function(response) {
                if(response.status=="OK"){
                    results = response.rows[0].elements;
                    results.forEach(function(result,index) {
                        self.settings.stores[index]['distance_text'] = result['distance']['text'];
                        self.settings.stores[index]['distance_value'] = result['distance']['value'];
                    });
                    var sort = self.settings.orderby.sort;
                    var sorted = _.sortBy(self.settings.stores, 'distance_value');
                    if(sort=='desc'){
                        sorted = sorted.reverse();
                    }
                    self.settings.stores = sorted;
                }
            }
        });

    };

    StoreFinder.prototype.sortStoresByQty = function () {
        var sort = this.settings.orderby.sort;
        var sorted = _.sortBy(this.settings.stores, 'available_qty');
        if(sort=='desc'){
            sorted = sorted.reverse();
        }
        this.settings.stores = sorted;

    };

    StoreFinder.prototype.showStores = function () {
        var stores = this.settings.stores;
        var self = this;
        $(self.settings.storesElement).html("");
        stores.forEach(function (store, index) {

            var storeinfo = self.createTemplateStoreList(store['warehouse_id'], store['lat']+","+store['lng'], store['warehouse_name'] ,store['warehouse_address'], store['available_qty'] , store['description']);
            var number_items = self.settings.DisplaySetting.number_item_display;
            if(index < number_items){
                $(self.element).children(self.settings.storesElement).append(storeinfo);
            }
            if(index == number_items){
                $(self.element).children(self.settings.storesElement).append('<a href="#" class="view-mores-stores" > View Mores</a>');
            }
            $(self.settings.storesFinderDetail).find(self.settings.storesElement).append(storeinfo);
        });
    };

    StoreFinder.prototype.createTemplateStoreList = function (id, location, name, address, qty, description ) {
        var storeinfo = "<li class='"+this.settings.storeElementName+"' data-id='"+id+"' data-location='"+location+"' >";
        storeinfo += "<div class='store-info-header'>";
            if(this.settings.DisplaySetting.showstocking) {
                storeinfo += "<div class='info-left'>";
            }
                if(this.settings.DisplaySetting.showname) {
                    storeinfo += "<span class='store-name'>" + name + "</span>";
                }
                if(this.settings.DisplaySetting.showaddress) {
                    storeinfo += "<span class='store-address'>" + address + "</span>";
                }
            if(this.settings.DisplaySetting.showstocking) {
                storeinfo += "</div>";
            }
            if(this.settings.DisplaySetting.showstocking){
                storeinfo += "<div class='info-right'>";
                    if(this.settings.DisplaySetting.showstocking==1){
                        storeinfo += "<span>"+this.settings.DisplaySetting.instock_label+"</span>";
                    }
                    if(this.settings.DisplaySetting.showstocking==2){
                        storeinfo += "<span class='store-available'>"+this.settings.DisplaySetting.instock_label+"</span>";
                        storeinfo += "<span class='store-qty'>"+qty+" "+this.settings.DisplaySetting.item_label+"</span>";
                    }
                storeinfo += "</div>";
            }

        storeinfo += "</div>";
        if(this.settings.DisplaySetting.showdescription && description){
            storeinfo += "<div class='infostore-description'>";
            storeinfo += "<p>"+description+"</p>";
            storeinfo += "</div>";
        }

        storeinfo += "</li>";
        return storeinfo;
    };

    StoreFinder.prototype.selectStore = function(id){
        var stores = this.settings.stores;
        var self = this;
        stores.forEach(function (store) {
            if(store['warehouse_id']==id){
                self.settings.selectedStore = store;
                self.settings.selectedId = id;
                self.createTemplateStoreDetails(store);
            }
        });
    };
    StoreFinder.prototype.createTemplateStoreDetails = function (store) {
        var detailHtml = $(this.settings.storeDetail);
        detailHtml.html("");
        //Button Back List
        detailHtml.append("<span class='back-toolbar'>Back </span>");
        detailHtml.append("<h3>"+store['warehouse_name']+"</h3>");
        detailHtml.append("<h3>"+store['warehouse_address']+"</h3>");

    };
    StoreFinder.prototype.onListenControlStore = function () {
        var self = this;
        $(this.settings.storesElement).on('click', this.settings.storeElement, function () {
            $(self.settings.storesFinderDetail).find(self.settings.storesElement).hide();
            $(self.settings.storesFinderDetail).find(self.settings.storeDetail).show();
            if($(this).data('id')== self.settings.selectedId) {
                return false;
            }
            self.selectStore($(this).data('id'));
            self.showDirectionsToStore();
        });
        $(this.settings.storesFinderDetail).on('click', this.settings.backToolbar, function () {
            $(self.settings.storesFinderDetail).find(self.settings.storesElement).show();
            $(self.settings.storesFinderDetail).find(self.settings.storeDetail).hide();
        });
        $(this.element).on('click', '.view-mores-stores', function () {
            $("body").addClass('modal-map-locked');
            $(self.settings.storesFinderDetail).addClass('active');
            $(self.settings.overlayElement).addClass('active');
        });
        this.settingChangedOptionsProducts();
    };


    StoreFinder.prototype.setStoresEnable = function () {

        var result = _.chain(this.getListWarehousesProducts()).groupBy('warehouse_id')
            .map(function(value, key) {
                return _.reduce(value, function(result, currentObject) {
                    return {
                        warehouse_id : currentObject.warehouse_id,
                        warehouse_name: currentObject.warehouse_name,
                        available_qty:  parseInt(result.available_qty) + parseInt(currentObject.available_qty),
                        warehouse_address: currentObject.street +", "+ currentObject.city,
                        lat : currentObject.lat,
                        lng : currentObject.lng
                    }
                }, {
                    available_qty: 0
                });
            })
            .value();
        this.settings.stores = result;
    };
    StoreFinder.prototype.getListWarehousesProducts = function () {
        var stores = this.settings.store_products;
        var productIds = this.settings.productIds;
        var result = $.grep(stores, function( n, i ) {
            var result = $.inArray(n.product_id, productIds );
            var qty = parseInt(n.available_qty);
            if(result >=0 && qty){
                return true;
            }
            return false;
        });
        return result;
    };

    StoreFinder.prototype.settingChangedOptionsProducts = function () {
        var self = this;
        $('.super-attribute-select').on('change', function (e) {
            var productIds = self.getListProducts();
            self.settings.productIds = productIds;
            if(self.settings.productIds){
                self.setStoresEnable();
            }
            self.sortStores();
            self.showStores();
        });

        $('.configurable-swatch-list li:not(.not-available) a').on( "click",function (e) {
            if($(this).hasClass("not-available"))return false;
            setTimeout(
                function () {
                    $(".super-attribute-select").promise().done(function() {
                        var productIds = self.getListProducts();
                        self.settings.productIds = productIds;
                        if(self.settings.productIds){
                            self.setStoresEnable();
                        }
                        self.sortStores();
                        self.showStores();
                    });
                }, 100
            );
        });

    };

    StoreFinder.prototype.getListProducts = function () {
        var lists = [];
        $('.super-attribute-select').each(function( index, element ) {
            var element = $(this).get(0);
            var config = element.options[element.selectedIndex].config;
            if (typeof config == 'undefined'){
                return false;
            }
            var products = element.options[element.selectedIndex].config.allowedProducts;
            lists.push(products);
        });
        return this.intersectAll(lists);
    };

    StoreFinder.prototype.intersectAll = function (lists) {
        if (lists.length == 0) return [];
        else if (lists.length == 1) return lists[0];
        var result = lists[0];
        for (var i = 1; i < lists.length; i++) {
            if (!result.length) break;
            result = result.intersect(lists[i]);
        }
        return result;
    };


    StoreFinder.prototype.showDirectionsToStore = function () {
        var iframe = $(this.settings.mapElement);
        var origin = "";
        if(this.settings.customer.lat_shipping && this.settings.customer.lng_shipping){
            origin = this.settings.customer.lat_shipping+','+this.settings.customer.lng_shipping;
        }
        var destination = '';
        var store = this.settings.selectedStore;
        if(!store){
            store = this.settings.defaultStore;
        }
        destination = store['lat']+','+store['lng'];
        if(!origin){
            origin = destination;
        }
        iframe.attr( "src", "https://www.google.com/maps/embed/v1/directions?key=AIzaSyDszlbWP-zggO-J_Pk4go5tdaMIjZUCAio&origin="+origin+"&destination="+destination+"&avoid=tolls|highways");
    };
    StoreFinder.prototype.displayMap = function () {
        var self = this;
        $(this.element).children(this.settings.storesElement).on('click', this.settings.storeElement, function () {
            $("body").addClass('modal-map-locked');
            $(self.settings.storesFinderDetail).addClass('active');
            $(self.settings.overlayElement).addClass('active');
        });
        $(self.settings.storesFinderDetail).on('click','.close',function () {
            $("body").removeClass('modal-map-locked');
            $(self.settings.storesFinderDetail).removeClass('active');
            $(self.settings.overlayElement).removeClass('active');
        });
    }
    $.fn['storesfinder'] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, "stores_finder")) {
                $.data(this, "stores_finder", new StoreFinder( this, options ));
            }
        });
    };



} )( jQuery, window, document );