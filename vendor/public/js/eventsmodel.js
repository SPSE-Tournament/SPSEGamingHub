$('.modal-event-join').on('show.bs.modal', function (event) {
let button = $(event.relatedTarget)
let eventId = button.data('eventid')
let gameId = button.data('gameid')
let eventUrl = button.data('eventurl')
let modal = $(this)
modal.find('.event-id-input').val(eventId)
modal.find('.game-id-input').val(gameId)
modal.find('.event-url-input').val(eventUrl)
});

document.querySelector("#eventLink").classList.add("nav-links-selected");
document.querySelector("#homeLink").classList.remove("nav-links-selected");
document.querySelector("#adminLink").classList.remove("nav-links-selected");
