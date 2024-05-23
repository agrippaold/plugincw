function shuffle(array) {
  var currentIndex = array.length,
    randomIndex;

  // While there remain elements to shuffle...
  while (0 !== currentIndex) {
    // Pick a remaining element...
    randomIndex = Math.floor(Math.random() * currentIndex);
    currentIndex--;

    // And swap it with the current element.
    [array[currentIndex], array[randomIndex]] = [
      array[randomIndex],
      array[currentIndex],
    ];
  }

  return array;
}

function spin(message) {
  // Play the sound
  // wheel.play();

  const box         = document.getElementById("box");
  const element     = document.getElementById("mainbox");

  const spinCount = 3;
  const prizePosition = 4;
  const offset = (360 / 12) * prizePosition;
  const spinValue = 360 * spinCount - offset;

  var spin = shuffle([
    3000,
    -3000,
    4000,
    -4000,
    5000,
    -5000,
    6000,
    -6000
  ]);
  // Process the spinning
  box.style.setProperty("transition", "all ease 5s");
  box.style.transform = "rotate(" + spin[0] + "deg)";
  element.classList.remove("animate");
  setTimeout(function () {
    element.classList.add("animate");
  }, 5000);

  setTimeout(function () {
    // applause.play();
    var html = '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; text-align:center; font-size: 100px; font-weight: 700; line-height: 110px; text-transform: uppercase; color: #fff; text-shadow: 0 0 9.4px #61CE70; z-index: 9999;">'+message+'</div>';

    jQuery("#mainbox").append(html);
  }, 5500);

  // Delay and set to normal state
  setTimeout(function () {
    box.style.setProperty("transition", "initial");
    // box.style.transform = "rotate(90deg)";
  }, 6000);
}

jQuery(document).on("ready", function () {

  jQuery(document).on("click", "#show_wheel", function (e) {
    jQuery("#wheelModal").iziModal("open");
  });

  jQuery(document).on("click", ".spin", function() {
    jQuery.ajax({
      url: cws_games_wheel_ajax_object.ajax_url,
      type: "POST",
      dataType: "json",
      data: {
            action: 'spinWheelOfFortune'
          },
      beforeSend: function() {
        
      },
      success: function(response) {

        if (response.status > 0) {
          spin(response.status_txt);
        } else {
          alert(response.status_txt);
        }
        
      },
        error: function(error) {
        console.log('Something went wrong `UpdateWallet`');
      }
    }).done(function() {

    });
  });

});