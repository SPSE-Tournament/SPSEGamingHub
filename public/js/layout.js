for (let i of document.querySelectorAll(".nav-links-selected")) {
  i.classList.remove("nav-links-selected");
}

function setMultAttributes(el, attrs) {
  for (let i in attrs) {
    el.setAttribute(i, attrs[i]);
  }
}

function loadUserLiveText(str, livesearchelem, hintelem, inputId) {
  if (str.length < 3) {
    document.querySelector(`.${hintelem}`).innerHTML = "";
    document.querySelector(`.${livesearchelem}`).style.display = "none";
    return;
  }
  fetch(`/api/users/livesearch/${str}`)
    .then((res) => res.json())
    .then((hint) => {
      console.log(hint);
      document.querySelector(`.${hintelem}`).innerHTML = "";
      for (let i of hint) {
        document.querySelector(
          `.${hintelem}`
        ).innerHTML += `<p class="hint-p mb-0 rounded p-2" onclick="selectUser('${inputId}', '${
          i.name + "#" + i.hexid
        }', '${livesearchelem}')">${i.name}</p>`;
      }
      document.querySelector(`.${livesearchelem}`).style.display = "flex";
    })
    .catch((err) => console.error(err));
}

function loadTeamLiveText(str, livesearchelem, hintelem) {
  if (str.length < 3) {
    document.querySelector("." + hintelem).innerHTML = "";
    document.querySelector("." + livesearchelem).style.display = "none";
    return;
  }
  fetch(`/api/teams/livesearch/${str}`)
    .then((res) => res.json())
    .then((hint) => {
      console.log(hint);
      document.querySelector("." + hintelem).innerHTML = "";
      for (let i of hint) {
        document.querySelector(
          "." + hintelem
        ).innerHTML += `<p class="hint-p mb-0 rounded p-2" onclick="selectUser('hintable-team', '${i.name}', '${livesearchelem}')">${i.name}</p>`;
      }
      document.querySelector("." + livesearchelem).style.display = "flex";
    })
    .catch((err) => console.error(err));
}

function selectUser(elem, str, livesearchelem) {
  document.querySelector(`#${elem}`).value = str;
  document.querySelector(`.${livesearchelem}`).style.display = "none";
}

function toggleNav() {
  const hambImg = document.querySelector(".hamburger img");
  if (document.querySelector(".overlay").style.height == "87vh") {
    document.querySelector(".overlay").style.height = "0";
    hambImg.setAttribute("src", "/public/images/hamburger.svg");
  } else {
    document.querySelector(".overlay").style.height = "87vh";
    hambImg.setAttribute("src", "/public/images/hamburger-opened.svg");
  }
}

$(".toast").toast("show");
