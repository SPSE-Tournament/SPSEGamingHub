
    for (let i of document.querySelectorAll(".nav-links-selected")) {
      i.classList.remove("nav-links-selected");
    }

    function loadUserLiveText(str, livesearchelem, hintelem) {
      if (str.length < 3) {
        document.querySelector("."+hintelem).innerHTML ="";
        document.querySelector('.'+livesearchelem).style.display = "none";
        return;
      }
      fetch(`/api/users/livesearch/${str}`)
      .then(res=>res.json())
      .then(hint => {
        console.log(hint);
        document.querySelector("."+hintelem).innerHTML ="";
        for (let i of hint) {
          document.querySelector("."+hintelem).innerHTML += `<p class="hint rounded p-2" onclick="selectUser('newMessage_username', '${i.name+"#"+i.hexid}')">${i.name}</p>`;
        }
        document.querySelector('.'+livesearchelem).style.display = "block";
      })
      .catch(err => console.error(err));
    }

    function loadTeamLiveText(str, livesearchelem, hintelem) {
      if (str.length < 3) {
        document.querySelector("."+hintelem).innerHTML ="";
        document.querySelector('.'+livesearchelem).style.display = "none";
        return;
      }
      fetch(`/api/teams/livesearch/${str}`)
      .then(res=>res.json())
      .then(hint => {
        console.log(hint);
        document.querySelector("."+hintelem).innerHTML ="";
        for (let i of hint) {
          document.querySelector("."+hintelem).innerHTML += `<p class="hint rounded p-2" onclick="selectUser('newMessage_username', '${i.name}')">${i.name}</p>`;
        }
        document.querySelector('.'+livesearchelem).style.display = "block";
      })
      .catch(err => console.error(err));
    }

    function selectUser(elem,str) {
      document.getElementById(elem).value = str;
      document.querySelector('.livesearch').style.display = "none";
    }


    function toggleNav() {
      if (document.querySelector(".overlay").style.height == "87vh")
        document.querySelector(".overlay").style.height = "0";
      else
        document.querySelector(".overlay").style.height = "87vh";

    }

    $('.toast').toast('show');
