    var liensMenu = document.querySelectorAll('#cssmenu > ul > div > li > a');
    var listeMenu = document.querySelectorAll('#cssmenu > ul > div > li');
    var sousMenu = document.querySelectorAll("#cssmenu > ul > div > li > ul");
    var ouvert = false;
    var menu = document.querySelector("nav ul");
    var tailleFenetre = null;

    function parcourirMenu(display){
        for(var i = 0; i < liensMenu.length; i++){
            if(liensMenu[i].className !== "titre-menu"){
                liensMenu[i].parentNode.style.display = display;
            }

            if(liensMenu[i].className !== "titre-menu"){
                liensMenu[i].parentNode.style.display = display;
            }
        }

        for(var i = 0; i < sousMenu.length; i++){
            sousMenu[i].previousElementSibling.style.pointerEvents = "none";
        }
    }


    function init(){
        parcourirMenu("none");
        menu.parentNode.id = "cssmenuPetit";

        var poigne = document.getElementById("poigne");

        if(poigne === null)
        {
            var poigne = document.createElement("span");
            poigne.id = "poigne";
            poigne.style.color = "white";
            poigne.innerHTML = " +";
            liensMenu[0].parentNode.appendChild(poigne);

            poigne.addEventListener('click', function() {
                if(ouvert === false){
                    parcourirMenu("block");
                    ouvert = true;
                    poigne.innerHTML = " -";
                }
                else{
                    parcourirMenu("none");
                    ouvert = false;
                    poigne.innerHTML = " +";
                }

            });
        }

        for(var i = 0; i < sousMenu.length; i++){
            sousMenu[i].previousElementSibling.style.pointerEvents = "none";
            sousMenu[i].style.display = "none";
        }

        for(var j = 0; j < listeMenu.length; j++){
            if(listeMenu[j].lastElementChild.tagName === "UL"){

                listeMenu[j].addEventListener('click', function(e) {
                    if(e.target.lastElementChild.style.display === "none")
                    {
                        e.target.lastElementChild.style.display = "block";
                    }

                    else if(e.target.lastElementChild.style.display === "block")
                    {
                        e.target.lastElementChild.style.display = "none";
                    }

                });
            }
        }
    }

    function unInit(ifChoix){
        if(ifChoix === undefined)
        {
            ifChoix = true;
        }

        parcourirMenu("");
        menu.parentNode.id = "cssmenu";

        var poigne = document.querySelector("li > #poigne");
        if(poigne !== null){
            liensMenu[0].parentNode.removeChild(poigne);
        }

        for(var i = 0; i < sousMenu.length; i++){
            sousMenu[i].previousElementSibling.style = "";
            sousMenu[i].style.display = "inline-block";
        }

        if(ifChoix === true)
        {
            choix(false);
        }
    }

    function choix(typeUnInit){
        if(typeUnInit === undefined)
        {
            typeUnInit = true;
        }

        var tailleMenu = menu.offsetHeight;

        if (tailleMenu > 90 || window.innerWidth < 900) {
            if(ouvert === false){
                init();
                tailleFenetre = window.innerWidth;
            }
        }

        else {
            if(window.innerWidth > tailleFenetre){
                if(typeUnInit === true){
                    unInit();
                }
                else{
                    unInit(false);
                }
            }
        }
    }

    choix();

    window.onresize = function() {
        choix();
    }