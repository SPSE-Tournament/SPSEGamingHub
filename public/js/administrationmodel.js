$('.modal-log-detail').on('show.bs.modal', function (event) {
let button = $(event.relatedTarget)
let logId = button.data('logid')
let modal = $(this)
let xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4 && this.status == 200) {
  let start = this.responseText.indexOf("<!-- LogDetailStart -->");
  let end = this.responseText.indexOf("<!-- LogDetailEnd -->") + "<!-- LogDetailEnd -->".length;
  document.querySelector(".modal-log-detail-body").innerHTML = this.responseText.slice(start,end);
}
};
xhttp.open("GET", "administration/getlog/"+logId, true);
xhttp.send();
modal.find('.modal-title').text('Log')
});

function getGameForm(value) {
  let xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
    let start = this.responseText.indexOf("<!-- GameFormStart -->");
    let end = this.responseText.indexOf("<!-- GameFormEnd -->") + "<!-- GameFormEnd -->".length;
    document.querySelector(".live-form-game-update").innerHTML = this.responseText.slice(start,end);
  }
  };
  xhttp.open("GET", "administration/getgameform/"+value, true);
  xhttp.send();
}

function addUserInput() {
  document.querySelector('.user-input-group')
}
