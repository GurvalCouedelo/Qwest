//var suffixeAjax = "http:/" + "/127.0.0.1/Qwest/Exercices/index.php/";
var suffixeAjax = "https:/" + "/exercices.titann.fr/index.php/";

function init(id){
    window.id = id;
    window.intro = false;
    
//    Initialisation du bouton d'apparition de la fenêtre
    
    var body = document.querySelector('body'),
        boutonCreation = document.querySelector('#bouton-fenetre'),
        boutonSubmit = document.querySelector('#bouton-question-submit'),
        boutonSuppression = document.querySelector('#bouton-question-suppression'),
        boutonDisponibilite = document.querySelector('#bouton-disponibilite'),
        formulaireTypeQuestion = document.querySelector('#formulaire-type-question'),
        popUpCreation = document.querySelector('#pop-up-creation'),
        popUpSuppression = document.querySelector('#pop-up-suppression');
    
    boutonCreation.addEventListener("mouseup", function(){
        popUpCreation.style.display = "block";
    }, true);
    
//    Création des fonctionnalités de base des pop-ups
    
    body.addEventListener("mouseup", function(e){
        if(popUpCreation.style.display == "block" || popUpSuppression.style.display){
            if(e.target.getAttribute("id") != "pop-up-creation" || e.target.getAttribute("id") != "pop-up-suppression"){
                var elementBoucle = e.target,
                    trouve = false;
                
                while(elementBoucle.tagName !== "HTML"){
                    if(elementBoucle.getAttribute("id") == "pop-up-creation" || elementBoucle.getAttribute("id") == "pop-up-suppression"){
                        trouve = true;
                        break;
                    }
                    
                    elementBoucle = elementBoucle.parentNode;
                }
                
                if(trouve == false){
                    popUpCreation.style.display = "none";
                    popUpSuppression.style.display = "none";
                    
                    window.idSuppression = null;
                }
            }
        }
        
    }, true);
    
//    Initialisation du bouton de soumission
    
    boutonSubmit.addEventListener("click", function(e){
        e.preventDefault();
        
        var xhr = new XMLHttpRequest();
        var formData = new FormData(formulaireTypeQuestion);
        formData.append(formulaireTypeQuestion.firstElementChild, formulaireTypeQuestion.firstElementChild.value);
        
        xhr.open('POST', suffixeAjax + "enregistrer/creerNouvelleQuestion/" + window.id);
        xhr.send(formData);
        
        xhr.addEventListener("readystatechange", function(){
            if(xhr.readyState === 4){
                popUpCreation.style.display = "none";
                chargerMenu();
            }
        });
    });
    
    //    Initialisation du bouton de suppression
    
    boutonSuppression.addEventListener("click", function(e){
        e.preventDefault();
        
        var xhr = new XMLHttpRequest();
        xhr.open('GET', suffixeAjax + "supprimer/question/" + boutonSuppression.getAttribute("id-question"));
        xhr.send();
        
        xhr.addEventListener("readystatechange", function(){
            if(xhr.readyState === 4){
                popUpSuppression.style.display = "none";
                chargerMenu();
            }
        });
    });
    
    boutonDisponibilite.addEventListener("click", function(e){
        var xhr = new XMLHttpRequest();
        xhr.open('GET', suffixeAjax + "obtenir/changerDisponibilite/" + id);
        xhr.send();
        
        xhr.addEventListener("readystatechange", function(){
            if(xhr.readyState === 4){
                boutonDisponibilite.innerHTML = xhr.responseText;
            }
        });
    });
}

function chargerMenu(){
    var menu = document.querySelector('#menu-questions ul');
    
//    Vidage du menu
    
    menu.innerHTML = "";
    
//    Envoie de la requète
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', suffixeAjax + 'obtenir/questions/' + window.id);
    xhr.send();
    
//    Réception des questions
    
    xhr.addEventListener("readystatechange", function(){
        if(xhr.readyState === 4){
            var intro = document.createElement('li');
            intro.innerHTML = "Intro";
                
            intro.addEventListener("mouseup", function(e){
                var element = e.target;
                    
                while(element.localName != "li"){
                    element = element.parentNode;
                }
                    
                if(element.id != window.idSuppression){
                    chargerIntro();
                    if(window.pageActuelle != null){
                        window.pageActuelle.className = "";
                    }

                    e.target.className = "pageActuelle";
                    window.pageActuelle = e.target;
                }
            }, false);
                
            menu.appendChild(intro);
            
            var reponse = JSON.parse(xhr.responseText),
                listeQuestions = reponse["listeQuestions"],
                tableauQuestions = new Array(),
                tableauBoutonsSuppression = new Array(),
                popUpSuppression = document.querySelector('#pop-up-suppression'),
                boutonQuestionSuppression = document.querySelector('#bouton-question-suppression');
            
            for (var i in listeQuestions) {
                tableauQuestions[i] = document.createElement('li');
                tableauBoutonsSuppression[i] = document.createElement("span");
                tableauBoutonsSuppression[i].innerHTML = "✘";
                tableauBoutonsSuppression[i].className = "bouton-suppression-question";
                tableauQuestions[i].innerHTML = (parseInt(i) + 1) + " | " + listeQuestions[i].nom_type + " ";
                tableauQuestions[i].appendChild(tableauBoutonsSuppression[i]);
                tableauQuestions[i].id = listeQuestions[i].id;
                
                tableauQuestions[i].addEventListener("mouseup", function(e){
                    var element = e.target;
                    
                    while(element.localName != "li"){
                        element = element.parentNode;
                    }
                    
                    if(element.id != window.idSuppression){
                       chargerPage(e.target.id);
                        if(window.pageActuelle != null){
                            window.pageActuelle.className = "";
                        }

                        e.target.className = "pageActuelle";
                        window.pageActuelle = e.target;
                    }
                    
                    window.intro = false;
                }, false);
                
                tableauBoutonsSuppression[i].addEventListener("mouseup", function(e){
                    window.idSuppression = e.target.parentNode.getAttribute("id");
                    boutonQuestionSuppression.setAttribute("id-question", window.idSuppression);
                    popUpSuppression.style.display = "block";
                    
                    
                    
                }, false);
                
                menu.appendChild(tableauQuestions[i]);
            }
        }
    })
}


function chargerPage(id, scroll = false, scrollEnregistre = null){
    var conFor = document.querySelector('#conteneur-formulaire');
    
    //    Vidage de la page
    
    conFor.innerHTML = "";
    
    // Récupération du code de la page
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', suffixeAjax + 'formulaires/toutEnUn/' + id);
    xhr.send();
    
    xhr.addEventListener("readystatechange", function(){
        if(xhr.readyState === 4){
            conFor.innerHTML = xhr.responseText;
            
            $('#my-editor').trumbowyg();
            $('.mes-editeurs').trumbowyg();
            
            initialiserFormulaire();
            
//            Gestion du défilement
            
            if(scroll == true){
                var derniereProposition = document.querySelector('#conteneur-formulaire form:last-of-type');
                conFor.scrollTo(derniereProposition.offsetLeft, derniereProposition.offsetTop);
            }
            
            if(scrollEnregistre != null){
                var proposition = document.querySelector('form[id_entite=\"' + scrollEnregistre.getAttribute("id_entite") + '\"]'),
                    position = proposition.getBoundingClientRect();
                
                conFor.scrollTo(position.x, position.y);
            }
            
            
//            Mise en place des foctionnalités des boutons de page hors formulaires
            
            var boutonPropositionCreation = document.querySelector('#bouton-proposition-creation'),
                boutonPropositionsSuppression = document.querySelectorAll('.bouton-proposition-suppression'),
                boutonRessourcesSuppression = document.querySelectorAll('.bouton-ressources-suppression');
            
            boutonPropositionCreation.addEventListener("click", function(e){
                e.preventDefault();
                
                var xhr = new XMLHttpRequest();
                xhr.open('GET', suffixeAjax + "enregistrer/creerNouvelleProposition/" + e.target.getAttribute("question-id"));
                xhr.send();

                xhr.addEventListener("readystatechange", function(){
                    if(xhr.readyState === 4){
                        chargerPage(id, true);
                    }
                });
            });
            
            for(var i = 0; i < boutonPropositionsSuppression.length; i++){
                boutonPropositionsSuppression[i].addEventListener("click", function(e){
                    scrollEnregistre = e.target.parentNode.previousElementSibling;
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', suffixeAjax + "supprimer/" + e.target.getAttribute("type-entite") + "/" + e.target.getAttribute("entite-id"));
                    xhr.send();

                    xhr.addEventListener("readystatechange", function(){
                        if(xhr.readyState === 4){
                            chargerPage(id, false, scrollEnregistre);
                        }
                    });
                });
            }
            
            for(var i = 0; i < boutonRessourcesSuppression.length; i++){
                boutonRessourcesSuppression[i].addEventListener("click", function(e){
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', suffixeAjax + "supprimer/ressource/" + e.target.getAttribute("question-id") + "/" + e.target.getAttribute("ressource-id"));
                    xhr.send();

                    xhr.addEventListener("readystatechange", function(){
                        if(xhr.readyState === 4){
                            chargerPage(id);
                        }
                    });
                });
            }
        }
    });
}


function chargerIntro(){
    var conFor = document.querySelector('#conteneur-formulaire');
    
    //    Vidage de la page
    
    conFor.innerHTML = "";
    
    console.log(suffixeAjax + 'formulaires/intro/' + id);
    // Récupération du code de la page
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', suffixeAjax + 'formulaires/intro/' + id);
    xhr.send();
    
    xhr.addEventListener("readystatechange", function(){
        if(xhr.readyState === 4){
            console.log(suffixeAjax + 'formulaires/intro/' + id);
            conFor.innerHTML = xhr.responseText;
            
            $('.mes-editeurs').trumbowyg();
            
            window.intro = true;
            initialiserFormulaire();
        }
    });
}



