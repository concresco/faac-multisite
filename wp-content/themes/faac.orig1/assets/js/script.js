console.log('[IAKI] ~ script loaded');

// Example starter JavaScript for disabling form submissions if there are invalid fields
(function () {
	'use strict'

	// Fetch all the forms we want to apply custom Bootstrap validation styles to
	var forms = document.querySelectorAll('.needs-validation')

	// Loop over them and prevent submission
	Array.prototype.slice.call(forms)
	.forEach(function (form) {
		form.addEventListener('submit', function (event) {
			if (!form.checkValidity()) {
				event.preventDefault()
				event.stopPropagation()
			}

			form.classList.add('was-validated')
		}, false)
	})
		
		
	// show more product with button click in product lines section homepage
	jQuery('button.show-product-lines').click(function () {
		jQuery('.second-line .product-lines').slideToggle('200ms', 'linear');
		jQuery('.second-line .product-lines').css('display', 'flex');

		if ($('button.show-product-lines').hasClass('active')){
			jQuery('button.show-product-lines').removeClass('active');
		}
		else {
			jQuery('button.show-product-lines').addClass('active');
		}
	});	
		

	// SLIDER HEADER HOMEPAGE
	if (jQuery("#home_slider .swiper-wrapper .swiper-slide").length == 1) {
		var home_swiper = new Swiper("#home_slider", {
			autoplay: false,
			autoHight: false,
			slidePerView: 1,
			loop: false,
			pagination: {
				el: '.swiper-pagination',
				type: 'bullets',
				clickable: true
			},
			navigation: {
				prevEl: ".slider-prev",
				nextEl: ".slider-next",
			}
		});
	} else {
		var home_swiper = new Swiper("#home_slider", {
			autoplay: true,
			speed: 1000,
			autoHight: false,
			slidePerView: 1,
			loop: true,
			pagination: {
				el: '.swiper-pagination',
				type: 'bullets',
				clickable: true
			},
			navigation: {
				prevEl: ".slider-prev",
				nextEl: ".slider-next",
			}
		});
	}


	// SLIDER ULTIMATE NEWS HOMEPAGE
	var news_swiper = new Swiper("#latest_news", {
		autoplay: false,
		autoHeight: false,
		slidePerView: 1,
		loop: true,
		navigation: {
			prevEl: ".slider-prev",
			nextEl: ".slider-next",
		}
	});

	// SLIDER(S) TIMELINE (Any quantity is supported)
	var arr = [];
	jQuery(".timeline").each(function(index, element) {
		// console.log(element);
		var el = jQuery( this );
		var eid = parseInt(el.attr('data-swiper-id'));
		arr.push({
			eid: eid,
			swiper: new Swiper(('#timeline_'+eid+' .swiper'), {
				autoplay: false,
				autoHeight: false,
				slidesPerView: 4,
				loop: false,
				spaceBetween: 0,
				navigation: {
					prevEl: '#timeline_'+eid+' .navigation .slider-prev',
					nextEl: '#timeline_'+eid+' .navigation .slider-next'
				},
				breakpoints: {
					0: {
						slidesPerView: 1.5
					},
					640: {
						slidesPerView: 2.5
					},
					// when window width is >= 1020px
					1020: {
						slidesPerView: 3.5
					}
				}
			})
		})
	});
	jQuery('.year-selection select').on('change', function(e) {
		var value = jQuery( this ).val();
		if (value) {
			var selected = parseInt(value);
			var el = jQuery( this ).closest('.timeline');
			var id = parseInt(el.attr('data-swiper-id'));
			const results = arr.filter(obj => {
				return obj.eid === id;
			});
			results[0].swiper.slideTo(selected);
		}		
	});
	
	// ACTIVITIES SLIDER HOMEPAGE
	var activities_swiper = new Swiper("#activities_slider", {
		autoplay: false,
		autoHight: false,
		breakpoints: {
			// when window width is >= 320px
			320: {
				slidesPerView: 2.5,
				spaceBetween: 8
			},
			// when window width is >= 640px
			640: {
				slidesPerView: 4.5,
				spaceBetween: 12
			},
			// when window width is >= 1020px
			1020: {
				slidesPerView: 6.5,
				spaceBetween: 16
			}
		},
		loop: true,
		navigation: {
			prevEl: ".slider-prev",
			nextEl: ".slider-next"
		}
	});
	
	// SIMPLY SLIDER in BUILDER PAGE
	var simply_swiper = new Swiper("#simply_slider", {
		autoplay: true,
		autoHight: false,
		slidesPerView: 1,
		loop: true,
		navigation: {
			prevEl: ".slider-prev",
			nextEl: ".slider-next"
		}
	});

	// SIMPLY SLIDER in BUILDER PAGE
	if (jQuery("#simply_products_swiper .swiper-wrapper .swiper-slide").length == 1) {
		var simply_products_swiper = new Swiper("#simply_products_swiper", {
			autoplay: false,
			autoHight: false,
			slidesPerView: 1,
			loop: false,
			navigation: {
				prevEl: ".slider-prev",
				nextEl: ".slider-next"
			}
		});
	} else {
		var simply_products_swiper = new Swiper("#simply_products_swiper", {
			autoplay: true,
			autoHight: false,
			slidesPerView: 1,
			loop: true,
			navigation: {
				prevEl: ".slider-prev",
				nextEl: ".slider-next"
			}
		});
	}

	// SLIDER PRODUCT GALLERY
	if (jQuery("#product_gallery .swiper-wrapper .swiper-slide").length == 1) {
		//console.log(jQuery("#product_gallery .swiper-wrapper .swiper-slide").length);
		var product_swiper = new Swiper("#product_gallery", {
			autoplay: false,
			autoHight: false,
			slidePerView: 1,
			loop: false,
			pagination: {
				el: '.swiper-pagination',
				type: 'bullets',
				clickable: true
			},
			navigation: {
				prevEl: ".slider-prev",
				nextEl: ".slider-next",
			}
		});
	} else {
		var product_swiper = new Swiper("#product_gallery", {
			autoplay: true,
			speed: 1000,
			autoHight: false,
			slidePerView: 1,
			loop: true,
			pagination: {
				el: '.swiper-pagination',
				type: 'bullets',
				clickable: true
			},
			navigation: {
				prevEl: ".slider-prev",
				nextEl: ".slider-next",
			}
		});
	}
	
	// VIDEO GALLERY INTO SLIDER
	if (jQuery("#video_gallery .swiper-wrapper .swiper-slide").length == 1) {
		var product_swiper = new Swiper("#video_gallery", {
			autoplay: false,
			autoHight: false,
			slidePerView: 1,
			loop: false,
			pagination: {
				el: '.swiper-pagination',
				type: 'bullets',
				clickable: true
			},
			navigation: {
				prevEl: ".slider-prev",
				nextEl: ".slider-next",
			}
		});
	} else {
		var product_swiper = new Swiper("#video_gallery", {
			autoplay: true,
			speed: 1000,
			autoHight: false,
			slidePerView: 1,
			loop: true,
			pagination: {
				el: '.swiper-pagination',
				type: 'bullets',
				clickable: true
			},
			navigation: {
				prevEl: ".slider-prev",
				nextEl: ".slider-next",
			}
		});
	}

	//FUNCTION TO GET AND AUTO PLAY YOUTUBE VIDEO FROM DATATAG
	$(document).ready(function(){
		
		// var url = $("#videoModal-1iframe").attr('src');
		// if ($("#videoModal-1").is(":hidden")) {
		// 	$("#videoModal-1iframe").attr('src', '');
		// } elseif ($("#videoModal-1").hasClass('show', function() {
		// 	$("#videoModal-1iframe").attr('src', url);
		// }));

		// var url = $("#videoModal-2iframe").attr('src');
		// if ($("#videoModal-2").is(":hidden")) {
		// 	$("#videoModal-2iframe").attr('src', '');
		// } else {
		// 	$("#videoModal-2iframe").attr('src', url);

		// }
		// if ($("#videoModal-2").on('.modal.show')){

		// } else {
		// 	$("#videoModal-2iframe").attr('src', '');
		// }
		
	});

	// $("#videoModal-2").on('hidden.modal', function() {
	// 	$("#videoModal-2iframe").attr('src', '');
	// });
	// $("#videoModal-2").on('show.modal', function() {
	// 	$("#videoModal-2iframe").attr('src', url);
	// });
  	// end video gallery


	// ******************************************
	// ADD READ MORE BUTTON IN TEXT AREA TRUNCATE
	//
	jQuery(function ($) {
	function AddReadMore() {
		//This limit you can set after how much characters you want to show Read More.
		var carLmt = 450;
		// Text to show when text is collapsed - NOTE: Get from Helpers.php
		// var readMoreTxt = "Show more";
		// Text to show when text is expanded - NOTE: Get from Helpers.php
		// var readLessTxt = "Show less";

		//Traverse all selectors with this class and manupulate HTML part to show Read More
		$(".add-read-more").each(function () {
			if ($(this).find(".first-section").length)
				return;

			var point = '<span class="point">...</span>';
			//var allstr = $(this).text();
			var allstr = $(this).html();
			console.log(allstr.length+' '+carLmt);
			/* if (allstr.length > carLmt) {
				var firstSet = allstr.substring(0, carLmt);
				console.log(firstSet);
				var secdHalf = allstr.substring(carLmt, allstr.length);
				var strtoadd = firstSet + point + "<span class='second-section'>" + secdHalf + "</span><br><span class='read-more-btn icon-down'  title='" + titleReadMoreTxt + "'>" + readMoreTxt + "</span><span class='read-less-btn icon-top' title='" + titleReadLessTxt + "'>" + readLessTxt + "</span>";
				$(this).html(strtoadd);
			} */
			if (allstr.length > carLmt) {
				var regx = new RegExp(/(<[^>]*>)/g);
				var counter = 0;
				var strArray = allstr.split(regx);

				for (var i = 0, len = strArray.length; i < len; i++) {
					//ignore the array elements that is HTML tags
					if ( !(regx.test(strArray[i])) ) {
					//if the counter is 100, remove this element with text
						if (counter == carLmt) {
						strArray.splice(i, 1);
						continue; //ignore next commands and continue the for loop
					}
					//if the counter != 100, increase the counter with this element length
					counter = counter + strArray[i].length;
					//if is over 100, slice the text of this element to match the total of 100 chars and set the counter to 100
					if (counter > carLmt) {
						var diff = counter - carLmt;
						strArray[i] = strArray[i].slice(0, -diff);
						counter = carLmt;
					}
				}
				}
				//new string from the array
				var new_string = strArray.join('');
				//remove empty html tags from the array
				new_string = new_string.replace(/(<(?!\/)[^>]+>)+(<\/[^>]+>)/g, "");


				var firstSet = new_string;
				var secdHalf = allstr;
				//var strtoadd = firstSet + point + "<span class='second-section'>" + secdHalf + "</span><br><span class='read-more-btn icon-down'  title='" + titleReadMoreTxt + "'>" + readMoreTxt + "</span><span class='read-less-btn icon-top' title='" + titleReadLessTxt + "'>" + readLessTxt + "</span>";
				var strtoadd = "<div class='first-section'>" + firstSet + point + "</div><div class='second-section'>" + secdHalf + "</div><br><span class='read-more-btn icon-down'  title='" + titleReadMoreTxt + "'>" + readMoreTxt + "</span><span class='read-less-btn icon-top' title='" + titleReadLessTxt + "'>" + readLessTxt + "</span>";
				
				$(this).html(strtoadd);
				
			}
		});

		//Read More and Read Less Click Event binding
		$(document).on("click", ".read-more-btn", function () {
			$('.second-section').show();
			$('.first-section').hide();
		});
		$(document).on("click", ".read-less-btn", function () {
			$('.second-section').hide();
			$('.first-section').show();
		});
		$(document).on("click", ".read-more-btn,.read-less-btn", function () {
			$(this).closest(".add-read-more").toggleClass("show-less-content show-more-content");
		});
	}

	AddReadMore();
	});
	
	// function btn_toggle(){
	// 	if	( jQuery(".cat-btn-wrapper button.btn-filter-toggle").attr("aria-expanded", "true")){
	// 		jQuery( ".cat-btn-wrapper a.cat-link" ).addClass( "show" );
	// 	} else {
	// 		jQuery( ".cat-btn-wrapper a.cat-link" ).removeClass( "show" );
	// 	}
	// };

})()

