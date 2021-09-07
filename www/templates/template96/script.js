var chkMob = window.innerWidth < 580;

$(function() {
$('#form14').validate({focusInvalid:true,errorClass:"input_error"});
	!chkMob && desctopManipulation();
	chkMob && mobileManipulation();

	var navText = ["<i class='fa fa-angle-left'></i>","<i class='fa fa-angle-right'></i>"];
	
$('.service-box-wrap').owlCarousel({
		items:1,
		dots:false,
		autoplay:true,
		autoplayTimeout:3000,
		nav: true,
		navText: navText,
		autoplayHoverPause:true,
		responsive:{
			600:{
				items:3
			},
			600:{
				items:3,
				margin:20
			},
			900: {
				items: 4,
			}
		}
	});
	$('#mainSlider').owlCarousel({
		items:1,
		nav:false,
		loop:true,
		dots: true,
		autoplay:true,
		autoplayTimeout:5000,
		autoplayHoverPause:true,
		lazyLoad: true,
		responsive: {
				900: {
					nav: true,
				navText: navText,
					dost: false
				}
		}
	});
	$('#newsLandingSlider').owlCarousel({
		items:1,
		nav:false,
		loop:true,
		dots: true,
		autoplay:false,
		autoplayTimeout:5000,
		autoplayHoverPause:true,
		lazyLoad: true,
		responsive: {
			900: {
				nav: true,
				navText : ["<div class=\"news-detail-slider__prev\"></div>","<div class=\"news-detail-slider__next\"></div>"],
			}
		}
	});
	$('#reviewSlider').owlCarousel({
		items:3,
		dots:false,
		autoplay:true,
		autoplayTimeout:3000,
		nav: true,
		navText: navText,
		autoplayHoverPause:true,
		responsive:{
			0:{
				items:1
			},
			600:{
				items:3,
				margin:20
			}
		}
	});
	$('#licenseSlider').owlCarousel({
		loop:false,
		dots:false,
		lazyLoad:true,
		navText: navText,
		nav:true,
		responsive:{
			0:{
				items:1
			},
			600:{
				items:5,
				margin:20,
				nav:true
			}
		}
	});
	$('#specialistSlider').owlCarousel({
		dots:false,
		navText: navText,
		responsive:{
			0:{
				items:1,
				loop:true
			},
			600:{
				items:2,
				margin:30,
				nav:true
			}
		}
	});
	$('#videoSlider').owlCarousel({
		loop:true,
		dots: true,
		video:true,
		lazyLoad:true,
		center:true,
		navText: navText,
		responsive:{
			0:{
				items:1
			},
			600:{
				items:1,
				margin:30,
				nav:true,
			}
		}
	});

       if($('.card-header').length) {
         $('.card-header').each(function() {
	         $(this).click(function() {
		         $(this).next('.card-body').toggle();
           $(this).toggleClass("open")
	         })
           
          })
        }

	$.extend({
		showXslTemplate: function(path, formId, xslid, d, docname) {
			$(".modal, .modal-backdrop").remove();

			path += '?getForm=' + formId + '&xsl=' + xslid;

			if(typeof d != 'undefined' && d !='')
			{
				path += '&' + d;
			}
                        if(typeof docname != 'undefined' && docname !='')
			{
				path += '&docname=' + docname;
			}
			$.clientRequest({path: path, 'callBack': $.showXslTemplateCallback, context: ''});

			return false;
		},
		showXslTemplateCallback: function(data, status, jqXHR) {
			$.loadingScreen('hide');
			$("body").append(data.html);
			$(".modal").modal("show");
		},
		sendForm: function(path, jObject, formId, xslid, d) {
			path += '?getForm=' + formId + '&xsl=' + xslid;

			if(typeof d != 'undefined' && d !='')
			{
				path += '&' + d;
			}

			$.clientRequest({path: path, 'callBack': $.sendFormCallback, context: jObject});

			return false;
		},
		sendFormCallback: function(data, status, jqXHR) {
			$.loadingScreen('hide');
			$(this).html(data.html);
		}
	});
	$(document).on("click", function (event) {
		$("#city-select").prop("checked", false)
		$(".city-label").removeClass("open")
	})
	if ($("#city-select")) {
		var input = $("#city-select");
		var links = $(".select__link")
		$("#city-select").prop("checked", false)
		$("#city-select").click(function(e) {
			e.stopPropagation();
			
		})
		$(".city-label").click(function(e) {
			e.stopPropagation();
			$(this).toggleClass("open")
			
		})
		links.each(function() {
			if (input.val() == $(this).attr('data-link')) {
				$(this).hide()

			}
			if ($(this).attr("data-link") == input.attr("value")) {
				$(".city-label").text($(this).attr("value"))
				
			}
		})
		
	}
	$(".close").each(function() {
	$(this).click(function () {
	$('body').toggleClass("overflow-hidden")
		$(".close").each(function() {
			$(this).hide()
		})

	})
}) 
$(".burger").each(function() {
	$(this).click(function () {
	$('body').toggleClass("overflow-hidden")
		$(".close").each(function() {
			$(this).show()
		})
	})
}) 
$("#js-description").click(function(e) {
	e.preventDefault();
	$("#js-description").hide()
	$(".mobile-description").removeClass("mobile-description");


})
	$(".select__link").each(function() {
		$(this).attr("href", 'https://' + $(this).attr('data-link'))
	$(this).click(function(e) {
	e.preventDefault()
		location.href = 'https://' + $(this).attr('data-link');
	})
	})
$('.nav-item.dropdown').each(function() {
	$(this).on('click', function(){
		$(".dropdown.show").find(".dropdown-toggle").each(function(){
			$(this).click(function() {
				location.href =  $(this).attr('href');
			})
		})
		
	});	
})
	
															
	if($('.js-contacts-map').length > 0){
		if(typeof(ymaps) !== "undefined") {
			ymaps.ready(сontactsMap);
		}
	}

});

function сontactsMap() {
	var $myMap = new ymaps.Map('contactsMap', {
		center: [55.76, 37.64],
		zoom: 10,
		controls: ['smallMapDefaultSet']
	}, {
		minZoom: 5,
		maxZoom: 15,
	});

	var $myCol = new ymaps.GeoObjectCollection();
	$('.js-contacts-address').each(function(i, el){
		ymaps.geocode($(el).attr('data-address'), {
			results: 1
		}).then(function (res) {
			var coords = res.geoObjects.get(0).geometry.getCoordinates();
			$(el).attr('href', 'yandexnavi://build_route_on_map?lat_to=' + coords[0] + '&lon_to=' + coords[1])
			var balloonContent = res.geoObjects.get(0).properties.get('balloonContent');
			var $placemark = new ymaps.Placemark(coords, {balloonContentBody: balloonContent});
			$myCol.add($placemark);
			$myMap.geoObjects.add($myCol);
			$myMap.setBounds($myCol.getBounds(), {
				checkZoomRange: false
			});
		});
	});
}


function desctopManipulation()
{
	
}
function mobileManipulation()
{

}