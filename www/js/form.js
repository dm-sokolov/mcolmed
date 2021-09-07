$(document).ready(function() {
  var is_complete = false;
  var sum = 0;
  var answer = new Array(-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1);
  var answerLength = answer.length;
  var score0_35 = "Вы являетесь счастливым обладателем здоровых ног. Сохранить красоту ног и защитить их от варикозного расширения Вам поможет правильное питание, регулярное посещение бассейна, легкие спортивные нагрузки, а так же умеренный режим труда и отдыха. Чтобы больше узнать о здоровье вен и в будущем предотвратить развитие варикоза запишитесь на прием к нашему специалисту.";
  var score36_75 = "Сейчас ваши ноги скорее всего здоровы, либо имеется варикозное расширение вен на ранней стадии. Обязательно посетите нашего специалиста, ведь лучшим оружием против этой коварной болезни является ее предупреждение и своевременное начало лечения.";
  var score76_150 = "Вам незамедлительно нужно обратиться к врачу флебологу! Последствия варикозного расширения вен могут быть очень серьезными, главное вовремя начать лечение болезни.";

  $('.questions input:radio').each(function(key, value) {
    $(this).change(function() {
      if((key % 3 === 0) || (key === 0)) {
        answer[Math.floor(key / 3)] = 0;
      }

      if(((key - 1) % 3 === 0) || ((key - 1) === 0)) {
        answer[Math.floor(key / 3)] = 5;
      }

      if(((key - 2) % 3 === 0) || ((key - 2) === 0)) {
        answer[Math.floor(key / 3)] = 10;
      }

      sum = 0;
      for (var i = 0; i < answerLength; i++) {
        if(answer[i] === -1) {
          is_complete = false;
          break;
        } else {
          sum += answer[i];
          is_complete = true;
        }
      }

      if(is_complete) {
        if (sum <= 35) {
          $('#answer').html(score0_35);
        } else if (sum <= 75 && sum >= 35) {
          $('#answer').html(score36_75);
        } else if (sum >= 76) {
          $('#answer').html(score76_150);
        }
      }
    });
  });
});
