$(document).ready(function() {

    $('#carouselExample').on('slide.bs.carousel', function (e) {


        var $e = $(e.relatedTarget);
        var idx = $e.index();
        var itemsPerSlide = 4;
        var totalItems = $('.carousel-item').length;

        if (idx >= totalItems-(itemsPerSlide-1)) {
            var it = itemsPerSlide - (totalItems - idx);
            for (var i=0; i<it; i++) {
                // append slides to end
                if (e.direction=="left") {
                    $('.carousel-item').eq(i).appendTo('.carousel-inner');
                }
                else {
                    $('.carousel-item').eq(0).appendTo('.carousel-inner');
                }
            }
        }
    });

    $('#carouselExample').carousel({
        interval: 4000
    });

    //Funktion textkeys
    var strVal = $('.descriptionText > p');
    var sp = document.getElementById('sp');

    strVal.each(function(){
        childNodes(this);
    });
    
    function childNodes(node) {
        var next;
        if (node.nodeType === 1) {
            // (Element node)
            if (node = node.firstChild) {
                do {
                    // Recursively call traverseChildNodes
                    // on each child node
                    next = node.nextSibling;
                    childNodes(node);
                } while(node = next);
            }
        } else if (node.nodeType === 3) {
            // (Text node)
            if (/{{SPORTART}}/.test(node.data)) {

                replaceNodeString(node);
            }
        }
    }

    function replaceNodeString(textNode){
        var temp = document.createElement('div');
        var sportart = sp.getAttribute('data-sportart');
        temp.innerHTML = textNode.data.replace(/{{SPORTART}}/g,sportart);

        textNode.parentNode.append(temp.innerHTML);

        textNode.remove(textNode);
    }


    if($('.check-td-empty').each(function(){

        if($(this).html().replace(/\s|&nbsp;/g, '').length === 0){

            $(this).closest("tr").hide();
        }
    }))

    $('#navbarSideButton').on('click', function() {
        $('#navbarSide').addClass('reveal');
    });
    // Open navbarSide when button is clicked
    $('#navbarSideButton').on('click', function() {
        $('#navbarSide').addClass('reveal');
        $('.overlay').show();
    });

    // Close navbarSide when the outside of menu is clicked
    $('.overlay').on('click', function(){
        $('#navbarSide').removeClass('reveal');
        $('.overlay').hide();
    });

    $('.close').on('click', function(){
        $('#navbarSide').removeClass('reveal');
        $('.overlay').hide();
    });

    $('.box').matchHeight();

    $(".img-zoomin").click(function(){
        $(this).find(".active-field").toggleClass("invisible visible");
        var providercheck = $(this).find('.providercheck');

        if ($(".visible")[0]){
            $('.disabledSporttype').toggleClass("disabledSporttype able");
        }else{
            $('.able').toggleClass("able disabledSporttype");
        }

        if($(this).find(".active-field").hasClass('visible')){
            providercheck.prop("checked",true);
        }else {
            providercheck.prop("checked", false);
        }

    });

    if ($(".visible")[0]){
        $('.disabledSporttype').toggleClass("disabledSporttype able");
    }else{
        $('.able').toggleClass("able disabledSporttype");
    }
    
    $("#accordion").collapse('hide');

    $(".accordionButton").click(function(e){
        if(!$(e.target).hasClass('arrow')){
            $(this).toggleClass("accordion-passive accordion-active");
            if($(this).children().is(':checked')){
                $(this).children().attr('checked', false);
            }else{
                $(this).children().attr('checked', true);
            }
        }
    });

    $('.tooltipBtn').click(function(e){
        $(this).tooltip('show');
        e.preventDefault();
    });

    $('.tooltipBtn').hover(function(e){
        $(this).tooltip('show');
    });

    $('.tooltipButton').click(function(e){
        $(this).tooltip('show');
        e.preventDefault();
    });

    $('.tooltipButton').hover(function(e){
        $(this).tooltip('show');
    });

    if(document.getElementById('cookieForm') !== null){
        if($('#my-modal').length > 0) {
            document.getElementById('cookieForm').onsubmit = function () {
                Cookies.set('name', 'isTrue');
            };
        }
    }

    if(document.getElementById('cookieForm') !== null){
        if($('#landing-modal').length > 0) {
            document.getElementById('cookieForm').onsubmit = function () {
                Cookies.set('name', 'isTrue');
            };
        }
    }

    var to;
    if(!Cookies.get('name')){
        if($('#my-modal').length > 0){
            to = setTimeout(function(){
                $('#my-modal').modal({
                    keyboard: false,
                    backdrop: 'static'
                });
                $('#my-modal').modal('show');
            });
        }
    }
    if(!Cookies.get('name')){
        if($('#landing-modal').length > 0){
            to = setTimeout(function(){
                $('#landing-modal').modal({
                    keyboard: false,
                    backdrop: 'static'
                });
                $('#landing-modal').modal('show');
            },8000);
        }
    }

    $(".prov-click").click(function(e){

        var target = $(e.target);

        if(target.hasClass('hiddenButton')){
            return;
        }

        if($(".checked")[0]) {
            $(this).next('.selected-provider').toggleClass("unchecked checked");
            $(this).next('.selected-provider').find('.hiddenButton').css({"pointer-events": "auto"});
        }else{
            $(this).next('.selected-provider').toggleClass("checked unchecked");
            $(this).next('.selected-provider').find('.hiddenButton').css({"pointer-events": "none"});
        }

        var totalPrice = parseFloat($('.totalPrice').text().replace(',', '.'));

        if($(this).next('.selected-provider').hasClass("unchecked")){
            var price = parseFloat($(this).next('.selected-provider').find('.price').text().replace(',', '.'));
            totalPrice = totalPrice + price;
            $('.totalPrice').text($.number(totalPrice,2));
        }

        if($(this).next('.selected-provider').hasClass("checked")){
            $(this).next('.selected-provider').find('.hiddenButton').css({"pointer-events": "none"});
            price = parseFloat($(this).next('.selected-provider').find('.price').text().replace(',', '.'));
            totalPrice = totalPrice - price;
            $('.totalPrice').text($.number(totalPrice,2));
        }

    });

    $(".selected-provider").click(function(event){
        if (!$(event.target).is('.provLink')){
            window.open($(this).find('.provLink').attr('href'));
        }
    });


    //count leagues
    var leagueArray = [];
    $(".league-shortcut").each(function() {
        if (isNaN(leagueArray[$(this).data("league")])) {
            leagueArray[$(this).data("league")] = 0;
        }

        leagueArray[$(this).data("league")]++;
    });

    //add leagues to provider attributes
    $(".selected-provider").each(function(){
        var prov = $(this);
        $(this).find(".league-shortcut").each(function(){
            prov.attr('data-leagues', prov.attr('data-leagues') + ',' + $(this).data("league"));
        });
    });

    var productArray = [];
    $(".selected-provider").each(function(){
        var provider = $(this);
        console.log(provider);
        productArray = [$(this).data("provider")];
        console.log(productArray);
    });



    /*var provArray = [];
    $(".selected-provider").each(function(){
        var provider = $(this);
        var leagueCnt = $(this).find(".league-shortcut").length;

        $(this).find(".league-shortcut").each(function(){
            if(leagueArray[$(this).data("league")] === 1){
                enableProvider(provider);
                provider.addClass('mandatory');
            }else{
                if(leagueArray[$(this).data("league")] > 1 && leagueCnt == 1){
                    disableProvider(provider);
                }
            }
        });

        provArray = [$(this).data("provider")];

        //Sonderfall Kombi-Anbieter
        if(provider.data('combination') === 1 && provider.find('.league-shortcut').length === 1){
            enableProvider(provider);

            var l = provider.find('.league-shortcut').data('league');
            if(leagueArray[l] > 1){
                disableProvider(provider);
            }

        }else if(provider.data('combination') === 1 && provider.find('.league-shortcut').length > 1){
            provider.find(".league-shortcut").each(function(){
                var league = $(this).data('league');

                if(league !== 13 && league !== 103) { //nicht 1. Bundesliga && nicht CL
                    var element = $('.league-shortcut[data-league="' + league + '"]').closest('.selected-provider').not(provider);
                    if(element.data('leagues') == provider.data('leagues')){
                        disableProvider(element);
                    }else{
                        if(!element.hasClass('checked')){
                            enableProvider(element);
                        }
                    }
                }else{
                    enableProvider(provider);
                }
            });
        }

        if(provider.hasClass('mandatory')){
            var leagues = provider.attr('data-leagues').substring(1).split(',');

            for(var i = 0; i < leagues.length; i++){
                var element = $('.league-shortcut[data-league="' + leagues[i] + '"]').closest('.selected-provider').not(provider);

                if(element.length > 0){
                    var elementLeagues = element.attr('data-leagues').substring(1).split(',');

                    var intersection = intersect(leagues, elementLeagues);

                    if(intersection.length == elementLeagues.length){
                        disableProvider(element);
                    }else{
                        enableProvider(element);
                    }
                }
            }
        }

        //Sonderfall Sky und Eurosport beide nur 1. Bundesliga -> beide farbig
        //if(provider.data('provider') === 'Sky Bundesliga' || provider.data('provider') === 'Eurosport'){
        //    if(provider.find('.league-shortcut[data-league="13"]').length === 1){ //1. Bundesliga
        //        provider.addClass('mandatory');
        //        enableProvider(provider);
        //    }
        //  }

        //Sonderfall Sky und Eurosport beide nur 1. Bundesliga -> beide farbig
        //if(provider.data('provider') === 'Sky Bundesliga' || provider.data('provider') === 'DAZN'){
        //    if(provider.find('.league-shortcut[data-league="13"]').length === 1){ //1. Bundesliga
        //        provider.addClass('mandatory');
        //        enableProvider(provider);
        //    }
        //}

        //Sonderfall Sky und DAZN beide nur 1. Bundesliga -> beide farbig
        //if(provider.data('provider') === 'Sky Sport' || provider.data('provider') === 'DAZN'){
        //    if(provider.find('.league-shortcut[data-league="103"]').length === 1){ //CL
        //        provider.addClass('mandatory');
        //        enableProvider(provider);
        //    }
        //}

        //Sonderfall mehrere Anbieter zeigen gleiche Ligen: nur erster farbig, Rest grau
        //var leagues = provider.attr('data-leagues');
        //console.log(leagues);
        //if($('.selected-provider[data-leagues="' + leagues + '"]').length > 1 && leagues !== ',103,13'){
        //    enableProvider($('.selected-provider[data-leagues="' + leagues + '"]:first'));

        //   $('.selected-provider[data-leagues="' + leagues + '"]').slice(1).each(function(){
        //        disableProvider($(this));
        //   });
        //}

        });*/

    setPrice();

    function setPrice(){
        var price = 0;
        var totalPrice = 0;

        $(".selected-provider").each(function(){
            if($(this).hasClass("unchecked")){
                price = parseFloat($(this).find('.price').text().replace(',', '.'));
                totalPrice = totalPrice + price;
            }
        });

        $('.totalPrice').text($.number(totalPrice,2));
    }

    function enableProvider(provider){
        provider.removeClass("checked");
        provider.addClass("unchecked");
        provider.find('.hiddenButton').css({"pointer-events": "auto"});
    }

    function disableProvider(provider){
        if(!provider.hasClass('mandatory')){
            provider.removeClass("unchecked");
            provider.addClass("checked");
            provider.find('.hiddenButton').css({"pointer-events": "none"});
        }
    }

    function intersect(a, b) {
        var t;
        if (b.length > a.length) t = b, b = a, a = t; // indexOf to loop over shorter
        return a.filter(function (e) {
            return b.indexOf(e) > -1;
        });

    }
});
