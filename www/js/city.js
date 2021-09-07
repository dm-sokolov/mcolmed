$(function() {
  var cities = [
    {'id': 'yekaterinburg', 'name': 'Екатеринбург', 'url': 'https://www.mcolmed.ru/'},
    {'id': 'tagil', 'name': 'Нижний Тагил', 'url': 'https://ntagil.mcolmed.ru'},
    {'id': 'serov', 'name': 'Серов', 'url': 'https://serov.mcolmed.ru'}
  ];

  var default_city_id = 'yekaterinburg';
  var current_city_id = default_city_id;
  var detected_city_id = null;
  var user_selected_city_id = null;
  var is_city_found = false;

  if($('.city_select_window').length) {
    $('.city_select_window').click(function() {
      $('.city_overlay').show();
      $('.city_window').show();

      var cities_element = $('.city_window > .cities > ul');
      cities_element.empty();
      var city_change = $('.city_change');
      city_change.unbind();
      $.each(cities, function(key, value) {
        cities_element.append('<li><a href="#" class="city_change">' + value['name'] + '</a></li>');
      });

      $('.city_change').click(function(element) {
        var clicked_city_name = $(element.target).text();

        $.each(cities, function(key, value) {
          if(clicked_city_name == value['name']) {
            $.cookie('city', value['id'], {expires: 180, domain: 'mcolmed.ru'});
            window.location.assign(value['url']);
          }
        });

        return false;
      });

      return false;
    });
  }
  $('.city_overlay').click(function() {
    $('.city_window').hide();
    $('.city_overlay').hide();
  });
  $('.city_window > .close > a').click(function() {
    $('.city_window').hide();
    $('.city_overlay').hide();

    return false;
  });

  if($.cookie('city') != undefined) {
    user_selected_city_id = $.cookie('city');
  }

  $.getJSON('/ipgeobase/city.php').done(function(data) {
    var responce = data;

    if(responce['city'] != null) {
      $.each(cities, function(key, value) {
        if(value['name'] == responce['city']) {
          is_city_found = true;
          detected_city_id = value['id'];
        }
      });
    }

    if(user_selected_city_id == null) {
      if(is_city_found) {
        if(detected_city_id != default_city_id) {
          // $('.city_select_window').click();
        }
      }
    }
  });
});
