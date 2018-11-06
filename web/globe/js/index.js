$(document).ready(function () {
  var
    numberOfSteps = $('.user-guide p').length,
    width = parseInt($('.user-guide p').css("width")),
    maxWidth = (numberOfSteps - 1) * width,
    stepID = 0;

  $("ul li[data-num='1']").addClass('active');
  $('.step span').html('Step 1');

  $('body').on('click','.user-guide__next', function(){
    var
      marginLeft = parseInt($('.slider-turn').css('margin-left')),
      mod = marginLeft % width;

    if (-marginLeft < maxWidth && mod === 0) {
      marginLeft -= width;

      $('.slider-turn').animate({
        'margin-left': marginLeft
      },300);
      $('ul li.active').addClass('true').removeClass('active');

      $('ul li[data-num="'+ ++stepID +'"]').addClass('active');
      $('.step span').html( 'Step ' + stepID );
    }
  });

  $('body').on('click','.user-guide__close',function(){
    $('.user-guide').animate({
      'opacity':0
    },600);
    $('.user-guide').animate({
      'top':-1200
    }, {
      duration: 2300,
      queue: false
    });
    $('.user-guide__open').animate({
      'top':'50%'
    });
  });

  $('body').on('click','.user-guide__open',function() {
    numberOfSteps = $('.user-guide p').length,
    width = parseInt($('.user-guide p').css("width")),
    maxWidth = (numberOfSteps - 1) * width;
    stepID = 0;

    $('ul li.active').removeClass('true').removeClass('active');
    $("ul li[data-num='1']").addClass('active');
    $('.step span').html('Step 1');

    $('.user-guide__open').animate({
      'top':-1000
    });

    $('.user-guide').animate({
      'opacity': 1
    },400);

    $('.user-guide').animate({
      'top':'50%'
    }, {
      duration: 800,
      queue: false
    });

  });
});
