$('.modal-message-detail').on('show.bs.modal', function (event) {
let button = $(event.relatedTarget)
let mesId = button.data('messageid')
let from = button.data('sender')
let modal = $(this)
let xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4 && this.status == 200) {
  let start = this.responseText.indexOf('<!-- MessageStart -->');
  let end = this.responseText.indexOf("<!-- MessageEnd -->") + "<!-- MessageEnd -->".length;
   document.querySelector(".modal-message-detail-body").innerHTML=this.responseText.slice(start,end);
}
};
xhttp.open("GET", "profile/getmessage/" + mesId, true);
xhttp.send();
modal.find('.modal-title').text('Message from ' + from)
});
