let countDownDate = new Date(document.querySelector('.event-timestamp').value).getTime();
setInterval(function() {
  let now = new Date().getTime();
  let distance = countDownDate - now;
  let days = Math.floor(distance / (1000 * 60 * 60 * 24));
  let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  let seconds = Math.floor((distance % (1000 * 60)) / 1000);
  let response = "<span class='bg-dark p-2'>" + days + " d : " + hours + " h : " + minutes + " m : " + seconds + " s</span>";
  document.querySelector(".timer-countdown").innerHTML = response;
  if (distance < 0) {
    clearInterval();
    document.querySelector(".timer-countdown").innerHTML = "Live";
  }
}, 1000);
